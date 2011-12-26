<?php
if(!isset($databaseName)) {
    $databaseName = $_GET['databaseName'];
    $modelName = $_GET['modelName'];
}

$deleteModelFile = new Form('deleteModelFile', array(
    'view' => 'Module:models/control/settings/models/delete-model-file',
    'controller' => 'Module:models/ModelsForms',
    'function' => 'deleteModelFile',
    'submitButtonText' => 'Confirm Deletion',
    'style' => 'width: 600px;',
));

$deleteModelFile->addFormSection(
    new FormSection('deleteClassFileSection', array(
        'description' => '
            <p>You have specified these models for class file deletion:</p>
            <ul style="margin: 0 0 0 1.5em;">
                <li>'.$modelName.'</li>
            </ul>
        '
    ))
);

$deleteModelFile->addFormComponent(
    new FormComponentHidden('databaseName', $databaseName)
);
$deleteModelFile->addFormComponent(
    new FormComponentHidden('modelName', $modelName)
);
?>