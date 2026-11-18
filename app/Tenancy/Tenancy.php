<?php

namespace App\Tenancy;

use Closure;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

// Enters and leaves a creator's schema. Only the id is ever put into SQL, and only as an integer.
class Tenancy
{
    private ?int $current = null;

    public function current(): ?int
    {
        return $this->current;
    }

    public function enter(int $creatorId): void
    {
        if ($creatorId < 1) {
            throw new InvalidArgumentException('Invalid creator id.');
        }

        $name = self::schemaName($creatorId);
        DB::statement('SET ROLE "'.$name.'"');
        DB::statement('SET search_path TO "'.$name.'"');
        $this->current = $creatorId;
    }

    public function leave(): void
    {
        DB::statement('RESET ROLE');
        DB::statement('SET search_path TO public');
        $this->current = null;
    }

    // Always resets, even when the callback throws, so a failed job cannot leak a schema into the next one.
    public function run(int $creatorId, Closure $callback): mixed
    {
        $this->enter($creatorId);

        try {
            return $callback();
        } finally {
            $this->leave();
        }
    }

    // For the few pages inside a creator's schema that must also touch a public table (the profile). The schema is
    // left for the callback and entered again afterwards, even if the callback throws.
    public function outside(Closure $callback): mixed
    {
        $creatorId = $this->current;
        if ($creatorId === null) {
            return $callback();
        }

        $this->leave();

        try {
            return $callback();
        } finally {
            $this->enter($creatorId);
        }
    }

    public static function schemaName(int $creatorId): string
    {
        return 'creator_'.$creatorId;
    }
}
