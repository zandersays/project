<?php
if(!isset($databaseName)) {
    $databaseName = $_GET['databaseName'];
    $modelName = $_GET['modelName'];
}

$generateModelFile = new Form('generateModelFile', array(
    'view' => 'Module:models/control/settings/models/generate-model-file',
    'controller' => 'Module:models/ModelsForms',
    'function' => 'generateModelFile',
    'submitButtonText' => 'Confirm Generation',
    'style' => 'width: 600px;',
));

$generateModelFile->addFormSection(
    new FormSection('generateClassFileSection', array(
        'description' => '
            <p>You have specified these models for class file generation:</p>
            <ul style="margin: 0 0 0 1.5em;">
                <li>'.$modelName.'</li>
            </ul>
        '
    ))
);

$generateModelFile->addFormComponent(
    new FormComponentHidden('databaseName', $databaseName)
);
$generateModelFile->addFormComponent(
    new FormComponentHidden('modelName', $modelName)
);
?>