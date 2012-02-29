<?php
$addAnInstance = new Form('addAnInstance', array(
    'view' => 'Project:control/settings/instances/add-an-instance',
    'controller' => 'Project:control/InstancesForms',
    'function' => 'addAnInstance',
    'submitButtonText' => 'Add Instance',
    'style' => 'width: 600px;',
));

$addAnInstanceSection = new FormSection('instance', array(
    'description' => '
    ',
));
$addAnInstanceSection->addFormComponentArray(array(
    new FormComponentSingleLineText('instanceId', 'Instance ID:', array(
        'validationOptions' => array('required'),
        'width' => 'long',
    )),
    new FormComponentMultipleChoice('instanceType', 'Instance type:', array(
        array('label' => 'Development', 'value' => 'Development'),
        array('label' => 'Staging', 'value' => 'Staging'),
        array('label' => 'Production', 'value' => 'Production'),
    ), array(
        'validationOptions' => array('required'),
        'multipleChoiceType' => 'radio',
        'tip' => '<p>Often, web development involves development, staging, and production servers. The development server provides a safe environment to write new code. The staging server is used to test and review new code from the development server. After review, code is pushed from the staging server to the production server, which is publicly available to handle live requests.</p>'
    )),
    new FormComponentSingleLineText('projectPath', 'Project path:', array(
        'validationOptions' => array('required'),
        'initialValue' => Project::getProjectPath(),
        'width' => 'mediumLong',
    )),
    new FormComponentSingleLineText('instancePath', 'Instance path:', array(
        'validationOptions' => array('required'),
        'initialValue' => Project::getInstancePath(),
        'width' => 'mediumLong',
    )),
    new FormComponentSingleLineText('instanceHost', 'Instance host:', array(
        'validationOptions' => array('required'),
        'initialValue' => $_SERVER['HTTP_HOST'],
        'width' => 'mediumLong',
    )),
    new FormComponentSingleLineText('instanceAccessPath', 'Instance access path:', array(
        'validationOptions' => array('required'),
        'initialValue' => Project::getInstanceAccessPath(),
        'width' => 'mediumLong',
    )),
));

$addAnInstance->addFormSection($addAnInstanceSection);
?>