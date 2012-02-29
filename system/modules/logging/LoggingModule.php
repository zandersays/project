<?php
class LoggingModule extends Module {

    public static function load($settings) {
        print_r($settings);
    }

    public static function install() {

    }

    public static function checkDependencies() {

    }

    public static function activate() {

    }

    public static function deactivate() {

    }

    public static function delete() {

    }

    public static function getAuthors() {
        return array(
            array(
                'name' => 'Kam Sheffield',
                'email' => 'kamsheffield@gmail.com',
                'url' => 'http://www.kamsheffield.com/',
            ),
        );
    }

    public static function getClasses() {
        return array(
            'Log' => 'source/Log.php',
            'LogLevel' => 'source/LogLevel.php',
            'LogManager' => 'source/LogManager.php',
            'Logger' => 'source/Logger.php',
            'RollingLog' => 'source/RollingLog.php',
            'DatabaseLog' => 'source/DatabaseLog.php',
        );
    }

    public static function getControlNavigation() {

    }

    public static function getDefaultSettings() {

    }

    public static function getDependencies() {
        return array(
            'modules' => array(
                'Errors',
            ),
        );
    }

    public static function getDescription() {

    }

    public static function getPermissions() {

    }

    public static function getName() {
        return 'Logging';
    }

    public static function getUrl() {

    }

    public static function getVersion() {
        return array(
            'number' => '.01',
            'dateTime' => '2009-09-17 01:01:01',
        );
    }

    public static function uninstall() {

    }

}
?>