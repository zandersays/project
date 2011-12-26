<?php
if(!isset($instanceId)) {
    $instanceId = $_GET['instanceId'];
}

$instance = Project::getInstance($instanceId);

$editInstance = new Form('addAInstance', array(
    'view' => 'Project:control/settings/instances/edit-instance',
    'controller' => 'Project:control/InstancesForms',
    'function' => 'editInstance',
    'submitButtonText' => 'Save Changes',
    'style' => 'width: 600px;',
));

$editInstanceInstanceSection = new FormSection('instance', array(
    'title' => '<h2 style="display: inline;"></h2> <p style="display: inline;"><a class="buttonLink minusSquareGrey" href="../../delete-instances/instanceId:'.$instanceId.'/">Delete Instance</a><p>',
));
$editInstanceInstanceSection->addFormComponentArray(array(
    new FormComponentSingleLineText('instanceId', 'Instance ID:', array(
        'validationOptions' => array('required'),
        'width' => 'long',
        'initialValue' => $instance['id'],
    )),
    new FormComponentMultipleChoice('instanceType', 'Instance type:', array(
        array('label' => 'Development', 'value' => 'Development'),
        array('label' => 'Staging', 'value' => 'Staging'),
        array('label' => 'Production', 'value' => 'Production'),
    ), array(
        'validationOptions' => array('required'),
        'multipleChoiceType' => 'radio',
        'tip' => '<p>Often, web development involves development, staging, and production servers. The development server provides a safe environment to write new code. The staging server is used to test and review new code from the development server. After review, code is pushed from the staging server to the production server, which is publicly available to handle live requests.</p>',
        'initialValue' => $instance['type'],
    )),
    new FormComponentSingleLineText('projectPath', 'Project path:', array(
        'validationOptions' => array('required'),
        'width' => 'mediumLong',
        'initialValue' => $instance['projectPath'],
    )),
    new FormComponentSingleLineText('instancePath', 'Instance path:', array(
        'validationOptions' => array('required'),
        'width' => 'mediumLong',
        'initialValue' => $instance['path'],
    )),
    new FormComponentSingleLineText('instanceHost', 'Instance host:', array(
        'validationOptions' => array('required'),
        'width' => 'mediumLong',
        'initialValue' => $instance['host'],
    )),
    new FormComponentSingleLineText('instanceAccessPath', 'Instance access path:', array(
        'validationOptions' => array('required'),
        'width' => 'mediumLong',
        'initialValue' => $instance['accessPath'],
    )),
));

$editInstance->addFormSection($editInstanceInstanceSection);
?>