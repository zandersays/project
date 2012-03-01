<?php
if(!isset($routeHash)) {
    $routeHash = $_GET['routeHash'];
}

$route = Router::getRouteByRouteHash($routeHash);

$deleteRoute = new Form('deleteRoute', array(
    'view' => 'Module:routes/control/settings/routes/delete-route',
    'controller' => 'Module:routes/RoutesForms',
    'function' => 'deleteRoute',
    'submitButtonText' => 'Confirm Deletion',
    'style' => 'width: 600px;',
));

$deleteRoute->addFormSection(
    new FormSection('deleteRouteSection', array(
        'description' => '
            <p>You have specified this route for deletion:</p>
            <ul style="margin: 0 0 0 1.5em;">
                <li>'.$route['expression'].'</li>
            </ul>
        '
    ))
);

$deleteRoute->addFormComponent(
    new FormComponentHidden('routeHash', $routeHash)
);
?>