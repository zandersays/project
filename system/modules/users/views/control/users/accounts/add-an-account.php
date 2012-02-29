<?php
require_once('system/core/Project.php');

$addAnAccount = new Form('addAnAccount', array(
    'action' => Project::getInstanceAccessPath().'project/control/users/accounts/add-an-account.php',
    'submitButtonText' => 'Add Account',
    'style' => 'width: 600px;',
));

$accountTypeSection = new FormSection('accountType', array(
    'description' => '
        <p>An account is a collection of users. After creating an account, you may specify an account owner and other account members.<p>
    ',
));
$accountTypeSection->addFormComponentArray(array(
    new FormComponentDropDown('accountType', 'Account type', array(
            array('label' => 'RentScore', 'value' => 'RentScore'),
            array('label' => 'Project', 'value' => 'Project'),
        ), array(
            'validationOptions' => array('required'),
        )
    ),
    new FormComponentSingleLineText('accountName', 'Account name:', array(
        'validationOptions' => array('required'),
        'width' => 'long',
    )),
));

$addAnAccount->addFormSection($accountTypeSection);

$addAnAccount->processRequest(true);

function onSubmit($formValues) {

    $response = array();
    $response['failureNoticeHtml'] = Json::encode($formValues);

    // Check to see if the account type name already exists (lower case comparison)

    // Provide a link to manage the accoutn type

    return $response;
}
?>