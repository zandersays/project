<?php
$setup = new Form('setup', array(
    'view' => 'Project:setup/setup',
    'controller' => 'Project:setup/SetupForms',
    'function' => 'setup',
    'title' => '<h1><img src="../project/images/control/project.png" alt="Project" ></h1>',
));

// Settings
$settingsSection = new FormSection('settings', array(
    'title' => '
        <h1>Setup Your New Installation</h1>
        <h2>General Information</h2>'
    ,
));
$settingsSection->add(array(
    new FormComponentSingleLineText('siteTitle', 'Site title:', array(
        'validationOptions' => array('required'),
        'initialValue' => 'Project',
        'width' => 'longest',
    )),
    new FormComponentMultipleChoice('instanceType', 'Instance type:', array(
        array('label' => 'Development', 'value' => 'Development', 'checked' => true),
        array('label' => 'Staging', 'value' => 'Staging'),
        array('label' => 'Production', 'value' => 'Production'),
    ), array(
        'validationOptions' => array('required'),
        'multipleChoiceType' => 'radio',
        'tip' => '<p>Often, web development involves development, staging, and production servers. The development server provides a safe environment to write new code. The staging server is used to test and review new code from the development server. After review, code is pushed from the staging server to the production server, which is publicly available to handle live requests.</p>'
    )),
));

// Administrator
$administratorSection = new FormSection('administrator', array(
    'title' => '<h2>Administrator Login</h2>',
));
$administratorSection->add(array(
    new FormComponentSingleLineText('administratorUsername', 'Administrator Username:', array(
        'validationOptions' => array('required', 'username'),
        'initialValue' => 'kirkouimet',
    )),
    new FormComponentSingleLineText('administratorEmail', 'Administrator E-mail:', array(
        'validationOptions' => array('required', 'email'),
        'width' => 'mediumLong',
        'initialValue' => 'kirk@kirkouimet.com',
    )),
    new FormComponentSingleLineText('administratorPassword', 'Administrator Password:', array(
        'validationOptions' => array('required', 'password'),
        'type' => 'password',
        'initialValue' => 'access',
    )),
));

$setup->add($settingsSection);
$setup->add($administratorSection);
?>