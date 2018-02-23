JSON Database Scripts
=====================

The following command line PHP scripts exist for interfacing with the JSON
database files:

* `addComposerPackage`
* `delComposerPackage`

Command line PHP scripts will have a `.php` extension in the github repository
but will not have an extension as installed on the system, to prevent
accidental execution of them with a seriously poorly configured web server that
allows remote access to them.


`addComposerPackage`
--------------------

This script is intended to be executed by the package manager (e.g. `rpm`) when
installing a package into the PHP CCM ecosystem.

With RPM it should be referenced in the `%post` section of the RPM spec file.

A macro should define the full path to the script, e.g.

    %define ccmaddpkg /usr/share/ccm/bin/addComposerPackage
    Requires(post):   %{ccmaddpkg}
    Requires(post):   %{_bindir}/php

Then in the `%post` section of the spec file:

    %post
    %{ccmaddpkg} %{branch} %{pkgvendor} %{pkgname} %{pkgversion} %{pkgsecurityv} %{pkgtweakv}

Please see the [RPM Spec File Standard](https://github.com/AliceWonderMiscreations/php-ccm/blob/master/RPM_SPEC.md)
for more information on RPM packaging of CCM libraries.


`delComposerPackage`
--------------------

This script is intended to be executed by the package manager (e.g. `rpm`) when
deleting a package from the PHP CCM ecosystem.

With RPM it should be referenced in the `%postun` section of the RPM spec file.
It should *not* be run when a package is removed for the purpose of being
replaced by a different version of the same package.

A macro should define the full path to the script, e.g.

    %define ccmdelpkg /usr/share/ccm/bin/delComposerPackage
    Requires(postun): %{ccmdelpkg}
    Requires(postun): %{_bindir}/php

Then in the `%postun` section of the spec file:

    %postun
    if [ "$1" -ge 1 ]; then
        %{ccmdelpkg} %{branch} %{pkgvendor} %{pkgname}
    fi

In the condition `if then` clause, the argument `$1` is how many packages of
the same identical name remain on the system after the package has been
removed.

It is using the name of the package as RPM sees it, not the `%{pkgname}`, so
it will do the right thing in CCM where the same library installed in
different branches will have a different package name as RPM sees them.

Please see the [RPM Spec File Standard](https://github.com/AliceWonderMiscreations/php-ccm/blob/master/RPM_SPEC.md)
for more information on RPM packaging of CCM libraries.