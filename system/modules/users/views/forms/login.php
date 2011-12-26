<?php
// Set the redirect
if(isset($_GET['redirect'])) {
    $redirect = Url::decode($_GET['redirect']);
}

$login = new Form('login', array(
    'view' => 'Module:users/forms/login',
    'controller' => 'Module:users/UsersForms',
    'function' => 'login',
    'submitButtonText' => 'Log In',
    'title' => '<h1>Log In</h1>',
));

$login->add(array(
    new FormComponentSingleLineText('loginIdentifier', 'Username or e-mail:', array(
        'validationOptions' => array('required'),
    )),
    new FormComponentSingleLineText('loginPassword', 'Password:', array(
        'validationOptions' => array('required', 'password'),
        'type' => 'password',
        'enterSubmits' => true,
    )),
    new FormComponentHidden('loginRedirect', isset($redirect) ? $redirect : null),
    new FormComponentMultipleChoice('loginRememberMe', '', array(
        array('label' => 'Remember me', 'value' => 'yes'),
    ), array(
        'style' => Module::isActive('Users') ? '' : 'display: none;',
    )),
));
?>