Promise API Version Scheme
==========================

What I call a ‘Promise’ class is an abstract class that requires certain
features be implemented by the classes that extend the class.

Promises are installed in `/usr/share/ccm/stable/libraries/ccm/promises` and
use the `CCM/Promises` namespace.

Each Promise class will have a defined versioned API that will follow a version
scheme consisting of three non-negative base 10 integers delimited by a dot.

This version scheme is the so-called *Major*.*Minor*.*Point* versioning scheme.


Increments to the API Point Release
-----------------------------------

A change to the Promise API that does not require any modification to the
classes that extend it in order for those classes to continue to have the same
expected behavior will increment the Point Release.

For example, the `AutoloadPromise` class currently defines four branches within
the CCM root that it searches. If that was changed to define a fifth branch,
then as long as the default search path of `local:stable` did not change, no
changes would be needed to either classes that extend it or scripts that use
the classes that extend it for them to continue to work as expected.


Increments to the API Minor Release
-----------------------------------

A change to a Promise API may require modifications to the classes that extend
them yet still provide backwards compatability to scripts that use the classes
that extend them.

For example, a new abstract method may be added to the Promise that must be
implemented by the classes that extend it, but the classes that extend it will
continue to work with scripts that use them as expected without those scripts
needing any modification.

In these cases, a suffix will be added to the class name after the word
`Promise` so that the old Promise can also remain.


Increments to the API Major Release
-----------------------------------

A change to a Promise API that is *not* backwards compatible will increment
the Major release.

Both the Promise and classes that extend the Promise will need to use a new
class name to avoid conflicts with the older API.


Application Stability
---------------------

Using this scheme allows for many different versions of the same Promise to
exist at the same time, so that Promise classes can be developed as needed
without breaking PHP applications that are not updated to reflect the changes.


RPM Spec File Requires and Provides
-----------------------------------

The RPM that provides a Promise will use the virtual provides:

    Provides: CCM-promise(nameMajor)   = major.minor.point
    Provides: CCM-mmpromise(nameMajor) = major.minor

Where *name* is the lower case name of the promise and *Major* is the major
version of the Promise.

The RPM that provides a class that extends a Promise will use the virtial
provide:

    Provides: CCM-mmkeptpromise(nameMajor) = major.minor
    Requires: CCM-mmpromise(nameMajor)     = major.minor

A PHP application that only cares about the major.0.0 API will then have:

    Requires: CCM-mmkeptpromise(nameMajor)

If the PHP application needs features added in a specific minor release:

    Requires: CCM-mmkeptpromise(nameMajor) >= major.minor

If the PHP application needs a feature in a particular point release:

    Requires: CCM-mmkeptpromise(nameMajor) >= major.minor
    Requires: CCM-promise(nameMajor)       >= major.minor.point

In the vast majority of cases, a web application that needs the implementation
of a Promise will only need to worry about the major version.

In the vast majority of cases, the AutoloadPromise is the only Promise that PHP
applications will need to worry about, the rest are likely to just be for
utility scripts.
