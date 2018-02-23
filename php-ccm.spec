%define pkgversion 0.0.3
# Increment below by one when tweaking the spec file but the version has not
#  changed and the security patch release has not changed
%define pkgtweakv 1

# Increment below by one when applying a security patch to the current version
#  or when switching from pre-release to official release of a version.
# Reset to 1 if updating the version (or 0 if updating to a pre-release of
#  a new version)
%define pkgsecurityv 0

# When there is a need for additional information in the release tag, uncomment
#  below to define it. The definition of this macro should always start with a
#  dot.
#  %%define pkgoptother .whatever

# Do not change these
%define basedir %{_datadir}/ccm
%define _defaultdocdir %{basedir}/doc

Name:		php-ccm
Version:	%{pkgversion}
Release:	%{pkgsecurityv}.ccm.%{pkgtweakv}%{?pkgoptother}
BuildArch:	noarch
Summary:	PHP Utility Classes for the CCM Project

Group:		php/libraries
License:	MIT
URL:		https://github.com/AliceWonderMiscreations/CCM
Source0:	CCM-%{version}.tar.gz

#checksums
Source20:	CCM-%{version}.sha256

#BuildRequires:	
Requires:	php(language) >= 5.3.0
Requires: php-pecl(json)

Provides: php-ccm-promises
Provides: php-ccm-filesystem
Provides:	CCM-promise(autoload1) = 1.0.0
Provides:	CCM-mmpromise(autoload1) = 1.0
Provides:	CCM-promise(packagedatabase1) = 1.0.0
Provides:	CCM-mmpromise(packagedatabase1) = 1.0

%description
This package provides the base PHP CCM file system structure and the
abstract classes needed for the PHP CCM management utilities.

%package -n php-ccm-autoloader
Group:		php/libraries
Summary:	The default PHP-CCM class autoloader

Provides:	CCM-mmkeptpromise(autoload1) = 1.0
Requires:	CCM-mmpromise(autoload1) = 1.0

%description -n php-ccm-autoloader
This package provides the default php-ccm class autoloader.

%package -n php-ccm-jsondb
Group:    php/utilities
Summary:  Scripts for managing the JSON database of installed packages

Provides:	CCM-mmkeptpromise(packagedatabase1) = 1.0
Requires:	CCM-mmpromise(packagedatabase1) = 1.0

%description -n php-ccm-jsondb
This package provides the shell scripts used by the operating system package
manager to maintain the JSON database of what versions of what packages have
been installed.

A future version of this package will include a utility for querying whether
or not updates are available.

%prep
( cd %_sourcedir; sha256sum -c %{SOURCE20} )

%setup -q -n CCM-%{version}
find . -type f -print |while read file; do
  chmod 644 ${file}
done

%build

%install
# the directory structure
mkdir -p %{buildroot}%{basedir}/{bin,doc,jsondb}
mkdir -p %{buildroot}%{basedir}/local/{libraries,applications}
mkdir -p %{buildroot}%{basedir}/stable/{libraries,applications}
mkdir -p %{buildroot}%{basedir}/devel/{libraries,applications}
mkdir -p %{buildroot}%{basedir}/custom/{libraries,applications}
touch %{buildroot}%{basedir}/jsondb/local.dblock
touch %{buildroot}%{basedir}/jsondb/stable.dblock
touch %{buildroot}%{basedir}/jsondb/devel.dblock
touch %{buildroot}%{basedir}/jsondb/local.json
touch %{buildroot}%{basedir}/jsondb/stable.json
touch %{buildroot}%{basedir}/jsondb/devel.json

mkdir -p %{buildroot}%{basedir}/stable/libraries/ccm/promises

install -m644 promises/AutoloadPromise.php %{buildroot}%{basedir}/stable/libraries/ccm/promises/
install -m644 promises/PackageDatabasePromise.php %{buildroot}%{basedir}/stable/libraries/ccm/promises/

install -m644 example/ClassLoader.php %{buildroot}%{basedir}/
install -m755 bin/addComposerPackage.php %{buildroot}%{basedir}/bin/addComposerPackage
install -m755 bin/delComposerPackage.php %{buildroot}%{basedir}/bin/delComposerPackage


%files
%defattr(-,root,root,-)
%license LICENSE.md
%doc README.md LICENSE.md composer.json docs/CLI_PHP.md docs/PromiseAPI.md docs/AutoloadPromise-* docs/PackageDatabasePromise-*
%dir %{basedir}
%dir %{basedir}/bin
%dir %{basedir}/doc
%dir %{basedir}/local
%dir %{basedir}/local/libraries
%dir %{basedir}/local/applications
%dir %{basedir}/stable
%dir %{basedir}/stable/libraries
%dir %{basedir}/stable/applications
%dir %{basedir}/devel
%dir %{basedir}/devel/libraries
%dir %{basedir}/devel/applications
%dir %{basedir}/custom
%dir %{basedir}/custom/libraries
%dir %{basedir}/custom/applications
%dir %{basedir}/stable/libraries/ccm
%{basedir}/stable/libraries/ccm/promises


%files -n php-ccm-autoloader
%defattr(-,root,root,-)
%license LICENSE.md
%doc LICENSE.md docs/ClassLoader.md example/autoload.php
%{basedir}/ClassLoader.php


%files -n php-ccm-jsondb
%defattr(-,root,root,-)
%license LICENSE.md
%doc LICENSE.md docs/jsonDatabaseScripts.md
%attr(0755,root,root) %{basedir}/bin/addComposerPackage
%attr(0755,root,root) %{basedir}/bin/delComposerPackage
%dir %{basedir}/jsondb
%ghost %{basedir}/jsondb/*.dblock
%ghost %{basedir}/jsondb/*.json



%changelog
* Thu Feb 22 2018 Alice Wonder <buildmaster@librelamp.com> - 0.0.3-0.ccm.1
- add jsondb promise, utility scripts (new subpackage)
- combine promises and filesystem into one package

* Tue Feb 20 2018 Alice Wonder <buildmaster@librelamp.com> - 0.0.2-0.ccm.1
- update for further testing, added filesystem sub-package

* Sun Feb 18 2018 Alice Wonder <buildmaster@librelamp.com> - 0.0.1-0.ccm.1
- Initial spec file.
