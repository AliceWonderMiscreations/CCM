<?php
/*
 * example application autoload.php script
 */

$branchpath = 'stable';

require('/usr/share/ccm/ClassLoader.php');
$CCM = new \CCM\ClassLoader();
$CCM->changeDefaultSearchPath($branchpath);

// uncomment below to enable APCu cache of paths
// $CCM->setCacheKey('whatever');

spl_autoload_register(function ($class) {
  global $CCM;
  $CCM->loadClass($class);
});

spl_autoload_register(function ($class) {
  global $CCM;
  $CCM->pearClass($class);
});
?>
