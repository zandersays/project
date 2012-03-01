<?php
class RoutesModule extends Module {

    public static function getName() {
        return 'Routes';
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
        if(isset($settings['routes']) && !empty($settings['routes'])) {
            Router::addRoutes($settings['routes']);
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
            'Route' => 'source/Route.php',
        );
    }

    public static function getControlNavigation() {
        return array(
            array(
                'title' => 'Settings',
                'subItems' => array(
                    array(
                        'title' => 'Routes',
                        'path' => 'modules/routes/settings/routes/',
                    ),
                ),
            ),
        );
    }

    public static function getDefaultSettings() {
        return array(
            'routes' => array(
                array(
                    'expression' => '/',
                    'controllerName' => 'Main',
                    'functionName' => 'index',
                    'data' => array(
                        array(
                            'key' => '',
                            'value' => '',
                        ),
                    ),
                    'description' => 'The root route. This route\'s expression may not be modified. This route has no parent or siblings.',
                    'caching' => false,
                    'policies' => '',
                    'childRoutes' => array(
                        array(
                            'status' => 'active',
                            'expression' => 'api/(.*?)/(.*?)/(.*)',
                            'description' => '',
                            'priority' => '',
                            'controllerName' => 'Api',
                            'functionName' => '',
                            'data' => array(
                                array(
                                    'key' => 1,
                                    'value' => 'apiName',
                                ),
                                array(
                                    'key' => 2,
                                    'value' => 'commandName',
                                ),
                                array(
                                    'key' => 3,
                                    'value' => ':hash',
                                ),
                            ),
                        ),
                        array(
                            'status' => 'active',
                            'expression' => 'project/setup/',
                            'description' => 'The Project setup route.',
                            'priority' => '',
                            'controllerName' => 'Setup',
                            'functionName' => '',
                            'data' => array(
                                array(
                                    'key' => '',
                                    'value' => '',
                                ),
                            ),
                        ),
                        array(
                            'status' => 'active',
                            'expression' => 'project/(.*)',
                            'description' => 'The Project control panel route.',
                            'priority' => '',
                            'controllerName' => 'Control',
                            'functionName' => '',
                            'data' => array(
                                array(
                                    'key' => 1,
                                    'value' => 'path',
                                ),
                            ),
                        ),
                        array(
                            'description' => 'Catch all route. The first capture group is the controller and the second capture group is the specific controller function.',
                            'expression' => '(.*?/)',
                            'data' => array(
                                array(
                                    'key' => 1,
                                    'value' => ':controller',
                                ),
                                array(
                                    'key' => 2,
                                    'value' => ':function',
                                ),
                            ),
                            'status' => 'active',
                            'priority' => '',
                            'controllerName' => '',
                            'functionName' => '',
                        ),
                    ),
                    'status' => 'active',
                    'priority' => 1,
                ),
            ),
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