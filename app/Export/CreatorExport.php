<?php

namespace App\Export;

use Illuminate\Support\Facades\DB;
use ZipArchive;

// A zip of plain CSV files, one per table of the creator's schema. Must run inside the tenancy (the search path is
// the creator's schema). Secrets are left out by name, and anything new that is added to the schema is exported by default.
class CreatorExport
{
    // Tokens, keys and feed links are never exported (docs/DATABASE.md).
    public const SKIP_TABLES = ['account_tokens', 'ai_keys', 'feed_tokens', 'migrations'];

    /** @return string path of a temporary zip file; the caller deletes it */
    public function build(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'export');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);

        $tables = collect(DB::select("select table_name from information_schema.tables where table_schema = current_schema() and table_type = 'BASE TABLE' order by table_name"))
            ->pluck('table_name')->diff(self::SKIP_TABLES);
        foreach ($tables as $table) {
            $zip->addFromString($table.'.csv', $this->csv($table));
        }
        $zip->addFromString('README.txt', $this->readme($tables->all()));
        $zip->close();

        return $path;
    }

    // A byte order mark so Excel reads Hebrew correctly; json columns and dates are plain text.
    private function csv(string $table): string
    {
        $out = fopen('php://temp', 'w+');
        fwrite($out, "\xEF\xBB\xBF");
        $header = false;
        foreach (DB::table($table)->orderByRaw('1')->cursor() as $row) {
            $row = (array) $row;
            if (! $header) {
                fputcsv($out, array_keys($row));
                $header = true;
            }
            fputcsv($out, array_map(fn ($v) => is_bool($v) ? (int) $v : $v, $row));
        }
        if (! $header) { // an empty table still gets its column names
            fputcsv($out, array_column(DB::select('select column_name from information_schema.columns where table_schema = current_schema() and table_name = ? order by ordinal_position', [$table]), 'column_name'));
        }
        rewind($out);

        return (string) stream_get_contents($out);
    }

    private function readme(array $tables): string
    {
        return "Your data from domina-creator, exported ".now()->toDateTimeString()." UTC.\n"
            ."One CSV file per table (UTF-8, comma separated, first row = column names). Open in Excel or Google Sheets.\n\n"
            ."Not included, on purpose: account tokens, AI keys and feed links (secrets).\n"
            ."Pictures: only their addresses are listed (media_url, thumbnail_url). Small kept copies are not in this zip; kept_path names the file.\n"
            ."Posts: contents.csv (texts), content_copies.csv (the copy on each network), content_transcripts.csv, content_versions.csv (history).\n\n"
            ."Files: ".implode(', ', array_map(fn ($t) => $t.'.csv', $tables)).".\n";
    }
}
