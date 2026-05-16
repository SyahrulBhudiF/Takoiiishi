<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'unit', 'minimum_stock', 'usage_per_portion'])]
class Ingredient extends Model
{
    use HasUuids;
}
