<?php
$login = Controller::getVariable('Module:users/forms/login');

$login
    ->set('view', 'Module:users/dialogs/login')
    ->set('function', 'loginDialog');
?>