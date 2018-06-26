<?php

namespace Soumen\Role\Helpers;

class Migration
{
    /**
     * Generates table name for the pivot table.
     *
     * @return String
     * @author Soumen Dey
     **/
    public static function generatePivotTableName($pivotTableName)
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
