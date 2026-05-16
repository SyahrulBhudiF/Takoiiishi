<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'address', 'type'])]
class Outlet extends Model
{
    use HasUuids;

    public static function pusat(): ?self
    {
        return self::query()->where('type', 'pusat')->first();
    }
}
