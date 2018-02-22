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