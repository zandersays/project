<?php
class ApisModule extends Module {

    public static function getName() {
        return 'APIs';
    }

    public static function getVersion() {
        return array(
            'number' => '1',
            'dateTime' => 'YYYY-MM-DD HH:MM:SS',
        );
    }

    public static function getDescription() {
        return '';
    }

    public static function getUrl() {
        return '';
    }

    public static function getAuthors() {
        return array(
            array(
                'name' => 'Kirk Ouimet',
                'email' => 'kirk@kirkouimet.com',
                'url' => 'http://www.kirkouimet.com/',
            )
        );
    }

    public static function load($settings) {
        // Load the APIs
        if(isset($settings['apis'])) {
            // Go through each API
            foreach($settings['apis'] as $apiName => $api) {
                // If the API is enabled
                if($api['status'] == 'enabled') {
                    // Try to include the API if a file exists for it (this is a Project convention)
                    $potentialClassFile = Project::getInstancePath().'controllers/apis/'.String::stripSpaces(String::camelCaseToTitle($apiName.'Api.php'));
                    $file = File::exists($potentialClassFile, false);
                    if($file) {
                        if(Number::isInteger($file)) {
                            Project::requireOnce($potentialClassFile);
                        }
                        else {
                            Project::requireOnce($file);    
                        }
                    }
                
                    // Include the explicit API files
                    if(isset($api['classes']) && Arr::is($api['classes'])) {
                        foreach($api['classes'] as $className => $file) {
                            // Handle Project files
                            if(String::startsWith('Project:', $file)) {
                                Project::addAutoLoadClasses(array($className => String::replace('Project:', '', $file)), 'project');
                            }
                            // Handle instance files
                            else {
                                Project::addAutoLoadClasses(array($className => $file), 'instance');
                            }

                            // Run the initialize function if it exists, pass in settings
                            if(Object::methodExists('initialize', $className)) {
                                call_user_func(array($className, 'initialize'), $api['settings']);
                            }
                        }
                    }
                }
            }
        }
    }

    public static function install() {
    }

    public static function activate() {

    }

    public static function deactivate() {

    }

    public static function delete() {

    }

    public static function getClasses() {
        return array(
            'Api' => 'source/Api.php',
        );
    }

    public static function getControlNavigation() {
        return array(
            array(
                'title' => 'Settings',
                'subItems' => array(
                    array(
                        'title' => 'APIs',
                        'path' => 'modules/apis/settings/apis/',
                        'subItems' => array(
                            array(
                                'title' => 'Internal APIs',
                                'path' => 'modules/apis/settings/apis/internal-apis/',
                                'subItems' => array(
                                    array(
                                        'title' => 'Add an Internal API',
                                        'path' => 'modules/apis/settings/apis/internal-apis/add-an-internal-api/',
                                    ),
                                ),
                            ),
                            array(
                                'title' => 'External APIs',
                                'path' => 'modules/apis/settings/apis/external-apis/',
                                'subItems' => array(
                                    array(
                                        'title' => 'Add an External API',
                                        'path' => 'modules/apis/settings/apis/external-apis/add-an-external-api/',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    public static function getDefaultSettings() {
        return array(
        );
    }

    public static function getDependencies() {

    }

    public static function getPermissions() {

    }

    public static function uninstall() {

    }

}
?>