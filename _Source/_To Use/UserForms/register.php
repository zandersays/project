
<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/php/site.php');

// Create the form
$registrationForm = new Form('registrationForm', array(
    'action' => str_replace($_SERVER['DOCUMENT_ROOT'], '', __FILE__),
    'submitButtonText' => 'Register',
));

// Create the form page
$page = new FormPage($registrationForm->id.'Page', array(
    'title' => '<h1>Register</h1>',
));

// Create the form section
$section = new FormSection($registrationForm->id.'Section', array(
));

// Add components to the section
$section->addFormComponentArray(array(

    new FormComponentName('name', 'Name:', array(
        'validationOptions' => array('required'),
    )),

    new FormComponentSingleLineText('username', 'Username:', array(
        'validationOptions' => array(
            'required',
            'username',
            'serverSide' => array('url' => 'http://'.$_SERVER['HTTP_HOST'].'/api/?api=user&apiUser=web&apiKey=web&command=checkUsernameAvailability', 'task' => 'checkUserNameAvailability'),
        ),
    )),

    new FormComponentSingleLineText('email', 'E-mail address:', array(
        'validationOptions' => array('required', 'email'),
        'width' => 'mediumLong',
    )),

    new FormComponentSingleLineText('password', 'Choose a password:', array(
        'type' => 'password',
        'validationOptions' => array('required', 'password'),
    )),

    new FormComponentSingleLineText('passwordConfirm', 'Confirm password:', array(
        'type' => 'password',
        'validationOptions' => array('required', 'matches' => 'password'),
    )),

));

// Add the section to the page
$page->addFormSection($section);

// Add the page to the form
$registrationForm->addFormPage($page);

// Set the function for a successful form submission
function onSubmit($formValues) {
    $response = array();
    
    // Set the form values
    $formValues = $formValues->registrationFormPage->registrationFormSection;

    $meta = array(
        'name' => $formValues->name,
    );

    $registration = $_SESSION['user']->register($formValues->username, $formValues->email, $formValues->password, $meta);

    if($registration['status'] == 'success') {
        $response['successPageHtml'] = $registration['response'];
    }
    else {
        //$response['failureHtml'] = $registration['response'];
        $response['failureHtml'] = json_encode($formValues);
    }

    return $response;
}

// Process any request to the form
$registrationForm->processRequest();
?>