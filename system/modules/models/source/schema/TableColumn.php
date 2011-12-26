<?php

/**
 * Description of TableColumn
 *
 * @author Kam Sheffield
 * @version 08/15/2011
 */
class TableColumn {

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $dataType;

    /**
     *
     * @var string or int
     */
    protected $length;

    /**
     *
     * @var boolean
     */
    protected $isPrimaryKey;

    /**
     *
     * @var boolean
     */
    protected $isForeignKey;

    /**
     *
     * @var boolean
     */
    protected $isNonNull;

    /**
     *
     * @var boolean
     */
    protected $isBinary;

    /**
     *
     * @var boolean
     */
    protected $isUnsigned;

    /**
     *
     * @var boolean
     */
    protected $isZeroFill;

    /**
     *
     * @var boolean
     */
    protected $isAutoIncrementing;

    /**
     *
     * @var string
     */
    protected $defaultValue;

    /**
     *
     * @param string $name
     * @param string $dataType
     * @param string $defaultValue
     * @param string $length
     */
    public function __construct($name = '', $dataType = '', $defaultValue = null, $length = null) {
        $this->name = $name;
        $this->dataType = $dataType;
        $this->isAutoIncrementing = false;
        $this->isBinary = false;
        $this->isNonNull = false;
        $this->isPrimaryKey = false;
        $this->isForeignKey = false;
        $this->isUnsigned = false;
        $this->isZeroFill = false;
        $this->defaultValue = $defaultValue;
        $this->length = $length;
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
     * @return string
     */
    public function getDataType() {
        return $this->dataType;
    }

    /**
     *
     * @param string $dataType
     */
    public function setDataType($dataType) {
        $this->dataType = $dataType;
    }

    /**
     *
     * @return string or int
     */
    public function getLength() {
        return $this->length;
    }

    /**
     *
     * @param string or int $length
     */
    public function setLength($length) {
        $this->length = $length;
    }

    /**
     *
     * @return boolean
     */
    public function getIsPrimaryKey() {
        return $this->isPrimaryKey;
    }

    /**
     *
     * @param boolean $isPrimaryKey
     */
    public function setIsPrimaryKey($isPrimaryKey) {
        $this->isPrimaryKey = $isPrimaryKey;
    }

    /**
     *
     * @return boolean
     */
    public function getIsForeignKey() {
        return $this->isForeignKey;
    }

    /**
     *
     * @param booelan $isForeignKey
     */
    public function setIsForeignKey($isForeignKey) {
        $this->isForeignKey = $isForeignKey;
    }

    /**
     *
     * @return boolean
     */
    public function getIsNonNull() {
        return $this->isNonNull;
    }

    /**
     *
     * @param boolean $isNonNull
     */
    public function setIsNonNull($isNonNull) {
        $this->isNonNull = $isNonNull;
    }

    /**
     *
     * @return boolean
     */
    public function getIsBinary() {
        return $this->isBinary;
    }

    /**
     *
     * @param boolean $isBinaryColumn
     */
    public function setIsBinary($isBinaryColumn) {
        $this->isBinary = $isBinaryColumn;
    }

    /**
     *
     * @return boolean
     */
    public function getIsUnsigned() {
        return $this->isUnsigned;
    }

    /**
     *
     * @param boolean $isUnsigned
     */
    public function setIsUnsigned($isUnsigned) {
        $this->isUnsigned = $isUnsigned;
    }

    /**
     *
     * @return boolean
     */
    public function getIsZeroFill() {
        return $this->isZeroFill;
    }

    /**
     *
     * @param boolean $isZeroFill
     */
    public function setIsZeroFill($isZeroFill) {
        $this->isZeroFill = $isZeroFill;
    }

    /**
     *
     * @return boolean
     */
    public function getIsAutoIncrementing() {
        return $this->isAutoIncrementing;
    }

    /**
     *
     * @param boolean $isAutoIncrementing
     */
    public function setIsAutoIncrementing($isAutoIncrementing) {
        $this->isAutoIncrementing = $isAutoIncrementing;
    }

    /**
     *
     * @return string
     */
    public function getDefaultValue() {
        return $this->defaultValue;
    }

    /**
     *
     * @param string $defaultValue
     */
    public function setDefaultValue($defaultValue) {
        $this->defaultValue = $defaultValue;
    }

    /**
     *
     * @param TableColumn $tableColumn
     * @return array
     */
    public function compare(TableColumn $tableColumn) {
        $comparisonArray = array();

        if($this->name != $tableColumn->name) {
            $comparisonArray['name'] = $tableColumn->name;
        }

        if($this->dataType != $tableColumn->dataType) {
            $comparisonArray['dataType'] = $tableColumn->dataType;
        }

        if($this->defaultValue != $tableColumn->defaultValue) {
            $comparisonArray['defaultValue'] = $tableColumn->defaultValue;
        }

        if($this->length != $tableColumn->length) {
            $comparisonArray['length'] = $tableColumn->length;
        }

        if($this->isAutoIncrementing != $tableColumn->isAutoIncrementing) {
            $comparisonArray['isAutoIncrementing'] = $tableColumn->isAutoIncrementing;
        }

        if($this->isBinary != $tableColumn->isBinary) {
            $comparisonArray['isBinaryColumn'] = $tableColumn->isBinary;
        }

        if($this->isForeignKey != $tableColumn->isForeignKey) {
            $comparisonArray['isForeignKey'] = $tableColumn->isForeignKey;
        }

        if($this->isNonNull != $tableColumn->isNonNull) {
            $comparisonArray['isNonNull'] = $tableColumn->isNonNull;
        }

        if($this->isPrimaryKey != $tableColumn->isPrimaryKey) {
            $comparisonArray['isPrimaryKey'] = $tableColumn->isPrimaryKey;
        }

        if($this->isUnsigned != $tableColumn->isUnsigned) {
            $comparisonArray['isUnsigned'] = $tableColumn->isUnsigned;
        }

        if($this->isZeroFill != $tableColumn->isZeroFill) {
            $comparisonArray['isZeroFill'] = $tableColumn->isZeroFill;
        }

        return $comparisonArray;
    }
}

?>
