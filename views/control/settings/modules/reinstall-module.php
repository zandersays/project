<?php
if(!isset($moduleKey)) {
    $moduleKey = $_GET['moduleKey'];
}

$reinstallModule = new Form('reinstallModule', array(
    'view' => 'Project:control/settings/modules/reinstall-module',
    'controller' => 'Project:control/ModulesForms',
    'function' => 'reinstallModule',
    'submitButtonText' => 'Confirm Reinstallation',
    'style' => 'width: 600px;',
));

$reinstallModule->addFormSection(
    new FormSection('reinstallModuleSection', array(
        'description' => '
            <p>You have specified this module for reinstallation:</p>
            <ul style="margin: 0 0 0 1.5em;">
                <li>'.$moduleKey.'</li>
            </ul>
        '
    ))
);

$reinstallModule->addFormComponent(
    new FormComponentHidden('moduleKey', $moduleKey)
);
?>