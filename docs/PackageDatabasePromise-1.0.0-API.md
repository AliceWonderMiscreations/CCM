PackageDatabasePromise 1.0.0 API
================================

Implementations of this API *should* be done within the PHP shell scripts that
use this API rather than within a separate class file. This API is not intended
for web application use.

Unless there is a bug in one of the functions in the Promise, implementers
should not replace any of the functions when they extend the class.

Implementers do need to provide a constructor that sets the `branch` property
and then either calls the `cleanStaleLock()` method *or* sets the `dbfile` and
`dbfile` properties.

An example implementation:

    require('/usr/share/ccm/stable/libraries/ccm/promises/PackageDatabasePromise.php');

    class foo extends \CCM\Promises\PackageDatabasePromise
    {
        public function __construct( string $branch ) {
            $this->branch = $branch;
            $this->cleanStaleLock();
        }
    }

That should be defined in the PHP shell script where the Promise is used, and
as such it does not need to be in a namespace.

Example implementations within PHP shell scripts can be found within the
`addComposerPackage.php` and `delComposerPackage.php` shell scripts within the
`bin/` directory of the
[github repository](https://github.com/AliceWonderMiscreations/CCM/tree/master/bin)
for this project.

Notice those scripts also call `set_time_limit(35)` as a stale lock file could
cause operations within the class to exceed typical script time limits. 35
seconds allows the class to gracefully fail when it can not create a lock file.


The JSON Database Files
-----------------------

The three CCM branches `local`, `stable`, and `devel` are intended for classes
managed by the operating system Package Manager (e.g. [RPM](http://rpm.org/) or
[DPKG](https://wiki.debian.org/DebianPackageManagement)).

In the `stable` branch, the latest available version is generally desired but
in some cases, a PHP application may __require__ an older or newer API of a
library than what is packaged for the `stable` branch. For this reason, online
repositories that can be used with tools like [yum](http://yum.baseurl.org/) or
[apt-get](https://wiki.debian.org/apt-get) will not be provided for the `local`
and `devel` branches within the CCM project.

Without a package repository, a mechanism is needed by which system
administrators can check for applicable updates, especially security updates,
to the PHP classes they have installed. A local database that can be checked
daily through a cron job is the logical solution to this problem.

JSON was chosen for the database due to its simplicity and ubiquity and easy
human readability as an ordinary text file. PHP versions earlier than 5.4.0
do not produce a very human readable JSON output, but PHP versions prior to
5.3.0 do not support namespacing and 5.4.0 itself is really old. PHP 5.3.x
however *should* work with this API.

Each branch will have its own JSON database file. Within each database file,
each vendor will have its own named property. Within each vendor, each package
will have its own named property. Within each package, the following three
properties will exist:

1. `version` - a string containing the package version number.
2. `security` - a non-negative integer that is bumped whenever a security
related fix to a specific version is made.
3. `tweak` - a non-negative integer that is bumbed whenever a non-security
related fix to a specific version is made.

The database should be updated by scriptlets that are part of the package that
is installed within the CCM root.

An example of what the database might look like is provided in the Appendix.


Protected Property `$branch`
----------------------------

In the `PackageDatabasePromise` this property __MUST__ be set by the
constructor of the class that extends this Promise.

This __MUST__ be set to a value of `local`, `stable`, or `devel`.


Protected Property `$dbfile` and `$dblock`
------------------------------------------

In the `PackageDatabasePromise` these properties can either be set by the
constructor, or the constructor can call the `cleanStaleLock()` method
to set them. The latter is preferable.


Public Static Method `getPromiseAPI()`
--------------------------------------

Returns the API for the PackageDatabasePromise.


Protected Method `cleanStaleLock()`
-----------------------------------

A lock file is only created when the class needs to write the database file.

Even with a very large database of packages, such an operation should never
take more than a few seconds.

In the event failure to delete a lock file occurs, the presence of a lock file
will interfere with the proper operation of the class.

This method will remove any lock file that is at least ten seconds old. It
should be called by the constructor of the class that extends this Promise.

The `$branch` property needs to be set *before* this method is called.


Public Method `addPackage ( string $vendor, string $package, string $version, int $securityv, int $tweakv )`
------------------------------------------------------------------------------------------------------------

This method either adds a package to the database or updates the information
about a package in the database.

The first arguement is the vendor of the package and the second arguement is the
name of the package.

The third arguement is the version, and then the security level integer, and
finally the packaging tweak,


Public Method `delPackage ( string $vendor, string $package )`
--------------------------------------------------------------

This method deletes a package from the database.


Public Method `listPackages()`
------------------------------

This method outputs an array of all packages in a manner that will allow a DNS
query to check for updates.


APPENDIX
========


DNS Version Checking
--------------------

The plan is to use DNS to check installed versions against the currently
available versions of a package.

DNS `TXT` records that correspond with the version, security release, and tweak
release will return with the most current API compatible package.

This is not yet implemented. Basically the strings representing each package
that are part of the array generated by the `listPackages()` method will have a
domain appended to them to give the string to query from the DNS system.

When the returned version is newer or the version is the same but the returned
Security version is newer, the system administrator will be sent an alert
e-mail so they can update the package installed.

This *may* require some additional public functions be added to this class.


Future Plans
------------

Public methods that allow querying version information for specific packages or
all packages from a specific vendor will be added to this class.

A public method that allows sorting the database will also be added at some
point but the priority is low, such a method is only beneficial to humans
reading the JSON file.


Example Database
----------------

To be added soon.










