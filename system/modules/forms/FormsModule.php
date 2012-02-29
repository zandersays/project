<?php
class FormsModule extends Module {

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
            ),
            array(
                'name' => 'Seth Jensen',
                'email' => 'seth@sethjdesign.com',
                'url' => 'http://www.sethjdesign.com/',
            ),
        );
    }

    public static function getClasses() {
        return array(
            'Form' => 'source/Form.php',
            'FormPage' => 'source/FormPage.php',
            'FormSection' => 'source/FormSection.php',
            'FormComponent' => 'source/FormComponent.php',
            'FormComponentSingleLineText' => 'source/FormComponentSingleLineText.php',
            'FormComponentMultipleChoice' => 'source/FormComponentMultipleChoice.php',
            'FormComponentDropDown' => 'source/FormComponentDropDown.php',
            'FormComponentTextArea' => 'source/FormComponentTextArea.php',
            'FormComponentDate' => 'source/FormComponentDate.php',
            'FormComponentFile' => 'source/FormComponentFile.php',
            'FormComponentName' => 'source/FormComponentName.php',
            'FormComponentHidden' => 'source/FormComponentHidden.php',
            'FormComponentAddress' => 'source/FormComponentAddress.php',
            'FormComponentCreditCard' => 'source/FormComponentCreditCard.php',
            'FormComponentLikert' => 'source/FormComponentLikert.php',
            'FormComponentHtml' => 'source/FormComponentHtml.php',
            'FormsApi' => 'controllers/apis/FormsApi.php',
            'FormsForms' => 'controllers/forms/FormsForms.php',
        );
     }

    public static function getControlNavigation() {
        return array();
        return array(
            array(
                'title' => 'Settings',
                'subItems' => array(
                    array(
                        'title' => 'Forms',
                        'path' => 'modules/forms/settings/forms/',
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
        return 'Forms';
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