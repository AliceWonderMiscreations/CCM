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


PEAR Search Directories
-----------------------

When the `phpinclude` path does not include a PEAR install directory or a
needed PEAR module can not be found within the the `phpinclude` path, the
autoloader for PEAR modules will look in `/usr/share/ccm/pear` before looking
in other directories.

The version 1.0.0 of the Promise also defines the following two additional
search paths:

1. `/usr/local/share/pear`
2. `/usr/share/pear`

However implementations of the Promise are not required to do so, they are only
required to search with `/usr/share/ccm/pear` if the needed module can not be
found within the `phpinclude` path.


File Suffix Search Order
------------------------

When determing the name of a file to automatically require, the following three
suffixes will be tried in this order:

1. `.php`
2. `.class.php`
3. `.inc.php`


Public Method `getPromiseAPI()`
-------------------------------

This method __MUST__ return the `const APIV` property defined in the
`AutoloadPromise` class. The AutoloadPromise class provides a function that
meets the needs of the API. Extending the Promise will inherit a suitable
method.


Public Method `version()`
-------------------------

This method __MUST__ return a string identifying the version of the class
extending the `AutoloadPromise` class.


Public Method `changeDefaultSearchPath( string $string )`
---------------------------------------------------------

This public method takes a `:` delimited string containing the branches within
the CCM root that the application wants the autoloader to search for a matching
class. For example:

   $obj->changeDefaultSearchPath('custom:stable');

would instruct the class to look for matches first in the `custom` branch, and
then in the `stable` branch, and it would *not* look for matches in either the
`local` or `devel` branches.

Implementations of the AutoloadPromise class must __not__ change the default
search path if fed an invalid argument. The Promise class provides a function
that meets the needs of the API. Extending the Promise will inherit a suitable
method.


Public Method `filelist( array $array )`
----------------------------------------

This method takes an array of file names relative to the branch and requires
them if they are found within the branches the class is configured to use.
Example of how this method works:

    $arr = array();
    $arr[] = '/libraries/sabre/uri/functions.php';
    $arr[] = '/libraries/sabre/xml/Deserializer/functions.php';
    $arr[] = '/libraries/sabre/xml/Serializer/functions.php';
    $obj->filelist($arr);

The files (assuming they are found) are loaded as soon as the method is called.


Public Method `classMap( array $array )`
----------------------------------------

The arguement is a `key --> value` array where the key is a ‘Fully Qualified
Class Name’ and the value is a path relative to the branch.

This tells the autoloading class specifically what file to load when a class
defined as a key is called.

This method is useful when either the filename containing the class does not
match the class name and/or when the namespace does not match the directory
hierarchy.


Public Method `loadClass( string $class )`
------------------------------------------

The arguement is a string containing the ‘Fully Qualified Class Name’. When
called, this method attempts to load the class.

PHP application `autoload.php` scripts should register this method with
`spl_autoload_register` so that it is automatically called when a class is
referenced:

    spl_autoload_register(function ($class) {
        global $obj;
        $obj->loadClass($class);
    });

That method should load the needed class file if one of three conditions are
met:

A) The filesystem location of the file to load is cached by the `ClassLoader`
class.

B) The location of the file relative to the branch was defined using the
previously mentioned `classMap( array $array )` method.

C) The location of the file can be determined by the name of the class in
combination with the namespace of the class.

For condition C to be met, the name of the file containing the class to be used
__MUST__ match the name of class with one of the three previously mentioned
file suffixes.

A class named Fubar would thus need to be defined in a file named `Fubar.php`,
`Fubar.class.php`, or `Fubar.inc.php`.


### Automatc Discovery Directory Structure

Automatic discovery of the right file to load also depends upon the directory
structure matching the namespace of the class.

When the class has a namespace that is two or more levels deep, the first
subdirectory within the `library/` directory __MUST__ be a lower case version
of the first level namespace, and the second subdirectory __MUST__ be a
lower case version of the second level namespace.

For example, take the Fully Qualified Class Name `Sabre/Xml/Element/Uri` and
let `LIBDIR` represent `branch/libraries` where `branch` is the branch the
class is installed in.

If the file defining the class is:

    LIBDIR/sabre/xml/Element/Uri.{php|class.php|inc.php}

Then Case C mentioned above is satsisfied and the `loadClass( string $class )`
method should load the class.

When a class only has one namespace level, it must be installed within LIBDIR
such that the first directory is a lower case representation of the namespace
and the second directory is a lower case representation of the class name.

For example, the class `Patchwork\JSqueeze` would need to be located at:

    LIBDIR/patchwork/jsqueeze/JSqueeze.{php|class.php|inc.php}

to be automatically found and loaded when the class is called.


Public Method `pearClass( string $class )`
------------------------------------------









