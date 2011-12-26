<?php
class CookiesModule extends Module {

    public static function load($settings) {
        Cookie::$expiration = $settings['expiration'];
        Cookie::$domain = $settings['domain'];
        Cookie::$path = $settings['path'];
        Cookie::$signing = $settings['signing'];
        Cookie::$signingSalt = $settings['signingSalt'];
        Cookie::$httpsOnly = $settings['httpsOnly'];
        Cookie::$httpProtocolOnly = $settings['httpProtocolOnly'];
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
            'Cookie' => 'source/Cookie.php',
        );
    }

    public static function getControlNavigation() {
        return array(
            array(
                'title' => 'Settings',
                'subItems' => array(
                    array(
                        'title' => 'Cookies',
                        'path' => 'modules/cookies/settings/cookies/',
                    ),
                ),
            ),
        );
    }

    public static function getDefaultSettings() {
        return array(
            'expiration' => 2592000,
            'domain' => null,
            'path' => '/',
            'signing' => true,
            'signingSalt' => 'phr@m3wrk',
            'httpsOnly' => false,
            'httpProtocolOnly' => false,
        );
    }

    public static function getDependencies() {

    }

    public static function getDescription() {

    }

    public static function getPermissions() {

    }

    public static function getName() {
        return 'Cookies';
    }

    public static function getUrl() {

    }

    public static function getVersion() {

    }

    public static function uninstall() {

    }

}
?>