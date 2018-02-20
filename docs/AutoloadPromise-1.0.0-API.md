AutoloadPromise 1.0.0 API
=========================

Implementationa of the AutoloadPromise 1.0.0 API *should* be in a class name
called `ClassLoader`.

Implementations for global use __MUST__ use that class name within the
namespace `CCM` and be installed at `/usr/share/ccm/ClassLoader.php`.

In the event an application needs methods not defined by this API, the
application is free to copy the reference implementation of the API (located in
the `example/ directory of the github repo) and add the methods the application
needs. The application should however change the namespace to a namespace
specific to the application rather than the `CCM` namespace.

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

The version 1.0.0 of the AutoloadPromise also defines the following two
additional search paths:

1. `/usr/local/share/pear`
2. `/usr/share/pear`

However implementations of the AutoloadPromise are not required to do so, they
are only required to search with `/usr/share/ccm/pear` if the needed module can
not be found within the `phpinclude` path.


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
meets the needs of the API. Extending the AutoloadPromise class will inherit a
suitable method.


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
search path if fed an invalid argument. The AutoloadPromise class provides a
function that meets the needs of the API. Extending the Promise will inherit a
suitable method.


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

The arguement is a string containing the ‘Fully Qualified Class Name’. When
called, this method attempts to load the class. It should convert the FQCN to
the directory and filename structure used by PEAR.

The class should then be loaded if one of the following two conditions are met:

A) The file can be found withing the `phpinclude` path.

B) The file can be found within the the `ClassLoader` configured PEAR search
directories, and it __MUST__ search `/usr/share/ccm/pear` before searching
through other possible paths for PEAR modules.

PHP applications that depend upon PEAR modules should register this method with
`spl_autoload_register`:

    spl_autoload_register(function ($class) {
        global $obj;
        $obj->pearClass($class);
    });


Public Method `localSystemClass( string $class )`
-------------------------------------------------

This method exists for the convenience of system administrators. We often have
our own classes that are defined in class files within the `phpinclude` path.

This method should load them as long as the file name matches the class name.
This method should not care about the namespace, it should only look for
filenames that match the class name. Those who need it should be able to
register it via:

    spl_autoload_register(function ($class) {
        global $obj;
        $obj->localSystemClass($class);
    });

This method should only search within the `phpinclude` path. The AutoloadPromise
class provides a function that meets the needs of the API. Extending the Promise
AutoloadPromise class will inherit a suitable method.


Public Method `setCacheKey( string $string )`
---------------------------------------------

By default, implementions of the AutoloadPromise __MUST__ have caching of the
file system location of class files disabled. Many cache implementations are
not secure to use on shared hosting platforms.

If an implementation of the AutoloadPromise API does not support caching at all
then this method should simply return.

If the implementation does support caching, calling this method with a string
argument of at least one character in length should enable the caching.

The string argument is called the *application key* and can be any string with
a length greater than zero, including a single space.

When two instances of the ClassLoader have an identical *application key* and
have an identical branch search path, then the cache key to set and retrieve
the file path for a given FQCN should be identical.

When the *application key* and/or the branch search path differ, then the
cache key used to set and retrieve the file path for a given FQCN should also
differ.


### Do Not Always Cache

Implementations of the AutoloadPromise __MUST NOT__ cache the path to classes
specified in either the `classMap()` method or the `localSystemClass()` method.

Only paths that are discovered using the class name and namespace or PEAR
modules should have the path to the file to load cached.

This is to avoid possible collisions that could occur if two applications have
the same *application key* and branch path, but one uses either of those two
methods to explicitly load a version of the same FQCN that differs from what
would be loaded from automated path discovery.


### Application Implementation Note

PHP applications should not call this method by default. Many shared hosting
services have APCu enabled by default, but it is not safe to use caching of
file paths that will be executed via APCu on a shared server.

System administrators should be required to specifically modify the application
`autoload.php` script to enable this feature.


### Cache Performance Note

In theory caching the resolved file locations should increase performance as
fewer filesystem I/O calls are needed to determine the location of the file
that needs to be loaded.

With platter hard drives that use a disc arm, I suspect there is a real world
benefit to caching the file locations associated with a class.

However, I am not sure the performance difference is all that great when a
server with modern SSD drives is used.

Furthermore the design of CCM reduces the searching required, applications
will generally only have one or two branches defined in their search path and
the file containing the needed class will usually have a `.php` suffix which is
the first suffix searched.

When testing is done, if the performance benefits from caching are not very
signigicant then future versions of the AutoloadPromise API may remove caching.
