#!/usr/bin/php
<?php
// this script has not been tested
set_time_limit(35);

require('/usr/share/ccm/stable/libraries/ccm/promises/PackageDatabasePromise.php');

class foo extends \CCM\Promises\PackageDatabasePromise
{
    public function __construct( string $branch ) {
        $this->branch = $branch;
        $this->cleanStaleLock();
    }
}

$count = count($argv);

if($count !== 7) {
    echo "Usage: addComposerPackage [branch] [vendor] [package] [version] [securityv] [tweakv]\n";
    exit;
}

$branch = trim(strtolower($argv[1]));
if(! in_array($branch, array('local', 'stable', 'devel')) {
    echo "Branch must be one of: local, stable, or devel\n";
    exit;
}
$vendor = trim(strtolower($argv[2]));
if(strlen($vendor) === 0) {
    echo "Vendor can not be empty string.\n";
    exit;
}
$package = trim(strtolower($argv[3]));
if(strlen($package) === 0) {
    echo "Package can not be empty string.\n";
    exit;
}
$version = trim(strtolower($argv[4]));
if(strlen($package) === 0) {
    echo "Version can not be empty string.\n";
    exit;
}
$security = trim(strtolower($argv[5]));
if(! is_numeric($security)) {
    echo "Security Release must be a non-negative integer\n";
    exit;
}
$security = intval($security, 10);
if($security < 0) {
    echo "Security Release must be a non-negative integer\n";
    exit;
}
$tweak = trim(strtolower($argv[6]));
if(! is_numeric($tweak)) {
    echo "Tweak Release must be a non-negative integer\n";
    exit;
}
$tweak = intval($tweak, 10);
if($security < 0) {
    echo "Tweek Release must be a non-negative integer\n";
    exit;
}

$obj = new foo($branch);

$obj->addPackage($vendor, $package, $version, $security, $tweak);

exit;

?>