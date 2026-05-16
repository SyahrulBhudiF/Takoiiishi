<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['purchase_id', 'ingredient_id', 'quantity', 'price', 'subtotal'])]
class PurchaseItem extends Model
{
    use HasUuids;

    public $timestamps = false;
}
