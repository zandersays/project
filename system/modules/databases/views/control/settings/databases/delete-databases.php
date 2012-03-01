<?php
$deleteDatabases = new Form('deleteDatabases', array(
    'view' => 'Module:databases/control/settings/databases/delete-databases',
    'viewData' => $data,
    'controller' => 'Module:databases/DatabasesForms',
    'function' => 'deleteDatabases',
    'submitButtonText' => 'Confirm Deletion',
    'style' => 'width: 600px;',
));

$databases = Project::getModuleSettings('Databases');

$deleteDatabases->addFormSection(
    new FormSection('deleteDatabasesSection', array(
        'description' => '
            <p>You have specified these databases for deletion:</p>
            <ul style="margin: 0 0 0 1.5em;">
                <li>'.$databases['databases'][$databaseIndex]['name'].'</li>
            </ul>
        '
    ))
);

$deleteDatabases->addFormComponent(
    new FormComponentHidden('databaseIndex', $databaseIndex)
);
?>