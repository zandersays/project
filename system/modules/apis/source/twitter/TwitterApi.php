<?php
class TwitterApi extends Api {

    public $commands = array(
    );
        
    public static $consumerKey;
    public static $consumerSecret;
    public static $twitterOAuth;
    
    public static function initialize($settings) {
        // Get the credentials for the current instance type (conditionally)
        $credentials = $settings['credentials'][String::lowerFirstCharacter(Project::getInstanceType())];
        
        // Populate values from provided settings
        self::$consumerKey = isset($credentials['consumerKey']) ? $credentials['consumerKey'] : self::$consumerKey;
        self::$consumerSecret = isset($credentials['consumerSecret']) ? $credentials['consumerSecret'] : self::$consumerSecret;
    }
}
?>
