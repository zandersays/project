<?php
$contact = new Form('contact', array(
    'view' => 'Module:forms/forms/contact',
    'controller' => 'Module:forms/FormsForms',
    'function' => 'contact',
    'title' => '<h1>Contact</h1>',
));
$contact->add(array(
    new FormComponentSingleLineText('contactName', 'Name', array(
        'validationOptions' => array('required'),
    )),
    new FormComponentSingleLineText('contactEmail', 'E-mail', array(
        'validationOptions' => array('required', 'email'),
    )),
    new FormComponentSingleLineText('contactSubject', 'Subject', array(
        'validationOptions' => array('required'),
    )),
    new FormComponentTextArea('contactMessage', 'Message', array(
        'validationOptions' => array('required'),
    )),
));
?>