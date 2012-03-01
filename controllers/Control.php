<?php
class Control extends Controller {

    function index($data) {
        //print_r($_SESSION);
        //print_r($data);

        // Set the default path to be the dashboard
        if(empty($data['path'])) {
            Router::redirect(Project::getInstanceAccessPath().'project/dashboard/');
        }
        // If they are logging out
        else if($data['path'] == 'logout') {
            UserApi::logout(Project::getInstanceAccessPath().'project/login/');
        }
        // If they are trying to run setup
        else if($data['path'] == 'setup') {
            Router::redirect(Project::getInstanceAccessPath().'project/');
        }
        // Redirect to the login
        // TODO: This should happen at the route policy level
        else if(!UserApi::$user && $data['path'] != 'login') {
            $redirect = Project::getInstanceAccessPath().'project/'.$data['path'].'/';
            $redirect = Url::encode($redirect);
            Router::redirect(Project::getInstanceAccessPath().'project/login/?redirect='.$redirect);
        }
        // Temporary fix, only allow the settings.php admin user access the control panel
        else if(UserApi::$user['username'] != Project::getAdministratorEmail() && UserApi::$user['username'] != Project::getAdministratorUsername() && $data['path'] != 'login') {
            Router::redirect(Project::getInstanceAccessPath());
        }

        // Setup the path pieces and any path arguments
        $data['pathPieces'] = String::explode('/', $data['path']);
        $data['pathArguments'] = array();
        // Rebuild the path pieces array by checking for path arguments
        $pathPieces = array();
        foreach($data['pathPieces'] as $pathPiece) {
            if(!String::contains(':', $pathPiece)) {
                $pathPieces[] = $pathPiece;
            }
            else {
                $argument = String::explode(':', $pathPiece);
                if(Json::is($argument[1])) {
                    $argument[1] = Json::decode($argument[1]);
                }
                $data['pathArguments'][$argument[0]] = $argument[1];
            }
        }
        $data['pathPieces'] = $pathPieces;
        $data['path'] = Arr::implode('/', $data['pathPieces']);
        $pathTitle = String::dashesToTitle($data['pathPieces'][Arr::size($data['pathPieces']) - 1]);

        // Build out the trail
        $trailPieces = array();
        $trailPieces[] = array('title' => Project::getSiteTitle(), 'path' => '');
        // Check if it a is a module call
        if($data['pathPieces'][0] == 'modules') {
            $moduleCall = true;
        }
        else {
            $moduleCall = false;
        }
        $trailPath = '';
        foreach($data['pathPieces'] as $pathPiece) {
            $trailPath .= $pathPiece.'/';
            $trailPieces[] = array('title' => String::dashesToTitle($pathPiece), 'path' => $trailPath);
        }
        // Handle modules being places in settings
        if($moduleCall && isset($trailPieces[1]) && $trailPieces[1]['title'] == 'Modules' && isset($trailPieces[3]) && $trailPieces[3]['title'] == 'Settings') {
            $trailPieces[1] = null;
            $trailPieces[2] = null;
            $trailPieces = Arr::rekey(Arr::filter($trailPieces));

            // Reset the settings path
            $trailPieces[1]['path'] = 'settings/';
        }
        else if($moduleCall) {
            $trailPieces[1] = null;
            $trailPieces = Arr::rekey(Arr::filter($trailPieces));
        }
        //print_r($trailPieces);

        // Handle module calls
        if($data['pathPieces'][0] == 'modules') {
            $function = 'modules';
            $data['pathPieces'][0] = null;
            $data['modulePath'] = $data['pathPieces'][1];
            $data['moduleName'] = String::title(String::reverseUrlPath($data['pathPieces'][1]));
            $data['pathPieces'][1] = null;
            $data['pathPieces'] = Arr::rekey(Arr::filter($data['pathPieces']));
            $data['path'] = Arr::implode('/', $data['pathPieces']);
        }
        // Handle core calls
        else {
            $function = String::dashesToCamelCase(Arr::implode('-', $data['pathPieces']));
        }

        // Create a document
        $this->view = new HtmlDocument();
        $data['document'] = $this->view;

        // CSS
        $this->view->css[] = 'project/styles/project.css';        
        $this->view->css[] = 'project/styles/forms.css';
        

        // JavaScript
        $this->view->javaScript[] = 'project/scripts/jQuery.js';
        $this->view->javaScript[] = 'project/scripts/Class.js';
        $this->view->javaScript[] = 'project/scripts/Json.js';
        $this->view->javaScript[] = 'project/scripts/Form.php';
        $this->view->javaScript[] = 'project/scripts/Project.js';
        $this->view->javaScript[] = 'project/scripts/Security.js';
        
        // Build the page
        $this->view->head->append($this->getView('Project:control/head'));
        
        // Control panel mode
        if($data['path'] !== 'setup' && $data['path'] !== 'login') {
            $data['document']->title = $pathTitle.' - Project ('.Project::getSiteTitle().')';
            $this->view->css[] = 'project/styles/control.css';
            $this->view->body->append('
                <div id="topWrapper">'.
                    $this->getHtmlElement('Project:control/header', array('siteTitle' => Project::getSiteTitle(), 'instanceType' => Project::getInstanceType())).'
                    <div id="contentWrapper">'.
                        $this->sideNavigation(array('trailPieces' => $trailPieces)).'
                        <div id="content">'.
                            $this->getHtmlElement('Project:control/trail', array('trailPieces' => $trailPieces, 'siteTitle' => Project::getSiteTitle(), 'pathTitle' => $pathTitle)).
                            $this->{$function}($data).'
                        </div>
                    </div>
                </div>');
            $this->view->body->append($this->getHtmlElement('Project:control/footer'));
        }
        // Login
        else {
            $data['document']->title = $pathTitle.' - Project';
            $this->view->css[] = 'project/styles/login.css';
            $this->view->body->append($this->{$data['path']}($data));
        }

        return $this->view;
    }

    public function login($data) {
        return $this->getHtmlElement('Project:control/user/forms/login', $data);
    }

    public function setup($data) {
        return $this->getHtmlElement('Project:control/'.String::camelCaseToDashes($data['path']), $data);
    }

    public function sideNavigation($data) {
        // Set the essential navigation items
        $data['navigationItems'] = $this->getNavigationItems();

        return $this->getHtmlElement('Project:control/side-navigation', $data);
    }

    public function modules($data) {
        $moduleKey = Module::getModuleKey($data['moduleName']);

        if(!Arr::contains($moduleKey, Project::getActiveModules())) {
            return '<p>'.$data['moduleName'].' module not enabled.</p>';
        }

        require_once(Project::getProjectPath().'system/modules/'.$data['modulePath'].'/controllers/'.$moduleKey.'Control.php');
        $controllerName = $moduleKey.'Control';
        $controller = new $controllerName();
        $data['function'] = String::dashesToCamelCase(Arr::implode('-', $data['pathPieces']));
        
        return $controller->index($data);
    }

    public function getNavigationItems() {
        $essentialNavigationItems = array(
            array(
                'title' => 'Dashboard',
                'path' => 'dashboard/',
            ),
            array(
                'title' => 'Settings',
                'path' => 'settings/',
                'subItems' => array(
                    array(
                        'title' => 'Instances',
                        'path' => 'settings/instances/',
                        'subItems' => array(
                            array(
                                'title' => 'Add an Instance',
                                'path' => 'settings/instances/add-an-instance/',
                            )
                        ),
                    ),
                    array(
                        'title' => 'Modules',
                        'path' => 'settings/modules/',
                    ),
                    array(
                        'title' => 'Administrator',
                        'path' => 'settings/administrator/',
                        'subItems' => array(
                            array(
                                'title' => 'Change Password',
                                'path' => 'settings/administrator/change-password/',
                            )
                        ),
                    ),
                ),
            ),
        );

        // Get the essential navigaton items
        $navigationItems = $essentialNavigationItems;

        // Get the navigation items to merge to the essential navigation items
        $navigationItemsToMerge = array();
        foreach($this->getActiveModuleNavigationItems() as $moduleNavigationItemsArray) {
            foreach($moduleNavigationItemsArray as $moduleNavigationItems) {
                $navigationItems[] = $moduleNavigationItems;
            }
        }

        $navigationItems = $this->mergeNavigationItems($navigationItems);

        return $navigationItems;
    }

    function mergeNavigationItems($subitems) {
        $titles = Array();
        foreach($subitems as $subkey => $subvalue) {
            if(!is_array($subvalue)
                    || !isset($subvalue['title'])
                    || !isset($subvalue['subItems'])
                    || !is_array($subvalue['subItems'])) {

                //Leave this element as-is as it doesn't conform to expectations.
                continue;
            }
            if(isset($titles[$subvalue['title']])) {
                foreach($subvalue['subItems'] as $subItem) {
                    $subitems[$titles[$subvalue['title']]]['subItems'][] = $subItem;
                }
                unset($subitems[$subkey]);
            }
            else {
                $titles[$subvalue['title']] = $subkey;
            }
        }
        foreach($subitems as $subkey => $subvalue) {
            if(is_array($subvalue) && isset($subvalue['subItems'])) {
                $subitems[$subkey]['subItems'] = $this->mergeNavigationItems($subvalue['subItems']);
            }
        }
        return $subitems;
    }

    public function getActiveModuleNavigationItems() {
        $navigationItems = array();
        foreach(Project::getActiveModules() as $moduleKey) {
            $moduleNavigationItems = call_user_func(array($moduleKey.'Module', 'getControlNavigation'));
            if(!empty($moduleNavigationItems)) {
                $navigationItems[$moduleKey] = $moduleNavigationItems;
            }
        }

        return $navigationItems;
    }

    function dashboard($data) {
        return $this->getView('Project:control/'.String::camelCaseToDashes($data['path']));
    }

    function settings($data) {
        return $this->getView('Project:control/'.String::camelCaseToDashes($data['path']), $data);
    }

    function settingsInstances($data) {
        return $this->getView('Project:control/'.String::camelCaseToDashes($data['path']), array('instances' => Project::getInstances()));
    }

    function settingsInstancesAddAnInstance($data) {
        return $this->getHtmlElement('Project:control/'.String::camelCaseToDashes($data['path']));
    }

    function settingsInstancesEditInstance($data) {
        return $this->getHtmlElement('Project:control/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

    function settingsInstancesDeleteInstances($data) {
        return $this->getHtmlElement('Project:control/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

    function settingsModules($data) {
        // Essential modules (from Project)
        $data['essentialModules'] = Project::getEssentialModules();
        Arr::sort($data['essentialModules']);

        // Active modules (from settings)
        $data['activeModules'] = Arr::difference(Project::getActiveModules(), Project::getEssentialModules());
        Arr::sort($data['activeModules']);

        // Inactive modules (from settings)
        $data['inactiveModules'] = Project::getInactiveModules();

        // System modules
        $data['systemModules'] = Project::getSystemModules();

        // Instance modules
        $data['instanceModules'] = Project::getInstanceModules();
        
        $data['notInstalledModules'] = Arr::difference(Project::getSystemModules(), $data['activeModules']);
        $data['notInstalledModules'] = Arr::difference($data['notInstalledModules'], $data['essentialModules']);

        // Get the module names
        foreach($data as $key => &$value) {
            if(String::endsWith('Modules', $key)) {
                foreach($value as &$moduleKey) {
                    $moduleName = call_user_func(array(Module::getModuleClass($moduleKey), 'getName'));
                    $moduleKey = array('moduleKey' => $moduleKey, 'moduleName' => $moduleName);
                }
            }
        }

        return $this->getView('Project:control/'.String::camelCaseToDashes($data['path']), $data);
    }

    function settingsModulesActivateModule($data) {
        return $this->getHtmlElement('Project:control/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

    function settingsModulesDeactivateModule($data) {
        return $this->getHtmlElement('Project:control/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

    function settingsModulesInstallModule($data) {
        return $this->getHtmlElement('Project:control/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

    function settingsModulesUninstallModule($data) {
        return $this->getHtmlElement('Project:control/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

    function settingsModulesReinstallModule($data) {
        return $this->getHtmlElement('Project:control/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

    function settingsAdministrator($data) {
        return $this->getHtmlElement('Project:control/'.String::camelCaseToDashes($data['path']));
    }

    function settingsAdministratorChangePassword($data) {
        return $this->getHtmlElement('Project:control/'.String::camelCaseToDashes($data['path']));
    }

}
?>