<?php
if(!isset($moduleKey)) {
    $moduleKey = '';
}

$uninstallModule = new Form('uninstallModule', array(
    'view' => 'Project:control/settings/modules/uninstall-module',
    'controller' => 'Project:control/ModulesForms',
    'function' => 'uninstallModule',
    'submitButtonText' => 'Confirm Uninstallation',
    'style' => 'width: 600px;',
));

$uninstallModule->addFormSection(
    new FormSection('uninstallModuleSection', array(
        'description' => '
            <p>You have specified this module for uninstallation:</p>
            <ul style="margin: 0 0 0 1.5em;">
                <li>'.$moduleKey.'</li>
            </ul>
        '
    ))
);

$uninstallModule->addFormComponent(
    new FormComponentHidden('moduleKey', $moduleKey)
);
?>