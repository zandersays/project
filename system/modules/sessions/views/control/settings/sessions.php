<?php
$sessionsSettings = Project::getModuleSettings('Sessions');

$sessions = new Form('sessions', array(
    'view' => 'Module:sessions/control/settings/sessions',
    'controller' => 'Module:sessions/SessionsForms',
    'function' => 'editSessionsSettings',
    'submitButtonText' => 'Save Changes',
    'style' => 'width: 600px;',
));

$sessions->addFormComponentArray(array(
    new FormComponentDropDown('driver', 'Driver:', array(
            array('label' => 'Native', 'value' => 'Native'),
            array('label' => 'Database', 'value' => 'Database'),
            array('label' => 'Cookie', 'value' => 'Cookie'),
        ), array(
            'validationOptions' => array('required'),
            'initialValue' => $sessionsSettings['driver'],
        )
    ),
    new FormComponentSingleLineText('garbageCollectionProbability', 'Garbage collection probability:', array(
        'validationOptions' => array('required'),
        'tip' => '<p>1:x odds that garbage collection will run on the session table. Recommended value is 500.</p>',
        'initialValue' => $sessionsSettings['garbageCollectionProbability'],
    )),
    new FormComponentSingleLineText('expiration', 'Max lifetime in seconds:', array(
        'tip' => '<p>The number of seconds sessions will last before expiring. The default is 24 minutes (1440) seconds.</p>',
        'validationOptions' => array('required'),
        'initialValue' => $sessionsSettings['expiration'],
    )),
    new FormComponentMultipleChoice('regenerate', 'Regenerate session ID on every request:', array(
            array('label' => 'Yes', 'value' => 'Yes'),
            array('label' => 'No', 'value' => 'No'),
        ), array(
            'tip' => '<p>This will enhance security. It will also extend the session\'s life on every request. A session\'s life is extended anytime the content of the session is modified or when the session is regenerated.</p>',
            'validationOptions' => array('required'),
            'initialValue' => $sessionsSettings['regenerate'] ? 'Yes' : 'No',
            'multipleChoiceType' => 'radio',
        )
    ),
));
?>