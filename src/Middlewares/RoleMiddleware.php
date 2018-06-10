<?php

namespace Soumen\Role\Middlewares;

use Closure;
use Soumen\Role\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if (strtolower($roles[0]) === 'required') {
            unset($roles[0]);
            
            if (!$request->user()->hasAllRoles($roles)) {
                throw UnauthorizedException::rolesAll($roles);
            }

            return $next($request);
        }

        if (!$request->user()->hasAnyRole($roles)) {
            throw UnauthorizedException::role($roles);
        }

        return $next($request);
    }
}
