<?php
$register = Controller::getHtmlElement('Module:users/forms/register');

$register
    ->set('view', 'Module:users/dialogs/register')
    ->set('function', 'registerDialog');
?>