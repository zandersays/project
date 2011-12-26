<?php
if(!isset($routeHash)) {
    $routeHash = $_GET['routeHash'];
}

$addChildRoute = new Form('addChildRoute', array(
    'view' => 'Module:routes/control/settings/routes/add-child-route',
    'controller' => 'Module:routes/RoutesForms',
    'function' => 'addChildRoute',
    'submitButtonText' => 'Add Child Route',
    'style' => 'width: 600px;',
));

$parentRoutesHtml = '';
if(isset($parentRoutes)) {
    foreach($parentRoutes as $parentRoute) {
        $parentRoutesHtml = '<a href="../../edit-route/routeHash:'.$parentRoute['hash'].'/" style="color: #666;">'.$parentRoute['expression'].'</a> '.$parentRoutesHtml;
    }
}
// Set defaults for route
if(!isset($route) || Object::is($route)) {
    $route = array();
    $route['expression'] = '';
}

$addRouteChildSection = new FormSection('route', array(
    'title' => '<h2 style="display: inline;">Parent Route(s): '.$parentRoutesHtml.'</h2>',
));

$addRouteChildSection->addFormComponentArray(array(
    new FormComponentHtml('<h2>General</h2>'),
    new FormComponentDropDown('status', 'Status', array(
            array('label' => 'Active', 'value' => 'active'),
            array('label' => 'Temporarily Disabled', 'value' => 'temporarilyDisabled'),
            array('label' => 'Permanently Disabled', 'value' => 'permanentlyDisabled'),
        ), array(
            'validationOptions' => array('required'),
        )
    ),
    new FormComponentSingleLineText('expression', 'Expression:', array(
        'width' => 'longest',
        'validationOptions' => array('required'),
    )),
    new FormComponentTextArea('description', 'Description:', array(
        'height' => 'short',
        'width' => 'long',
    )),
    new FormComponentSingleLineText('priority', 'Priority:', array(
    )),
    new FormComponentHtml('<h2 style="clear: both;">Resource</h2>'),
    new FormComponentMultipleChoice('redirectOn', '', array(
        array('label' => 'This route redirects to another resource.', 'value' => 'on'),
    ), array(
    )),
    new FormComponentSingleLineText('redirect', 'Redirect:', array(
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('redirectOn'),
            'jsFunction' => "$('#redirectOn-choice1').is(':checked');",
        ),
        'width' => 'longest',
        'validationOptions' => array('required'),
    )),
    new FormComponentSingleLineText('controllerName', 'Controller name:', array(
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('redirectOn'),
            'jsFunction' => "!$('#redirectOn-choice1').is(':checked');",
        ),
    )),
    new FormComponentSingleLineText('functionName', 'Function name:', array(
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('redirectOn'),
            'jsFunction' => "!$('#redirectOn-choice1').is(':checked');",
        ),
        'style' => 'clear: none;',
    )),

    new FormComponentHidden('routeHash', $routeHash),
));

$addChildRoute->addFormSection($addRouteChildSection);

// Load the initial values for the data section
$dataInitialValues = array();
if(isset($route['data']) && Arr::is($route['data'])) {
    foreach($route['data'] as $index => $keyValue) {
        $dataInitialValues[] = array('key' => $keyValue['key'], 'value' => $keyValue['value']);
    }
}

$dataSection = new FormSection('data', array(
    'title' => '<h3>Data</h3>',
    'instanceOptions' => array(
        'max' => 0,
        'initialValues' => $dataInitialValues,
    ),
    'dependencyOptions' => array(
        'display' => 'hide',
        'dependentOn' => array('redirectOn'),
        'jsFunction' => "!$('#redirectOn-choice1').is(':checked');",
    ),
));
$dataSection->addFormComponentArray(array(
    new FormComponentSingleLineText('key', 'Key:', array(
    )),
    new FormComponentSingleLineText('value', 'Value:', array(
    )),
));
$addChildRoute->addFormSection($dataSection);
?>