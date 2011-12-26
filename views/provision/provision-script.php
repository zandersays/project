<?php
require_once('system/core/Project.php');

if(!isset($_GET['instancePath'])) {
    echo 'FAILURE.'."\n".'FATAL ERROR: instancePath GET variable not set.'."\n";
    exit();
}

if(!isset($_GET['instanceHost'])) {
    echo 'FAILURE.'."\n".'FATAL ERROR: instanceHost GET variable not set.'."\n";
    exit();
}

// Get the site directory
$instancePath = $_GET['instancePath'];
if(!String::endsWith('/', $instancePath)) {
    $instancePath = $instancePath.'/';
}

// Remove this! Testing only - this will remove the instancePath directory
Dir::delete($instancePath);

// Create the directory
echo 'Creating '.$instancePath.' ...';

// Check if the directory already exists
if(Dir::exists($instancePath)) {
    echo ' FAILURE.'."\n".'FATAL ERROR: '.$instancePath.' already exists.'."\n";
    exit();
}
else {
    // Create the directory
    Dir::create($instancePath);

    // Set the permissions on the new directory
    Dir::chmod($instancePath, 0777);

    // Make sure the directory was created
    if(!Dir::exists($instancePath)) {
        echo ' FAILURE.'."\n".'FATAL ERROR: '.$instancePath.' could not be created. Please check your file permissions.'."\n";
        exit();
    }
    else if(Dir::chmod($instancePath) != 16895) {
        echo ' FAILURE.'."\n".'FATAL ERROR: '.$instancePath.' was created successfully but the appropriate permissions could not be set.'."\n";
        exit();
    }
    else {
        echo ' SUCCESS.'."\n";
    }
}

// Copy the templated source files to the directory
echo 'Copying files ...';
Dir::copy('template', $instancePath);
echo ' SUCCESS.'."\n";

// Adjust the htaccess file
echo 'Adjusting .htaccess for instance access path...';
if(isset($_GET['instanceAccessPath'])) {
    if(String::endsWith('/', $_GET['instanceAccessPath'])) {
        $instanceAccessPath = String::stripTrailingCharacters(1, $_GET['instanceAccessPath']);
    }
    else {
        $instanceAccessPath = $_GET['instanceAccessPath'].'/';
    }

    if(String::startsWith('/', $instanceAccessPath)) {
        $instanceAccessPath = String::replace('/', '', $instanceAccessPath, 1);
    }
}
else {
    $instanceAccessPath = '';
}
$htaccess = File::content($instancePath.'.htaccess');
$htaccess = String::replace('[instanceAccessPath]', $instanceAccessPath, $htaccess);
File::write($instancePath.'.htaccess', $htaccess);
echo ' SUCCESS.'."\n";

$instanceAccessPath = '/'.$instanceAccessPath.'/';
$instanceAccessPath = String::replace('//', '/', $instanceAccessPath);

// Adjust the settings file
echo 'Adjusting settings for instance...';
include($instancePath.'project/settings.php');
$settings = Json::decode($settings);
//print_r($settings);
$settings->instances[0]->projectPath = getcwd().'/';
$settings->instances[0]->path = $instancePath;
$settings->instances[0]->accessPath = $instanceAccessPath;
$settings->instances[0]->host = $_GET['instanceHost'];
//print_r($settings);
File::write($instancePath.'project/settings.php', '<?php
$settings = \'
'.String::trim(Json::indent(Json::encode($settings))).'
\';
?>'
);
echo ' SUCCESS.'."\n";

echo "\n".'Project instance successfully provisioned at '.$instancePath.'.'."\n".'Setup a virtual host and visit the base directory of your site to customize your installation.';
?>