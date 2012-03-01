<?php
$generateModelFiles = new Form('generateModelFile', array(
    'view' => 'Module:models/control/settings/models/generate-model-files',
    'viewData' => $data,
    'controller' => 'Module:models/ModelsForms',
    'function' => 'generateModelFiles',
    'submitButtonText' => 'Confirm Generation',
    'style' => 'width: 600px;',
));

$generateModelFiles->addFormComponent(
    new FormComponentHtml('
        <p>You have specified to generate all models files for this database:</p>
        <ul style="margin: 0 0 0 1.5em;">
        <li>'.$databaseName.'</li>
        </ul>
    ')
);

$generateModelFiles->addFormComponent(
    new FormComponentHidden('databaseName', $databaseName)
);
?>