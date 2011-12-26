<?php
if(!isset($moduleKey)) {
    $moduleKey = $_GET['moduleKey'];
}

$deactivateModule = new Form('deactivateModule', array(
    'view' => 'Project:control/settings/modules/deactivate-module',
    'controller' => 'Project:control/ModulesForms',
    'function' => 'deactivateModule',
    'submitButtonText' => 'Confirm Deactivation',
    'style' => 'width: 600px;',
));

$deactivateModule->addFormSection(
    new FormSection('deactivateModuleSection', array(
        'description' => '
            <p>You have specified this module for deactivation:</p>
            <ul style="margin: 0 0 0 1.5em;">
                <li>'.$moduleKey.'</li>
            </ul>
        '
    ))
);

$deactivateModule->addFormComponent(
    new FormComponentHidden('moduleKey', $moduleKey)
);
?>