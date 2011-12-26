<?php
class TemplateModule extends Module {
  
    public static $title = 'Template';
    public static $version = array(
        'number' => '1',
        'dateTime' => 'YYYY-MM-DD HH:MM:SS',
    );
    public static $description = 'Template for modules.';
    public static $url = '';
    public static $authors = array(
        array(
            'name' => 'Kirk Ouimet',
            'email' => 'kirk@kirkouimet.com',
            'url' => 'http://www.kirkouimet.com/',
        ),
    );
    public static $control = array(
        'navigation' => array(
            'Settings' => 'Template',
        ),
        'permissions' => array(),
        'settings' => array(),
    );

    public static function load() {

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

}
?>