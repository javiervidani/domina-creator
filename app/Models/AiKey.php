<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiKey extends Model
{
    protected $guarded = [];

    // Never serialised: a key must not leave the server in a page, a log or an export.
    protected $hidden = ['api_key'];

    protected function casts(): array
    {
        return ['api_key' => 'encrypted', 'enabled' => 'boolean', 'is_default' => 'boolean', 'last_tested_at' => 'datetime', 'last_test_ok' => 'boolean'];
    }

    /** The key used for AI suggestions: the default one if it is on, otherwise the oldest enabled key. */
    public static function usable(): ?self
    {
        return static::where('enabled', true)->orderByDesc('is_default')->orderBy('id')->first();
    }

    // The only form of the key the creator ever sees.
    public function masked(): string
    {
        return self::mask($this->api_key);
    }

    public static function mask(string $key): string
    {
        return strlen($key) <= 8 ? '••••' : '••••'.substr($key, -4);
    }
}
