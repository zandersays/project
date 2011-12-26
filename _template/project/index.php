<?php
// Dynamically identify the Project instance and begin processing
require_once('settings.php');
$instanceFound = false;
foreach($settings['instances'] as $instance) {
    if(($_SERVER['HTTP_HOST'] == $instance['host'] || $_SERVER['SERVER_NAME'] == $instance['host']) && is_dir($instance['projectPath']) && file_exists($instance['projectPath'].'system/core/Project.php')) {
        $instanceFound = true;
        require_once($instance['projectPath'].'system/core/Project.php');
        break;
    }
}

// Handle misconfiguration
if(!$instanceFound) {
    echo '
        <h1>Misconfiguration</h1>
        <p>Current host: '.$_SERVER['HTTP_HOST'].'</p>
        <p>Server name: '.$_SERVER['SERVER_NAME'].'</p>
    ';
}
?>