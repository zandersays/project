<?php
class Route {
    
    public $request;
    public $expression;
    public $fullExpression;
    public $matchesRequest = false;
    public $controllerName = 'Main';
    public $controller;
    public $functionName = 'index';
    public $description;
    public $childRoutes;
    public $status;
    public $priority;
    public $data;
    public $cache;
    public $policies;
    public $parentRoutes;
    public $redirect;

    public function Route($request, $routeOptions, $parentRouteExpressions = '', $parentRoutes = array()) {
        // Setup an empty data variable
        $data = array();

        // Store the parent routes
        $this->parentRoutes = $parentRoutes;

        // Store the raw request
        $this->request = $request;

        // Handle empty requests
        if(empty($this->request)) {
            $this->matchesRequest = true;
        }
        else {
            // Set the options
            foreach($routeOptions as $key => $keyValue) {
                $this->{$key} = $keyValue;
            }
            //echo $this->request.'<br />';

            // Create the full route expression
            $this->fullExpression = $parentRouteExpressions.$this->expression;
            //echo 'Full expression: '.$this->fullExpression.'<br />';

            // Check if there is a capturing group in the full request
            if(String::contains('(', $this->fullExpression)) {
                $hasRegex = true;
                $partBeforeRegex = String::sub($this->fullExpression, 0, String::position('(', $this->fullExpression));
                //echo 'Part before regex: '.$partBeforeRegex.'<br />';
                if(!String::startsWith($partBeforeRegex, $this->request)) {
                    $hasRegex = false;
                }
            }
            else {
                $hasRegex = false;
            }
            
            // Use regex if there is a capturing group in the request definition
            if($hasRegex && preg_match_all('/'.String::addSlashes($this->fullExpression, '/').'/i', $this->request, $matches) > 0) {
                //echo 'Regex matched on '.$this->fullExpression.' with '.$this->request.'<br />'."\n";

                $this->matchesRequest = true;

                // Format the data in the matches
                $newMatches = array();
                // Get the number of parent route capture groups - this allows each child route to use capture groups indexed starting at 1
                $parentRouteCaptureGroups = String::subCount($parentRouteExpressions, '(') + 1;
                //echo 'Parent route capture groups: '.$parentRouteCaptureGroups.'<br />';
                for($i = $parentRouteCaptureGroups; $i < sizeof($matches); $i++) {
                    $newMatches[] = $matches[$i][0];
                }
                $matches = $newMatches;
                
                foreach($matches as $index => $keyValue) {
                    $matches[$index] = String::trim($keyValue, '/');
                }

                // Look for controller names, function names, or specific variables
                foreach($this->data as $index => $keyValue) {
                    if($keyValue['value'] == ':controller' && isset($matches[intval($keyValue['key']) - 1])) {
                        $this->controllerName = String::upperFirstCharacter($matches[intval($keyValue['key']) - 1]);
                        unset($matches[intval($keyValue['key']) - 1]);
                    }
                    else if($keyValue['value'] == ':function' && isset($matches[intval($keyValue['key']) - 1])) {
                        $this->functionName = String::upperFirstCharacter($matches[intval($keyValue['key']) - 1]);
                        unset($matches[intval($keyValue['key']) - 1]);
                    }
                    else if($keyValue['value'] == ':hash' && isset($matches[intval($keyValue['key']) - 1])) {
                        $hash = explode('/', $matches[intval($keyValue['key']) - 1]);
                        foreach($hash as $string) {
                            if(String::contains(':', $string)) {
                                $array = explode(':', $string);
                                $data[$array[0]] = $array[1];    
                            }
                        }
                        unset($matches[intval($keyValue['key']) - 1]);
                    }
                    else if(is_int(intval($keyValue['key'])) && isset($matches[intval($keyValue['key']) - 1])) {
                        $data[$keyValue['value']] = $matches[intval($keyValue['key']) - 1];
                        unset($matches[intval($keyValue['key']) - 1]);
                    }
                }

                // Set the rest of the data
                $variableCount = 1;
                foreach($matches as $match) {
                    if(!empty($match)) {
                        //$data['var'.$variableCount] = $match;
                        $variableCount++;
                    }
                }
            }
            // Do not use regex if there is no capturing group in the request definition
            else if($this->fullExpression == $this->request) {
                //echo $request.' matched against '.$this->fullExpression.'<br />';
                if(Arr::is($this->data)) {
                    foreach($this->data as $index => $keyValue) {
                        $data[$keyValue['key']] = $keyValue['value'];
                    }
                }
                
                $this->matchesRequest = true;
            }
        }

        // Only perform these checks if there is a request match
        if($this->matchesRequest) {
            //echo $this->request;

            // If there is a forced controller
            if(isset($routeOptions['controllerName']) && !empty($routeOptions['controllerName'])) {
                //echo 'There is a controller! --->'.$routeOptions['controllerName'].' <---<br />';
                $this->controllerName = $routeOptions['controllerName'];
            }

            // If there is a forced function
            if(isset($routeOptions['functionName']) && !empty($routeOptions['functionName'])) {
                //echo 'There is a function! --->'.$routeOptions['functionName'].' <---<br />';
                $this->functionName = $routeOptions['functionName'];
            }
            else {
                //echo 'Function not set, defaulting to index<br />';
                $this->functionName = 'index';
            }

            // Make sure the controller exists and include it
            // Check if the controller is already included
            if(class_exists($this->controllerName)) {
                //echo 'Class '.$this->controllerName.' exists.<br />';
                $this->controller = new $this->controllerName;

                // Make sure the controller function exists
                if(!method_exists($this->controller, $this->functionName)) {
                    //echo 'Function '.$this->functionName.' does not exist.<br />';
                    $this->matchesRequest = false;
                }
            }
            // Check the site path
            else if(File::exists(Project::getInstancePath().'controllers/'.$this->controllerName.'.php')) {
                //echo 'Controller file exists: '.$this->controllerName.'<br />';
                require_once(Project::getInstancePath().'controllers/'.$this->controllerName.'.php');
                $controllerName = String::explode('/', $this->controllerName);
                $controllerName = Arr::last($controllerName);
                $this->controller = new $controllerName;

                // Make sure the controller function exists
                if(!method_exists($this->controller, $this->functionName)) {
                    //echo 'Function '.$this->functionName.' does not exist.';
                    $this->matchesRequest = false;
                }
            }
            // Check the project path
            else if(File::exists(Project::getProjectPath().'controllers/'.$this->controllerName.'.php')) {
                require_once(Project::getProjectPath().'controllers/'.$this->controllerName.'.php');
                $this->controller = new $this->controllerName;

                // Make sure the controller function exists
                if(!method_exists($this->controller, $this->functionName)) {
                    //echo 'Function '.$this->functionName.' does not exist.';
                    $this->matchesRequest = false;
                }
            }
            else {
                //echo 'Controller file not found: '.Project::getInstancePath().'controllers/'.$this->controllerName.'.php<br />';
                $this->matchesRequest = false;
            }

            // Define the rest of data array as outlined in the route definition
            // Merge parent route data
            foreach($this->parentRoutes as $parentRoute) {
                if(Arr::is($parentRoute->data)) {
                    //print_r($parentRoute->data);
                    foreach($parentRoute->data as $index => $keyValue) {
                        if(!Arr::is($keyValue)) {
                            $data[$index] = $keyValue;
                        }
                        else if(!is_int($keyValue['key']) && !empty($keyValue['key']) && !empty($keyValue['value'])) {
                            //echo 'Adding '.$keyValue['key'].'<br />';
                            $data[$keyValue['key']] = $keyValue['value'];
                        }
                    }
                }
            }

            // Gather route data for current route
            if(Arr::is($this->data)) {
                foreach($this->data as $keyValue) {
                    if(!is_int($keyValue['key']) && isset($keyValue['key'][1]) && $keyValue['key'][1] != ':' && !empty($keyValue['value'])) {
                        //echo 'Adding '.$keyValue['key'].'<br />';
                        $data[$keyValue['key']] = $keyValue['value'];
                    }
                }
            }

            $this->data = $data;
        }

        return $this;
    }

    function follow() {
        //print_r($this); exit();
        return $this->controller->{$this->functionName}($this->data);
    }

}
?>