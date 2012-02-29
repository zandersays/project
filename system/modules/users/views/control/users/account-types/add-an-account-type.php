<?php
require_once('system/core/Project.php');

$addAnAccountType = new Form('addAnAccountType', array(
    'action' => Project::getInstanceAccessPath().'project/control/users/account-types/add-an-account-type.php',
    'submitButtonText' => 'Add Account Type',
    'style' => 'width: 600px;',
));

$accountTypeSection = new FormSection('accountType', array(
    'description' => '
        <p>Every account of users has an account type attribute. The account type describes what permissions are available to members of the account.</p>
        <p><img src="/project/images/control/account-type-example.gif" alt="Project Account Type Example" /></p>
        <p>You may create your own account types with specific permissions for the accounts on your site. We have a <a>tutorial</a> to show you how to use these permissions. On installation, Project automatically creates two account types for you, (1) the Project account type, and (2) an account type named after the Project instance.</p>        
    ',
));
$accountTypeSection->addFormComponentArray(array(
    new FormComponentSingleLineText('accountTypeName', 'Account Type Name:', array(
        'validationOptions' => array('required'),
    )),
    new FormComponentTextArea('accountTypeDescription', 'Account Type Description:', array(
        
    )),
));

$addAnAccountType->addFormSection($accountTypeSection);

$addAnAccountType->processRequest(true);

function onSubmit($formValues) {

    $response = array();
    $response['failureNoticeHtml'] = Json::encode($formValues);

    // Check to see if the account type name already exists (lower case comparison)

    // Provide a link to manage the accoutn type

    return $response;
}
?>