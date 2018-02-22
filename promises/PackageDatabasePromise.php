<?php

/* Not fully tested */

/* classes that extend this promise should
   set the branch property and then run
   $this->cleanStaleLock() as part of the
   constructor
*/

/* scripts that call this should use set_time_limit(35) */

namespace CCM\Promises;

abstract class PackageDatabasePromise
{

    /* class properties */

    const APIMAJOR = 1;
    const APIMINOR = 0;
    const APIPOINT = 0;

    // Directory with the JSON database files
    const DBDIR = '/usr/share/ccm/jsondb/';
    // for testing I comment out above and uncomment below
    //const DBDIR = '/home/alice/jsondb/';

    protected $lockkey = '';
    protected $branch = 'fubar';
    protected $dbfile = '';
    protected $dblock = '/tmp/foo';
    protected $database = array();


    /* class methods */

    public static function getPromiseAPI() {
        $string = self::APIMAJOR . '.' . self::APIMINOR . '.' . self::APIPOINT;
        return $string;
    }

    protected function err( string $message ) {
        echo "Error: " . $message . "\n";
    }

    protected function checkBranch() {
        $validBranches = array('local', 'stable', 'devel');
        if(! in_array($this->branch, $validBranches)) {
            $this->err($this->branch . ' is not a valid branch.');
            return false;
        }
        return true;
    }

    // makes sure the lock file was created by this instance
    protected function readTouch ( $file ) {
        if(file_exists($file)) {
          if(! $handle = @fopen($file, 'r')) {         
              $this->err("Could not open " . $file . " for reading.");
              return false;
          }
          if( ! $string = fread($handle, 40)) {
              $this->err("Could not read lock file.");
              return false;
          }
          if( (strlen($this->lockkey) === 40) && (strcmp($string, $this->lockkey) === 0)) {
              return true;
          }
        }
        return false;
    }

    // creates a lock file containing a unique random string
    protected function uniqueTouch( $file ) {
        // exit if there is already a lock file
        if(file_exists($file)) {
            return false;
        }
        if(function_exists("random_bytes")) {
          $data = random_bytes(4);
        } else {
          $data = rand(0, 4294967295);
        }
        // why shuffle? because we can, dammit!
        $this->lockkey = str_shuffle(hash('ripemd160', $data));
        if( ! $handle = @fopen($file, 'w')) {
            $this->err("Could not open " . $file . " for writing.");
            return false;
        }
        fwrite($handle, $this->lockkey);
        fclose($handle);
        return $this->readTouch($file);
    }

    // try to create a lock file, keep trying if one exists
    protected function createLockFile() {
        if(! $this->checkBranch()) {
            return false;
        }
        $count = 0;
        while(true) {
            if($this->uniqueTouch($this->dblock)) {
                return true;
            }
            $count++;
            sleep(1);
            if($count > 30) {
                $this->err("Can not create lock file needed to modify database.");
                return false;
            }
        }
    }

    // removes the lock file
    protected function deleteLockFile() {
        if(file_exists($this->dblock)) {
            @unlink($this->dblock);
        }
    }

    // read the json database into a PHP array
    protected function readDatabase () {
        // if file doesn't exist do nothing
        if(file_exists($this->dbfile)) {
            $json = file_get_contents($this->dbfile);
            if($arr = json_decode($json, true)) {
                $this->database = $arr;
                return true;
            } else {
                $this->err('The PHP function json_decode Could not parse ' . $this->dbfile);
                return false;
            }
        }
        return true;
    }

    // write the PHP array to a json database
    protected function writeDatabase () {
        if(! $this->checkBranch()) {
            return false;
        }
        $phpversion = explode('.', phpversion());
        // JSON_PRETTY_PRINT requires php >= 5.4.0
        $majorphp = intval($phpversion[0]);
        $minorphp = intval($phpversion[1]);
        if(($majorphp === 5) && ($minorphp < 4)) {
            $json = json_encode($this->database);
        } else {
            $json = json_encode($this->database, JSON_PRETTY_PRINT);
        }
        if( ! $handle = @fopen($this->dbfile, 'w')) {
            $this->err("Could not open " . $this->dbfile . " for writing.");
            return false;
        }
        fwrite($handle, $json);
        fclose($handle);
        return true;
    }

    // it is recommended this be run by the constructor
    public function cleanStaleLock () {
        $this->dbfile = self::DBDIR . $this->branch . '.json';
        $this->dblock = self::DBDIR . $this->branch . '.dblock';
        if(file_exists($this->dblock)) {
            $mtime = filemtime($this->dblock);
            $now = time();
            $diff = $now - $mtime;
            // No valid reason for it to be > 5 minutes old
            if($diff > 300) {
                $this->deleteLockFile();
            }
        }
    }

    // add a package to the database
    public function addPackage ( string $vendor, string $package, string $version, int $securityv, int $tweakv ) {
        if(! $this->checkBranch()) {
            return false;
        }
        $vendor = trim(strtolower($vendor));
        $package = trim(strtolower($package));
        if((strlen($vendor) === 0) || (strlen($package) === 0))  {
            $this->err("Vendor / Package can not be empty");
            return false;
        }
        if($this->createLockFile() && $this->readDatabase()) {
            if(! isset($this->database[$vendor])) {
                $this->database[$vendor] = array();
            }
            if(! isset($this->database[$vendor][$package])) {
                $this->database[$vendor][$package] = array();
            }
            $this->database[$vendor][$package]['version'] = $version;
            $this->database[$vendor][$package]['security'] = $securityv;
            $this->database[$vendor][$package]['tweak'] = $tweakv;
            $this->writeDatabase();
        } else {
            $this->err("Could not add/update package in database.");
        }
        $this->deleteLockFile();
    }

    // delete a package from the database
    public function delPackage ( string $vendor, string $package ) {
        if(! $this->checkBranch()) {
            return false;
        }
        $vendor = trim(strtolower($vendor));
        $package = trim(strtolower($package));
        if((strlen($vendor) === 0) || (strlen($package) === 0))  {
            $this->err("Vendor / Package can not be empty");
            return false;
        }
        if($this->createLockFile() && $this->readDatabase()) {
            if(isset($this->database[$vendor][$package])) {
                unset($this->database[$vendor][$package]);
            }
            if(isset($this->database[$vendor])) {
                $count = count($this->database[$vendor]);
                if($count === 0) {
                    unset($this->database[$vendor]);
                }
            }
            $count = count($this->database);
            if($count === 0) {
                //attempt to nuke the json
                @unlink($this->dbfile);
            } else {
                $this->writeDatabase();
            }
        } else {
            $this->err("Could not delete package from database.");
        }
        $this->deleteLockFile();    
    }

    // returns an array of every package in the branch - in the
    //  format useful for security update checks
    public function listPackages() {
        $installed = array();
        if($this->checkBranch()) {
            if($this->readDatabase()) {
                foreach($this->database as $vendor => $vendArray) {
                    foreach($vendArray as $package => $packArray) {
                        $version = $packArray['version'];
                        $version = preg_replace('/\./', '_', $version);
                        $security = $packArray['security'];
                        $tweak = $packArray['tweak'];
                        $installed[] = $version . '-' . $security . '-' . $tweak . '.' . $package . '.' . $vendor;
                    }
                }
            }
        }
        return $installed;
    }
}

?>