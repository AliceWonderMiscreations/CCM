#!/usr/bin/php
<?php
// this script has not been tested
set_time_limit(35);

require('/usr/share/ccm/stable/libraries/ccm/promises/PackageDatabasePromise.php');

class foo extends \CCM\Promises\PackageDatabasePromise
{
    public function __construct( string $branch ) {
        $this->branch = $branch;
        $this->dbfile = self::DBDIR . $branch . '.json';
        $this->dblock = self::DBDIR . $branch . '.dblock';
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

// remove stale lockfile
$lock = $obj->dblock;
if(file_exists($lock)) {
    $mtime = filemtime($lock);
    $now = time();
    $diff = $now - $mtime;
    // No valid reason for it to be > 5 minutes old
    if($diff > 300) {
        $obj->deleteLockFile();
    }
}

$obj->addPackage($vendor, $package, $version, $security, $tweak);

exit;

?>