<?php
class FoursquareApi extends Api {

    public $commands = array(
    );
        
    public static $clientId;
    public static $clientSecret;
    
    public static function initialize($settings) {
        // Get the credentials for the current instance type (conditionally)
        $credentials = $settings['credentials'][String::lowerFirstCharacter(Project::getInstanceType())];
        
        // Populate values from provided settings
        self::$clientId = isset($credentials['clientId']) ? $credentials['clientId'] : self::$clientId;
        self::$clientSecret = isset($credentials['clientSecret']) ? $credentials['clientSecret'] : self::$clientSecret;
    }
}
?>
