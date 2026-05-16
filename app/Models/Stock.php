<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['outlet_id', 'ingredient_id', 'quantity'])]
class Stock extends Model
{
    use HasUuids;
}
