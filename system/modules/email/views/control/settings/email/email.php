<?php
$email = new Form('email', array(
    'view' => 'Module:email/control/settings/email',
    'controller' => 'Module:email/EmailForms',
    'function' => 'editEmailSettings',
    'submitButtonText' => 'Save Changes',
    'style' => 'width: 600px;',
));

$emailSettings = Project::getModuleSettings('Email');

$email->addFormComponentArray(array(
    new FormComponentMultipleChoice('defaultMailType', 'Default mail type:', array(
        array('label' => 'Text', 'value' => 'text'),
        array('label' => 'HTML', 'value' => 'html'),
    ), array(
        'multipleChoiceType' => 'radio',
        'initialValue' => $emailSettings['defaultMailType'],
        'validationOptions' => array('required'),
    )),
    new FormComponentDropDown('defaultCharacterSet', 'Default character set:', array(
        array('label' => 'UTF-8', 'value' => 'utf-8'),
        array('label' => 'ISO-8859-1', 'value' => 'iso-8859-1'),
        array('label' => 'US-ASCII', 'value' => 'us-ascii'),
    ), array(
        'initialValue' => $emailSettings['defaultCharacterSet'],
        'validationOptions' => array('required'),
    )),
    new FormComponentSingleLineText('defaultUserAgent', 'Default user agent:', array(
        'validationOptions' => array('required'),
        'initialValue' => $emailSettings['defaultUserAgent'],
    )),
    new FormComponentDropDown('defaultProtocol', 'Default protocol:', array(
        array('label' => 'mail', 'value' => 'mail'),
        array('label' => 'sendmail', 'value' => 'sendmail'),
        array('label' => 'smtp', 'value' => 'smtp'),
    ), array(
        'initialValue' => $emailSettings['defaultProtocol'],
        'validationOptions' => array('required'),
    )),
    new FormComponentSingleLineText('defaultSmtpHost', 'Default SMTP host:', array(
        'validationOptions' => array('required'),
        'initialValue' => $emailSettings['defaultSmtpHost'],
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('defaultProtocol'),
            'jsFunction' => "$('#defaultProtocol').val() == 'smtp';",
        ),
    )),
    new FormComponentSingleLineText('defaultSmtpPort', 'Default SMTP port:', array(
        'validationOptions' => array('required'),
        'initialValue' => $emailSettings['defaultSmtpPort'],
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('defaultProtocol'),
            'jsFunction' => "$('#defaultProtocol').val() == 'smtp';",
        ),
    )),
    new FormComponentSingleLineText('defaultSmtpUsername', 'Default SMTP username:', array(
        'initialValue' => $emailSettings['defaultSmtpUsername'],
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('defaultProtocol'),
            'jsFunction' => "$('#defaultProtocol').val() == 'smtp';",
        ),
    )),
    new FormComponentSingleLineText('defaultSmtpPassword', 'Default SMTP password:', array(
        'initialValue' => $emailSettings['defaultSmtpPassword'],
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('defaultProtocol'),
            'jsFunction' => "$('#defaultProtocol').val() == 'smtp';",
        ),
    )),
    new FormComponentSingleLineText('defaultSmtpTimeout', 'Default SMTP timeout:', array(
        'validationOptions' => array('required'),
        'initialValue' => $emailSettings['defaultSmtpTimeout'],
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('defaultProtocol'),
            'jsFunction' => "$('#defaultProtocol').val() == 'smtp';",
        ),
    )),
    new FormComponentSingleLineText('defaultSendMailPath', 'Default sendmail path:', array(
        'validationOptions' => array('required'),
        'initialValue' => $emailSettings['defaultSendMailPath'],
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('defaultProtocol'),
            'jsFunction' => "$('#defaultProtocol').val() == 'sendmail';",
        ),
    )),
    new FormComponentMultipleChoice('defaultWordWrap', '', array(
            array('value' => 'yes', 'label' => 'Word wrap enabled by default', 'checked' => $emailSettings['defaultWordWrap'])
        ),
        array(
        )
    ),
    new FormComponentSingleLineText('defaultWordWrapCharacters', 'Default word wrap characters:', array(
        'validationOptions' => array('required'),
        'initialValue' => $emailSettings['defaultWordWrapCharacters'],
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('defaultWordWrap'),
            'jsFunction' => "$('#defaultWordWrap-choice1').is(':checked');",
        ),
    )),
));
?>