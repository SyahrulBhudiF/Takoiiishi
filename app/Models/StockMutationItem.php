<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['stock_mutation_id', 'ingredient_id', 'quantity'])]
class StockMutationItem extends Model
{
    use HasUuids;

    public $timestamps = false;

    public function stockMutation(): BelongsTo
    {
        return $this->belongsTo(StockMutation::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
