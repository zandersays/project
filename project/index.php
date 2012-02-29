<?php
// get the settings
global $settings;
require_once('settings.php');

// get the instance
global $instance;
$instance = $settings['instances'][0];

// start project
chdir(dirname(__FILE__));
require_once('../system/core/Project.php');
?>