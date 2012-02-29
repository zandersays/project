<?php
$addAUser = new Form('addAUser', array(
    'view' => 'Module:users/control/users/users/add-a-user',
    'controller' => 'Module:users/UsersForms',
    'function' => 'addAUser',
    'submitButtonText' => 'Add a User',
    'style' => 'width: 600px;',
    'onSubmitStartClientSide' => "$('#reformedPassword').val(Security.hexSha512($('#password').val())); var emptyPassword = ''; var passwordLength = $('#password').val().length; while(emptyPassword.length < passwordLength) emptyPassword += '*'; $('#password').val(emptyPassword);",
));

$addAUser->addFormComponentArray(array(
    new FormComponentHtml('
        <p>A user is composed of a username, password, and one or more e-mail addresses. Users may login using either their username or any of their e-mail addresses.<p>
        <p>For clarification, in Project <b>accounts are not users</b>. Accounts are groups of users. Users may be associated to as many accounts as desired.</p>
    '),
    new FormComponentSingleLineText('username', 'Username:', array(
        'validationOptions' => array('required', 'username'),
    )),
    new FormComponentDropDown('status', 'Status:', array(
        array('label' => 'Active', 'value' => 'active'),
        array('label' => 'Unverified', 'value' => 'unverified'),
    ), array(
        'validationOptions' => array('required'),
    )),
    new FormComponentSingleLineText('password', 'Password:', array(
        'validationOptions' => array('required', 'password'),
        'type' => 'password',
    )),
    new FormComponentHidden('reformedPassword', ''),
    new FormComponentSingleLineText('email', 'E-mail address:', array(
        'validationOptions' => array('required', 'email'),
    )),
    /*
    new FormComponentMultipleChoice('notifyByEmail', '', array(
        array('label' => 'Notify user via e-mail?', 'value' => 'yes'),
    )),
    new FormComponentTextArea('notifyByEmailMessage', 'Custom message:', array(
        'width' => 'long',
        'height' => 'short',
        'dependencyOptions' => array(
            'display' => 'hide',
            'jsFunction' => "$('#notifyByEmail-choice1').is(':checked');",
            'dependentOn' => 'notifyByEmail',
        ),
        'initialValue' => '',
    )),
    */
));
?>