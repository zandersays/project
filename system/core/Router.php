<?php
class Router {

    public static $routes = array();
    private static $headers = array();
    public $request;
    public $response;

    public function Router($request) {
        // Set the request class variable
        $this->request = $request;

        // Identify the current route
        $route = $this->matchRoute($request);
        //echo $request.'<br />'; print_r($route); exit();

        // Handle null requests
        if($request == 'null/') {
            $this->response = '';
        }
        // Send the request through security
        else {
            $security = new Security($route, $request);
            $this->response = $security->response;
        }
    }

    public function matchRoute($request, $routeArray = null, $parentRouteExpressions = '', $parentRoutes = array()) {
        // Initialize the route array
        if($routeArray == null) {
            $routeArray = self::getRoutes();
        }

        // Recurse through any child routes to find the matching route
        $response = null;
        foreach($routeArray as $routeOptions) {
            $route = new Route($request, $routeOptions, $parentRouteExpressions, $parentRoutes);
            $routeFound = $route->matchesRequest;

            // If a match is found, check to see if any of the children still match
            if($routeFound && isset($routeOptions['childRoutes']) && Arr::is($routeOptions['childRoutes'])) {
                //echo $request.' matched against '.$route->fullExpression.'<br />';

                $routeArray[] = $routeOptions;
                $parentRoutes[] = $route;
                $response = $this->matchRoute($request, $routeOptions['childRoutes'], $parentRouteExpressions.$routeOptions['expression'], $parentRoutes);
                if($response !== null) {
                    return $response;
                }
                else {
                    return $route;
                }
            }
            // Break iteration if a match is found and there are no more children
            else if($routeFound) {
                return $route;
            }
            // If the route isn't found, try the children
            else if(isset($routeOptions['childRoutes']) && Arr::is($routeOptions['childRoutes'])) {
                //echo $request.' did not match against '.$route->fullExpression.', checking child routes<br />';
                $routeArray[] = $routeOptions;
                $parentRoutes[] = $route;
                $response = $this->matchRoute($request, $routeOptions['childRoutes'], $parentRouteExpressions.$routeOptions['expression'], $parentRoutes);
                if($response !== null) {
                    return $response;
                }
            }
            // No child routes
            else {
                //echo $request.' did not match against '.$route->fullExpression.'<br />';
            }
        }

        return $response;
    }

    public static function getRouteByRouteHash($hash, $routeArray = null, $previousRouteExpressions = '') {
        // Initialize the route array
        if($routeArray == null) {
            $routes = Project::getModuleSettings('Routes');
            $routeArray = $routes['routes'];
        }

        // Recurse through any child routes to find the matching route
        $response = null;
        foreach($routeArray as $route) {
            if(Security::md5($previousRouteExpressions.$route['expression']) == $hash) {
                return $route;
            }
            
            if(isset($route['childRoutes']) && Arr::is($route['childRoutes'])) {
                $response = Router::getRouteByRouteHash($hash, $route['childRoutes'], $previousRouteExpressions.$route['expression']);
                if($response !== null) {
                    return $response;
                }
            }

        }

        return $response;
    }

    public static function deleteRouteByRouteHash($hash, $routeArray = null, $previousRouteExpressions = '') {
        // Initialize the route array
        if($routeArray == null) {
            $routes = Project::getModuleSettings('Routes');
            $routeArray = $routes['routes'];
        }

        // Recurse through any child routes to find the matching route
        $response = null;
        foreach($routeArray as &$route) {
            if(Security::md5($previousRouteExpressions.$route['expression']) == $hash) {
                $route = null;
                $routeArray = Arr::filter($routeArray);
                return $routeArray;
            }

            if(isset($route['childRoutes']) && Arr::is($route['childRoutes'])) {
                $response = Router::deleteRouteByRouteHash($hash, $route['childRoutes'], $previousRouteExpressions.$route['expression']);
                if($response !== null) {
                    return $routeArray;
                }
            }
        }

        return $routeArray;
    }
   
    public static function setRouteByRouteHash($newRoute, $hash, $routeArray = null, $previousRouteExpressions = '') {
        // Initialize the route array
        if($routeArray == null) {
            $routes = Project::getModuleSettings('Routes');
            $routeArray = $routes['routes'];
        }

        // Recurse through any child routes to find the matching route
        $response = null;
        foreach($routeArray as &$route) {
            if(Security::md5($previousRouteExpressions.$route['expression']) == $hash) {
                $route = $newRoute;
                return $routeArray;
            }

            if(isset($route['childRoutes']) && Arr::is($route['childRoutes'])) {
                $response = Router::setRouteByRouteHash($newRoute, $hash, $route['childRoutes'], $previousRouteExpressions.$route['expression']);
                if($response !== null) {
                    return $routeArray;
                }
            }

        }
        
        return $routeArray;
    }

    public static function addChildRouteByRouteHash($childRoute, $hash, $routeArray = null, $previousRouteExpressions = '') {
        // Initialize the route array
        if($routeArray == null) {
            $routes = Project::getModuleSettings('Routes');
            $routeArray = $routes['routes'];
        }

        // Recurse through any child routes to find the matching route
        $response = null;
        foreach($routeArray as &$route) {
            if(Security::md5($previousRouteExpressions.$route['expression']) == $hash) {
                $route['childRoutes'][] = $childRoute;
                return $routeArray;
            }

            if(isset($route['childRoutes']) && Arr::is($route['childRoutes'])) {
                $response = Router::addChildRouteByRouteHash($childRoute, $hash, $route['childRoutes'], $previousRouteExpressions.$route['expression']);
                if($response !== null) {
                    return $routeArray;
                }
            }
        }

        return $routeArray;
    }

    public static function getParentRouteByRouteHash($routeHash, $routeArray = null, $fullRouteExpression = '') {
        // Initialize the route array
        if($routeArray == null) {
            $routes = Project::getModuleSettings('Routes');
            $routeArray = $routes['routes'];
        }

        // Recurse through any child routes to find the matching route
        $response = null;
        foreach($routeArray as $currentRoute) {
            if(isset($currentRoute['childRoutes']) && Arr::is($currentRoute['childRoutes'])) {
                foreach($currentRoute['childRoutes'] as $childRoute) {
                    //echo $fullRouteExpression.$currentRoute['expression'].$childRoute['expression'].' - '.Security::md5($fullRouteExpression.$childRoute['expression']).' vs. '.$routeHash.'<br />';
                    if(Security::md5($fullRouteExpression.$currentRoute['expression'].$childRoute['expression']) == $routeHash) {
                        //echo 'Match!';
                        $currentRoute['hash'] = Security::md5($fullRouteExpression.$currentRoute['expression']);
                        $response = $currentRoute;
                        break;
                    }

                    // Recursion
                    if($response == null && isset($childRoute['childRoutes']) && Arr::is($childRoute['childRoutes'])) {
                        $response = Router::getParentRouteByRouteHash($routeHash, $currentRoute['childRoutes'], $fullRouteExpression.$currentRoute['expression']);
                    }
                }
            }
        }

        return $response;
    }

    public static function getRoutes() {
        return Router::$routes;
    }

    public static function addRoutes($routes) {
        Router::$routes = Arr::merge(Router::$routes, $routes);
    }

    public static function prependRoute($route) {
        Arr::unshift($route, Router::$routes);
    }

    public static function appendRoute($route) {
        Router::$routes[] = $route;
    }

    public static function redirect($path) {
        header('Location: '.$path); exit();
    }
    
    public static function getAnalytics() {
        $user = $_SERVER['HTTP_USER_AGENT'];
        $browser = 'Unknown';
        $browserName = '';
        $platform = 'Unknown';
        $version= '';

        // First get the platform?
        if(preg_match('/linux/i', $user)) {
            $platform = 'Linux';
        }
        else if(preg_match('/macintosh|mac os x/i', $user)) {
            $platform = 'Mac';
        }
        else if(preg_match('/windows|win32/i', $user)) {
            $platform = 'Windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i', $user) && !preg_match('/Opera/i', $user)) {
            $browser = 'Internet Explorer';
            $browserName = "MSIE";
        }
        else if(preg_match('/Firefox/i', $user)) {
            $browser = 'Mozilla Firefox';
            $browserName = "Firefox";
        }
        else if(preg_match('/Chrome/i', $user)) {
            $browser = 'Google Chrome';
            $browserName = "Chrome";
        }
        else if(preg_match('/Safari/i', $user)) {
            $browser = 'Apple Safari';
            $browserName = "Safari";
        }
        else if(preg_match('/Opera/i', $user)) {
            $browser = 'Opera';
            $browserName = "Opera";
        }
        else if(preg_match('/Netscape/i', $user)) {
            $browser = 'Netscape';
            $browserName = "Netscape";
        }

        // Finally get the correct version number
        $known = array('Version', $browserName, 'other');
        $pattern = '#(?<browser>' . join('|', $known).')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if(!preg_match_all($pattern, $user, $matches)) {
            // We have no matching number just continue
        }

        // See how many we have
        $i = count($matches['browser']);
        if($i != 1) {
            // We will have two since we are not using 'other' argument yet
            // See if version is before or after the name
            if(strripos($user,"Version") < strripos($user,$browserName)){
                $version= $matches['version'][0];
            }
            else {
                $version= $matches['version'][1];
            }
        }
        else {
            $version= $matches['version'][0];
        }

        // Check if we have a number
        if($version == null || $version== '') {
            $version = null;
        }

        $user = array(
            'userAgent' => $user,
            'browserName' => $browser,
            'browserVersion' => $version,
            'platform' => $platform,
        );

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
        $port = '';
        if($_SERVER['SERVER_PORT'] != '80') {
            $port = ':'.$_SERVER['SERVER_PORT'];
        }

        $url = $protocol.'://'.$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];

        return array(
            'request' => array(
                'method' => $_SERVER['REQUEST_METHOD'],
                'protocol' => $protocol,
                'host' => $_SERVER['SERVER_NAME'],
                'port' => $_SERVER['SERVER_PORT'],
                'path' => $_SERVER['REQUEST_URI'],
                'queryString' => $_SERVER['QUERY_STRING'],
                'url' => $protocol.'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'],
                'referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null,
                'time' => $_SERVER['REQUEST_TIME'],
            ),
            'user' => array(
                'agent' => $user['userAgent'],
                'language' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '',
                'ipAddress' => $_SERVER['REMOTE_ADDR'],
                'browser' => array(
                    'name' => $user['browserName'],
                    'version' => $user['browserVersion'],
                    'platform' => $user['platform']
                ),
            ),
            'server' => array(
                'ipAddress' => $_SERVER['SERVER_ADDR'],
                'software' => $_SERVER['SERVER_SOFTWARE'],
            ),
        );
    }

    public static function getHeaders() {
        return Router::$headers;
    }
    
    public static function set404Header() {
        Router::$headers[] = 'HTTP/1.0 404 Not Found';
    }

    public static function setHeader($header) {
        Router::$headers[] = $header;
    }

    public static function clearHeaders() {
        Router::$headers = array();
    }

    public function headers() {
        foreach(Router::getHeaders() as $header) {
            header($header);
        }

        return $this;
    }

    public function response() {
        Router::output($this->response);

        return $this;
    }

    public static function output($output) {
        // Make sure we are working with a string
        if((Object::is($output) && Object::methodExists('__toString', $output)) || String::is($output)) {
            echo $output;
        }
        else {
            echo '<p>Error: Response is not a string.</p>';
            Arr::printFormatted($output);
        }
    }

}
?>