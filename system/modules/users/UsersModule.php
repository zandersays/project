<?php
class UsersModule extends Module {

    public static function load($settings) {
        // Check to see if the user exists in the session
        UserApi::$user =& Session::$data['user'];

        // If the user does not exist in the section, check to see a login cookie is set
        if(!UserApi::$user) {
            // If a login cookie is set, attempt to login the user through the cookie, and make a note that it is a cookie login
            UserApi::loginUsingCookie();
        }
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
                'name' => 'Kirk Ouimet',
                'email' => 'kirk@kirkouimet.com',
                'url' => 'http://www.kirkouimet.com/',
            )
        );
    }

    public static function getClasses() {
        return array(
            'UserApi' => 'controllers/apis/UserApi.php',
            'AccountApi' => 'controllers/apis/UserApi.php',
            'UsersForms' => 'controllers/forms/UsersForms.php',
        );
    }

    public static function getControlNavigation() {
        return array(
            array(
                'title' => 'Settings',
                'subItems' => array(
                    array(
                        'title' => 'Users',
                        'path' => 'modules/users/settings/users/',
                        'subItems' => array(
                            array(
                                'title' => 'Users',
                                'path' => 'modules/users/settings/users/users/',
                                'subItems' => array(
                                    array(
                                        'title' => 'Registration E-mail',
                                        'path' => 'modules/users/settings/users/users/registration-email',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'title' => 'Users',
                'path' => 'modules/users/',
                'subItems' => array(
                    array(
                        'title' => 'Users',
                        'path' => 'modules/users/users/',
                        'subItems' => array(
                            array(
                                'title' => 'Add a User',
                                'path' => 'modules/users/users/add-a-user/',
                            ),
                        ),
                    ),
                    array(
                        'title' => 'Accounts',
                        'path' => 'modules/users/accounts/',
                        'subItems' => array(
                            array(
                                'title' => 'Add an Account',
                                'path' => 'modules/users/accounts/add-an-account/',
                            ),
                            array(
                                'title' => 'Account Types',
                                'path' => 'modules/users/accounts/account-types/',
                                'subItems' => array(
                                    array(
                                        'title' => 'Add an Account Type',
                                        'path' => 'modules/users/accounts/account-types/add-an-account-type/',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        //'Twitter, Facebook, Open ID, etc integration',
        //'Permissions, groups, roles, etc',
        //'Create a Project Administrator who has all power',
        //'Create custom permissions levels for any user',
    }

    public static function getDefaultSettings() {
        return array();
    }

    public static function getDependencies() {
        return array(
            'models' => array(
                'User',
                'UserEmail',
                'UserEmailVerification',
                'UserPasswordReset',
                //'UserAccount',
                //'UserAccountRole',
                //'UserAccountPermission',
                'UserCookieLogin',
                //'Account',
                //'AccountTypePermission',
                'UserSession',
                //'AccountType',
                //'AccountTypeRole',
                //'AccountTypeRolePermission',
                //'AccountFeature',
                //'AccountTypeFeature',
            ),
            'modules' => array(
                'Sessions',
                'Cookies',
                'Databases',
                'Models',
            ),
        );
    }

    public static function getDescription() {

    }

    public static function getPermissions() {

    }

    public static function getName() {
        return 'Users';
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
