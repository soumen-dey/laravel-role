<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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
        $pivotTableName = $this->generatePivotTableName(config('role.pivot_table'));

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
        Schema::dropIfExists($this->generatePivotTableName(config('role.pivot_table')));
    }

    /**
     * Generates table name for the pivot table.
     *
     * @return String
     * @author Soumen Dey
     **/
    public function generatePivotTableName($pivotTableName)
    {
        if (!$pivotTableName) {
            $names[] = config('role.table_name');
            $names[] = config('role.associated_model_table_name');
            sort($names);
            $pivotTableName = str_singular($names[0]).'_'.str_singular($names[1]);    
        }

        return $pivotTableName;
    }
}