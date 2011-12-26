<?php
class DatabasesModule extends Module {

    public static function load($settings) {
        if(!empty($settings['databases'])) {
            foreach($settings['databases'] as $databaseIndex => $database) {
                if($database['instances'] == 'all' || (Arr::is($database['instances']) && Arr::contains(Project::getInstanceId(), $database['instances']))) {
                    $databaseDriver = 'DatabaseDriver'.$database['type'];
                    $databaseDriver = new $databaseDriver($database['name'], $database['host'], $database['username'], $database['password'], (isset($database['port']) ? $database['port'] : '3306'), (isset($database['socket']) ? $database['socket'] : null), (isset($database['databaseOptions']) ? $database['databaseOptions'] : array()));
                    Database::addDatabaseDriver($databaseDriver);
                    
                    // Set the database context (this needs to be updated to allow for local contexts within the model configuration)
                    if(Module::isActive('Models') && $database['globalContext']) {
                        ModelContext::setGlobalContext($databaseDriver);
                    }   
                }
            }
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
                'name' => 'Kam Sheffield',
                'email' => 'kamsheffield@gmail.com',
                'url' => 'http://www.kamsheffield.com/',
            ),
        );
    }

    public static function getClasses() {
        return array(
            // Core
            'Database' => 'source/Database.php',
            'DatabaseManager' => 'source/DatabaseManager.php',

            // Drivers
            'DatabaseDriver' => 'source/drivers/DatabaseDriver.php',
            'DatabaseDriverMySql' => 'source/drivers/DatabaseDriverMySql.php',
            'DatabaseDriverTestDriver' => 'source/drivers/DatabaseDriverTestDriver.php',
            'DatabaseDriverTypes' => 'source/drivers/DatabaseDriverTypes.php',

            // Exceptions
            'DatabaseException' => 'source/exceptions/DatabaseException.php',
        );
    }

    public static function getControlNavigation() {
        return array(
            array(
                'title' => 'Settings',
                'subItems' => array(
                    array(
                        'title' => 'Databases',
                        'path' => 'modules/databases/settings/databases/',
                        'subItems' => array(
                            array(
                                'title' => 'Add a Database',
                                'path' => 'modules/databases/settings/databases/add-a-database/',
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
        return 'Databases';
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