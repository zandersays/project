<?php

/**
 * Description of ModelManager
 *
 * @author Kam Sheffield
 * @version 08/23/2011
 */
class ModelManager {

    /**
     *
     * @param string $databaseName
     * @param boolean $isRelative
     * @return string
     */
    public static function getModelPathFromDatabaseName($databaseName, $isRelative = false) {
        $modelPath = null;

        $databases = Project::getModuleSettings('Databases');
        // Set the appropriate model path using the database settings
        foreach($databases['databases'] as $database) {
            if($database['name'] == $databaseName) {
                if($isRelative) {
                    $modelPath = $database['modelPath'];
                }
                else {
                    $modelPath = Project::getInstancePath().'models/'.$database['modelPath'];
                }
                break;
            }
        }

        return $modelPath;
    }

    /**
     *
     * @var ModelDriver
     */
    private $modelDriver;

    /**
     * Path to the Models folder.
     *
     * @var string
     */
    private $modelPath;

    /**
     *
     * @var boolean
     */
    private $emitSql;

    /**
     *
     * @param DatabaseDriver $databaseDriver
     * @param string $modelPath
     * @param boolean $emitSql
     */
    public function __construct(DatabaseDriver $databaseDriver, $modelPath, $emitSql = false) {
        $this->modelDriver = ModelDriverFactory::create($databaseDriver);
        if($modelPath{String::length($modelPath) - 1} != '/') {
            $modelPath .= '/';
        }
        $this->modelPath = $modelPath;
        $this->emitSql = $emitSql;
    }

    /**
     *
     * @param type $modelName
     * @return Table
     */
    public function getModelSchemaFromDatabaseTable($modelName) {
        $schemaGenerator = new SchemaGenerator($modelName, $this->modelDriver);
        $table = $schemaGenerator->getSchema();
        return $table;
    }

    /**
     *
     * @param TableAlterer $tableAlterer
     * @return array
     */
    public function alterDatabaseTable(TableAlterer $tableAlterer) {
        return $this->modelDriver->alterTable($tableAlterer);
    }

    /**
     *
     * @param string $modelSchema A JSON encoded string of a Table object
     * @return array
     */
    public function createDatabaseTableFromSchema($modelSchema) {
        $table = Object::fromJson($modelSchema);
        return $this->createModelDatabaseTableFromTableSchema($table);
    }

    /**
     *
     * @param Table $table
     * @return array
     */
    public function createDatabaseTableFromTableObject(Table $table) {
        return $this->modelDriver->createTable($table);
    }

    /**
     *
     * @param string $modelName
     * @return array
     */
    public function truncateDatabaseTable($modelName) {
        $tableName = String::camelCaseToUnderscores($modelName);
        return $this->modelDriver->truncateTable($tableName);
    }

    /**
     *
     * @param string $modelName
     * @return array
     */
    public function dropModelDatabaseTable($modelName) {
        $tableName = String::camelCaseToUnderscores($modelName);
        return $this->modelDriver->dropTable($tableName);
    }

    /**
     *
     * @param string $modelName
     * @param string $pathToSaveFileTo
     * @return boolean
     */
    public function createModelSchemaFileFromDatabaseTable($modelName, $pathToSaveFileTo) {
        $schemaGenerator = new SchemaGenerator($modelName);
        $table = $schemaGenerator->getSchema();
        $modelSchema = Object::toJson($table, true, true);
        return $this->createModelSchemaFileFromSchema($modelName, $modelSchema, $pathToSaveFileTo);
    }

    /**
     *
     * @param string $modelName
     * @param string $modelSchema A JSON encoded version of a Table object
     * @param string $pathToSaveFileTo
     * @return boolean
     */
    public function createModelSchemaFileFromSchema($modelName, $modelSchema, $pathToSaveFileTo) {
        if($pathToSaveFileTo{String::length($pathToSaveFileTo) - 1} != '/') {
            $pathToSaveFileTo .=  '/';
        }

        $fileName = $pathToSaveFileTo.$modelName.'.pmf';
        if(!File::exists($fileName)) {
            if(!File::create($fileName)) {
                return false;
            }
        }

        return File::write($fileName, $modelSchema);
    }

    /**
     *
     * @param string $modelName
     * @return boolean
     */
    public function createModelPhpClassFileFromDatabaseTable($modelName) {
        try {
            $schemaGenerator = new SchemaGenerator($modelName, $this->modelDriver);
            $table = $schemaGenerator->getSchema();
            $phpClassGenerator = new PhpClassGenerator($modelName, $table, $this->modelDriver);
            return $this->savePhpClassFile($modelName, $phpClassGenerator->getClass());
        }
        catch(Exception $e) {
            print_r($e);
        }
    }

    /**
     *
     * @param string $modelSchema A JSON encoded version of a Table object
     * @return boolean
     */
    public function createModelPhpClassFileFromModelSchema($modelSchema) {
        $table = Object::fromJson($modelSchema);
        $phpClassGenerator = new PhpClassGenerator($modelName, $table, $this->modelDriver);
        return $this->savePhpClassFile($modelName, $phpClassGenerator->getClass());
    }

    /**
     *
     * @param string $modelName
     * @param string $pathToModelSchemaFolder
     * @return boolean
     */
    public function createModelPhpClassFileFromModelSchemaFile($modelName, $pathToModelSchemaFolder) {
        if($pathToModelSchemaFolder{String::length($pathToModelSchemaFolder) - 1} != '/') {
            $pathToModelSchemaFolder .=  '/';
        }

        $modelSchema = File::content($pathToModelSchemaFolder.$modelName.'.pmf');
        return $this->createModelPhpClassFileFromModelSchema($modelSchema);
    }

    private function savePhpClassFile($modelName, $phpClassFile) {
        $fileName = $this->modelPath.$modelName.'.php';
        if(!File::exists($fileName)) {
            if(!File::create($fileName)) {
                return false;
            }
        }

        return File::write($fileName, $phpClassFile);
    }

    /**
     *
     * @param string $modelName
     * @return boolean
     */
    public function deleteModelPhpClassFile($modelName) {
        return unlink($this->modelPath.$modelName.'php');
    }

    /**
     *
     * @param string $modelName
     * @param array $modelRequirements
     * @return Table
     */
    public function createTableObjectFromModelRequirements($modelName, Array $modelRequirements) {
        return Table::createFromModelRequirements($modelName, $modelRequirements);
    }

    /**
     *
     * @param string $modelName
     * @param array $modelFieldRequirements
     * @return boolean
     */
    public function checkModelRequirements($modelName, Array $modelFieldRequirements) {
        $requirements = array();
        try {
            $tableName = String::camelCaseToUnderscores($modelName);
            $tableDescription = $this->modelDriver->getDescriptionForTable($tableName);
            foreach($modelFieldRequirements as $modelField) {
                $columnDescription = $this->getColumnFromTableDescription($modelField['name'], $tableDescription);
                if($columnDescription != null) {
                    $requirements[$modelField['name']]['columnExistsInTable'] = true;

                    if($modelField['type'] == $columnDescription['Type']) {
                        $requirements[$modelField['name']]['typeMatch'] = true;
                    }
                    else {
                        $requirements[$modelField['name']]['typeMatch'] = false;
                        $requirements[$modelField['name']]['databaseType'] = $columnDescription['Type'];
                        $requirements[$modelField['name']]['modelType'] = $modelField['type'];
                    }
                }
                else {
                    $requirements[$modelField['name']]['columnExistsInTable'] = false;
                    $requirements[$modelField['name']]['typeMatch'] = false;
                    $requirements[$modelField['name']]['databaseType'] = '';
                    $requirements[$modelField['name']]['modelType'] = $modelField['type'];
                }
            }
            $requirements['tableExists'] = true;
        }
        catch(ModelException $e) {
            $requirements['tableExists'] = false;
        }
        return $requirements;
    }

    private function getColumnFromTableDescription($columnName, $tableDescription) {
        foreach($tableDescription as $columnDescription) {
            if($columnDescription['Field'] == $columnName) {
                return $columnDescription;
            }
        }
        return null;
    }

    /**
     *
     * @param string $modelName
     * @return string
     */
    public function getModel($modelName) {
        $modelData = array();
        $files = Dir::read($this->modelPath);
        $modelFile = '';
        foreach($files as $file) {
            $newFile = String::subString($file, String::lastIndexOf("/", $file) + 1);
            $newFile = String::subString($newFile, 0, String::indexOf('.', $newFile));

            if($newFile == $modelName) {
                $modelFile = $file;
                $modelData['existsInModels'] = true;
                break;
            }
        }

        if(!array_key_exists('existsInModels', $modelData)) {
            $modelData['existsInModels'] = false;
        }

        $tableName = '';
        if($modelData['existsInModels']) {
            require_once($modelFile);
            $model = new $modelName();
            $tableName = $model->getTableName();
            $modelData['timeCreated'] = File::modificationTime($modelFile);
        }
        else {
            // guess
            $tableName = String::camelCaseToUnderscores($modelName);
            $modelData['timeCreated'] = '';
        }

        $existingModelTables = $this->modelDriver->getTableNames();
        if(Arr::contains($tableName, $existingModelTables)) {
            $modelData['existsInDatabase'] = true;

            $tableDescription = $this->modelDriver->getDescriptionForTable($tableName);
            $newTableDescription = array();
            foreach($tableDescription as $columnMeta) {
                $newTableDescription[$columnMeta['Field']] = array();
                $newTableDescription[$columnMeta['Field']]['pdoType'] = $this->modelDriver->parseDataTypeForPhpPdo($columnMeta['Type']);
            }
            $tableDescription = $newTableDescription;

            $fieldMeta = call_user_func(array($modelName, 'getMetaArray'), 'columns');

            $modelData['fields'] = $this->compareModelToTableSchema($fieldMeta, $tableDescription);
            $modelData['existsInDatabase'] = true;
            $modelData['tableName'] = $tableName;
        }
        else {
            $modelData['fields'] = array();
            $modelData['existsInDatabase'] = false;
            $modelData['tableName'] = '';
        }

        return $modelData;
    }

    /**
     *
     * @return array
     */
    public function getModels() {
        // get all of the models in the models folder
        $existingModelClassInfo = $this->getInformationOnModelClasses();

        // get a list of database tables
        $existingModelTables = $this->modelDriver->getTableNames();

        // compare the models with the database schema
        $modelData = array();
        foreach($existingModelClassInfo as $modelInfo) {
            // do a last second include
            require_once($modelInfo['fileName']);

            // instance the model
            $model = new $modelInfo['modelName']();
            $tableName = $model->getTableName();

            // If the table exists in the database
            if(Arr::contains($tableName, $existingModelTables)) {
                $tableDescription = $this->modelDriver->getDescriptionForTable($tableName);
                $newTableDescription = array();
                foreach($tableDescription as $columnMeta) {
                    $newTableDescription[$columnMeta['Field']] = array();
                    $newTableDescription[$columnMeta['Field']]['pdoType'] = $this->modelDriver->parseDataTypeForPhpPdo($columnMeta['Type']);
                }
                $tableDescription = $newTableDescription;

                $fieldMeta = call_user_func(array($model->getModelName(), 'getMetaArray'), 'columns');                

                $modelData[$model->getModelName()]['fields'] = $this->compareModelToTableSchema($fieldMeta, $tableDescription);
                $modelData[$model->getModelName()]['existsInDatabase'] = true;
                $modelData[$model->getModelName()]['tableName'] = $tableName;
                $modelData[$model->getModelName()]['timeCreated'] = $modelInfo['createdTime'];
            }
            else {
                $modelData[$model->getModelName()] = array('existsInDatabase' => false);
            }
        }

        foreach($existingModelTables as $existingTable) {
            if(!$this->modelExistsForTable($existingTable, $modelData)) {
                $modelName = String::underscoresToCamelCase($existingTable, true);
                $modelData[$modelName] = array();
                $modelData[$modelName]['fields'] = array();
                $modelData[$modelName]['existsInDatabase'] = true;
                $modelData[$modelName]['existsInModels'] = false;
                $modelData[$modelName]['timeCreated'] = 'Never';
                $modelData[$modelName]['tableName'] = $existingTable;
            }
        }

        return $modelData;
    }

    private function modelExistsForTable($tableName, Array &$modelData) {
        foreach($modelData as $modelName => &$modelMeta) {
            if($modelMeta['tableName'] == $tableName) {
                $modelMeta['existsInModels'] = true;
                return true;
            }
        }
        return false;
    }

    private function getInformationOnModelClasses() {
        // get all the files in the models folder
        $directoryContents = Dir::read($this->modelPath);

        // get the info for each file
        $modelInfo = array();
        foreach($directoryContents as $classFile) {
            // get the content of the class file
            $fileContent = File::content($classFile);

            // find the actual model name
            $modelPosition = String::position('class ', $fileContent) + 6;
            $modelPositionEnd = String::position(' extends', $fileContent, $modelPosition);
            $modelName = String::sub($fileContent, $modelPosition, $modelPositionEnd - $modelPosition);

            // get the modification time
            $modificationTime = date("F j, Y, g:i a", File::modificationTime($classFile));

            $modelInfo[] = array(
                'fileName' => $classFile,
                'modelName' => $modelName,
                'createdTime' => $modificationTime
            );
        }

        return $modelInfo;
    }

    private function compareModelToTableSchema(Array $modelFields, Array $tableSchema) {
        $modelMeta = array();
        foreach($modelFields as $fieldName => $fieldMeta) {
            $modelMeta[$fieldName] = array();
            if(array_key_exists($fieldName, $tableSchema)) {
                $modelMeta[$fieldName]['existsInDatabase'] = true;

                // check the data type
                if($fieldMeta == $tableSchema[$fieldName]['pdoType']) {
                    $modelMeta[$fieldName]['typeMatch'] = true;
                }
                else {
                    $modelMeta[$fieldName]['typeMatch'] = false;
                }
            }
            else {
                $modelMeta[$fieldName]['existsInDatabase'] = false;
            }
        }

        foreach($tableSchema as $columnName => $columnMeta) {
            if(array_key_exists($columnName, $modelMeta)) {
                $modelMeta[$columnName]['existsInModels'] = true;
            }
            else {
                $modelMeta[$columnName]['existsInModels'] = false;
            }
        }

        return $modelMeta;
    }
}

?>
