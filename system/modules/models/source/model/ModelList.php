<?php

/**
 * Description of ModelList
 *
 * @author Kam Sheffield
 * @version 09/14/2011
 */
class ModelList {

    /**
     *
     * @var string
     */
    protected $modelType;

    /**
     *
     * @var array
     */
    protected $modelArray;

    /**
     *
     * @var array
     */
    protected $primaryKeyArray;

    /**
     *
     * @param type $modelName
     * @param type $modelArray
     */
    public function __construct($modelName, Array $modelArray = array()) {
        $this->modelType = $modelName;
        $this->modelArray = $modelArray;
        $this->primaryKeyArray = array();
    }

    /**
     *
     * @return string
     */
    public function getType() {
        return $this->modelType;
    }

    /**
     * Alias for getCount()
     * 
     * @return int
     */
    public function getSize() {
        return $this->getCount();
    }
    
    /**
     *
     * @return int
     */
    public function getCount() {
        return count($this->modelArray);
    }

    /**
     *
     * @return Model
     */
    public function getFirst() {
        if(count($this->modelArray) > 0) {
            return $this->modelArray[0];
        }
        return null;
    }

    /**
     *
     * @return Model
     */
    public function getLast() {
        $count = count($this->modelArray);
        if($count > 0) {
            return $this->modelArray[$count - 1];
        }
        return null;
    }

    /**
     *
     * @param int $index
     * @return Model
     */
    public function get($index) {
        return $this->modelArray[$index];
    }

    /**
     *
     * @return array
     */
    public function asArray() {
        return $this->modelArray;
    }

    /**
     *
     * @param boolean $isRecursive
     * @return array
     */
    public function toArray($isRecusive = false) {
        $modelArray = array();
        foreach($this->modelArray as $model) {
            $modelArray[] = $model->toArray($isRecusive);
        }
        return $modelArray;
    }

    /**
     * Alias for push.
     *
     * @param Model $model
     * @return boolean
     */
    public function add(Model $model) {
        return $this->push($model);
    }

    /**
     *
     * @param Model $model
     * @return boolean
     */
    public function push(Model $model) {
        if($model instanceof $this->modelType && $this->okToAdd($model)) {
            array_push($this->modelArray, $model);
            return true;
        }
        return false;
    }

    /**
     *
     * @param Model $model
     * @return boolean
     */
    public function pushFront(Model $model) {
        if($model instanceof $this->modelType && $this->okToAdd($model)) {
            array_unshift($this->modelArray, $model);
            return true;
        }
        return false;
    }

    /**
     *
     * @return Model
     */
    public function pop() {
        return array_pop($this->modelArray);
    }

    /**
     *
     * @return Model
     */
    public function popFront() {
        return array_shift($this->modelArray);
    }

    /**
     *
     * @param int $index
     * @return Model
     */
    public function remove($index) {
        $model = $this->modelArray[$index];
        unset($this->modelArray[$index]);
        return $model;
    }

    /**
     *
     * @param string $fieldName
     * @return array
     */
    public function getAll($fieldName) {
        // string off the table if a fully qualified name was given
        if(String::contains('.', $fieldName)) {
            $fieldName = String::subString($fieldName, String::lastIndexOf('.', $fieldName) + 1);
        }

        $array = array();
        foreach($this->modelArray as $model) {
            $array[] = $model->get($fieldName);
        }
        return $array;
    }

    /**
     *
     * @param string $fieldName
     * @param mixed $value
     */
    public function setAll($fieldName, $value) {
        // string off the table if a fully qualified name was given
        if(String::contains('.', $fieldName)) {
            $fieldName = String::subString($fieldName, String::lastIndexOf('.', $fieldName) + 1);
        }

        foreach($this->modelArray as $model) {
            $model->set($fieldName, $value);
        }
    }

    /**
     *
     * @exception ModelException If the save operation fails at the database level.
     */
    public function save() {
        foreach($this->modelArray as $model) {
            $model->save();
        }
    }

    /**
     *
     * @exception ModelException If the delete operation fails at the database level.
     */
    public function delete() {
        foreach($this->modelArray as $model) {
            $model->delete();
        }
    }

    protected function okToAdd(Model $model) {
        $primaryKeyValue = $model->get($model->getPrimaryKeyName());
        if($primaryKeyValue != null) {
            if(array_key_exists($primaryKeyValue, $this->primaryKeyArray)) {
                return false;
            }
            else {
                $this->primaryKeyArray[$primaryKeyValue] = null;
                return true;
            }
        }
        return true;
    }
}

?>
