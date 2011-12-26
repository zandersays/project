<?php
if(!isset($instanceId)) {
    $instanceId = $_GET['instanceId'];
}

$deleteInstances = new Form('deleteInstances', array(
    'view' => 'Project:control/settings/instances/delete-instances',
    'controller' => 'Project:control/InstancesForms',
    'function' => 'deleteInstances',
    'submitButtonText' => 'Confirm Deletion',
    'style' => 'width: 600px;',
));

$deleteInstances->addFormSection(
    new FormSection('deleteInstancesSection', array(
        'description' => '
            <p>You have specified these instances for deletion:</p>
            <ul style="margin: 0 0 0 1.5em;">
                <li>'.$instanceId.'</li>
            </ul>
        '
    ))
);

$deleteInstances->addFormComponent(
    new FormComponentHidden('instanceId', $instanceId)
);
?>