%define pkgversion 0.0.2
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

Name:		php-ccm-utilities
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

Provides:	CCM-promise(autoload1) = 1.0.0
Provides:	CCM-mmpromise(autoload1) = 1.0

%description
This package provides utility classes needed for the PHP-CCM ecosystem.
This is a package in development, the PHP-CCM ecosystem is not ready
for deployment.

%package -n php-ccm-autoloader
Group:		php/libraries
Summary:	The default PHP-CCM class autoloader

Provides:	CCM-mmkeptpromise(autoload1) = 1.0
Requires:	CCM-mmpromise(autoload1) = 1.0

%description -n php-ccm-autoloader
This package provides the default php-ccm class autoloader.

%prep
( cd %_sourcedir; sha256sum -c %{SOURCE20} )

%setup -q -n CCM-%{version}
find . -type f -print |while read file; do
  chmod 644 ${file}
done

%build

%install
mkdir -p %{buildroot}%{basedir}/stable/libraries/ccm/promises
mv promises/* %{buildroot}%{basedir}/stable/libraries/ccm/promises/
install -m644 example/ClassLoader.php %{buildroot}%{basedir}/


%files
%defattr(-,root,root,-)
%license LICENSE.md
%doc README.md LICENSE.md composer.json docs/PromiseAPI.md docs/AutoloadPromise-*
%{basedir}/stable/libraries/ccm/

%files -n php-ccm-autoloader
%defattr(-,root,root,-)
%license LICENSE.md
%doc LICENSE.md docs/ClassLoader.md example/autoload.php
%{basedir}/ClassLoader.php



%changelog
* Tue Feb 20 2018 Alice Wonder <buildmaster@librelamp.com> - 0.0.2-0.ccm.1
- update for further testing

* Sun Feb 18 2018 Alice Wonder <buildmaster@librelamp.com> - 0.0.1-0.ccm.1
- Initial spec file.
