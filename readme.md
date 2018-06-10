# laravel-role

### A lightweight Access Control package for Laravel 5.6 and above.

This package allows you to manage roles for your users, its very lightweight and require no extra dependencies.

## Installation

Via Composer

``` bash
$ composer require soumen-dey/laravel-role
```

For Laravel 5.5 and above the service provider will automatically get registered. Still if it is not registered, just add the service provider in `config/app.php` file.

```php
'providers' => [
    // ...
    Soumen\Role\RoleServiceProvider::class,
];
```

#### Migrations

The migrations for this package will automatically run when you run the ```php artisan:migrate``` command.

**Note:** Make sure that you have your associated model table already migrated before using the ```php artisan migrate``` command for this package.

#### Configurations

You need to publish the config file with:

``` php
php artisan vendor:publish --provider="Soumen\Role\RoleServiceProvider" --tag="role.config"
```

This will publish ```role.php``` file under the ```config``` directory.

You can use this package without modifying the default configurations. The defaults are set to work with the Laravel's default model for auth which is the ```User``` model. However you can change the configurations based on your need.

The configurations are:

``` php
'table_name' => 'roles',
'pivot_name' => 'role_user',
'associated_model' => App\User::class,
'associated_model_table_name' => 'users',
```

**Note:** If you change the default ```User``` model, make sure to change the model's table name and the pivot table name.

``` php
'associated_model_table_name' => 'admins', // Make sure to change this value
'associated_model' => App\Admin::class,
```

**Tip:** If you assign a null value to the pivot table name, this package will automatically generate it for you.

``` php
'pivot_name' => null, // this package will automatically generate the table name
```

## Usage

* [Setup](#setup)
* [Creating Roles](#creating-roles)
* [Retrieving Roles](#retrieving-roles)
* [Assigning Roles](#assigning-roles-to-the-model)
* [Revoking Roles](#revoking-roles-from-the-model)
	* [Sync Roles](#sync-roles)
* [Role Associations](#role-associations-with-the-model)
	* [Determining Role Associations](#determining-role-associations)
* [Using the Middleware](#using-the-middleware)

### Setup

Add the ```Soumen\Role\Traits\HasRoles``` to your ```User``` model or any other model that you want to associate roles with. That's it! You are all set to go!

``` php
use Soumen\Role\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRoles;

    // ...
}
```

### Creating Roles

You can create new roles:

``` php
use Soumen\Role\Models\Role;

$role = Role::create(['moderator', 'editor', 'admin']);
$role = Role::create('moderator', 'editor', 'admin');
$role = Role::create('moderator');
```

You can pass an array of role names or several role names at once.

### Retrieving Roles

You can retrieve the roles by one of these methods:

``` php
use Soumen\Role\Models\Role;

$role = Role::find(1);
$role = Role::find('admin');
```

Retrieve a role by its ```id```

``` php
$role = Role::findById(1);
```

Retrieve a role by its ```name```

``` php
$role = Role::findByName('admin');
```

All these methods will throw a ```RoleNotFound``` exception if a role is not found, to change this behavior pass a second optional argument as ```true```, in such case the method will return ```null``` if a role is not found.

``` php
$role = Role::find(1, true); // will not throw an exception
$role = Role::findById(1, true); // will not throw an exception
$role = Role::findByName('admin', true); // will not throw an exception
```

A role can also be created if not found:

``` php
$role = Role::findOrCreate('editor'); // will return the Role instance
```

Check if a role exists:

``` php
$role = Role::exists('admin');
```

If a role exists, this method will return the ```Role``` instance else it will return ```false```.

### Assigning Roles to the model

Roles can be easily assigned by using one of these methods:

``` php
$user = User::find(1);

$user->assignRoles('admin', 'moderator');
```

You can also assign roles by their ```id``` or their model instances:

``` php
use Soumen\Role\Models\Role;

$role1 = Role::find('admin');
$role2 = Role::find('moderator');

$user->assignRoles(1, 2);
$user->assignRoles($role1, $role2);
```

You can also pass an array:

``` php
$user->assignRoles([1, 2]);
$user->assignRoles([$role1, $role2]);
$user->assignRoles(['admin', 'moderator']);
```
There is also an ```assignRole()``` method that does the same thing.

### Revoking roles from the model

Roles can be revoked or removed from the model by one of these methods:

By their ```id```

``` php
$user = User::find(1);

$user->revokeRoles(1, 2);
$user->revokeRoles([1, 2]);
```

By their ```name```:

``` php
$user->revokeRoles('admin', 'moderator');
$user->revokeRoles(['admin', 'moderator']);
```

By the ```Role``` instance:

``` php
use Soumen\Role\Models\Role;

$role1 = Role::find('admin');
$role2 = Role::find('moderator');

$user->revokeRoles($role1, $role2);
$user->revokeRoles([$role1, $role2]);
```

This package is very flexible, in an extreme scenario you can also do this and still it won't complain :) :

``` php
$role3 = Role::find('editor');

$user->revokeRoles(1, 'moderator', $role3);
$user->revokeRoles([1, 'moderator', $role3]);

```

There is also a ```removeRole()``` method that does the same thing.

#### Sync Roles

Roles can be removed at once by the above methods, but roles can be removed and assigned at the same time:

``` php
use Soumen\Role\Models\Role;

$admin = Role::find('admin');
$moderator = Role::find('moderator');

$user->syncRoles(1, 2);
$user->syncRoles($admin, $moderator);
$user->syncRoles('admin', 'moderator');
```

You can also pass an array of either role ```name```, ```id``` or ```Role``` instance.

### Role associations with the model

The ```HasRoles``` trait adds Eloquent Relationship to the associated model, so you can do this:

``` php
$user = User::find(1);

$user->roles; // returns a collection of associated Role instances
```

Names of the associated roles can be fetched:

``` php
$user->getRoleNames(); // returns an array of associated role names
```

The ```HasRoles``` trait also adds a role scope to your models to scope the query to certain roles:

``` php
$users = User::role('editor')->get(); // Returns only users with role 'editor'
```

It can be also used as:

``` php
use Soumen\Role\Models\Role;

$admin = Role::find('admin');
$roles = Role::whereIn('id', [1, 2])->get();

$users = User::role(1)->get(); // integer as the parameter
$users = User::role($admin)->get(); // Role instance as the parameter
$users = User::role($roles)->get(); // Collection of Role instances as the parameter
$users = User::role('admin')->get(); // string as the parameter
```

#### Determining Role associations

**Check if the model has **any** of the specified roles *(OR)*:**

Using the role ```id```:
``` php
$user = User::find(1);

$user->hasRole(1);
$user->hasRole([1, 2]);
```

Using the role ```name```:

``` php
$user->hasRole('admin');
$user->hasRole(['admin', 'moderator']);
```

Using the ```Role``` instance:

``` php
use Soumen\Role\Models\Role;

$admin = Role::find('admin');
$moderator = Role::find('moderator');

$user->hasRole($admin);
$user->hasRole([$admin, $moderator]);
```

You can also do this:

``` php
$user->hasRole([1, 'editor', $moderator]);
```

There is another method available:

``` php
$user->hasAnyRole(1, 2, 3); // returns true or false
$user->hasAnyRole(1, $moderator, 'editor'); // returns true or false
$user->hasAnyRole('admin', 'moderator', 'editor'); // returns true or false
```

The only difference between ```hasRole()``` and ```hasAnyRole()``` is that you can pass as many arguments as you like to the ```hasAnyRole()``` method.

> Note that both these methods returns a ```boolean```.

**Check if the model has **all** the specified roles *(AND)*:**

``` php
$user->hasAllRoles(1, 2, 3); // returns true or false
$user->hasAllRoles(1, $moderator, 'editor'); // returns true or false
$user->hasAllRoles('admin', 'moderator', 'editor'); // returns true or false
```

This method returns ```true``` only if **all** the specified roles are associated with the model, else it returns ```false```.

**The ```is()``` method**

This method is a quick way of determining if a model has a certain role. It is a very simple method and is faster than the above methods (the performance difference is very small, almost negligible).

``` php
$user->is('admin') // returns true or false
```

This method only accept one ```string``` argument which is the ```name``` of the role.

### Using the Middleware

This package comes with ```RoleMiddleware``` middleware. You can add it inside your ```app/Http/Kernel.php``` file.

``` php
protected $routeMiddleware = [
    // ...
    'role' => \Soumen\Role\Middlewares\RoleMiddleware::class,
];
```

You can protect your routes using the middleware:

``` php
Route::group(['middleware' => ['role:admin']], function () {
    //
});
```

You can also use the middleware in a single route:

``` php
Route::get('/', 'SomeController@method')->middleware('role:admin');
```

You can specify multiple roles in the middleware by separating them with a ```,``` comma:

``` php
Route::get('/', 'SomeController@method')->middleware('role:admin,editor');
```

**Note:** The above method will determine if the model has **any one** _(OR)_ of the specified roles.

To determine if a model has **all** _(AND)_ of the specified roles, use the ```required``` flag:

``` php
Route::get('/', 'SomeController@method')->middleware('role:required,admin,editor');
```
**Note:** The ```required``` flag should be right after the middleware name, which in this case is ```role```. Thus the string should look like:

``` php
'role:required,admin,moderator,editor'
```

### Using Blade Directive

By default, this package does not ship with any custom blade directive but you can add one easily. Assuming the default associated model is the ```User``` model, just follow the steps:

In your ```app/Providers/AppServiceProvider.php``` add the following inside the ```boot``` method:

``` php
use Illuminate\Support\Facades\Blade;

public function boot() 
{
    // ..
    
    Blade::if('role', function ($rolename) {
        return auth()->check() && auth()->user()->is($rolename);
    });	
}
```

You can now use the directive:

``` php
@role('admin')
    The user is an admin!
@else
    The user is not an admin!
@endrole
```

For a more role specific directive:

``` php
use Illuminate\Support\Facades\Blade;

public function boot() 
{
    // ..
    
    Blade::if('admin', function () {
        return auth()->check() && auth()->user()->is('admin');
    });	
}
```

You can now use the directive:

``` php
@admin
    The user is an admin!
@else
    The user is not an admin!
@endadmin
```

Or if you don't want any custom directive, you can do:

``` php
@if(auth()->user()->is('admin'))
    The user is an admin!
@else
    The user is not an admin!
@endif
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email me at <soumendeyemail@gmail.com>.

## Credits

- [Soumen Dey][link-author]
- [All Contributors][link-contributors]

## License

This package is released under the MIT License (MIT). Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/soumen-dey/laravel-role.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/soumen-dey/laravel-role.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/soumen-dey/laravel-role/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/soumen-dey/laravel-role
[link-downloads]: https://packagist.org/packages/soumen-dey/laravel-role
[link-travis]: https://travis-ci.org/soumen-dey/laravel-role
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/soumen-dey
[link-contributors]: ../../contributors
