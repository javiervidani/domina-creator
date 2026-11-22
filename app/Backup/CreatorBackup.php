<?php

namespace App\Backup;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

// One creator's schema as a pg_dump file, encrypted with a key that belongs to that creator (derived from APP_KEY), on the
// private disk. The app login runs it with --role=creator_<id>, so no admin login is used. If APP_KEY is lost the files cannot be read.
class CreatorBackup
{
    private const MAGIC = 'DCB1'; // file layout: magic, 12-byte IV, 16-byte tag, ciphertext

    public static function disk()
    {
        return Storage::disk('local');
    }

    /** @return string the stored file name, for example backups/7/2026-11-22_030000.dump.enc */
    public function backup(int $creatorId): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'dcb');
        $result = $this->run([$this->bin('pg_dump'), ...$this->connection(), '--role=creator_'.$creatorId, '-n', 'creator_'.$creatorId, '-Fc', '-f', $tmp]);
        if ($result->failed() || filesize($tmp) === 0) {
            @unlink($tmp);
            throw new RuntimeException('pg_dump failed: '.mb_substr($this->clean($result->errorOutput()), 0, 300));
        }

        $name = "backups/{$creatorId}/".now()->format('Y-m-d_His').'.dump.enc';
        self::disk()->put($name, $this->encrypt($creatorId, (string) file_get_contents($tmp)));
        @unlink($tmp);

        return $name;
    }

    /** Keeps the newest $keep files of a creator, removes the rest. */
    public function prune(int $creatorId, int $keep): int
    {
        $old = collect(self::disk()->files("backups/{$creatorId}"))->sort()->values()->slice(0, -max(1, $keep))->all();
        self::disk()->delete($old);

        return count($old);
    }

    // The schema must not hold tables yet (drop it first, deliberately); an empty one is made by provision_creator().
    public function restore(int $creatorId, string $file): void
    {
        $existing = DB::connection('pgsql_admin')->selectOne('select count(*) as n from pg_tables where schemaname = ?', ['creator_'.$creatorId])->n;
        if ($existing > 0) {
            throw new RuntimeException("Schema creator_{$creatorId} already has {$existing} tables. Drop it first, then run this again.");
        }
        $plain = $this->decrypt($creatorId, (string) file_get_contents($file));

        DB::select('select public.provision_creator(?)', [$creatorId]);
        $dump = tempnam(sys_get_temp_dir(), 'dcr');
        $list = tempnam(sys_get_temp_dir(), 'dcl');
        file_put_contents($dump, $plain);
        // The schema itself already exists, so its line is left out of the restore list.
        $toc = $this->run([$this->bin('pg_restore'), '-l', $dump])->output();
        file_put_contents($list, implode("\n", array_filter(explode("\n", $toc), fn ($l) => ! str_contains($l, ' SCHEMA '))));

        $result = $this->run([$this->bin('pg_restore'), ...$this->connection(), '--role=creator_'.$creatorId, '--no-owner', '--no-privileges', '-L', $list, $dump]);
        @unlink($dump);
        @unlink($list);
        if ($result->failed()) {
            throw new RuntimeException('pg_restore failed: '.mb_substr($this->clean($result->errorOutput()), 0, 300));
        }
    }

    private function run(array $command)
    {
        return Process::timeout(900)->env(['PGPASSWORD' => (string) config('database.connections.pgsql.password')])->run($command);
    }

    private function bin(string $name): string
    {
        return (string) config('services.backup.'.$name, $name);
    }

    private function connection(): array
    {
        $c = config('database.connections.pgsql');

        return ['-h', (string) $c['host'], '-p', (string) $c['port'], '-U', (string) $c['username'], '-d', (string) $c['database']];
    }

    private function clean(string $text): string
    {
        return str_replace((string) config('database.connections.pgsql.password'), '[password]', $text);
    }

    private function key(int $creatorId): string
    {
        return hash_hmac('sha256', 'creator-backup-'.$creatorId, base64_decode(substr((string) config('app.key'), 7)), true);
    }

    private function encrypt(int $creatorId, string $plain): string
    {
        $iv = random_bytes(12);
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', $this->key($creatorId), OPENSSL_RAW_DATA, $iv, $tag);

        return self::MAGIC.$iv.$tag.$cipher;
    }

    private function decrypt(int $creatorId, string $blob): string
    {
        if (! str_starts_with($blob, self::MAGIC) || strlen($blob) < 32) {
            throw new RuntimeException('This is not one of our backup files.');
        }
        $plain = openssl_decrypt(substr($blob, 32), 'aes-256-gcm', $this->key($creatorId), OPENSSL_RAW_DATA, substr($blob, 4, 12), substr($blob, 16, 16));
        if ($plain === false) {
            throw new RuntimeException('Could not decrypt: wrong creator id or a different APP_KEY.');
        }

        return $plain;
    }
}
