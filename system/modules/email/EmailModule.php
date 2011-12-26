<?php
class EmailModule extends Module {

    public static function load($settings) {
        Email::$defaultMailType = $settings['defaultMailType'];
        Email::$defaultCharacterSet = $settings['defaultCharacterSet'];
        Email::$defaultUserAgent = $settings['defaultUserAgent'];
        Email::$defaultProtocol = $settings['defaultProtocol'];
        Email::$defaultSendMailPath = $settings['defaultSendMailPath'];
        Email::$defaultSmtpHost = $settings['defaultSmtpHost'];
        Email::$defaultSmtpPort = $settings['defaultSmtpPort'];
        Email::$defaultSmtpUsername = $settings['defaultSmtpUsername'];
        Email::$defaultSmtpPassword = $settings['defaultSmtpPassword'];
        Email::$defaultSmtpTimeout = $settings['defaultSmtpTimeout'];
        Email::$defaultWordWrap = $settings['defaultWordWrap'];
        Email::$defaultWordWrapCharacters = $settings['defaultWordWrapCharacters'];
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
            'Email' => 'source/Email.php',
        );
    }

    public static function getControlNavigation() {
        return array(
            array(
                'title' => 'Settings',
                'subItems' => array(
                    array(
                        'title' => 'Email',
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
        return array(
            'defaultMailType' => 'text',
            'defaultCharacterSet' => 'utf-8',
            'defaultUserAgent' => 'Project',
            'defaultProtocol' => 'mail',
            'defaultSendMailPath' => '/usr/sbin/sendmail',
            'defaultSmtpHost' => '',
            'defaultSmtpPort' => 25,
            'defaultSmtpUsername' => '',
            'defaultSmtpPassword' => '',
            'defaultSmtpTimeout' => 5,
            'defaultWordWrap' => true,
            'defaultWordWrapCharacters' => 76,
        );
    }

    public static function getDependencies() {

    }

    public static function getDescription() {

    }

    public static function getPermissions() {

    }

    public static function getName() {
        return 'E-mail';
    }

    public static function getUrl() {

    }

    public static function getVersion() {

    }

    public static function uninstall() {

    }

}
?>