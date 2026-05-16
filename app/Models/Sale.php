<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['sale_date', 'outlet_id', 'portion_qty', 'created_by'])]
class Sale extends Model
{
    use HasUuids;
}
