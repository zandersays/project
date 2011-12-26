<?php
$register = new Form('register', array(
    'view' => 'Module:users/forms/register',
    'controller' => 'Module:users/UsersForms',
    'function' => 'register',
    'submitButtonText' => 'Register',
    'title' => '<h1>Register</h1>',
    'onSubmitStartClientSide' => "$('#registerReformedPassword').val(Security.hexSha512($('#registerPassword').val()));",
));

$register->addFormComponentArray(array(
    new FormComponentSingleLineText('registerUsername', 'Username:', array(
        'validationOptions' => array('required', 'username'),
    )),
    new FormComponentSingleLineText('registerEmail', 'E-mail address:', array(
        'validationOptions' => array('required', 'email'),
    )),
    new FormComponentSingleLineText('registerPassword', 'Password:', array(
        'validationOptions' => array('required', 'password'),
        'type' => 'password',
    )),
    new FormComponentSingleLineText('registerConfirmPassword', 'Confirm password:', array(
        'validationOptions' => array('required', 'password', 'matches' => 'registerPassword'),
        'type' => 'password',
        'enterSubmits' => true,
    )),
    new FormComponentHidden('registerReformedPassword', ''), 
));
?>