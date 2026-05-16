<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['outlet_id', 'ingredient_id', 'type', 'qty_in', 'qty_out', 'reference'])]
class StockMovement extends Model
{
    use HasUuids;

    const UPDATED_AT = null;
}
