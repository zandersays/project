<?php

/**
 * Description of ColumnIndex
 *
 * @author Kam Sheffield
 * @version 08/11/2011
 */
class TableIndex {

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var array
     */
    protected $columnsIndexedArray;

    /**
     *
     * @var string TableColumnIndexType
     */
    protected $type;

    /**
     *
     * @param string $name
     * @param string $type
     */
    public function __construct($name = '', $type = '') {
        $this->name = $name;
        $this->type = $type;
        $this->columnsIndexedArray = array();
    }

    /**
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     *
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     *
     * @param string $columnName
     * @param int $sequencesInIndex
     * @param boolean $isOrderAscending
     * @param string or int $length
     */
    public function addIndexedColumn($columnName, $sequenceInIndex, $isOrderAscending = true, $length = null) {
        if(!$this->containsIndexedColumn($columnName)) {
            $this->columnsIndexedArray[$sequenceInIndex] = array(
                'columnName' => $columnName,
                'isOrderAscending' => $isOrderAscending,
                'length' => $length
            );
            return true;
        }
        return false;
    }

    /**
     *
     * @param string $columnName
     */
    public function removeIndexedColumn($columnName) {
        foreach($this->columnsIndexedArray as $indexedColumn) {
            if($indexedColumn['columnName'] == $columnName) {
                unset($indexedColumn['columnName']);
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param string $columnName
     * @return boolean
     */
    public function containsIndexedColumn($columnName) {
        foreach($this->columnsIndexedArray as $indexedColumn) {
            if($indexedColumn['columnName'] == $columnName) {
                return true;
            }
        }
        return false;
    }

    public function getIndexedColumn($columnName) {
        foreach($this->columnsIndexedArray as $indexedColumn) {
            if($indexedColumn['columnName'] == $columnName) {
                return $indexedColumn;
            }
        }
        return null;
    }

    /**
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     *
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     *
     * @return array
     */
    public function getColumnsIndexedArray() {
        return $this->columnsIndexedArray;
    }

    /**
     *
     * @param array $columnsIndexedArray
     */
    public function setColumnsIndexedArray(Array $columnsIndexedArray) {
        $this->columnsIndexedArray = $columnsIndexedArray;
    }

    /**
     *
     * @param TableIndex $tableIndex
     * @return array
     */
    public function compare(TableIndex $tableIndex) {
        $comparisonArray = array();

        if($this->name != $tableIndex->name) {
            $comparisonArray['name'] = $tableIndex->name;
        }

        if($this->type != $tableIndex->type) {
            $comparisonArray['type'] = $tableIndex->type;
        }

        $alterIndex = false;
        $comparisonArray['indexedColumns'] = array();
        foreach($this->columnsIndexedArray as $indexColumn) {
            if($tableIndex->containsIndexedColumn($indexColumn['columnName'])) {
                $tableIndexedColumn = $tableIndex->getIndexedColumn($indexColumn['columnName']);

                if($indexColumn['length'] != $tableIndexedColumn['length']) {
                    $alterIndex = true;
                    break;
                }

                if($indexColumn['isOrderAscending'] != $tableIndexedColumn['isOrderAscending']) {
                    $alterIndex = true;
                    break;
                }
            }
            else {
                $alterIndex = true;
                break;
            }
        }

        if($alterIndex) {
            $comparisonArray['indexedColumns'] = $tableIndex->getColumnsIndexedArray();
        }

        return $comparisonArray;
    }
}

?>
