<?php
$cookies = new Form('cookies', array(
    'view' => 'Module:cookies/control/settings/cookies',
    'controller' => 'Module:cookies/CookiesForms',
    'function' => 'editCookiesSettings',
    'submitButtonText' => 'Save Changes',
    'style' => 'width: 600px;',
));

$cookiesSettings = Project::getModuleSettings('Cookies');

$cookies->addFormComponentArray(array(
    new FormComponentSingleLineText('expiration', 'Default max lifetime in seconds:', array(
        'tip' => '<p>The number of seconds before cookies expire. The default is 30 days (2592000) seconds.</p>',
        'validationOptions' => array('required'),
        'initialValue' => $cookiesSettings['expiration'],
    )),
    new FormComponentSingleLineText('domain', 'Default domain:', array(
        'tip' => '<p>The default domain that the cookies can be accessed from. For example, www.example.com or .example.com.</p>',
        'initialValue' => $cookiesSettings['domain'],
    )),
    new FormComponentSingleLineText('path', 'Default path:', array(
        'tip' => '<p>The default path that cookies can be accessed from. Usually /.</p>',
        'validationOptions' => array('required'),
        'initialValue' => $cookiesSettings['path'],
    )),
    new FormComponentMultipleChoice('httpsOnly', 'Default to secure (HTTPS) connections only:', array(
            array('label' => 'Yes', 'value' => 'Yes'),
            array('label' => 'No', 'value' => 'No'),
        ), array(
            'validationOptions' => array('required'),
            'initialValue' => $cookiesSettings['httpsOnly'] ? 'Yes' : 'No',
            'multipleChoiceType' => 'radio',
        )
    ),
    new FormComponentMultipleChoice('httpProtocolOnly', 'Default to HTTP protocol only:', array(
            array('label' => 'Yes', 'value' => 'Yes'),
            array('label' => 'No', 'value' => 'No'),
        ), array(
            'tip' => '<p>You may prevent JavaScript from accessing cookie values.</p>',
            'validationOptions' => array('required'),
            'initialValue' => $cookiesSettings['httpProtocolOnly'] ? 'Yes' : 'No',
            'multipleChoiceType' => 'radio',
        )
    ),
    new FormComponentMultipleChoice('signing', '', array(
            array('value' => 'Yes', 'label' => 'Sign cookies for greater security', 'checked' => $cookiesSettings['signing'])
        ),
        array(
        )
    ),
    new FormComponentSingleLineText('signingSalt', 'Signed cookie salt:', array(
        'tip' => '<p>A random string of characters.<br /><br /><b>Warning</b>: Changing this will invalidate all issued cookies. Do not share or expose this value, ever.</p>',
        'validationOptions' => array('required'),
        'initialValue' => $cookiesSettings['signingSalt'],
        'width' => 'longest',
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('signing'),
            'jsFunction' => "$('#signing-choice1').is(':checked');",
        ),
    )),
));
?>