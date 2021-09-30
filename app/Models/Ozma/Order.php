<?php

namespace App\Models\Ozma;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @mixin IdeHelperOrder
 */
class Order extends Model
{
    protected $table = "ozma_orders";

    protected $fillable = [
        "connector_id",
        "connector_type",
        "ozma_id",
        "ozma_stage_id",
    ];
}
