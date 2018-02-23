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

if($count !== 4) {
    echo "Usage: delComposerPackage [branch] [vendor] [package]\n";
    exit(1);
}

$branch = trim(strtolower($argv[1]));
if(! in_array($branch, array('local', 'stable', 'devel'))) {
    echo "Branch must be one of: local, stable, or devel\n";
    exit(1);
}
$vendor = trim(strtolower($argv[2]));
if(strlen($vendor) === 0) {
    echo "Vendor can not be empty string.\n";
    exit(1);
}
$package = trim(strtolower($argv[3]));
if(strlen($package) === 0) {
    echo "Package can not be empty string.\n";
    exit(1);
}

$obj = new foo($branch);

if (! $obj->delPackage($vendor, $package)) {
    exit(1);
}

exit(0);

?>