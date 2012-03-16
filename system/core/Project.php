<?php
class Project {

    /**
     * Singleton instance
     *
     * @var Project
     */
    private static $instance;

    // General
    private $projectVersion = '0.2';

    // Instance settings
    private $projectPath; // The system path of project
    private $siteTitle; // The title of the installation
    private $instanceId; // The ID of the current instance
    private $instancePath; // The system path of the current instance
    private $instanceAccessPath; // The base URL access of the current instance (typically just /)
    private $instanceHost;
    private $instanceType;
    private $instanceSetupTime;

    // Settings
    private $settings = array();

    // Essential modules
    private $essentialModules = array(
        'Errors',
        'Logging',
        'Routes',
        'Cookies',
        'Sessions',
        'Apis',
        'Forms',
        'Email',
    );

    // Array of class names => file paths
    private $classes;

    // A private constructor; prevents direct creation of object
    private function __construct() {
    }

    // Prevent users from cloning the instance
    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    // The singleton method
    public static function singleton() {
        if(!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
            self::$instance->projectPath = str_replace('system/core', '', dirname(__FILE__));
        }

        return self::$instance;
    }

    public static function saveSettings() {
        return File::write(Project::getInstancePath().'project/settings.php', "<?php".
            "\n".'$settings = '.Arr::php(Project::getSettings())."\n".
            "?>"
        );
    }

    public static function getSettings() {
        return Project::$instance->settings;
    }

    public static function setSettings($settings) {
        return Project::$instance->settings = $settings;
    }

    public static function getClasses() {
        return Project::$instance->classes;
    }

    public static function setClasses($classes) {
        return Project::$instance->classes = $classes;
    }

    public static function loadSettings() {
        // Define where the class files exist for autoloading
        Project::setClasses(array(
            // Core
            'Controller' => Project::getProjectPath().'system/core/Controller.php',
            'Module' => Project::getProjectPath().'system/core/Module.php',
            'Router' => Project::getProjectPath().'system/core/Router.php',
            'Security' => Project::getProjectPath().'system/core/Security.php',
            'View' => Project::getProjectPath().'system/core/View.php',

            // Exceptions
            'InvalidArgumentException' => Project::getProjectPath().'system/exceptions/InvalidArgumentException.php',
            'MethodNotImplementedException' => Project::getProjectPath().'system/exceptions/MethodNotImplementedException.php',
            'UnsupportedOperationException' => Project::getProjectPath().'system/exceptions/UnsupportedOperationException.php',

            // Utility
            'Arr' => Project::getProjectPath().'system/utility/Arr.php',
            'Browser' => Project::getProjectPath().'system/utility/Browser.php',
            'Dir' => Project::getProjectPath().'system/utility/Dir.php',
            'File' => Project::getProjectPath().'system/utility/File.php',
            'Image' => Project::getProjectPath().'system/utility/Image.php',
            'HtmlDocument' => Project::getProjectPath().'system/utility/HtmlDocument.php',
            'HtmlElement' => Project::getProjectPath().'system/utility/HtmlElement.php',
            'Network' => Project::getProjectPath().'system/utility/Network.php',
            'Number' => Project::getProjectPath().'system/utility/Number.php',
            'Object' => Project::getProjectPath().'system/utility/Object.php',
            'String' => Project::getProjectPath().'system/utility/String.php',
            'Json' => Project::getProjectPath().'system/utility/Json.php',
            'Time' => Project::getProjectPath().'system/utility/Time.php',
            'Url' => Project::getProjectPath().'system/utility/Url.php',
            'WebRequest' => Project::getProjectPath().'system/utility/WebRequest.php',
            'WebResponse' => Project::getProjectPath().'system/utility/WebResponse.php',
            'Yaml' => Project::getProjectPath().'system/utility/Yaml.php',
            'vCard' => Project::getProjectPath().'system/utility/vCard.php',

            // Users
            'UserApi' => Project::getProjectPath().'system/modules/users/controllers/apis/UserApi.php',
        ));

        // Check to see if settings have already been loaded from an index.php call
        global $settings;
        if(isset($settings)) {
            Project::setSettings($settings);
        }
        // If settings have not already been loaded, load them from settings.php
        else if(File::exists(Dir::name(getcwd()).'/project/settings.php')) {
            require_once(Dir::name(getcwd()).'/project/settings.php');
            Project::setSettings($settings);
        }

        // If settings are set, process them
        $settings = Project::getSettings();
        if(!empty($settings)) {
            // Set the site title
            if(isset($settings['siteTitle'])) {
                Project::setSiteTitle($settings['siteTitle']);
            }

            // Identify the specific instance settings
            foreach($settings['instances'] as $instance) {                
                if(($_SERVER['HTTP_HOST'] == $instance['host'] || $_SERVER['SERVER_NAME'] == $instance['host']) && is_dir($instance['projectPath'])) {
                    if(isset($instance['id'])) {
                        Project::setInstanceId($instance['id']);
                    }
                    if(isset($instance['path'])) {
                        Project::setInstancePath($instance['path']);
                    }
                    if(isset($instance['accessPath'])) {
                        Project::setInstanceAccessPath($instance['accessPath']);
                    }
                    if(isset($instance['host'])) {
                        Project::setInstanceHost($instance['host']);
                    }
                    if(isset($instance['type'])) {
                        Project::setInstanceType($instance['type']);
                    }
                    if(isset($instance['setupTime'])) {
                        Project::setInstanceSetupTime($instance['setupTime']);
                    }
                    
                    break;
                }
            }
        }
        else {
            Project::setSettings(array());
        }

        // Redirect to instance setup if necessary
        Project::setupInstance();
    }

    public static function loadModule($moduleName) {
        require_once(Project::getProjectPath().'system/modules/'.String::camelCaseToDashes($moduleName).'/'.String::replace(' ', '', $moduleName).'Module.php');
        Module::initialize($moduleName);
    }

    public static function loadModules() {
        // Get all modules in settings
        $modulesToLoad = Project::getModuleSettings();

        // Force the essential modules
        foreach(Project::getEssentialModules() as $essentialModule) {
            if(!Arr::hasKey($essentialModule, $modulesToLoad)) {
                $modulesToLoad[$essentialModule] = array();
            }
        }

        // TODO: Order the modules based on dependency priority, force

        // Go through settings and conditionally load other modules and initialize the modules
        foreach($modulesToLoad as $moduleName => $moduleSettings) {
            // Do not reload essential modules
            if(!Arr::contains($moduleName, Project::getEssentialModules())) {
                // Load the module file
                require_once(Project::getProjectPath().'system/modules/'.String::camelCaseToDashes($moduleName).'/'.String::replace(' ', '', $moduleName).'Module.php');
            }
            Module::initialize($moduleName);
        }

        // Load the modules
        foreach($modulesToLoad as $moduleName => $moduleSettings) {
            $moduleClass = $moduleName.'Module';

            // Load the settings
            $settings = Project::getModuleSettings($moduleName);
            if(empty($settings)) {
                $settings = Project::setModuleSettings($moduleName, call_user_func(array($moduleClass, 'getDefaultSettings')));
            }

            // Call the ::load method on the class
            call_user_func(array($moduleClass, 'load'), $settings);
        }
    }

    public static function getModuleSettings($moduleKey = null) {
        //echo 'Getting module settings ('.$moduleKey.')<br />';

        if($moduleKey === null) {
            return isset(Project::$instance->settings['modules']) ? Project::$instance->settings['modules'] : array();
        }
        else {
            return isset(Project::$instance->settings['modules']) && Arr::hasKey($moduleKey, Project::$instance->settings['modules']) ? Project::$instance->settings['modules'][$moduleKey] : false;
        }
    }

    public static function getInstance($instanceId = null) {
        // Get the current instance ID
        if($instanceId == null) {
            $instanceId = Project::$instance->instanceId;
        }
        
        // Find the instance
        foreach(Project::$instance->settings['instances'] as $instance) {
            if(!isset($instance['id']) || $instance['id'] == $instanceId) {
                return $instance;
            }
        }
        
        return false;
    }

    public static function addInstance($instance) {
        Project::$instance->settings['instances'][] = $instance;

        return $instance;
    }

    public static function updateInstance($instanceId, $instanceOptions) {
        foreach(Project::$instance->settings['instances'] as &$instance) {
            // If there is no instance ID (newly provisioned) or if the instance ID's match, or if there is only one instance
            if(!isset($instance['id']) || $instance['id'] == $instanceId || Arr::size(Project::$instance->settings['instances']) == 1) {
                foreach($instanceOptions as $key => $value) {
                    //echo 'Setting '.$key.' to '.$value.'<br />';
                    $instance[$key] = $value;
                }

                return $instance;
            }
        }

        return false;
    }

    public static function deleteInstance($instanceId) {
        //echo 'Deleting '.$instanceId;
        foreach(Project::$instance->settings['instances'] as &$instance) {
            if($instance['id'] == $instanceId) {
                $instance = null;
                break;
            }
        }

        Project::$instance->settings['instances'] = Arr::filter(Project::$instance->settings['instances']);

        return false;
    }

    public static function getInstances() {
        return Project::$instance->settings['instances'];
    }

    public static function getSiteTitle() {
        return Project::$instance->siteTitle;
    }

    public static function setSiteTitle($siteTitle) {
        Project::$instance->settings['siteTitle'] = $siteTitle;
        Project::$instance->siteTitle = $siteTitle;

        return $siteTitle;
    }

    public static function getProjectPath() {
        return Project::$instance->projectPath;
    }

    public static function setProjectPath($projectPath) {
        return Project::$instance->projectPath = $projectPath;
    }

    public static function getProjectVersion() {
        return Project::$instance->projectVersion;
    }

    public static function setProjectVersion($projectVersion) {
        return Project::$instance->projectVersion = $projectVersion;
    }

    public static function getInstanceId() {
        return Project::$instance->instanceId;
    }

    public static function setInstanceId($instanceId) {
        return Project::$instance->instanceId = $instanceId;
    }

    public static function getInstancePath() {
        return Project::$instance->instancePath;
    }

    public static function setInstancePath($instancePath) {
        return Project::$instance->instancePath = $instancePath;
    }

    public static function getInstanceAccessPath() {
        return Project::$instance->instanceAccessPath;
    }

    public static function setInstanceAccessPath($instanceAccessPath) {
        return Project::$instance->instanceAccessPath = $instanceAccessPath;
    }

    public static function getInstanceType() {
        return Project::$instance->instanceType;
    }

    public static function setInstanceType($instanceType) {
        return Project::$instance->instanceType = $instanceType;
    }

    public static function getInstanceHost() {
        return Project::$instance->instanceHost;
    }

    public static function setInstanceHost($instanceHost) {
        return Project::$instance->instanceHost = $instanceHost;
    }

    public static function getInstanceSetupTime() {
        return Project::$instance->instanceSetupTime;
    }

    public static function setInstanceSetupTime($instanceSetupTime) {
        return Project::$instance->instanceSetupTime = $instanceSetupTime;
    }

    public static function getAdministrator() {
        return Project::$instance->settings['administrator'];
    }

    public static function setAdministrator($administrator) {
        return Project::$instance->settings['administrator'] = $administrator;
    }

    public static function getAdministratorUsername() {
        return Project::$instance->settings['administrator']['username'];
    }

    public static function setAdministratorUsername($username) {
        return Project::$instance->settings['administrator']['username'] = $username;
    }

    public static function getAdministratorEmail() {
        return Project::$instance->settings['administrator']['email'];
    }

    public static function setAdministratorEmail($email) {
        return Project::$instance->settings['administrator']['email'] = $email;
    }

    public static function getAdministratorPasswordSalt() {
        return Project::$instance->settings['administrator']['passwordSalt'];
    }

    public static function setAdministratorPasswordSalt($passwordSalt) {
        return Project::$instance->settings['administrator']['passwordSalt'] = $passwordSalt;
    }

    public static function getAdministratorPassword() {
        return Project::$instance->settings['administrator']['password'];
    }

    public static function setAdministratorPassword($password) {
        return Project::$instance->settings['administrator']['password'] = $password;
    }

    public static function getEssentialModules() {
        return Project::$instance->essentialModules;
    }

    public static function getActiveModules() {
        $activeModules = array();
        foreach(Project::$instance->settings['modules'] as $moduleName => $moduleSettings) {
            $activeModules[] = $moduleName;
        }

        return $activeModules;
    }

    public static function getInactiveModules() {
        $inactiveModules = array();
        if(isset(Project::$instance->settings['inactiveModules'])) {
            foreach(Project::$instance->settings['inactiveModules'] as $moduleName => $moduleSettings) {
                $inactiveModules[] = $moduleName;
            }
        }

        return $inactiveModules;
    }

    public static function getSystemModules() {
        $modules = Dir::read(Project::$instance->projectPath.'system/modules/', false);
        foreach($modules as &$modulePath) {
            $modulePath = String::replace(Project::$instance->projectPath.'system/modules/', '', $modulePath);
            $modulePath = String::dashesToTitle($modulePath);
            $modulePath = Module::getModuleKey($modulePath);

            // Don't use any directory starting with and underscore
            if(!Module::exists($modulePath) || String::startsWith('_', $modulePath)) {
                $modulePath = null;
            }
        }
        $modules = Arr::filter($modules);
        Arr::sort($modules);

        return $modules;
    }

    public static function getInstanceModules() {
        $modules = Dir::read(Project::$instance->instancePath.'project/modules/', false);
        foreach($modules as &$modulePath) {
            $modulePath = String::replace(Project::$instance->projectPath.'system/modules/', '', $modulePath);
            $modulePath = String::dashesToTitle($modulePath);

            // Don't use any directory starting with and underscore
            if(String::startsWith('_', $modulePath)) {
                $modulePath = null;
            }
        }
        $modules = Arr::filter($modules);
        Arr::sort($modules);

        return $modules;
    }

    public static function setModuleSettings($moduleKey, $settings) {
        Project::$instance->settings['modules'][$moduleKey] = $settings;

        return $settings;
    }

    public static function deactivateModule($moduleKey) {
        Project::$instance->settings['inactiveModules'][$moduleKey] = Project::$instance->settings['modules'][$moduleKey];
        unset(Project::$instance->settings['modules'][$moduleKey]);

        return true;
    }

    public static function activateModule($moduleKey) {
        Project::$instance->settings['modules'][$moduleKey] = Project::$instance->settings['inactiveModules'][$moduleKey];
        unset(Project::$instance->settings['inactiveModules'][$moduleKey]);

        return true;
    }

    public static function uninstallModule($moduleKey) {
        unset(Project::$instance->settings['modules'][$moduleKey]);
        unset(Project::$instance->settings['inactiveModules'][$moduleKey]);

        return true;
    }

    public static function setupInstance() {
        // Check to see if the instance needs to be setup
        $instanceSetupTime = Project::getInstanceSetupTime();
        if(empty($instanceSetupTime) && isset($_GET['project']) && !String::contains('project/', $_GET['project']) && !String::contains('null/', $_GET['project']) && !String::contains('api/', $_GET['project'])) {
            //echo 'Setup triggered!'; exit();
            Router::redirect(Project::getInstanceAccessPath().'project/setup/');
        }
    }

    public static function startRouter() {
        //echo 'Starting router, looking for: '.$_GET['project']; exit();
        $router = new Router($_GET['project']);
        $router->headers()->response();
        exit();
    }

    public static function loadStaticProjectItem() {
        // Check to see if the request is for a project image,
        if(strpos($_GET['project'], 'project/images') !== false || strpos($_GET['project'], 'project/scripts') !== false || strpos($_GET['project'], 'project/styles') !== false) {
            global $instance;
            // If it ends with .php, include it
            if(strrpos($_GET['project'], '.php') === strlen($_GET['project']) - strlen('.php')) {
                include($instance['projectPath'].'views/'.str_replace('project/', '', $_GET['project']));
            }
            else {
                File::output($instance['projectPath'].'views/'.str_replace('project/', '', $_GET['project']));
            }

            return true;
        }
        else {
            return false;
        }
    }

    public static function addAutoLoadClasses($classes, $type = 'project') {
        //print_r($classes);
        if(!empty($classes)) {
            $path = $type == 'project' ? Project::$instance->projectPath : Project::$instance->instancePath;

            foreach($classes as $className => $classPath) {
                Project::$instance->classes[$className] = $path.$classPath;
            }
        }
    }

    public static function getHeaders() {
        if(!function_exists('getallheaders')) {
            $headers = array();
            $rx_http = '/\AHTTP_/';
            foreach($_SERVER as $key => $val) {
                if(preg_match($rx_http, $key)) {
                    $arh_key = preg_replace($rx_http, '', $key);
                    $rx_matches = array();
                    // do some nasty string manipulations to restore the original letter case
                    // this should work in most cases
                    $rx_matches = explode('_', $arh_key);
                    if(count($rx_matches) > 0 and strlen($arh_key) > 2) {
                        foreach($rx_matches as $ak_key => $ak_val)
                            $rx_matches[$ak_key] = ucfirst($ak_val);
                        $arh_key = implode('-', $rx_matches);
                    }
                    $headers[$arh_key] = $val;
                }
            }
        }
        else {
            $headers = getallheaders();
        }

        return $headers;
    }

    public static function requireOnce($file) {
        require_once($file);
    }

    public static function getAutoLoadClasses() {
        return Project::$instance->classes;
    }

    public static function autoLoad($className) {
        // Define where the class files exist
        global $instance;
        //$projectPath = Project::getProjectPath();
        $projectPath = $instance['projectPath'];
        $essentialClasses = array(
            // Core
            'File' => $projectPath.'system/utility/File.php',
            'String' => $projectPath.'system/utility/String.php',
        );

        // Check the core classes
        if(isset($essentialClasses[$className])) {
            require_once($essentialClasses[$className]);
            return true;
        }
        // Check the Project classes
        else if(isset(Project::$instance->classes[$className])) {
            require_once(Project::$instance->classes[$className]);

            return true;
        }
        // Require the file using the classes array
        else if(String::endsWith('Module', $className)) {
            $fileName = $projectPath.'system/modules/'.String::camelCaseToDashes(String::replace('Module', '', $className)).'/'.$className.'.php';
            if(File::exists($fileName)) {
                require_once($fileName);
                return true;
            }
            //else {
            //    echo 'File does not exist: '.$fileName.'<br />';
            //}
        }
        else {
            //echo 'Failed to load '.$className.'<br />';
            return false;
        }
    }

}

// Define the class autoloading function
spl_autoload_register('Project::autoLoad');

// If the request is not null and not to load a static Project item (image, script, or style), then start Project
if(isset($_GET['project']) && strpos($_GET['project'], 'null') === false && !Project::loadStaticProjectItem()) {
    Project::singleton();
    Project::loadSettings();
    Project::loadModules();
    Project::startRouter();
}
?>