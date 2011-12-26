<?php
class RelationshipsModule extends Module {

    public static function load($settings) {
        
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
            //'Database' => 'source/Database.php',
        );
    }

    public static function getControlNavigation() {
        return array(
            array(
                'title' => 'Relationships',
                'path' => 'modules/relationships/',
            ),
        );
    }

    public static function getDefaultSettings() {

    }

    public static function getDependencies() {

    }

    public static function getDescription() {

    }

    public static function getPermissions() {

    }

    public static function getName() {
        return 'Relationships';
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