<?php

/**
 * Description of ModelSelectorResults
 *
 * @author Kam Sheffield
 * @version 09/14/2011
 */
class ModelSelectorResults {

    /**
     * 
     * @var string
     */
    protected $tableName;
    
    /**
     *
     * @var string
     */
    protected $modelName;

    /**
     *
     * @var array
     */
    protected $modelArray;

    /**
     *
     * @var int
     */
    protected $count;

    /**
     *     
     * @param string $tableName
     * @param array $modelArray
     * @param int $count
     */
    public function __construct($tableName, Array $modelArray, $count) {
        $this->tableName = $tableName;
        $this->modelName = String::underscoresToCamelCase($tableName, true);
        $this->modelArray = $modelArray;
        $this->count = $count;
        if($count == null) {
            $this->count = count($modelArray);
        }
    }

    /**
     *
     * @return string
     */
    public function getTableName() {
        return $this->tableName;
    }

        
    /**
     *
     * @return string
     */
    public function getModelName() {
        return $this->modelName;
    }

    /**
     *
     * @return int
     */
    public function getCount() {
        return $this->count;
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
     * @return ModelList
     */
    public function toArray() {
        $array = array();
        foreach($this->modelArray as $model) {
            $array[] = $model->toArray();
        }
        return $array;
    }

    /**
     *
     * @return ModelList
     */
    public function asModelList() {
        return new ModelList($this->modelName, $this->modelArray);
    }

    /**
     *
     * @return array
     */
    public function asArray() {
        return $this->modelArray;
    }
}

?>
