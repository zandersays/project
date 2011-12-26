<?php
$administrator = new Form('administrator', array(
    'view' => 'Project:control/settings/administrator',
    'controller' => 'Project:control/AdministratorForms',
    'function' => 'editAdministrator',
    'submitButtonText' => 'Save Changes',
    'style' => 'width: 600px;',
));

$administrator->addFormComponentArray(array(
    new FormComponentHtml('<p style="margin-top: 4px;"><a class="buttonLink lock" href="change-password/">Change Password</a></p>'),
    new FormComponentSingleLineText('username', 'Username:', array(
        'validationOptions' => array('required'),
        'initialValue' => Project::getAdministratorUsername(),
    )),
    new FormComponentSingleLineText('email', 'E-mail address:', array(
        'validationOptions' => array('required'),
        'initialValue' => Project::getAdministratorEmail(),
    )),
));
?>