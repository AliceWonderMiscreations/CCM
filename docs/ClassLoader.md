The PHP-CCM AutoLoader
======================

The AutoLoader in its present state can be found in the file `ClassLoader.php`

This class is only partially tested.

My goal with it is to ‘Keep It Simple, Silly’ - something that annoys the
hell out of me with many auto-loaders out there.

PHP applications will need to explicitly require the path to the auto-
loading class in their application specific `autoload.php` script:

    require('/usr/share/ccm/ClassLoader.php');

The the `autoloader.php` for the application will need to create an instance of the
class:

    $CCM = new \CCM\ClassLoader();

Within the `/usr/share/ccm/` root, by default the class looks for matches
within the `local` branch first, then the `stable` branch. By default it will
not look for classes in the `devel` or `custom` branch.


Changing Branch Search Order
----------------------------

If the web application needs to change that search order, the order needs to
be changed within the application auto-loader script *before* anything else:

    $newsearchpath = 'devel:stable';
    $CCM->changeDefaultSearchPath($newsearchpath);

That will cause the class to look for matches in the `devel` branch first, and
then the `stable` branch, and it will not look in the `local` or `custom`
branches at all.


Loading Functions
-----------------

Unfortunately some libraries have functions in scripts that are not within a
class file. In PHP, functions can not have the scripts that define them auto-
loaded, at least not trivially. These scripts need to be loaded by the
application auto-loader. The full path to the scripts within the branch needs
to be defined in an array, and the array can then be fed to the $CCM object
using the `filelist` function:

    $arr = array('/libraries/sabre/uri/functions.php');
    $arr[] = '/libraries/sabre/xml/Deserializer/functions.php';
    $arr[] = '/libraries/sabre/xml/Serializer/functions.php';
    $CCM->filelist($arr);

Every script defined in the array will be loaded, assuming it can be found.


Loading Class Map (UNTESTED)
----------------------------

Unfortunately some libraries have classes in a file name that does not match
the class name. In those cases, a `key = value` map array needs to be created
so that the class can now which scripts to load when the class is used:

    $arr = array('Foo\Bar\Yummy\Carrots' => '/libraries/foo/bar/apples.php');
    $CCM->classMap($arr);

In cases where a `key` has already been defined, attempts to redefine the `key`
to something else will be ignored.

In the majority of cases, all classes are in appropriately named files and we
do not need to do anything special.


Register the Auto-Loader
------------------------

To register the auto-loader:

    spl_autoload_register(function ($class) {
      global $CCM;
      $CCM->loadClass($class);
    });


Register PEAR Auto-Loader (UNTESTED)
------------------------------------

In the event your web application uses any PEAR modules, for your convenience
there is an auto-loader for that as well:

    spl_autoload_register(function ($class) {
      global $CCM;
      $CCM->pearClass($class);
    });

That PEAR autoloader first looks to see if the PEAR repository is installed
within the `phpinclude` path. If it fails to find what it needs, it then will
first look for the needed files in `/usr/share/ccm/pear` followed by within
`/usr/local/share/pear` and `/usr/share/pear`.


Caching Path to Class File
--------------------------

If you have the PECL APCu binary module installed and enabled, you can have the
autoloader cache the path to class files once they have been found so that the
autoload class does not need to search for it every time a page loads.

Use the `setCacheKey` method to define a string to use:

    $CCM->setCacheKey('Some String Here');

The string should be at least one character in length.


Register Auto-Loader for Local Application Classes
--------------------------------------------------

Finally if you are like me, often you have a directory outside the web root in
your `phpinclude` path that has some class files, e.g. database connect classes
that contain the SQL password.

For your convenience, this application supports autoloading them too:

    spl_autoload_register(function ($class) {
      global $CCM;
      $CCM->localSystemClass($class);
    });

In those cases, the file containing the class must be named the same as the
class (ending in `.php`, `.class.php`, or `.inc.php`) and must be directly
within one of the directories in the `phpinclude` path, not a sub-directory.

Classes found by that function do not have their path cached.
