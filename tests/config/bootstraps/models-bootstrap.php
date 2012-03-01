<?php

/**
 * Sets up a the Models Module so
 * it can be tested.
 *
 * @author Kam Sheffield
 * @version 08/30/2011
 */

// run the project bootstrap first
require_once dirname(__FILE__).'/project-bootstrap.php';

// get to the models folder we want
global $instance;
$modelsFolder = $instance['projectPath'].'/tests/temp/';

// create the database connection
$databaseDriver = new DatabaseDriverMySql('project_unit_test', 'localhost', 'test', 'password');
$modelDriver = ModelDriverFactory::create($databaseDriver);

$databaseTables = $modelDriver->getTableNames();

// create and save a model foreach database table
foreach($databaseTables as $databaseTable) {
    // create the model name
    $modelName = String::underscoresToCamelCase($databaseTable, true);

    // get the schema out
    $schemaGenerator = new SchemaGenerator($modelName, $modelDriver);
    $table = $schemaGenerator->getSchema();

    // create the model from the schema
    $phpClassGenerator = new PhpClassGenerator($modelName, $table, $modelDriver);
    $modelClass = $phpClassGenerator->getClass();

    $modelFile = $modelsFolder.$modelName.'.php';

    // create the file if it does not yet exist
    if(!File::exists($modelFile)) {
        $fileCreated = File::create($modelFile);
        if(!$fileCreated) {
            die("Unable to create model file required for test: ".$modelFile);
        }
    }

    // write the data to the file
    $fileWritten = File::write($modelFile, $modelClass);
    if(!$fileWritten){
        die("Unable to write model file required for test: ".$modelFile);
    }

    require_once($modelFile);
}

// close the connection to the db
$databaseDriver->closeConnection();

?>
