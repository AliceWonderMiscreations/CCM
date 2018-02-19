AutoloadPromise 1.0.0 API
=========================

The file `AutoloadPromise.php` will always remain fully compatible with the
AutoloadPromise 1.0.0 API. Any changes that would break an application using
that API will require a different class name in a different promise file name.

Autoload classes that extend the `\CCM\Promises\AutoloadPromise` API are to
have the file name `ClassLoader.php` and use the class name `\CCM\ClassLoader`.

Autoload classes that extend a different API must use a different class name
and a different file name.

The `ClassLoader.php` file is expected to reside at
`/usr/share/ccm/ClassLoader.php` so that web applications wishing to use the
AutoloadPromise 1.0.0 API can require that file and get what they expect.

The AutoloadPromise 1.0.0 API promises to have the following features:


CCM Install Base Directory
--------------------------

The PHP-CCM Base Directory will __ALWAYS__ be defined as `/usr/share/ccm`.


CCM Branch Directories
----------------------

Within the PHP-CCM Base Directories, PHP class libraries may be installed
within one of the following four branches:

1. `local/`
2. `stable/`
3. `devel/`
4. `custom/`

By default, without configuration, the AutoloadPromise 1.0.0 API will first
look for files that define the requested class within the `local/` branch and
if not found there, it will look within the `stable/` branch.


Custom Branch Search Order
--------------------------

The public method `changeDefaultSearchPath( string $string )` will exist to
allow an application to alter the branches searched and the order in which they
are searched.

The single `string` argument is a `:` delimited list of the branches to be
searched. For example:

    $obj->changeDefaultSearchPath('custom:stable');

would instruct the class to look for matches first in the `custom` branch, and
then in the `stable` branch, and it would *not* look for matches in either the
`local` or `devel` branches.
