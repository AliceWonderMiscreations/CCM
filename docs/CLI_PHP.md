Command Line PHP Scripts
========================

This file describes how I am attempting to write the command line utility
scripts that will be used in the PHP CCM project.

Script utilities for the PHP CCM project will *generally* be written in PHP
except where PHP really does not make sense to do what needs to be done.


PHP Version
-----------

All scripts should work with all versions of PHP from 5.3.3 through the current
version of PHP (7.2.x as of 2018-02-22).

RHEL/CentOS 6 will continue to receive vendor support through
[November 30, 2020](https://wiki.centos.org/FAQ/General#head-fe8a0be91ee3e7dea812e8694491e1dde5b75e6d)
and CentOS 6 ships with PHP 5.3.3.

While I *highly* encourage system administrators of CentOS 6/7 to update to PHP
7.x, a frequent method of updating is to install the newer version of PHP
either within `/opt` or within `/usr/local` while leaving the operating system
provided version of PHP intact.

This means the update version of the `php` binary may not be available to
command line scripts, depending upon the `PATH` variable of the user calling
the script. Even when a CentOS 6 system administrator has installed a newer
version of PHP, the updated `php` binary used for command line scripts may not
be in the `PATH` used by the `rpm` utility or by the `cron` utility.


Script Shebang
--------------

All command line utility scripts must start with the shebang `#!/usr/bin/php`
as the very first line.

It was tempting to use `#!/usr/bin/env php` as that could bring in a newer
version of the PHP binary if a newer version is earlier in the defined `PATH`
when the script is invoked, however that makes it more difficult to use the
facilities of RPM to guarantee a suitable binary is available when the
scriptlets actually run.


Script Naming Scheme
--------------------

Executable command line scripts should __NOT__ end in `.php`. They should not
have any extension at all.

In the [CCM Github repository](https://github.com/AliceWonderMiscreations/CCM)
they should end in `.php` to make it obvious to users viewing the git tree that
they are PHP scripts, but when installed on the system they need to be without
the `.php` extension.


Binary PHP Extensions
---------------------

Unless absolutely necessary, PHP binary modules that are not part of a
‘standard’ PHP install should be avoided.

If the extension is part of a ‘standard’ PHP 7.x install but requires a PECL
extension for earlier PHP, as long as the extension exists then it is okay
to require it.


Non-Binary PHP Classes
----------------------

When a PHP CLI script requires a particular non-binary class, it must be a
class that works with PHP 5.3.3 and should be required full path. As many of
the PHP CLI scripts will be run by a user with administrative privileges, it is
dangerous to use an autoloader or the `phpinclude` path to find the needed file
to load.


PEAR
====

The CCM scripts related to managing PEAR will require PHP 5.4.0 or newer rather
that following the previously mentioned 5.3.3 version.

The PEAR management is completely optional. System administrators can continue
using whatever they currently use to manage PEAR packages and they will still
work with the PHP CCM project as long as the directory that contains their PEAR
modules is within the `phpinclude` path.

The current version of PEAR requires PHP 5.4.0 or newer. With that requirement
combined with nothing that needs to be run in an RPM post scriptlet or cron
job that will not also require PHP 5.4.0 or newer, there is no point at all
in requiring scripts related to PEAR management work with versions of PHP that
predate PHP 5.4.0.









