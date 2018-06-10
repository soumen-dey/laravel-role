<?php

namespace Soumen\Role\Exceptions;

use InvalidArgumentException;

class RoleAlreadyExists extends InvalidArgumentException
{
    public static function create($rolenames) {
    	$string = implode(', ', $rolenames);

    	return new static("The roles `{$string}` already exists.");
    }
}
