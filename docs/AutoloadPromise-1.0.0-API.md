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


Auto File Discovery Suffix Search Order
---------------------------------------

When a class library file is installed with a branch the Autoloader class
object is configured to search, the path the file to load may be automatically
discovered by the AutoloadPromise 1.0.0 API.

Automatic discovery of files to load based upon class name takes place when a
PHP application adds the `loadClass( string $class )` method to the PHP
`spl_autoload_register`, e.g.

    spl_autoload_register(function ($class) {
        global $obj;
        $obj->loadClass($class);
    });

For autodiscovery of file names from the class name, the AutoloadPromise 1.0.0
API will only automatically find the class if the file name matches the class
name *with case sensitive matching* and has a suffix of `.php`, `.class.php`,
or `.inc.php` and will look in that order.

So for example, the class `\Foo\Bar\Fubar` would need to be in a file named
`Fubar.php`, `Fubar.class.php`, or `Fubar.inc.php` to be automatically
disvovered by that method.


Auto File Discovery Directory Structure
---------------------------------------

When the `loadClass` method is set up with `spl_autoload_register` the
automatic discovery of class file names will take place if the following path
rules are followed.

Let LIBDIR represent _branch_/libraries where _branch_ is a branch within the
configured search path.

If the library has two or more namespace levels, it must be installed within
LIBDIR such that the first subdirectory is a lower case representation of the
top level namespace, the second subdirectory is a lower case representation of
the second namespace, and all addition directories are case sensitive
representions of any additional namespaces.

For example, the class `\Sabre\Xml\Element\Uri` would need to be located at:

    LIBDIR/sabre/xml/Element/Uri.{php|class.php|inc.php}

to be automatically found and loaded when the class is called.

When a class only has one namespace level, it must be installed within LIBDIR
such that the first directory is a lower case representation of the namespace
and the second directory is a lower case representation of the class name.

For example, the class `\Patchwork\JSqueeze` would need to be located at:

    LIBDIR/patchwork/jsqueeze/JSqueeze.{php|class.php|inc.php}

to be automatically found and loaded when the class is called.







