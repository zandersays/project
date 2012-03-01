<?php

/**
 * Generic bootstrap for Phramwrk projects.
 *
 * @author Kam Sheffield
 * @version 09/27/2011
 */

global $hostname;
$hostname = 'put your host name here';

// get the settings for this instance
global $settings;
require_once('../project/settings.php');

// figure out where project is at
$projectPath = $settings['instances'][0]['projectPath'];

// run the generic bootstrap
require_once($projectPath.'tests/config/bootstraps/project-bootstrap.php');

?>
