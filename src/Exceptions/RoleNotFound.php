<?php

namespace Soumen\Role\Exceptions;

use InvalidArgumentException;

class RoleNotFound extends InvalidArgumentException
{
    public static function name(string $rolename) {
        return new static("The role `{$rolename}` does not exists.");
    }

    public static function id(int $id) {
        return new static("There is no role with id `{$id}`.");
    }
}
