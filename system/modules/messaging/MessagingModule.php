<?php
class MessagingModule extends Module {

    public static function load($settings) {
    }

    public static function install() {

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
                'name' => 'Kirk Ouimet',
                'email' => 'kirk@kirkouimet.com',
                'url' => 'http://www.kirkouimet.com/',
            )
        );
    }

    public static function getClasses() {
        return array(
            //'Message' => 'source/Message.php',
        );
    }

    public static function getControlNavigation() {
        return array(
            array(
                'title' => 'Settings',
                'subItems' => array(
                    array(
                        'title' => 'Messaging',
                        'path' => 'modules/messaging/settings/messaging/',
                        'subItems' => array(
                            array(
                                'title' => 'E-mail',
                                'path' => 'modules/messaging/settings/messaging/email/',
                            ),
                        ),
                    ),
                ),
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
        return 'Messaging';
    }

    public static function getUrl() {

    }

    public static function getVersion() {

    }

    public static function uninstall() {

    }

}
?>