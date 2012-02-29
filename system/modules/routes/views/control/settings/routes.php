<?php
$routes = $routesSettings['routes'];
echo '<div style="width: 800px;">';
echo '<p style="width: 600px;">The routing system provides a robust way to handle user requests. You can configure routes to capture dynamic data using regular expressions and also pass static data to the controller and function of your choice. Routes can be further configured with caching, access policies, priorities, and more.</p>';

$routesUl = HtmlElement::ul(array('class' => 'routes'));

foreach($routes as $route) {
    //print_r($route);
    $routesUl->append(getRouteLi($route));
}

echo $routesUl;

function getRouteLi($route, $previousRouteExpressions = '') {
    $routeLi = HtmlElement::li();

    // Determine the status text
    $statusText = '';
    if(isset($route['status'])) {
        if($route['status'] == 'permanentlyDisabled') {
             $statusText = ' (Permanently Disabled)';
        }
        else if($route['status'] == 'temporarilyDisabled') {
            $statusText = ' (Temporarily Disabled)';
        }
    }   

    // Determine the redirect text
    $redirectText = isset($route['redirect']) && !empty($route['redirect']) ? ' (Redirects to '.$route['redirect'].')' : '';

    $controls = '
        <div onmouseover="$(this).find(\'.routeControls\').show();" onmouseout="$(this).find(\'.routeControls\').hide();" style="float: left; margin-right: .5em;">
            <a class="buttonLink cog" style="padding: .25em .6em .25em 1.1em;"></a>
            <ul class="routeControls" style="display: none;">
                <li><a class="buttonLink notepad" href="edit-route/routeHash:'.Security::md5($previousRouteExpressions.$route['expression']).'/">Edit</a></li>
                <li><a class="buttonLink database" href="edit-route-caching/routeHash:'.Security::md5($previousRouteExpressions.$route['expression']).'/">Caching</a></li>
                <li><a class="buttonLink lock" href="edit-route-policies/routeHash:'.Security::md5($previousRouteExpressions.$route['expression']).'/">Policies</a></li>
                <li><a class="buttonLink plusDotGreen" href="add-child-route/routeHash:'.Security::md5($previousRouteExpressions.$route['expression']).'/">Add Child</a></li>
                <li><a class="buttonLink minusSquareGrey" href="delete-route/routeHash:'.Security::md5($previousRouteExpressions.$route['expression']).'/">Delete</a></li>
            </ul>
        </div>
    ';

    $routeLi->text('<div class="routeTitle">'.$controls.'<span class="previousRouteExpressions">'.$previousRouteExpressions.'</span><a href="edit-route/routeHash:'.Security::md5($previousRouteExpressions.$route['expression']).'/"><b>'.$route['expression'].'</b></a>'.$statusText.$redirectText.'</div>');

    if(isset($route['childRoutes']) && !empty($route['childRoutes']) && Arr::is($route['childRoutes'])) {
        foreach($route['childRoutes'] as $childRoute) {
            $childRouteUl = HtmlElement::ul();
            $childRouteUl->append(getRouteLi($childRoute, $previousRouteExpressions.$route['expression']));
            $routeLi->append($childRouteUl);
        }
    }

    return $routeLi;
}

echo '</div>';
?>