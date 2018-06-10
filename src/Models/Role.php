<?php

namespace Soumen\Role\Models;

use Illuminate\Database\Eloquent\Model;
use Soumen\Role\Exceptions\RoleNotFound;
use Soumen\Role\Exceptions\RoleAlreadyExists;

class Role extends Model
{
	public $timestamps = false;
	protected $fillable = ['name'];

    public function __construct($attributes = [])
    {
    	parent::__construct($attributes);

    	$this->setTable(config('role.table_name'));
    }

    /**
     * User Role relationship.
     *
     **/
    public function users()
    {
        return $this->belongsToMany(config('role.associated_model'), 
                $this->generatePivotTableName(config('role.pivot_table')));
    }

    /**
     * Creates one or multiple Roles.
     *
     * @param Array | String $names
     * @return Collection(Soumen\Models\Role)
     * @throws Soumen\Exceptions\RoleAlreadyExists
     * @author Soumen Dey
     **/
    public static function create(...$names)
    {
    	$exceptions = [];
        $names = array_flatten($names);

        $roles = static::all();

        foreach($names as $name) {
        	if ($roles->contains('name', $name)) {
        		// Role already exists
        		// Add the role name to the exceptions array
        		$exceptions[] = $name;
        	}
        }

        // Check if the exceptions array is empty
        if (!empty($exceptions)) {
        	// Throw the exception
        	throw RoleAlreadyExists::create($exceptions);
        }

        // Create the roles
        $roles = collect($names)->map(function($name) {
        	return static::query()->create(['name' => $name]);
        });

        return ($roles->count() > 1) ? $roles : $roles->first();
    }

    /**
     * Find a role by its name.
     *
     * @param String $name
     * @param Boolean $ignoreException = false
     * @return Soumen\Models\Role
     * @throws Soumen\Exceptions\RoleNotFound
     * @author Soumen Dey
     **/
    public static function findByName($name, $ignoreException = false)
    {
        $role = static::where('name', $name)->first();

        if (!$role && !$ignoreException) {
        	throw RoleNotFound::name($name);
        }

        return $role;
    }

    /**
     * Find a role by its id.
     *
     * @param Integer $id
     * @param Boolean $ignoreException = false
     * @return Soumen\Models\Role
     * @throws Soumen\Exceptions\RoleNotFound
     * @author Soumen Dey
     **/
    public static function findById($id, $ignoreException = false)
    {
        $role = static::where('id', $id)->first();

        if (!$role && !$ignoreException) {
        	throw RoleNotFound::id($id);
        }

        return $role;
    }

    /**
     * Find a role by its name or id.
     *
     * @param String | Integer $param
     * @param Boolean $ignoreException = true
     * @return Soumen\Models\Role
     * @throws Soumen\Exceptions\RoleNotFound
     * @author Soumen Dey
     **/
    public static function find($param, $ignoreException = true)
    {
    	if (is_int($param)) {
    		// $param is an integer
    		return self::findById($param, $ignoreException);
    	}

    	if (is_string($param)) {
    		// $param is a string
    		return self::findByName($param, $ignoreException);
    	}
    }

   	/**
   	 * Find a role with the specified name or create one.
   	 *
   	 * @param String $name
   	 * @return Soumen\Models\Role
   	 * @author Soumen Dey
   	 **/
   	public static function findOrCreate($name)
   	{
   	    $role = static::where('name', $name)->first();

   	    if (!$role) {
   	    	return static::create(['name' => $name])->first();
   	    }

   	    return $role;
   	}

   	/**
   	 * Determine if a role exists.
   	 *
   	 * @param String | Integer $param
   	 * @return Soumen\Models\Role
   	 * @author Soumen Dey
   	 **/
   	public static function exists($param)
   	{
   		if (is_int($param)) {
    		// $param is an integer
    		$role = static::where('id', $param)->first();
    	}

    	if (is_string($param)) {
    		// $param is a string
    		$role = static::where('name', $param)->first();
    	}

        return ($role) ? $role : false;
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
