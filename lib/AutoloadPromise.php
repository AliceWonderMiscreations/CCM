<?php

/*
 *  An autoloading class for php-ccm MUST extend this class
 */

namespace AliceWonderMiscreations\CCM;

abstract class AutoloadPromise
{
    /* properties */
    // Directory with re-usable classes managed by php-ccm project
    protected $ccmBase = '/usr/share/ccm/';
    
    // branch search order within $ccmBase
    protected $ccmBranchOrder = array('local','stable');
    
    // The suffixes to look for with file names
    protected $suffixArray = array('.php', '.class.php', '.inc.php');
    
    // Where PEAR packages are usually installed
    protected $pearPathArray = array('/usr/share/ccm/pear', '/usr/local/share/pear/', '/usr/share/pear/');


    /* methods */

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
            throw new \Exception('The arguement ' . $string . ' is not a valid path string.');
            return false;
        }
    }
    
    /* an array of files to be loaded relative to branch/libraries */
    abstract public function filelist( array $array );
    
    /* an array of classes and the files that contain them relative to branch/libraries */
    abstract public function classMap( array $array );
    
    /* A class to be searched for within branch/libraries and loaded when found */
    abstract public function loadClass( string $class );
    
    /* A PEAR class to be searched for and loaded when found. Must search first
       within phpinclude path and then through every directory in $pearPathArray */
    abstract public function pearClass( string $class );
    
    /* searches for a class within the phpinclude path */
    abstract public function localSystemClass( string $class );
    
    /* This function must exist even if your implementation does not use caching */
    abstract public function setCacheKey( string $string );
    
    /* overloading */
    public function __call($name, $arguments) {
        throw new \Exception('Method ' . $name . ' is not defined in the ' . get_class($this) . ' class');
        return;
    }
}

?>