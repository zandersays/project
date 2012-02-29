<?php
$contact = Controller::getHtmlElement('Module:forms/forms/contact');

$contact
    ->set('view', 'Module:forms/dialogs/contact')
    ->set('function', 'contactDialog');

$contact->select('contactName')
    ->set('label', '')
    ->set('emptyValue', 'name');

$contact->select('contactEmail')
    ->set('label', '')
    ->set('emptyValue', 'e-mail address');

$contact->select('contactSubject')
    ->set('label', '')
    ->set('emptyValue', 'subject')
    ->set('width', '24em');

$contact->select('contactMessage')
    ->set('label', '')
    ->set('emptyValue', 'message')
    ->set('height', '8em');
?>