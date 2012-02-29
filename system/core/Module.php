<?php
abstract class Module {

    public static function initialize($moduleName) {
        //echo 'Initializing module '.$moduleName.'<br />';
        $moduleClass = $moduleName.'Module';

        // Add the auto load classes to Project
        $classes = call_user_func(array($moduleClass, 'getClasses'));

        // TODO: If the module is instance module
        // 
        // If the module is a project module
        foreach($classes as &$class) {
            $class = 'system/modules/'.String::camelCaseToDashes($moduleName).'/'.$class;
            //echo $class.'<br />';
        }
        Project::addAutoLoadClasses($classes, 'project');
    }

    abstract public static function getName();

    /**
     * Returns an array of version information with the module structured like
     * array(
     *     'number' => '',
     *     'dateTime' => 'YYYY-MM-DD HH:MM:SS',
     * );
     *
     * @return array
     */
    abstract public static function getVersion();

    abstract public static function getDescription();

    abstract public static function getUrl();

    /**
     * Returns an array of authors structured like
     * array(
     *     'name' => '',
     *     'email' => '',
     *     'url' => '',
     * );
     *
     * @return array
     */
    abstract public static function getAuthors();

    abstract public static function getControlNavigation();

    abstract public static function getPermissions();

    abstract public static function getDependencies();

    abstract public static function getDefaultSettings();

    abstract public static function getClasses();

    abstract public static function load($settings);
    
    abstract public static function install();

    abstract public static function uninstall();

    public static function exists($moduleKey) {
        return File::exists(Project::getProjectPath().'system/modules/'.String::camelCaseToDashes($moduleKey).'/'.self::getModuleClass($moduleKey).'.php');
    }

    // This is not general Project module settings, this is a config file just for the module
    public static function getLocalizedSettingsFile($moduleName) {
        return Project::getInstancePath().'project/settings-'.String::urlPath($moduleName).'.php';
    }

    // This is not general Project module settings, this is a settings file specific to a module
    public static function createLocalizedSettingsFile($moduleName) {
        $createFile = File::create(Module::getLocalizedSettingsFile($moduleName));
        Module::saveSettings($moduleName, array());
    }

    // This is not general Project module settings, this is a settings file specific to a module
    public static function deleteLocalizedSettingsFile($moduleName) {
        $deleteFile = File::delete(Module::getLocalizedSettingsFile($moduleName));

        return $deleteFile;
    }
    
    public static function getLocalizedSettings($moduleName) {
        return Controller::getVariable('LocalizedSettings:'.String::urlPath($moduleName));
    }

    // This is not general Project module settings, this is a config file just for the module
    public static function saveSettings($moduleName, $settings) {
        return File::write(Module::getLocalizedSettingsFile($moduleName), "<?php".
            "\n".'$'.String::titleToCamelCase($moduleName).'Settings = '.Arr::php($settings)."\n".
            "?>"
        );
    }

    public static function getModuleKey($moduleName) {
        return String::spacesToCamelCase($moduleName, true);
    }

    public static function getModuleClass($moduleKey) {
        return $moduleKey.'Module';
    }

    public static function isActive($moduleKey) {
        //echo 'Checking if '.$moduleKey.' is active.<br />';
        $moduleSettings = Project::getModuleSettings($moduleKey);

        if($moduleSettings === false) {
            //echo 'Module '.$moduleKey.' is not active.';
            return false;
        }
        else {
            //echo 'Module is active.';
            return true;
        }
    }

    public static function checkDependencies($moduleKey) {
        // Get the modules the current module depends on
        $dependencies = call_user_func(array(Module::getModuleClass($moduleKey), 'getDependencies'));

        // Store any missing modules
        $missingModules = array();
        $missingModels = array();

        // Check model dependencies
        if(isset($dependencies['models']) && Arr::is($dependencies['models'])) {
            if(Module::isActive('Models')) {
                foreach($dependencies['models'] as $dependentModel) {
                    if(!ModelsControl::modelExists($dependentModel)) {
                        $missingModels[] = $dependentModel;
                    }
                }
            }
        }

        // Check module dependencies
        if(isset($dependencies['modules']) && Arr::is($dependencies['modules'])) {
            foreach($dependencies['modules'] as $dependentModuleKey) {
                if(!Module::isActive($dependentModuleKey)) {
                    $missingModules[] = $dependentModuleKey;
                }
            }
        }

        if(Arr::size($missingModules) == 0 && Arr::size($missingModels) == 0 ) {
            return array('status' => 'success', 'response' => 'All dependencies met.');
        }
        else {
            return array('status' => 'failure', 'response' => 'Missing '.Arr::size($missingModules).' dependendent modules and '.Arr::size($missingModels).' models.', 'missingModules' => $missingModules, 'missingModels' => $missingModels);
        }
    }

    public static function checkExternalDependencies($moduleName) {
        // Get all of the active modules
        $activeModules = Project::getActiveModules();

        // Store any modules that are externally dependent
        $externallyDependentModules = array();

        if(Arr::is($activeModules)) {
            foreach($activeModules as $activeModuleName) {
                $activeModuleDependencies = call_user_func(array(Module::getModuleClass($activeModuleName), 'getDependencies'));
                $activeModuleDependencies = $activeModuleDependencies['modules'];
                if(Arr::is($activeModuleDependencies)) {
                    if(Arr::contains($moduleName, $activeModuleDependencies)) {
                        $externallyDependentModules[] = $activeModuleName;
                    }
                }
            }
        }

        if(Arr::size($externallyDependentModules) == 0) {
            return array('status' => 'success', 'response' => 'There are no active modules that depend on '.$moduleName.'.');
        }
        else {
            return array('status' => 'failure', 'response' => Arr::size($externallyDependentModules).' active module(s) depends on the '.$moduleName.' Module.', 'externallyDependentModules' => $externallyDependentModules);
        }
    }

    abstract public static function activate();

    abstract public static function deactivate();

    abstract public static function delete();
    
}
?>