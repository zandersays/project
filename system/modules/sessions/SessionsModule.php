<?php
class SessionsModule extends Module {

    public static function load($settings) {
        // Set the session driver
        $sessionDriver = 'SessionDriver'.$settings['driver'];
        Session::$sessionDriver = new $sessionDriver($settings);
        Session::$data =& Session::$sessionDriver->data;

        // Set the user
        UserApi::$user =& Session::get('user');
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
            'Session' => 'source/Session.php',
            'SessionDriver' => 'source/SessionDriver.php',
            'SessionDriverNative' => 'source/SessionDriverNative.php',
            'SessionDriverCookie' => 'source/SessionDriverCookie.php',
            'SessionDriverDatabase' => 'source/SessionDriverDatabase.php',
        );
    }

    public static function getControlNavigation() {
        return array(
            array(
                'title' => 'Settings',
                'subItems' => array(
                    array(
                        'title' => 'Sessions',
                        'path' => 'modules/sessions/settings/sessions/',
                    ),
                ),
            ),
        );
    }

    public static function getDefaultSettings() {
        return array(
            'driver' => 'Native',
            'garbageCollectionProbability' => 100,
            'expiration' => 1440,
            'regenerate' => false,
        );
    }

    public static function getDependencies() {
        return array(
            'modules' => array(
                'Cookies',
            )
        );
    }

    public static function getDescription() {

    }

    public static function getPermissions() {

    }

    public static function getName() {
        return 'Sessions';
    }

    public static function getVersion() {
        return array(
            'number' => 1,
            'dateTime' => 'YYYY-MM-DD HH:MM:SS',
        );
    }

    public static function getUrl() {

    }

    public static function uninstall() {

    }

}
?>
