<?php

/* Do not use - not all functions have been tested
     and I have modified some since last time I tested them */

/*
 +-----------------------------------------------------------------------+
 |                                                                       |
 | Copyright (c) 2018 Alice Wonder Miscreations                          |
 |  May be used under terms of MIT license                               |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Purpose: Autoload PHP class files                                     |
 +-----------------------------------------------------------------------+
*/

/* I attempt to *mostly* comply with PSR-2 
     but this class is NOT intended to loaded with an auto-loader, it
     is an auto-loader, so it is not installed in a directory
     structure for easy PSR-4 compliance */

namespace AliceWonderMiscreations\CCM;

class ClassLoader
{
    /* properties */
  
    // Directory with re-usable classes managed by this project
    protected $ccmBase = '/usr/share/ccm/';
    // The suffixes to look for with file names
    protected $suffixArray = array('.php', '.class.php', '.inc.php');
    // Where PEAR packages are usually installed
    protected $pearPathArray = array('/usr/share/ccm/pear', '/usr/local/share/pear/', '/usr/share/pear/');
    // Array for mapping class names to non-standard file paths
    protected $classMap = array();
    // branch search order within $ccmBase
    public $ccmBranchOrder = array('local','stable');
    // cache the path?
    protected $cachePath = false;
    // key for cache
    protected $cacheKey = '';
    // the version number
    private $vversion = '0.0.0';

    /* methods */
    
    /* return the version */
    public function version() {
        return $this->vversion;
    }
    
    // If caching is enabled and file location of class is
    //  cached, loads from file rather than searching for file
    protected function cacheCheck($class) {
      if($this->cachePath) {
        $string = $this->cacheKey . $class;
        $hkey = hash('ripemd160', $string);
        $hkey = substr($hkey, 5, 14);
        if($filename = apcu_fetch($hkey)) {
          if(file_exists($filename)) {
            require_once($filename);
            return true;
          }
        }
      }
      return false;
    }
    
    // If caching is enabled, caches the location on the
    //  filesystem associated with the class
    protected function wrapRequire($class, $filename) {
      if($this->cachePath) {
        $string = $this->cacheKey . $class;
        $hkey = hash('ripemd160', $string);
        $hkey = substr($hkey, 5, 14);
        // cache for about three hours
        //  randomizes cache time to spread out needed
        //  reloads as cache expires. Overthinking things??
        $cacheTime = 10800 + rand(0,1800);
        apcu_store($hkey, $filename, $cacheTime);
      }
      require_once($filename);
    }

    /* Loads a file using full path so phpinclude directory is not needed.
       Loads withing the /usr/share/ccm root according to specified branch
       unless second argument is set to true */
    protected function loadFile($path, $class='', $full = false) {
        if(substr($path, 0, 1) !== "/") {
            $path = "/" . $path;
        }
        foreach($this->ccmBranchOrder as $branch) {
            if($full) {
                $fullpath = $path;
            } else {
                $fullpath = $this->ccmBase . $branch . $path;
            }
            if(file_exists($fullpath)) {
                if(strlen($class) > 0) {
                    $this->wrapRequire($class, $fullpath);
                } else {
                    require_once($fullpath);
                }
                return;
            }
        }
        return false;
    }
  
    /* allows changing of the branch order for class file searching */
    public function changeDefaultSearchPath($string) {
        $arr = explode(':', $string);
        $newpath = array();
        foreach($arr as $branch) {
            $branch = trim(strtolower($branch));
            if(in_array($branch, array ('local', 'devel', 'stable', 'custom'))) {
                if(! in_array($branch, $newpath)) {
                    $newpath[] = $branch;
                }
            }
        }
        if(count($newpath) === count($arr)) {
            $this->ccmBranchOrder = $newpath;
        } else {
            return false;
        }
    }

    /* takes an array of files to be loaded and attempts to load them */
    public function filelist($arr) {
        foreach($arr as $path) {
            $this->loadFile($path);
        }
    }

    /* takes an array of classes where file name does not match the
       class name so the autoloader knows how to find them */
    public function classMap($arr) {
        foreach($arr as $key) {
            $value = $arr[$key];
            if(! array_key_exists($key, $this->classMap)) {
                $this->classMap[$key] = $value;
            }
        }
    }
  
    /* loads a library class within the ccm root */
    public function loadClass($class) {
        if($this->cacheCheck($class)) {
            return;
        }
        if(array_key_exists($class, $this->classMap)) {
            $path = $this->classMap($class);
            if(loadFile($path)) {
                return;
            }
        }
        $arr = explode("\\", $class);
        $j = count($arr);
        if($j < 3) {
            if($j === 2) {
                $arr[] = $arr[1];
            } else {
                return;
            }
        }
        $arr[0] = strtolower($arr[0]);
        $arr[1] = strtolower($arr[1]);
        $subpath = '/libraries/' . implode('/', $arr);

        foreach($this->suffixArray as $suffix) {
            $path = $subpath . $suffix;
            if($this->loadFile($path, $class)) {
                return;
            }
        }
    }

    /* loads a PEAR class */
    public function pearClass($class) {
        if(strpos($class, '.') === false) {
            $file = str_replace('_', '/', $class).'.php';
            if ($path = stream_resolve_include_path($file)) {
                require_once($path);
            } else {
                foreach($this->pearPathArray as $prefix) {
                    $fullpath = $prefix . $file;
                    if($this->loadFile($fullpath, $class, true)) {
                        return;
                    }
                }
            }
        }
    }

    /* loads a class within the phpinclude path if the
       file name matches the class name */
    public function localSystemClass($class) {
        $arr = explode("\\", $class);
        $class = end($arr);
        foreach($this->suffixArray as $suffix) {
            $file = $class . $suffix;
            if ($path = stream_resolve_include_path($file)) {
                require_once($path);
                return;
            }
        }
    }

    public function __construct($string="") {
        if (extension_loaded('apcu') && ini_get('apc.enabled')) {
            if(strlen($string) > 0) {
                $this->cachePath = true;
                $this->cacheKey = hash('ripemd160', $string);
            }
        }
    }
}
?>
