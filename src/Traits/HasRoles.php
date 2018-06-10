<?php

namespace Soumen\Role\Traits;

use Soumen\Role\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

trait HasRoles
{
	/**
	 * User Role relationship.
	 *
	 **/
	public function roles()
	{
	    return $this->belongsToMany(Role::class, 
	    	$this->generatePivotTableName(config('role.pivot_table')));
	}

	/**
	 * Scope the model for the specified roles.
	 *
	 * @param Integer | String | Collection | Role | Array $role
	 * @return Collection($this)
	 * @author Soumen Dey
	 **/
	public function scopeRole(Builder $query, $role)
	{
		$identifier = 'name';

		if ($role instanceof Collection) {
			// $role is an instance of Collection
			$role = $role->map(function($r) {
				return $r->name;
			})->toArray();
		}

		if (is_int($role)) {
			// $role is an integer
			$identifier = 'id';
			$role = [$role];	
		}

		if (is_string($role)) {
			// $role is a string
			$role = [$role];
		}

		if ($role instanceof Role) {
			// $role is an instance of Role
			$role = [$role->name];
		}

	    return $query->whereHas('roles', function($query) use ($role, $identifier) {
	    	return $query->whereIn($identifier, $role);
	    });
	}

	/**
	 * Assign the specified role to the model.
	 *
	 * @param String | Integer | Array | Soumen\Role\Models\Role $roles
	 * @return $this
	 * @author Soumen Dey
	 **/
	public function assignRoles(...$roles)
	{
	    $roles = collect($roles)
	    		->flatten()
	    		->map(function($role) {
	    			return $this->getStoredRole($role);
	    		})->all();

	    $this->roles()->saveMany($roles);

	    return $this;
	}

	/**
	 * Alisas for assignRoles() method.
	 *
	 * @param Array | String | Integer | Soumen\Role\Models\Role $roles
	 * @return $this
	 * @author Soumen Dey
	 **/
	public function assignRole(...$roles)
	{
	    return $this->assignRole($roles);
	}

	/**
	 * Revokes the specified roles from the model.
	 *
	 * @param Integer | String | Soumen\Role\Models\Role | Array $roles
	 * @return $this
	 * @author Soumen Dey
	 **/
	public function revokeRoles(...$roles)
	{
		$roles = collect($roles)
	    		->flatten()
	    		->map(function($role) {
	    			return $this->getStoredRole($role)->id;
	    		})->all();

	    $this->roles()->detach($roles);

	    return $this;
	}

	/**
	 * Alias for revokeRoles() method.
	 *
	 * @param Integer | String | Soumen\Role\Models\Role | Array $roles
	 * @return $this
	 * @author Soumen Dey
	 **/
	public function removeRole(...$roles)
	{
	    return $this->revokeRoles($roles);
	}

	/**
	 * Removes all the roles and assign the specified roles.
	 *
	 * @param String | Integer | Array | Soumen\Role\Models\Role $roles
	 * @return $this
	 * @author Soumen Dey
	 **/
	public function syncRoles(...$roles)
	{
	    $this->roles()->detach();

	    $this->assignRoles($roles);

	    return $this;
	}

	/**
	 * Determines if the user has the specified roles.
	 *
	 * @param String | Integer | Soumen\Role\Models\Role | Array $role
	 * @return Boolean
	 * @author Soumen Dey
	 **/
	public function hasRole($role)
	{
		if (is_int($role)) {
			// $role is an integer
			return $this->roles->contains('id', $role);
		}

		if (is_string($role)) {
			// $role is a string
			return $this->roles->contains('name', $role);
		}

		if ($role instanceof Role) {
			// $role is an instance of Role
			return $this->roles->contains('id', $role->id);
		}

		if (is_array($role)) {
			$roles = $this->roles;

			foreach ($role as $value) {
			    if ($this->hasRole($value)) {
			    	return true;
			    }
			}
		}

		return false;
	}

	/**
	 * Determines if the user has any of the specified role(s).
	 *
	 * @param String | Integer | Soumen\Role\Models\Role | Array $roles
	 * @return Boolean
	 * @author Soumen Dey
	 **/
	public function hasAnyRole(...$roles)
	{
	    $roles = array_flatten($roles);

	    foreach ($roles as $role) {
	    	if ($this->hasRole($role)) {
	    		return true;
	    	}
	    }

	    return false;
	}

	/**
	 * Determines if the user has all the specified role(s).
	 *
	 * @param String | Integer | Soumen\Role\Models\Role $names
	 * @return Boolean
	 * @author Soumen Dey
	 **/
	public function hasAllRoles(...$roles)
	{
		$roles = array_flatten($roles);

		foreach ($roles as $role) {
		    if (!$this->hasRole($role)) {
		    	return false;
		    }
		}

		return true;
	}

	/**
	 * Returns the name of the roles associated with the user.
	 *
	 * @return String
	 * @author Soumen Dey
	 **/
	public function getRoleNames()
	{
	    return $this->roles->pluck('name');
	}

	/**
	 * Alias for $this->roles.
	 *
	 * @return Collection(Soumen\Role\Models\Role)
	 * @author Soumen Dey
	 **/
	public function getRoles()
	{
	    return $this->roles;
	}

	/**
	 * Get the Role instance from the specified identifier.
	 *
	 * @param Integer | String $role
	 * @return Soumen\Role\Models\Role
	 * @author Soumen Dey
	 **/
	public function getStoredRole($role)
	{
		if ($role instanceof Role) {
			return Role::findById($role->id);
		}

		if (is_int($role)) {
			return Role::findById($role);
		}

		if (is_string($role)) {
			return Role::findByName($role);
		}
	}

	/**
	 * Determines if the user belongs to the specified role.
	 * This methods is virtually same as the hasRole() method,
	 * except that this method is very simple and therefore
	 * is faster (the speed difference is very small, almost negligible) 
	 * than the hasRole() method.
	 *
	 * @param String $role
	 * @return Boolean
	 * @author Soumen Dey
	 **/
	public function is($role)
	{
	    return $this->roles->contains('name', $role);
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