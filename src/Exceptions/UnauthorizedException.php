<?php

namespace Soumen\Role\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
	private $requiredRoles = [];

    public static function role(array $roles) 
    {
        $requiredRoles = $roles;

        $roles = array_map(function($role) { 
            return "`{$role}`"; 
        }, $roles);

    	if (count($roles) > 1) {
    		$message = 'User should have either '.implode(' or ', $roles).' role to proceed.';
    	} else {
    		$message = "User should have a {$roles[0]} role to proceed.";
    	}

    	return new static(403, $message, null, []);
    }

    public static function rolesAll(array $roles)
    {
        $requiredRoles = $roles;
        
		$roles = array_map(function($role) { 
			return "`{$role}`"; 
		}, $roles);

    	if (count($roles) > 1) {
    		$message = 'User should have both '.implode(' and ', $roles).' roles to proceed.';
    	} else {
    		$message = "User should have a {$roles[1]} role to proceed.";
    	}

    	return new static(403, $message, null, []);
    }

    public function getRequiredRoles() 
    {
    	return $this->requiredRoles;
    }
}
