<?php
if(!isset($moduleKey)) {
    $moduleKey = '';
}

$installModule = new Form('installModule', array(
    'view' => 'Project:control/settings/modules/install-module',
    'controller' => 'Project:control/ModulesForms',
    'function' => 'installModule',
    'submitButtonText' => 'Confirm Installation',
    'style' => 'width: 600px;',
));

$installModule->addFormSection(
    new FormSection('installModuleSection', array(
        'description' => '
            <p>You have specified this module for installation:</p>
            <ul style="margin: 0 0 0 1.5em;">
                <li>'.$moduleKey.'</li>
            </ul>
        '
    ))
);

$installModule->addFormComponent(
    new FormComponentHidden('moduleKey', $moduleKey)
);
?>