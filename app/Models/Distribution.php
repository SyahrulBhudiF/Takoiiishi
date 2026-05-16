<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['distribution_date', 'from_outlet_id', 'to_outlet_id', 'created_by'])]
class Distribution extends Model
{
    use HasUuids;
}
