<?php

/*
 *  An autoloading class for php-ccm MUST extend this class
 */

namespace CCM\Promises;

abstract class AutoloadPromise
{
    /* class properties */
    
    const APIMAJOR = 1;
    
    const APIMINOR = 0;
    
    const APIPOINT = 0;

    // Directory with re-usable classes managed by php-ccm project
    const CCMBASE = '/usr/share/ccm/';
 
    // branch search order within CCMBASE
    protected $ccmBranchOrder = array('local','stable');
    
    // this is only needed for caching the path
    protected $ccmBranchPathString = 'reset on branch order change';
 
    // The suffixes to look for with file names
    protected $suffixArray = array('.php', '.class.php', '.inc.php');
 
    // Where PEAR packages are usually installed
    protected $pearPathArray = array('/usr/share/ccm/pear', '/usr/share/pear', '/usr/share/php');


    /* class methods */

    public static function getPromiseAPI() {
        $string = self::APIMAJOR . '.' . self::APIMINOR . '.' . self::APIPOINT;
        return $string;
    }

    /* allows changing of the branch order for class file searching */
    public function changeDefaultSearchPath( string $string ) {
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
            $this->ccmBranchPathString = implode(':', $newpath);
            $this->ccmBranchOrder = $newpath;
        } else {
            error_log('Warning: The arguement ' . $string . ' is not a valid CCM path string.');
            return false;
        }
    }

    /* provide a version for the class extending this Promise */
    abstract public static function version();
 
    /* an array of files to be loaded relative to /usr/share/ccm/branch */
    abstract public function filelist( array $array );
 
    /* an array of classes and the files that contain them relative to /usr/share/ccm/branch */
    abstract public function classMap( array $array );
 
    /* A class to be searched for within branch/libraries and loaded when found */
    abstract public function loadClass( string $class );
 
    /* A PEAR class to be searched for and loaded when found. Must search first
       within phpinclude path and then through every directory in $pearPathArray */
    abstract public function pearClass( string $class );
 
    /* loads a class within the phpinclude path if the
       file name matches the class name */
    public function localSystemClass( string $class ) {
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
 
    /* This function must exist even if your implementation does not use caching */
    abstract public function setCacheKey( string $string );
 
    /* overloading */
    public function __call($name, $arguments) {
        error_log('Warning: Method ' . $name . ' is not defined in the ' . get_class($this) . ' class');
        return;
    }
}

?>