<?php
if(!isset($routeHash)) {
    $routeHash = $_GET['routeHash'];
}

$editRoute = new Form('editRoute', array(
    'view' => 'Module:routes/control/settings/routes/edit-route',
    'controller' => 'Module:routes/RoutesForms',
    'function' => 'editRoute',
    'submitButtonText' => 'Save Changes',
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

$editRouteSection = new FormSection('route', array(
    'title' => '<h2 style="display: inline;">'.$parentRoutesHtml.$route['expression'].'</h2>',
    'description' => '
        <ul class="editRouteControls">
            <li><a class="buttonLink database" href="../../edit-route-caching/routeHash:'.$routeHash.'/">Caching</a></li>
            <li><a class="buttonLink lock" href="../../edit-route-policies/routeHash:'.$routeHash.'/">Policies</a></li>
            <li><a class="buttonLink plusDotGreen" href="../../add-child-route/routeHash:'.$routeHash.'/">Add Child</a></li>
            <li><a class="buttonLink minusSquareGrey" href="../../delete-route/routeHash:'.$routeHash.'/">Delete</a></li>
        </ul>
    ',
));

$editRouteSection->addFormComponentArray(array(
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
        'initialValue' => isset($route['expression']) ? $route['expression'] : '',
        'validationOptions' => array('required'),
        'width' => 'longest',
    )),
    new FormComponentTextArea('description', 'Description:', array(
        'height' => 'short',
        'width' => 'long',
        'initialValue' => isset($route['description']) ? $route['description'] : '',
    )),    
    new FormComponentSingleLineText('priority', 'Priority:', array(
        'initialValue' => isset($route['priority']) ? $route['priority'] : '',
    )),
    new FormComponentHtml('<h2 style="clear: both;">Resource</h2>'),
    new FormComponentMultipleChoice('redirectOn', '', array(
        array('label' => 'This route redirects to another resource.', 'value' => 'on'),
    ), array(
    )),
    new FormComponentSingleLineText('redirect', 'Redirect:', array(
        'initialValue' => isset($route['redirect']) ? $route['redirect'] : '',
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('redirectOn'),
            'jsFunction' => "$('#redirectOn-choice1').is(':checked');",
        ),
        'width' => 'longest',
        'validationOptions' => array('required'),
    )),
    new FormComponentSingleLineText('controllerName', 'Controller name:', array(
        'initialValue' => isset($route['controllerName']) ? $route['controllerName'] : '',
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('redirectOn'),
            'jsFunction' => "!$('#redirectOn-choice1').is(':checked');",
        ),
    )),
    new FormComponentSingleLineText('functionName', 'Function name:', array(
        'initialValue' => isset($route['functionName']) ? $route['functionName'] : '',
        'dependencyOptions' => array(
            'display' => 'hide',
            'dependentOn' => array('redirectOn'),
            'jsFunction' => "!$('#redirectOn-choice1').is(':checked');",
        ),
        'style' => 'clear: none;',
    )),
    
    new FormComponentHidden('routeHash', $routeHash),
));

$editRoute->addFormSection($editRouteSection);

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
$editRoute->addFormSection($dataSection);
?>