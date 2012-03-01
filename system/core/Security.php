<?php
class Security {

    public $response;

    function Security($route, $request) {
        // VERIFY ROUTE PERMISSIONS

        //Identifies what the data is and encodes it appropriately (JSON, arrays, files, sessions, cookies, etc)
        //Verifies the data being sent to the controller is clean
        //Passes data to the controller
        //if request is not verified, show the error page
        //Handle's IP blocking

        // merge everything into data
        // Automatically convert JSON strings into objects
        /*
            if(is_string($argumentToAdd) && Utility::isJson($argumentToAdd)) { // Handle raw JSON
                $argumentToAdd = json_decode($argumentToAdd);
            }
            else if(is_string($argumentToAdd) && Utility::isJson(stripslashes($argumentToAdd))) { // Handle JSON that has been escaped
                $argumentToAdd = json_decode(stripslashes($argumentToAdd));
            }
            else if(is_string($argumentToAdd) && Utility::isJson(urldecode($argumentToAdd))) { // Handle JSON that has been URL encoded
                $argumentToAdd = json_decode(urldecode($argumentToAdd));
            }
            else if(is_string($argumentToAdd) && Utility::isJson(urldecode(stripslashes($argumentToAdd)))) { // Handle JSON that has been URL encoded and slashed
                $argumentToAdd = json_decode(urldecode(stripslashes($argumentToAdd)));
            }
            */


        //echo Project::getInstanceAccessPath().'project/';

        // Go through all of the parent routes and evaluate their route policies
        //echo 'Parent routes: '.Arr::size($route->parentRoutes); exit();
        //if(Arr::is($route->parentRoutes)) {
        //    foreach($route->parentRoutes as $parentRoute) {
        //        //$parentRoute->policy
        //    }
        //}
        
        // Check to see if the instance is password protected
        $instanceSettings = Project::getInstance();
        if(isset($instanceSettings['accessControl']) && isset($instanceSettings['accessControl']['enabled']) && $instanceSettings['accessControl']['enabled'] === true) {
            //echo 'Password protection is on this instance.';
            self::enforceInstanceAccessControl($instanceSettings, $route);
        }
        else {
            //echo 'Password protection is not on this instance.';
        }

        // Handle route redirects
        if(!empty($route->redirect)) {
            header('Location: '.$route->redirect);
            exit();
        }

        // Define the direct read file extension
        $directReadFileExtension = array(
            '.js',
            '.css',
            '.txt',
            '.jpg',
            '.jpeg',
            '.gif',
            '.png',
            '.ico',
            '.pdf',
        );
        // Check to see if the request is for a direct read file extention
        $requestIsForDirectReadFileExtension = false;
        foreach($directReadFileExtension as $directReadFileExtension) {
            if(String::endsWith($directReadFileExtension, $request)) {
                $requestIsForDirectReadFileExtension = true;
            }
        }
        
        // Handle Project control requests that are for direct reads
        if($route !== null && $route->controllerName == 'Control' && $requestIsForDirectReadFileExtension) {
            //echo Project::getProjectPath().'views/'.String::replace('project/', '', $route->request); exit();
            $this->response = File::output(Project::getProjectPath().'views/'.String::replace('project/', '', $route->request));
        }
        // Handle instance requests that are for direct reads
        else if($route === null && $requestIsForDirectReadFileExtension) {
            //echo Project::$instance->instancePath.'views/'.$request; exit();
            $this->response = File::output(Project::getInstancePath().'views/'.$request);
        }
        // Show a 404 page if the route does not exist
        else if($route === null) {
            //echo Project::$instance->instancePath.'views/'.$request; exit();
            Router::setHeader('HTTP/1.0 404 Not Found');
            
            $this->response = '
                <html>
                <head>
                    <title>404</title>
                </head>
                <body>
                    <h1>Page Not Found</h1>
                    <p>We weren\'t able to find the page at: '.$request.'</p>
                    <p>HTTP Error 404</p>
                </body>
                </html>
            ';
        }
        else {
            //print_r($route); exit();
            $this->response = $route->follow();
        }
        
        // Check the cache is caching is enabled
        // Check to see if the current route has caching enabled
        //$cacheHit = false; // start a cache with the request if the route has caching enabled
        //if($cacheHit) {
        //    // return the cache
        //    echo 'CACHE HIT';
        //}
        // TODO: Cache the view if caching is enabled
    }
    
    public static function enforceInstanceAccessControl($instanceSettings, $route) {
        $grantAccess = true;
        $remoteIpV4AddressIsAllowed = false;

        // Check the allowed IPv4 addresses
        if(isset($instanceSettings['accessControl']['allowedIpV4Addresses']) && Arr::is($instanceSettings['accessControl']['allowedIpV4Addresses'])) {
            // Check if the array contains their IP address
            foreach($instanceSettings['accessControl']['allowedIpV4Addresses'] as $ipV4Address) {
                //echo 'Now checking against '.$ipV4Address.'.';

                // Compare each octet
                $ipV4AddressOctets = String::explode('.', $ipV4Address);
                $remoteIpV4AddressOctets = String::explode('.', $_SERVER['REMOTE_ADDR']);
                $remoteIpV4AddressIsAllowed = true;
                for($i = 0; $i < Arr::size($ipV4AddressOctets); $i++) {
                    //echo 'Comparing '.$ipV4AddressOctets[$i].' against '.$remoteIpV4AddressOctets[$i].'.';
                    if($ipV4AddressOctets[$i] != $remoteIpV4AddressOctets[$i] && $ipV4AddressOctets[$i] != '*') {
                        //echo 'No match.';
                        $remoteIpV4AddressIsAllowed = false;
                        break;
                    }
                }

                // Get out of the foreach if we've found a match
                if($remoteIpV4AddressIsAllowed) {
                    break;
                }
            }
            
            if($remoteIpV4AddressIsAllowed) {
                //echo 'Access granted, '.$_SERVER['REMOTE_ADDR'].' is in the allowed IPv4 address list.';
            }
            else {
                //echo 'Access denied, '.$_SERVER['REMOTE_ADDR'].' is not in the allowed IPv4 address list.';
                $grantAccess = false;
            }
        }

        // Check if an administrator login is required
        if(isset($instanceSettings['accessControl']['requireAdministratorLogin']) && $instanceSettings['accessControl']['requireAdministratorLogin'] === true && !UserApi::$user) {               
            $grantAccess = false;
            //echo 'Access denied for not being logged in.';

            // Check to see if they need to login even if their IP address is in the list
            if($remoteIpV4AddressIsAllowed && isset($instanceSettings['accessControl']['requireAdministratorLoginForAllowedIpV4Addresses']) && $instanceSettings['accessControl']['requireAdministratorLoginForAllowedIpV4Addresses'] === false) {
                //echo 'Access granted because administrator login is not required for authorized IPv4 addresses.';
                $grantAccess = true;
            }
        }
        else if(UserApi::$user) {
            // Access granted because you are logged in
            //echo 'Access granted because you are logged in.';
            $grantAccess = true;
        }

        if(!$grantAccess) {
            if((!isset($route->data['path']) || $route->data['path'] != 'login') && (!isset($route->data['apiName']) || $route->data['apiName'] != 'forms')) {
                $redirect = $route->request;
                $redirect = Url::encode($redirect);
                //echo 'We should redirect to '.$redirect.' after login.';
                //print_r($route); exit();
                Router::redirect(Project::getInstanceAccessPath().'project/login/?redirect='.$redirect);    
            }
        }
        
    }

    public static function md5($string) {
        return md5($string);
    }
    
    public static function sha512($string) {
        return hash('sha512', $string);
    }

    public static function generateBase64Salt($length = 32) {
         // create a salt that ensures crypt creates an md5 hash
        $base64Characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
        $salt = '';
        for($i = 0; $i < $length; $i++) {
            $salt .= $base64Characters[mt_rand(0,63)];
        }

        return $salt;
    }
    
}
?>