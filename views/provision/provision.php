<?php
$provision = new Form('provision', array(
    'view' => 'Project:provision/provision',
    'controller' => 'Project:provision/ProvisionForms',
    'function' => 'provision',
    'title' => '<h1><img src="../project/images/control/project.png" alt="Project" ></h1>',
));

$provision->add(array(
    new FormComponentHtml('<h1>Provision a New Installation</h1>'),
    new FormComponentSingleLineText('instancePath', 'Instance path:', array(
        'validationOptions' => array('required'),
        'initialValue' => '/var/www/project.com/',
        'width' => 'longest',
    )),
    new FormComponentSingleLineText('instanceHost', 'Instance host:', array(
        'validationOptions' => array('required'),
        'initialValue' => 'dev.project.com',
        'width' => 'longest',
    )),
    new FormComponentSingleLineText('instanceAccessPath', 'Instance access path:', array(
        'initialValue' => '/',
        'validationOptions' => array('required'),
        'width' => 'longest',
    )),
    new FormComponentMultipleChoice('deleteInstancePath', '', array(
        array('label' => 'Delete the instance path before installation', 'value' => 'yes'),
    )),
));
?>