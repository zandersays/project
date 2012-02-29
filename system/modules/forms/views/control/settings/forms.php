<?php
$forms = new Form('form', array(
    'view' => 'Module:forms/control/settings/forms',
    'controller' => 'FormsForms',
    'function' => 'dothis',
));
$forms->addFormComponent(
    new FormComponentSingleLineText('Testing', 'Test:', array(
        'initialValue' => 'pizza',
    ))
);
?>