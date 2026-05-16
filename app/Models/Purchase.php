<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['purchase_date', 'created_by', 'total'])]
class Purchase extends Model
{
    use HasUuids;
}
