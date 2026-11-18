<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Small per-creator switches (notification choices). Lives in the creator's schema.
class Setting extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $guarded = [];

    public static function flag(string $key, bool $default = false): bool
    {
        $row = static::find($key);

        return $row ? $row->value === '1' : $default;
    }

    public static function put(string $key, bool $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value ? '1' : '0']);
    }
}
