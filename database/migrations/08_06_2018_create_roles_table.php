<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Soumen\Role\Helpers\Migration as Helper;
use Illuminate\Database\Migrations\Migration;

class RolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = config('role.table_name');
        $associatedTableName = config('role.associated_model_table_name');
        $associatedModelId = str_singular($associatedTableName).'_id';
        $pivotTableName = Helper::generatePivotTableName(config('role.pivot_table'));

        Schema::create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        Schema::create($pivotTableName, function (Blueprint $table) 
            use ($associatedModelId, $tableName, $associatedTableName) {
            
            $table->integer($associatedModelId)->unsigned();
            $table->integer('role_id')->unsigned();

            $table->foreign('role_id')
                    ->references('id')
                    ->on($tableName)
                    ->onDelete('cascade');

            $table->foreign($associatedModelId)
                    ->references('id')
                    ->on($associatedTableName)
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('role.table_name'));
        Schema::dropIfExists(Helper::generatePivotTableName(config('role.pivot_table')));
    }
}