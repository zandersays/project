<?php
class RoutesControl extends Controller {

    function index($data) {
        return $this->$data['function']($data);
    }

    function settingsRoutes($data) {
        $data['routesSettings'] = Project::getModuleSettings('Routes');

        return $this->getView('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }

    function settingsRoutesEditRoute($data) {
        $routeData = $data['pathArguments'];
        $routeData['route'] = Router::getRouteByRouteHash($routeData['routeHash']);

        // Get all of the parent routes
        $routeData['parentRoutes'] = array();
        do {
            // Start out with the current route
            if(Arr::size($routeData['parentRoutes']) == 0) {
                $hash = $routeData['routeHash'];
            }
            // Work back with each parent route found
            else {
                $hash = $routeData['parentRoutes'][Arr::size($routeData['parentRoutes']) - 1]['hash'];
            }

            $parentRoute = Router::getParentRouteByRouteHash($hash);

            if($parentRoute != null) {
                $routeData['parentRoutes'][] = $parentRoute;
            }
        }
        while($parentRoute != null);

        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $routeData);
    }

    function settingsRoutesAddChildRoute($data) {
        $routeData = $data['pathArguments'];
        $routeData['route'] = Router::getRouteByRouteHash($routeData['routeHash']);

        // Get all of the parent routes
        $routeData['parentRoutes'] = array();
        do {
            // Start out with the current route
            if(Arr::size($routeData['parentRoutes']) == 0) {
                $hash = $routeData['routeHash'];
                $routeData['route']['hash'] = $routeData['routeHash'];
                $routeData['parentRoutes'][] = $routeData['route'];
            }
            // Work back with each parent route found
            else {
                $hash = $routeData['parentRoutes'][Arr::size($routeData['parentRoutes']) - 1]['hash'];
            }

            $parentRoute = Router::getParentRouteByRouteHash($hash);

            if($parentRoute != null) {
                $routeData['parentRoutes'][] = $parentRoute;
            }
        }
        while($parentRoute != null);

        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $routeData);
    }

    function settingsRoutesDeleteRoute($data) {
        $routeData = $data['pathArguments'];

        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $routeData);
    }

}
?>