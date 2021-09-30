<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConnectorTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connector_types', function (Blueprint $table) {
            $table->char("alias", 16)->primary();
        });
        foreach (\App\Domain\Enum\Connectors\ConnectorType::all() as $type) {
            \Illuminate\Support\Facades\DB::table("connector_types")->updateOrInsert([
                "alias" => $type
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('connector_types');
    }
}
