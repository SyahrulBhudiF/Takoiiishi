<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['distribution_id', 'ingredient_id', 'quantity'])]
class DistributionItem extends Model
{
    use HasUuids;

    public $timestamps = false;

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
