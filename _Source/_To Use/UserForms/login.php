<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/php/site.php');

// Set the subtitle
if(isset($loginRequired) && $loginRequired == true && !$_SESSION['user']->loggedIn) {
    $loginFormTitle = '<h1>You Must Login to Access This Page</h1>';
}
else {
    $loginFormTitle = '<h1>Login</h1>';
}

// Create the form
$loginForm = new Form('loginForm', array(
    'submitButtonText' => 'Login',
    'title' => $loginFormTitle,
    'onSubmitFunctionServerSide' => 'onSubmitLoginForm',
    'onSubmitStartClientSide' => "
        $('#md5PasswordAndChapChallenge').val(utility.md5(utility.md5($('#password').val()) + $('#chapChallenge').val()));
        $('#chapChallenge').val('');
        var newPassword = '';
        var newPasswordLength = $('#password').val().length;
        for(var i = 0; i < newPasswordLength; i++) {
            newPassword += '0';
        }
        $('#password').val(newPassword);
    ",
));

// Check to see if the remember me checkbox should be checked by default
$rememberMe = array('value' => 'remember', 'label' => 'Remember me.');
if(isset($_COOKIE['passphrase'])) {
    $rememberMe['checked'] = true;
}

// Set the redirect URL
if(!isset($redirectUrl)) {
    $redirectUrl = '/';
}

// Create the form page
$page = new FormPage($loginForm->id.'Page', array(
    'submitInstructions' => '<p><a href="/user/register/">Need an account?</a></p>',
));

// Create the form section
$section = new FormSection($loginForm->id.'Section', array());

// Add components to the section
$section->addFormComponentArray(array(
    new FormComponentHidden('redirectUrl', $redirectUrl),
    new FormComponentHidden('chapChallenge', $_SESSION['user']->getChapChallenge()),
    new FormComponentHidden('md5PasswordAndChapChallenge', ''),
    new FormComponentSingleLineText('username', 'Username or e-mail:', array(
        'validationOptions' => array('required'),
    )),
    new FormComponentSingleLineText('password', 'Password:', array(
        'type' => 'password',
        'validationOptions' => array('required', 'password'),
    )),
    new FormComponentMultipleChoice('rememberMe', '', array($rememberMe), array(
    )),
));

// Add the section to the page
$page->addFormSection($section);

// Add the page to the form
$loginForm->addFormPage($page);

// Set the function for a successful form submission
function onSubmitLoginForm($formValues) {
    $formValues = $formValues->loginFormPage->loginFormSection;

    // Check to see if they want to be remembered
    if(!empty($formValues->rememberMe)) {
        $rememberLoginWithCookies = true;
    }
    else {
        $rememberLoginWithCookies = false;
    }

    // Attempt the login
    $login = $_SESSION['user']->login($formValues->username, $formValues->md5PasswordAndChapChallenge, $rememberLoginWithCookies);

    // Handle the login response
    if($login['status'] == 'success') {
        //$response['failureHtml'] = json_encode($formValues);
        $response['redirect'] = $formValues->redirectUrl;
    }
    else {
        $response['failureNoticeHtml'] = $login['response'];
        $response['failureJs'] = "
            $('#password').val('').focus();
        ";
    }

    return $response;
}

// Process any request to the form
$loginForm->processRequest();
?>