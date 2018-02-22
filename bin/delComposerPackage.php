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

if($count !== 4) {
    echo "Usage: delComposerPackage [branch] [vendor] [package]\n";
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

$obj->delPackage($vendor, $package);

exit;

?>