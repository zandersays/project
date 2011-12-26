<?php
class RoutesForms {

    function addChildRoute($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Create a new route
        $route = array();

        // Update the specific route
        $route['status'] = $formValues->route->status;
        $route['expression'] = $formValues->route->expression;
        $route['description'] = $formValues->route->description;
        $route['priority'] = $formValues->route->priority;

        // Redirect on
        if(!empty($formValues->route->redirect)) {
            $route['redirect'] = $formValues->route->redirect;
            unset($route['controllerName']);
            unset($route['functionName']);
            unset($route['data']);
        }
        // Redirect off
        else {
            unset($route['redirect']);
            $route['controllerName'] = $formValues->route->controllerName;
            $route['functionName'] = $formValues->route->functionName;

            // Make sure we are working with an array of arrays
            if(Object::is($formValues->data)) {
                $formValues->data = array($formValues->data);
            }
            foreach($formValues->data as &$data) {
                $data = Object::arr($data);
            }

            $route['data'] = $formValues->data;
        }

        // Save the route settings back
        $routesSettings = Project::getModuleSettings('Routes');
        $routesArray = Router::addChildRouteByRouteHash($route, $formValues->route->routeHash);
        $routesSettings['routes'] = $routesArray;
        Project::setModuleSettings('Routes', $routesSettings);

        //$response['failureNoticeHtml'] = Json::encode(Project::getModuleSettings('Routes')); return $response;

        // Save the route settings
        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Edited Route <b>'.$formValues->route->expression.'</b></h2><p>Visit the <a href="'.Project::getInstanceAccessPath().'project/modules/routes/settings/routes/">routes</a> page to see the change.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save route changes to settings.php file.';
        }

        return $response;
    }

    function deleteRoute($formValues) {
        $response = array();
        global $route;

        if($formValues->routeHash == Security::md5('/')) {
            return array('failureNoticeHtml' => 'You may not remove the root route.');
        }

        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Remove the route from the settings
        $routesSettings = Project::getModuleSettings('Routes');
        $routesArray = Router::deleteRouteByRouteHash($formValues->routeHash);
        $routesSettings['routes'] = $routesArray;
        Project::setModuleSettings('Routes', $routesSettings);

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Removed Route <b>'.$route['expression'].'</b></h2><p>Visit the <a href="'.Project::getInstanceAccessPath().'project/modules/routes/settings/routes/">routes</a> page to see the change.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to remove route from settings.php file.';
        }

        return $response;
    }

    function editRoute($formValues) {
        $response = array();

        if($formValues->route->routeHash == Security::md5('/') && $formValues->route->expression != '/') {
            return array('failureNoticeHtml' => 'You may not change root route expression from "/".');
        }

        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Read the route out
        $route = Router::getRouteByRouteHash($formValues->route->routeHash);

        // Update the specific route
        $route['status'] = $formValues->route->status;
        $route['expression'] = $formValues->route->expression;
        $route['description'] = $formValues->route->description;
        $route['priority'] = $formValues->route->priority;

        // Redirect on
        if(!empty($formValues->route->redirect)) {
            $route['redirect'] = $formValues->route->redirect;
            unset($route['controllerName']);
            unset($route['functionName']);
            unset($route['data']);
        }
        // Redirect off
        else {
            unset($route['redirect']);
            $route['controllerName'] = $formValues->route->controllerName;
            $route['functionName'] = $formValues->route->functionName;

            // Make sure we are working with an array of arrays
            if(Object::is($formValues->data)) {
                $formValues->data = array($formValues->data);
            }
            foreach($formValues->data as &$data) {
                $data = Object::arr($data);
            }

            $route['data'] = $formValues->data;
        }

        // Save the route settings back
        $routesSettings = Project::getModuleSettings('Routes');
        $routesArray = Router::setRouteByRouteHash($route, $formValues->route->routeHash);
        $routesSettings['routes'] = $routesArray;
        Project::setModuleSettings('Routes', $routesSettings);

        //$response['failureNoticeHtml'] = Json::encode(Project::getModuleSettings('Routes')); return $response;

        // Save the route settings
        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Edited Route <b>'.$formValues->route->expression.'</b></h2><p>Visit the <a href="'.Project::getInstanceAccessPath().'project/modules/routes/settings/routes/">routes</a> page to see the change.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save route changes to settings.php file.';
        }

        return $response;
    }

}
?>