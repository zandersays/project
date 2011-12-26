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