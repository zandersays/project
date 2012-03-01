<?php

/**
 * Description of ModelBuilder
 *
 * @author Kam Sheffield
 * @version 09/14/2011
 */
class ModelBuilder {

    /**
     *
     * @var string
     */
    private $baseTable;

    /**
     *
     * @var array
     */
    private $currentModelsArray;

    /**
     *
     * @var array
     */
    private $databaseResultsArray;

    /**
     *
     * @var array
     */
    private $associationArray;

    /**
     *
     * @var array
     */
    private $associationKeyArray;

    /**
     *
     * @var array
     */
    private $tableToModelArray;

    /**
     *
     * @var array
     */
    private $modelInsertStatusArray;

    /**
     *
     * @var array
     */
    private $instancedModelsArray;

    /**
     *
     * @param type $baseModel
     * @param array $databaseResultsArray
     * @param array $selectorDataArray
     */
    public function __construct($baseModel, Array $databaseResultsArray, Array $selectorDataArray) {
        $this->baseTable = $baseModel;
        $this->databaseResultsArray = $databaseResultsArray['data'];
        $this->currentModelsArray = array();
        $this->associationArray = array();
        $this->associationKeyArray = array();
        $this->tableToModelArray = array();
        $this->instancedModelsArray = array();
        foreach($selectorDataArray as $selectorData) {
            $tableName = $selectorData['tableName'];
            $this->currentModelsArray[$tableName] = null;
            if($selectorData['parent'] == null) {
                $this->associationArray[$tableName] = null;
            }
            else {
                $this->associationArray[$tableName] = $selectorData['parent'];
            }

            if($selectorData['relationKey'] == null) {
                $this->associationKeyArray[$tableName] = null;
            }
            else {
                $this->associationKeyArray[$tableName] = $selectorData['relationKey'];
            }

            $this->tableToModelArray[$tableName] = $selectorData['modelName'];
            $this->modelInsertStatusArray[$tableName] = true;
        }
    }

    /**
     *
     * @return array
     */
    public function getModels() {
        foreach($this->databaseResultsArray as $dataRow) {
            $tempModelData = $this->breakIntoModels($dataRow);
            foreach($tempModelData as $tableName => $tableData) {
                if(!$this->isNull($tableData)) {
                    if(!$this->isCurrent($tableName, $tableData)) {
                        $modelName = $this->tableToModelArray[$tableName];
                        $model = new $modelName($tableData);

                        if($this->hasParent($tableName)) {
                            if($this->shouldAppendChild($tableName)) {
                                $baseModel = $this->instancedModelsArray[count($this->instancedModelsArray) - 1];
                                $parentName = $this->associationArray[$tableName];
                                $appendKey = $this->associationKeyArray[$tableName];
                                $this->appendModelToParent($parentName, $model, $baseModel, $appendKey);
                            }
                        }
                        else {
                            $this->instancedModelsArray[] = $model;
                        }

                        $this->clearChildren($tableName);
                        $this->currentModelsArray[$tableName] = $tableData;
                    }
                }
            }
        }

        return $this->instancedModelsArray;
    }

    private function breakIntoModels(Array $dataRow) {
        $modelArray = array();
        $tempArray = array();
        $lastModelName = $this->baseTable;
        foreach($dataRow as $fullyQualifiedFieldName => $fieldValue) {
            $modelName = $this->getModelName($fullyQualifiedFieldName);
            if($modelName != $lastModelName) {
                $modelArray[$lastModelName] = $tempArray;
                $tempArray = array();
                $lastModelName = $modelName;
            }

            $fieldName = $this->getFieldName($fullyQualifiedFieldName);
            $tempArray[$fieldName] = $fieldValue;
        }
        $modelArray[$lastModelName] = $tempArray;
        return $modelArray;
    }

    private function getModelName($fullyQualifiedColumnName) {
        return String::sub($fullyQualifiedColumnName, 0, String::position('.', $fullyQualifiedColumnName));
    }

    private function getFieldName($fullyQualifiedColumnName) {
        return String::sub($fullyQualifiedColumnName, String::position('.', $fullyQualifiedColumnName) + 1, String::length($fullyQualifiedColumnName));
    }

    private function isNull(Array $tableData) {
        foreach($tableData as $columnName => $columnValue) {
            if($columnValue != null) {
                return false;
            }
        }
        return true;
    }

    private function isCurrent($tableName, Array $tableData) {
        if($this->currentModelsArray[$tableName] != null) {
            foreach($this->currentModelsArray[$tableName] as $columnName => $columnValue) {
                if($tableData[$columnName] != $columnValue) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    private function hasParent($tableName) {
        return $this->associationArray[$tableName] != null;
    }

    private function appendModelToParent($parentName, Model $modelToAppend, Model $currentModel, $appendKey) {
        $meta = Model::getModelMeta($currentModel->getModelName());
        $tableName = $currentModel->getTableName();
        if($tableName == $parentName) {
            $appened = $currentModel->addRelatedModel($modelToAppend, $appendKey);
            $this->modelInsertStatusArray[$modelToAppend->getTableName()] = $appened;
            return true;
        }
        else {
            // get the names of the children for the current model
            $children = array_keys($meta['foreignKeys']);

            foreach($children as $child) {
                /* @var ModelList */
                $modelList = $currentModel->getRelatedModelList($child);
                if($modelList->getSize() > 0) {
                    // get the last one
                    $childModel = $modelList->get($modelList->getSize() - 1);
                    $modelAdded = $this->appendModelToParent($parentName, $modelToAppend, $childModel, $appendKey);
                    if($modelAdded) {
                        return true;
                    }
                }
            }
            return false;
        }
    }

    private function clearChildren($tableName) {
        $tablesToCheck = array($tableName);
        $dataCleared = true;
        while($dataCleared) {
            $dataCleared = false;
            $newTablesToCheck = array();
            foreach($tablesToCheck as $tableToCheck) {
                foreach($this->currentModelsArray as $currentTableName => $currentModelData) {
                    if($this->associationArray[$currentTableName] == $tableToCheck) {
                        $this->currentModelsArray[$currentTableName] = null;
                        $dataCleared = true;
                        $newTablesToCheck[] = $currentTableName;
                    }
                }
            }
            $tablesToCheck = $newTablesToCheck;
        }
    }

    private function shouldAppendChild($tableName) {
        // if the parent was successfully added, then we should add the model...
        return $this->modelInsertStatusArray[$this->associationArray[$tableName]];
    }
}

?>
