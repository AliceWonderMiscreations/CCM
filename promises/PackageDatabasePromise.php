<?php

/* Not fully tested */

/* classes that extend this promise should
   set the branch and dbfile in the constructor.
   They should not change any of the methods except
   to fix a bug in the promise if there are any */

namespace CCM\Promises;

abstract class PackageDatabasePromise
{

    /* class properties */
    
    const APIMAJOR = 0;
    
    const APIMINOR = 0;
    
    const APIPOINT = 0;
    
    // Directory with the JSON database files
    const DBDIR = '/usr/share/ccm/jsondb/';
    //const DBDIR = '/home/alice/jsondb/';
    
    protected $lockkey = '';
    protected $branch = 'fubar';
    protected $dbfile = '';
    protected $database = array();

    
    /* class methods */
    
    public static function getPromiseAPI() {
        $string = self::APIMAJOR . '.' . self::APIMINOR . '.' . self::APIPOINT;
        return $string;
    }
    
    protected function err( string $message ) {
        echo "Error: " . $message . "\n";
    }
    
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
    
    protected function uniqueTouch( $file ) {
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
        //var_dump($this->lockkey);
        if( ! $handle = @fopen($file, 'w')) {
            $this->err("Could not open " . $file . " for writing.");
            return false;
        }
        fwrite($handle, $this->lockkey);
        fclose($handle);
        return $this->readTouch($file);
    }
    
    protected function createLockFile() {
        $count = 0;
        $lock = self::DBDIR . $this->branch . '.dblock';
        while(true) {
            if($this->uniqueTouch($lock)) {
                return true;
            }
            $count++;
            sleep(1);
            if($count > 0) {
                $this->err("Can not create lock file needed to modify database.");
                return false;
            }
        }
    }
    
    protected function deleteLockFile() {
        $lock = self::DBDIR . $this->branch . '.dblock';
        if(file_exists($lock)) {
            @unlink($lock);
        }
    }
    
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
    
    protected function writeDatabase () {
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
    
    public function addPackage ( string $vendor, string $package, string $version, int $securityv, int $tweakv ) {
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
    
    public function delPackage ( string $vendor, string $package ) {
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
        return $installed;
    }
}

?>