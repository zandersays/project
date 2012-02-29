<?php

/**
 * Description of Model
 *
 * @author Kam Sheffield
 * @version 09/14/2011
 */
abstract class Model {

    /**
     * Get the meta data array for the model specified.
     *
     * @param string $modelName
     * @return array
     */
    public static function getModelMeta($modelName, $metaKey = '') {
        // if the table name was passed, change it to a model name
        if(String::contains('_', $modelName)) {
            $modelName = String::underscoresToCamelCase($modelName, true);
        }

        // get the meta array from the model
        return call_user_func(array(
            $modelName, 'getMetaArray'
        ), $metaKey);
    }

    /**
     *
     * @var string
     */
    protected $modelName;

    /**
     *
     * @var string
     */
    protected $tableName;

    /**
     *
     * @var string
     */
    protected $primaryKeyName;

    /**
     * An map of field names to
     * their current values.
     *
     * @var array
     */
    protected $fieldArray;

    /**
     * A map of field names
     * to their new values, once
     * a value has been changed.
     *
     * @var array
     */
    protected $changedFieldArray;

    /**
     * An associative array of
     * ModelNames to ModelLists of
     * that Model.
     *
     * @var array
     */
    protected $relatedModelArray;

    /**
     * Whether or not the instance of this
     * model is new or from the database.
     *
     * @var bool
     */
    protected $wasInstancedFromDatabase;

    /**
     *
     * @param string $modelName
     * @param string $tableName
     * @param string $primaryKeyName
     * @param array $fieldValueArray
     * @param array $relatedModelArray
     */
    protected function __construct($modelName, $tableName, $primaryKeyName, Array $fieldValueArray, Array $relatedModelArray) {
        $this->modelName = $modelName;
        $this->tableName = $tableName;
        $this->primaryKeyName = $primaryKeyName;
        $this->fieldArray = array();
        $this->changedFieldArray = array();
        $this->relatedModelArray = array();

        // get the meta data for the model we are instanceing
        $meta = Model::getModelMeta($this->modelName);

        // check to see if any default values are being passed in from the database
        // initialize the fields
        $fieldNameArray = array_keys($meta['columns']);
        if(empty($fieldValueArray)) {
            // set each field value to null
            foreach($fieldNameArray as $fieldName) {
                $this->fieldArray[$fieldName] = null;
            }
        }
        else {
            // initialize any fields with data from the database
            foreach($fieldNameArray as $fieldName) {
                if(array_key_exists($fieldName, $fieldValueArray)) {
                    $this->fieldArray[$fieldName] = $fieldValueArray[$fieldName];
                }
                else {
                    $this->fieldArray[$fieldName] = null;
                }
            }
        }

        // initialize the related models
        $relatedModelNameArray = array_keys($meta['foreignKeys']);
        if(empty($relatedModelArray)) {
            foreach($relatedModelNameArray as $relatedModelName) {
                $this->relatedModelArray[$relatedModelName] = null;
            }
        }
        else {
            foreach($relatedModelNameArray as $relatedModelName) {
                if(array_key_exists($relatedModelName, $relatedModelArray)) {
                    $this->relatedModelArray[$relatedModelName] = $relatedModelArray[$relatedModelName];
                }
                else {
                    $this->relatedModelArray[$relatedModelName] = null;
                }
            }
        }

        if($fieldValueArray == null && $relatedModelArray == null) {
            $this->wasInstancedFromDatabase = false;
        }
        else {
            $this->wasInstancedFromDatabase = true;
        }
    }

    /**
     * Removes the current instance of this model
     * from the database.
     *
     * @exception ModelException If the delete operation fails at the database.
     */
    public function delete() {
        if($this->wasInstancedFromDatabase) {
            // get the database context we need
            $modelContext = ModelDatabaseContextManager::getInstance()->getModelDatabaseContextForTable($this->tableName);

            // delete this model from the database
            return $modelContext->deleteModel($this->tableName, $this->primaryKeyName, $this->fieldArray[$this->primaryKeyName]);
        }
    }

    /**
     *
     * @param boolean $refreshFields Whether or not to refresh the fields with the database values after the save operation occurrs.
     * @param boolean $isRecursive Currently ignored.
     * @exception ModelException If the save operation fails at the database.
     */
    public function save($refreshFields = false, $isRecursive = false) {
        // for now recursive save is disabled
        $toReturn = 0;
        if(count($this->changedFieldArray) > 0) {
            $modelContext = ModelDatabaseContextManager::getInstance()->getModelDatabaseContextForTable($this->tableName);

            if(!$this->wasInstancedFromDatabase) {
                $toReturn = $modelContext->insertModel($this->tableName, $this->changedFieldArray);
            }
            else {
                $toReturn = $modelContext->updateModel($this->tableName, $this->primaryKeyName, $this->fieldArray[$this->primaryKeyName], $this->changedFieldArray);
            }

            if($refreshFields) {
                $refreshedFields = $modelContext->refreshModel($this->tableName, $this->primaryKeyName, $this->fieldArray[$this->primaryKeyName]);
                foreach($refreshedFields as $fieldName => $fieldValue) {
                    $this->fieldArray[$fieldName] = $fieldValue;
                }

                if($this->fieldArray[$this->primaryKeyName] != null) {
                    $this->wasInstancedFromDatabase = true;
                }
            }

            // reset the changed fields
            $this->changedFieldArray = array();
        }

        return $toReturn;
    }

    /**
     * Get the value of the $fieldName
     * specified.
     *
     * @param string $fieldName
     * @return mixed The value of the given $fieldName
     * @exception ModelException If the field with $fieldName does not exist in the Model.
     */
    public function get($fieldName) {
        // strip off the table if a fully qualified name was given
        if(String::contains('.', $fieldName)) {
            $fieldName = String::subString($fieldName, String::lastIndexOf('.', $fieldName) + 1);
        }

        if(array_key_exists($fieldName, $this->fieldArray)) {
            if(Json::is($this->fieldArray[$fieldName])) {
                return Object::arr(Json::decode($this->fieldArray[$fieldName]));
            }
            else {
                return $this->fieldArray[$fieldName];
            }
        }
        else {
            throw new ModelException('Fatal Error: The Field: ' . $fieldName . ', does not exist in Model: ' . $this->modelName);
        }
    }

    /**
     * Set the value with the given
     * $fieldName with the given $value.
     * The $value specified will not be type
     * bound until save is called on this model.
     *
     * @param string $fieldName
     * @param mixed $value
     * @exception ModelException If the field with $fieldName does not exist in the Model.
     */
    public function set($fieldName, $value) {
        // strip off the table if a fully qualified name was given
        if(String::contains('.', $fieldName)) {
            $fieldName = String::subString($fieldName, String::lastIndexOf('.', $fieldName) + 1);
        }

        if(array_key_exists($fieldName, $this->fieldArray)) {
            if(Arr::is($value) || Object::is($value)) {
                $value = Json::encode($value);
            }

            // set the value in the fields
            $this->fieldArray[$fieldName] = $value;

            // add it to the changed fields
            $this->changedFieldArray[$fieldName] = $value;
        }
        else {
            throw new ModelException('FatalError: The Field: ' . $fieldName . ', does not exist in Model: ' . $this->modelName);
        }
    }

    /**
     * Returns a ModelList for the related
     * field name given.  The field name must
     * be fully qualified, ie table.column
     *
     * @param string $fieldName
     * @return ModelList
     */
    public function getRelatedModelList($fieldName) {
        // lazy initialize a model list for a related model type if we need too
        if(!array_key_exists($fieldName, $this->relatedModelArray) ||
                (array_key_exists($fieldName, $this->relatedModelArray) && $this->relatedModelArray[$fieldName] === null)) {

            // get the model name out of the key
            $modelName = String::subString($fieldName, 0, String::indexOf('.', $fieldName));
            $modelName = String::underscoresToCamelCase($modelName, true);

            $this->relatedModelArray[$fieldName] = new ModelList($modelName);
        }

        // give them the list they want
        return $this->relatedModelArray[$fieldName];
    }

    /**
     * Adds a Model to the ModelList associated with the
     * field name specified.  The field name must be
     * fully qualified, ie table.column.  If the Model
     * you are attempting to add is not related to this Model
     * then false is returned.
     *
     * @param Model $model
     * @param string $fieldName
     * @return boolean Whether or not the model was successfully added.
     */
    public function addRelatedModel(Model $model, $fieldName) {
        if(array_key_exists($fieldName, $this->relatedModelArray)) {
            if($this->relatedModelArray[$fieldName] == null) {
                $this->relatedModelArray[$fieldName] = new ModelList($model->getModelName());
            }
            return $this->relatedModelArray[$fieldName]->add($model);
        }
        else {
            return false;
        }
    }

    /**
     * Get a integer value that uniquely
     * identifies this instance of the Model
     * in the system.
     *
     * @return string
     */
    public function getUniqueId() {
        return spl_object_hash($this);
    }

    /**
     * Returns a hash code that uniquely identifies this Model.
     *
     * If the primary key field contains a value, the hash
     * is comprised of the table name and the primary key value.
     *
     * If the primary key field is null, then a has of the table
     * name and all current field values is used.
     *
     * @return int
     */
    public function getHashCode() {
        $toHash = $this->tableName;
        if($this->fieldArray[$this->primaryKeyName] != null) {
            $toHash .= $this->fieldArray[$this->primaryKeyName];
        }
        else {
            $fieldValues = array_values($this->fieldArray);
            foreach($fieldValues as $fieldValue) {
                $toHash .= $fieldValue;
            }
        }
        return crc32($toHash);
    }

    /**
     * Return the fields of this Model as an array.
     *
     * @param boolean $isRecursive If you want all child Models to be included in the array.
     * @return array
     */
    public function toArray($isRecursive = false) {
        $array = array();
        foreach($this->fieldArray as $key => $value) {
            if(Json::is($value)) {
                $value = Object::arr(Json::decode($value));
            }
            $array[String::underscoresToCamelCase($key)] = $value;
        }

        $array['relatedModels'] = array();
        if($isRecursive) {
            foreach($this->relatedModelArray as $tableName => $modelList) {
                $modelName = String::underscoresToCamelCase($tableName);
                if($modelList instanceof ModelList) {
                    $array['relatedModels'][$modelName] = $modelList->toArray($isRecursive);
                }
                else {
                    $array['relatedModels'][$modelName] = array();
                }
            }
        }

        return $array;
    }

    /**
     * The name of this Model.
     *
     * @return string
     */
    public function getModelName() {
        return $this->modelName;
    }

    /**
     * The table this Models was derived from.
     *
     * @return string
     */
    public function getTableName() {
        return $this->tableName;
    }

    /**
     * The name of the primary key column.
     *
     * @return string
     */
    public function getPrimaryKeyName() {
        return $this->primaryKeyName;
    }

    // future features-------------------------------------------------/

    protected function validate($fieldName, $fieldValue) {
        throw new MethodNotImplementedException('validate');
    }

    protected function toViewModel() {
        throw new MethodNotImplementedException('toViewModel');
    }
}

?>
