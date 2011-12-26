<?php
class ModelsControl extends Controller {

    function index($data) {
        return $this->$data['function']($data);
    }

    function settingsModels($data) {
        // Get the models from the database
        $models = array();
        $databases = Project::getModuleSettings('Databases');
        $databaseManager = DatabaseManager::getInstance();
        
        foreach($databaseManager->getAllDatabaseDrivers() as $databaseDriver) {
            // Set the appropriate model path using the database settings
            $modelPath = ModelManager::getModelPathFromDatabaseName($databaseDriver->getDatabaseName());
            
            // Create a model manager to get information from the models folder and the database
            $modelManager = new ModelManager($databaseDriver, $modelPath);
            
            // Models
            foreach($modelManager->getModels() as $modelName => $modelMeta) {
                // Check to see if the model is out of date
                $modelFieldsDoNotMatchDatabase = false;
                foreach($modelMeta['fields'] as $fieldMeta) {
                    if(!$fieldMeta['existsInDatabase'] || !$fieldMeta['typeMatch'] || !$fieldMeta['existsInModels']) {
                        $modelFieldsDoNotMatchDatabase = true;
                        break;
                    }
                }

                // Check to see if the model file exists
                if(!$modelMeta['existsInModels']) {
                    $status = 'Model File Does Not Exist';
                }
                // Check to see if the model is out of date
                else if($modelFieldsDoNotMatchDatabase) {
                    $status = 'Model Fields Do Not Match Database';
                }
                else if(!$modelMeta['existsInDatabase']) {
                    $status = 'Database Table Does Not Exist';
                }
                else {
                    $status = 'Model Up to Date';
                }

                $models[$databaseDriver->getDatabaseName()][$modelName] = array(
                    'status' => $status,
                    'tableName' => $modelMeta['tableName'],
                    'modificationTime' => $modelMeta['timeCreated'] !== 'Never' ? Time::timeSinceString($modelMeta['timeCreated']).' ago' : $modelMeta['timeCreated'],
                );
            }
        }

        // Sort the models
        Arr::sortByKey($models);
        foreach($models as $databaseName => &$modelArray) {
            Arr::sortByKey($modelArray);
        }

        return $this->getView('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), array('models' => $models));
    }

    function settingsModelsViewModel($data) {
        $modelName = $data['pathArguments']['modelName'];
        $databaseName = $data['pathArguments']['databaseName'];
        $path = $data['path'];
        $data = $data['pathArguments'];
        
        $data['document']->title = $modelName.' Model on Database '.$databaseName.' - Project ('.Project::getSiteTitle().')';
        
        $modelManager = new ModelManager(DatabaseManager::getInstance()->getDatabaseDriverByDatabaseName($databaseName), ModelManager::getModelPathFromDatabaseName($databaseName));
        //print_r($modelManager->getModelSchemaFromDatabaseTable($modelName));
        $modelMeta = null;
        $modelMeta = $modelManager->getModel($modelName);
        
        // Check to see if the model is out of date
        $modelFieldsDoNotMatchDatabase = false;
        foreach($modelMeta['fields'] as $fieldMeta) {
            if(!$fieldMeta['existsInDatabase'] || !$fieldMeta['typeMatch'] || !$fieldMeta['existsInModels']) {
                $modelFieldsDoNotMatchDatabase = true;
                break;
            }
        }
        
        // Check to see if the model file exists
        if(!$modelMeta['existsInModels']) {
            $data['status'] = 'Model File Does Not Exist';
        }
        // Check to see if the model is out of date
        else if($modelFieldsDoNotMatchDatabase) {
            $data['status'] = 'Model Fields Do Not Match Database';
        }
        else if(!$modelMeta['existsInDatabase']) {
            $data['status'] = 'Database Table Does Not Exist';
        }
        else {
            $data['status'] = 'Model Up to Date';
        }
        
        $modelSchema = $modelManager->getModelSchemaFromDatabaseTable($modelName);
        $modelSchemaArray = array();
        foreach($modelSchema->getColumnArray() as $tableColumn) {
            $modelSchemaArray[$tableColumn->getName()] = array(
                'name' => $tableColumn->getName(),
                'dataType' => $tableColumn->getDataType(),
                'length' => $tableColumn->getLength(),
                'primaryKey' => $tableColumn->getIsPrimaryKey(),
                'foreignKey' => $tableColumn->getIsForeignKey(),
                'nonNull' => $tableColumn->getIsNonNull(),
                'binary' => $tableColumn->getIsBinary(),
                'unsigned' => $tableColumn->getIsUnsigned(),
                'zeroFill' => $tableColumn->getIsZeroFill(),
                'autoIncrementing' => $tableColumn->getIsAutoIncrementing(),
                'defaultValue' => $tableColumn->getDefaultValue(),
            );
        }
        $data['modelSchema'] = $modelSchemaArray;
        
        // Outward related models
        $relatedModelsConstraints = $modelSchema->getForeignKeyConstraintArray();
        //print_r($relatedModelsConstraints); exit();
        $outwardRelatedModels = array();
        foreach($relatedModelsConstraints as $relatedModelConstraint) {
            $outwardRelatedModels[] = String::underscoresToCamelCase($relatedModelConstraint->getReferencedTableName(), true);
        }
        $data['outwardRelatedModels'] = $outwardRelatedModels;
        
        // Inward related models
        $relatedModelsConstraints = $modelSchema->getRelatedTableConstraintArray();
        $inwardRelatedModels = array();
        foreach($relatedModelsConstraints as $relatedModelConstraint) {
            $inwardRelatedModels[] = String::underscoresToCamelCase($relatedModelConstraint->getTableName(), true);
        }
        $data['inwardRelatedModels'] = $inwardRelatedModels;
                
        return $this->getHtmlElement('Module:models/'.String::camelCaseToDashes($path), $data);
    }

    function settingsModelsGenerateModelFiles($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

    function settingsModelsGenerateModelFile($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

    function settingsModelsDeleteModelFile($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

}

?>