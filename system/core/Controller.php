<?php
class Controller {

    public $view;
    
    public static function buildCascadingViewFunctionName($array, $currentView = 'view', $currentFunctionName = '') {
        $currentPiece = String::dashesToCamelCase($array[$currentView]);
        $functionName = $currentFunctionName.$currentPiece.'-';
        
        $nextView = $currentPiece.'View';
        if(isset($array[$nextView]) && $currentView !== $nextView) {
            return self::buildCascadingViewFunctionName($array, $nextView, $functionName);
        }
        else {
            return String::dashesToCamelCase($functionName);
        }
    }

    public static function requireOnce($controllerName) {
        Project::requireOnce(Project::getInstancePath().'controllers/'.$controllerName.'.php');
    }

    public static function getView($path, $data = array()) {
        // Set the view variables
        foreach($data as $key => $value) {
            if($key !== 'project' && $key != 'path') {
                ${$key} = $value;
            }
        }

        ob_start();
        // Handle project view calls
        if(String::startsWith('Project:', $path)) {
            include(Project::getProjectPath().'views/'.String::replace('Project:', '', $path).'.php');
        }
        // Handle system module calls
        else if(String::startsWith('Module:', $path)) {
            $moduleFile = Project::getProjectPath().'system/modules/'.String::replaceOccurences('\/', '/views/', String::replace('Module:', '', $path), 1).'.php';
            //echo $moduleFile.'<br />';
            $moduleControlFile = Project::getProjectPath().'system/modules/'.String::replaceOccurences('\/', '/views/control/', String::replace('Module:', '', $path), 1).'.php';
            //echo $moduleControlFile.'<br />'; exit();

            if(File::exists($moduleFile)) {
                include($moduleFile);
            }
            else if(File::exists($moduleControlFile)) {
                include($moduleControlFile);
            }
        }
        else {
            include(Project::getInstancePath().'views/'.$path.'.php');
        }
        $view = ob_get_clean();

        return $view;
    }

    public static function getVariable($path, $data = array()) {
        return self::getHtmlElement($path, $data);
    }

    public static function getHtmlElement($path, $data = array()) {
        //echo $path.'<br />'; //exit();

        // Set the view variables
        if(Arr::is($data) || Object::is($data)) {
            foreach($data as $key => $value) {
                if($key !== 'project' && $key != 'path') {
                    ${$key} = $value;
                }
            }
        }
                
        //ob_start();
        // Handle project view calls
        if(String::startsWith('Project:', $path)) {
            include(Project::getProjectPath().'views/'.String::replace('Project:', '', $path).'.php');
        }
        // Handle system module calls
        else if(String::startsWith('Module:', $path)) {
            $moduleFile = Project::getProjectPath().'system/modules/'.String::replaceOccurences('\/', '/views/', String::replace('Module:', '', $path), 1).'.php';
            //echo $moduleFile.'<br />';
            $moduleControlFile = Project::getProjectPath().'system/modules/'.String::replaceOccurences('\/', '/views/control/', String::replace('Module:', '', $path), 1).'.php';
            //echo $moduleControlFile.'<br />'; exit();

            if(File::exists($moduleFile)) {
                include($moduleFile);
            }
            else if(File::exists($moduleControlFile)) {
                include($moduleControlFile);
            }
        }
        else if(String::startsWith('LocalizedSettings:', $path)) {
            $path = 'settings-'.String::replace('LocalizedSettings:', '', $path);
            include(Project::getInstancePath().'project/'.$path.'.php');
        }
        else {
            include(Project::getInstancePath().'views/'.$path.'.php');
        }
        //ob_clean();

        // Find the variable name
        if(String::contains('/', $path)) {
            $variable = String::dashesToCamelCase(String::sub($path, String::lastIndexOf('/', $path) + 1));    
        }
        else {
            $variable = String::dashesToCamelCase($path);    
        }
        
        if(!isset(${$variable})) {
            $debugBacktrace = debug_backtrace();
            die('Variable "'.$variable.'" not found. Call came from '.$debugBacktrace[0]['file'].' line '.$debugBacktrace[0]['line'].'.');
        }
        else {
            return ${$variable};
        }        
    }

    public static function getControllerView($path, $data, $function = 'index') {
        // Get the name of the class
        $controllerName = String::explode('/', $path);
        $controllerName = $controllerName[Arr::size($controllerName) - 1];
        
        if(!class_exists($controllerName)) {
            include(Project::getInstancePath().'controllers/'.$path.'.php');
        }

        $controller = new $controllerName();
                
        return $controller->{$function}($data);
    }

}
?>