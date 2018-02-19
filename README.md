PHP Utility Classes for the Composer Class Manager
==================================================

This package contains utility classes that are needed by the
[php-ccm](https://github.com/AliceWonderMiscreations/php-ccm) project.

In many if not all cases, the utility will actually be an abstract class and
the class name will end with the word `Promise` as it promises the actual
utilities that extend the abstract class will have certain properties and
methods defined in the abstract class.

The purpose of doing it this way, for the actual utilities themselves, it
allows alternative implementations to be written and used.

The class files that extend the `*Promise` abstract classes should use the
`\CCM` namespace within the root namespace, e.g.

    <?php
    namespace CCM;
    class FooBar extends \CCM\Promises\WhateverPromise
    {
      code here
    }

That way utilities and applications can simply call

    $obj = new \CCM\FooBar;

And they will get an object that has the properties and methods defined in the
abstract class, so those properties and methods are safe to use regardless of
who's implementation of the Promise they are using.


AutoloadPromise
---------------

This is the `Promise` that needs to be extended for the CCM class autoloading.
When extending the class for a custom autoloader, the custome autoloader should
use the class name `\CCM\ClassLoader` and should explicitly load the promise
abstract class:

    require_once(__DIR__ . '/stable/libraries/ccm/promises/AutoloadPromise.php');

This is because autoloading does not exist until after an object of the class
has been created.

The autoloader should be in a file named `ClassLoader.php` and installed in the
root php-ccm directory as `/usr/share/ccm/ClassLoader.php` because that is
web applications will expect it to be.

The example autoloader (in the `example/` directory of the git repository) is
probably good enough for most use cases, but someone who prefers a different
caching engine may wish to create an alternative implementation of the abstract
`AutoloadPromise` class.


