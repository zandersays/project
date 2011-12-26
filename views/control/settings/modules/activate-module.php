<?php
if(!isset($moduleKey)) {
    $moduleKey = $_GET['moduleKey'];
}

$activateModule = new Form('activateModule', array(
    'view' => 'Project:control/settings/modules/activate-module',
    'controller' => 'Project:control/ModulesForms',
    'function' => 'activateModule',
    'submitButtonText' => 'Confirm Activation',
    'style' => 'width: 600px;',
));

$activateModule->addFormSection(
    new FormSection('activateModuleSection', array(
        'description' => '
            <p>You have specified this module for activation:</p>
            <ul style="margin: 0 0 0 1.5em;">
                <li>'.$moduleKey.'</li>
            </ul>
        '
    ))
);

$activateModule->addFormComponent(
    new FormComponentHidden('moduleKey', $moduleKey)
);
?>