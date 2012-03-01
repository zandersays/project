<?php

class ModelsModule extends Module {

    public static function load($settings) {
        // Add generated model classes to the auto loader
        $classes = array();
        if(isset($settings['models']) && Arr::is($settings['models'])) {
            foreach($settings['models'] as $modelName => $modelMeta) {
                $classes[$modelName] = 'models/'.$modelMeta['file'];
            }
        }
        Project::addAutoLoadClasses($classes, 'instance');
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
                'name' => 'Kam Sheffield',
                'email' => 'kamsheffield@gmail.com',
                'url' => 'http://www.kamsheffield.com/',
            ),
        );
    }

    public static function getClasses() {
        // Model module classes
        $classes = array(
            // Controller
            'ModelsControl' => 'controllers/ModelsControl.php',
		
            // context            
            'ModelContext' => 'source/context/ModelContext.php',
            'ModelDatabaseContext' => 'source/context/ModelDatabaseContext.php',
            'ModelDatabaseContextManager' => 'source/context/ModelDatabaseContextManager.php',
			
			// drivers
            'ModelDriver' => 'source/drivers/ModelDriver.php',
            'ModelDriverFactory' => 'source/drivers/ModelDriverFactory.php',
            'ModelDriverMySql' => 'source/drivers/ModelDriverMySql.php',

			// exceptions
            'ModelException' => 'source/exceptions/ModelException.php',
			
			// generation
            'PhpClassGenerator' => 'source/generation/PhpClassGenerator.php',
            'SchemaGenerator' => 'source/generation/SchemaGenerator.php',
			
			// manager
            'ModelManager' => 'source/manager/ModelManager.php',
			
            // model
            'Model' => 'source/model/Model.php',
            'ModelList' => 'source/model/ModelList.php',
           			
			// schema
            'ForeignKeyConstraint' => 'source/schema/ForeignKeyConstraint.php',
			'ForeignKeyConstraintUpdateType' => 'source/schema/ForeignKeyConstraintUpdateType.php',
			'RelatedTableConstraint' => 'source/schema/RelatedTableConstraint.php',
			'TableAlterer' => 'source/schema/TableAlterer.php',
            'TableColumn' => 'source/schema/TableColumn.php',
            'TableIndex' => 'source/schema/TableIndex.php',
            'TableIndexType' => 'source/schema/TableIndexType.php',
            'Table' => 'source/schema/Table.php',
                                    
			// selectors
            'Comparator' => 'source/selectors/Comparator.php',
            'FilterByFlags' => 'source/selectors/FilterByFlags.php',            
            'ModelBuilder' => 'source/selectors/ModelBuilder.php',
			'ModelDeleteSelector' => 'source/selectors/ModelDeleteSelector.php',
			'ModelSelector' => 'source/selectors/ModelSelector.php',
			'ModelSelectorResults' => 'source/selectors/ModelSelectorResults.php',
			'ModelUpdateSelector' => 'source/selectors/ModelUpdateSelector.php',
			'Selector' => 'source/selectors/Selector.php',
            'Sql' => 'source/selectors/Sql.php'
        );

        return $classes;
    }
    
    public static function getControlNavigation() {
        return array(
            array(
                'title' => 'Settings',
                'subItems' => array(
                    array(
                        'title' => 'Models',
                        'path' => 'modules/models/settings/models/',
                    ),
                ),
            ),
        );
    }

    public static function getDefaultSettings() {

    }

    public static function getDependencies() {
        return array(
            'modules' => array(
                'Databases',
            ),
        );
    }

    public static function getDescription() {

    }

    public static function getPermissions() {

    }

    public static function getName() {
        return 'Models';
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