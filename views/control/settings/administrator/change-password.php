<?php
$changePassword = new Form('changePassword', array(
    'view' => 'Project:control/settings/administrator/change-password',
    'controller' => 'Project:control/AdministratorForms',
    'function' => 'changeAdministratorPassword',
    'submitButtonText' => 'Change Password',
    'style' => 'width: 600px;',
));

$changePassword->addFormComponentArray(array(
    new FormComponentSingleLineText('currentPassword', 'Current password:', array(
        'validationOptions' => array('required', 'password'),
        'type' => 'password',
    )),
    new FormComponentSingleLineText('password', 'New password:', array(
        'validationOptions' => array('required', 'password'),
        'type' => 'password',
    )),
    new FormComponentSingleLineText('confirmPassword', 'Confirm new password:', array(
        'validationOptions' => array('required', 'password', 'matches' => 'password'),
        'type' => 'password',
    )),
));
?>