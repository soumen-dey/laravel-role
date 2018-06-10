<?php

return [

	/*
    |--------------------------------------------------------------------------
    | Roles Table Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of the table which will store all the roles for the
    | Role model.
    |
    */

    'table_name' => 'roles',

    /*
    |--------------------------------------------------------------------------
    | Pivot Table Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of the pivot table which will store the relationship
    | between the roles and the associated models.
    |
    */

    'pivot_name' => 'role_user',

    /*
    |--------------------------------------------------------------------------
    | Associated Model Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of the associated model.
    |
    */

    'associated_model' => App\User::class,

    /*
    |--------------------------------------------------------------------------
    | Associated Model Table Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of the table which stores the associated model.
    |
    */
    
    'associated_model_table_name' => 'users',
];