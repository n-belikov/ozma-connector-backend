<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOzmaOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ozma_orders', function (Blueprint $table) {
            $table->id();
            $table->char("connector_id", 32);
            $table->char("connector_type", 16);
            $table->unsignedBigInteger("ozma_id")->unique();
            $table->unsignedInteger("ozma_stage_id");

            $table->timestamps();

            $table->unique(["connector_id", "connector_type"]);

            $table->foreign("connector_type")->references("alias")->on("connector_types")->onDelete("restrict");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ozma_orders');
    }
}
