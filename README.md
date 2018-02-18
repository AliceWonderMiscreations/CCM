PHP Utility Classes for the Composer Class Manager
==================================================

This package contains utility classes that are needed by the
[php-ccm](https://github.com/AliceWonderMiscreations/php-ccm) project.

AliceWonderMiscreations\CCM\ClassLoader
---------------------------------------

This is the php-ccm autoloader class. It should be explicitly required by web
applications that use the php-ccm ecosystem to manage their dependencies.

See the `docs/ClassLoader.md` file for usage instructions.

When installed using the php-ccm packaging system, this class is installed in
the php-ccm base directory (`/usr/share/ccm`) because it needs to be manually
required by web application autoloader scripts.
