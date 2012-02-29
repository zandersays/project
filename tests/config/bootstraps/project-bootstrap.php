<?php

/**
 * Sets up a Project environment so that
 * unit-tests can be ran.
 *
 * @author Kam Sheffield
 * @version 09/28/2011
 */

$workingdir = getcwd();

// see if a host name was passed in
if(!isset($hostname)) {
    global $hostname;
    $hostname = gethostname();
    if($hostname === false) {
        $hostname = 'localhost';
    }        
}

// set the host name and server appropriatly
$_SERVER['HTTP_HOST'] = $hostname;
$_SERVER['SERVER_NAME'] = $hostname;

chdir(dirname(__FILE__));

// check to see if the settings have already been loaded
// if the call came from an external project then the 
// settings should already be loaded
if(!isset($settings)) {        
    global $settings;
    require_once('../../../project/settings.php');    
}

// if an instance is not already set then choose
// the default one and set it, this allows external 
// tests to explicitly set their instance if desired
if(!isset($instance)) {
    global $instance;
    $instance = $settings['instances'][0];    
}

// include project
require_once('../../../system/core/Project.php');

// reset the working dir
chdir($workingdir);

// start project
Project::singleton();
Project::loadSettings();
Project::loadModules();

// load any extra modules that may have been specified
if(isset($modules)) {    
    foreach($modules as $module) {
        Project::loadModule($module);
    }
}

// make a test temp folder in the instance path tests folder
$testFolder = $instance['path'].'/tests/temp';
if(!file_exists($testFolder)) {
    mkdir($testFolder, 0777);
}
 
?>
