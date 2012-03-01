<?php
$registrationEmail = new Form('usersAndAccounts', array(
    'view' => 'Module:users/control/settings/users/users/registration-email',
    'controller' => 'Module:users/forms/UsersForms',
    'function' => 'editRegistrationEmailSettings',
    'submitButtonText' => 'Save Changes',
    'style' => 'width: 600px;',
));

$usersAndAccountSettings = Project::getModuleSettings('UsersAndAccounts');
$emailOptions = isset($usersAndAccountSettings['users']) && isset($usersAndAccountSettings['users']['registrationEmail']) && isset($usersAndAccountSettings['users']['registrationEmail']['emailOptions']) ? $usersAndAccountSettings['users']['registrationEmail']['emailOptions'] : array();

$registrationEmail->addFormComponentArray(array(
    new FormComponentMultipleChoice('mailType', 'Mail type:', array(
        array('label' => 'Text', 'value' => 'text'),
        array('label' => 'HTML', 'value' => 'html'),
    ), array(
        'multipleChoiceType' => 'radio',
        'initialValue' => isset($emailOptions['mailType']) ? $emailOptions['mailType'] : Message::$defaultMailType,
        'validationOptions' => array('required'),
    )),
    new FormComponentDropDown('characterSet', 'Character set:', array(
        array('label' => 'UTF-8', 'value' => 'utf-8'),
        array('label' => 'ISO-8859-1', 'value' => 'iso-8859-1'),
        array('label' => 'US-ASCII', 'value' => 'us-ascii'),
    ), array(
        'initialValue' => isset($emailOptions['characterSet']) ? $emailOptions['characterSet'] : Message::$defaultCharacterSet,
        'validationOptions' => array('required'),
    )),
    new FormComponentSingleLineText('userAgent', 'User agent:', array(
        'validationOptions' => array('required'),
        'initialValue' => isset($emailOptions['userAgent']) ? $emailOptions['userAgent'] : Message::$defaultUserAgent,
    )),
    new FormComponentDropDown('protocol', 'Protocol:', array(
        array('label' => 'mail', 'value' => 'mail'),
        array('label' => 'sendmail', 'value' => 'sendmail'),
        array('label' => 'smtp', 'value' => 'smtp'),
    ), array(
        'initialValue' => isset($emailOptions['protocol']) ? $emailOptions['protocol'] : Message::$defaultProtocol,
        'validationOptions' => array('required'),
    )),
    new FormComponentSingleLineText('smtpHost', 'SMTP host:', array(
        'validationOptions' => array('required'),
        'initialValue' => isset($emailOptions['smtpHost']) ? $emailOptions['smtpHost'] : Message::$defaultSmtpHost,
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('protocol'),
            'jsFunction' => "$('#protocol').val() == 'smtp';",
        ),
    )),
    new FormComponentSingleLineText('smtpPort', 'SMTP port:', array(
        'validationOptions' => array('required'),
        'initialValue' => isset($emailOptions['smtpPort']) ? $emailOptions['smtpPort'] : Message::$defaultSmtpPort,
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('protocol'),
            'jsFunction' => "$('#protocol').val() == 'smtp';",
        ),
    )),
    new FormComponentSingleLineText('smtpUsername', 'SMTP username:', array(
        'initialValue' => isset($emailOptions['smtpUsername']) ? $emailOptions['smtpUsername'] : Message::$defaultSmtpUsername,
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('protocol'),
            'jsFunction' => "$('#protocol').val() == 'smtp';",
        ),
    )),
    new FormComponentSingleLineText('smtpPassword', 'SMTP password:', array(
        'initialValue' => isset($emailOptions['smtpPassword']) ? $emailOptions['smtpPassword'] : Message::$defaultSmtpPassword,
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('protocol'),
            'jsFunction' => "$('#protocol').val() == 'smtp';",
        ),
    )),
    new FormComponentSingleLineText('smtpTimeout', 'SMTP timeout:', array(
        'validationOptions' => array('required'),
        'initialValue' => isset($emailOptions['smtpTimeout']) ? $emailOptions['smtpTimeout'] : Message::$defaultSmtpTimeout,
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('protocol'),
            'jsFunction' => "$('#protocol').val() == 'smtp';",
        ),
    )),
    new FormComponentSingleLineText('sendMailPath', 'sendmail path:', array(
        'validationOptions' => array('required'),
        'initialValue' => isset($emailOptions['sendMailPath']) ? $emailOptions['sendMailPath'] : Message::$defaultSendMailPath,
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('protocol'),
            'jsFunction' => "$('#protocol').val() == 'sendmail';",
        ),
    )),
    new FormComponentMultipleChoice('wordWrap', '', array(
            array('value' => 'yes', 'label' => 'Word wrap enabled', 'checked' => isset($emailOptions['wordWrap']) ? $emailOptions['wordWrap'] : Message::$defaultWordWrap)
        ),
        array(
        )
    ),
    new FormComponentSingleLineText('wordWrapCharacters', 'Word wrap characters:', array(
        'validationOptions' => array('required'),
        'initialValue' => isset($emailOptions['wordWrapCharacters']) ? $emailOptions['wordWrapCharacters'] : Message::$defaultWordWrapCharacters,
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('wordWrap'),
            'jsFunction' => "$('#wordWrap-choice1').is(':checked');",
        ),
    )),
));
?>