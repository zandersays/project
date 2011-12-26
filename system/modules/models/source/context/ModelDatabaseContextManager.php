<?php

/**
 * Description of ModelDatabaseContext
 *
 * @author Kam Sheffield
 * @version 09/14/2011
 */
class ModelDatabaseContextManager {

    /**
     *
     * @return ModelDatabaseContextManager
     */
    public static function getInstance() {
        if(!self::$instance instanceof ModelDatabaseContextManager) {
            self::$instance = new ModelDatabaseContextManager();
        }
        return self::$instance;
    }

    /**
     *
     * @var ModelDatabaseContextManager
     */
    private static $instance;

    /**
     *
     * @var ModelDatabaseContext
     */
    private $globalModelDatabaseContext;

    /**
     *
     * @var array
     */
    private $localModelDatabaseContextList;

    /**
     *
     * @var array
     */
    private $instancedModelDatabaseContexts;

    /**
     *
     */
    private function __construct() {
        $this->globalModelDatabaseContext = null;
        $this->localModelDatabaseContextList = array();
        $this->instancedModelDatabaseContexts = array();
    }

    /**
     *
     * @param DatabaseDriver $databaseDriver
     */
    public function setGlobalModelDatabaseContext(DatabaseDriver $databaseDriver) {
        $this->globalModelDatabaseContext = new ModelDatabaseContext($databaseDriver);
    }

    /**
     *
     * @return ModelDatabaseContext
     */
    public function getGlobalModelDatabaseContext() {
        return $this->globalModelDatabaseContext;
    }

    /**
     *
     */
    public function removeGlobalModelDatabaseContext() {
       $this->globalModelDatabaseContext = null;
    }

    /**
     *
     * @return string
     */
    public function getGlobalModelDatabaseContextDatabaseId() {
        return $this->globalModelDatabaseContext->getDatabaseId();
    }

    /**
     *
     * @param string $modelName
     * @param DatabaseDriver $databaseDriver
     */
    public function setLocalModelDatabaseContext($modelName, DatabaseDriver $databaseDriver) {
        if(!array_key_exists($databaseDriver->getId(), $this->instancedModelDatabaseContexts)) {
            $this->instancedModelDatabaseContexts[$databaseDriver->getId()] = new ModelDatabaseContext($databaseDriver);
        }
        $this->localModelDatabaseContextList[$modelName] = $databaseDriver->getId();
    }

    /**
     *
     * @param string $modelName
     * @return ModelDatabaseContext
     */
    public function getLocalDatabaseContext($modelName) {
        if(array_key_exists($modelName, $this->localModelDatabaseContextList)) {
            $databaseId = $this->localModelDatabaseContextList[$modelName];
            if(array_key_exists($databaseId, $this->instancedModelDatabaseContexts)) {
                return $this->instancedModelDatabaseContexts[$databaseId];
            }
        }

        // TODO log me
        return null;
    }

    /**
     *
     * @param string $modelName
     */
    public function removeLocalModelDatabaseContext($modelName) {
        if(array_key_exists($modelName, $this->localModelDatabaseContextList)) {
            unset($this->localModelDatabaseContextList[$modelName]);
        }
        else {
            // TODO log me
        }
    }

    /**
     *
     * @param string $modelName
     * @return string
     */
    public function getLocalModelDatabaseContextDatabaseId($modelName) {
        $modelDatabaseContext = $this->getLocalDatabaseContext($modelName);
        if($modelDatabaseContext != null) {
            return $modelDatabaseContext->getDatabaseId();
        }
        return null;
    }

    /**
     *
     * @param string $modelName
     * @return ModelDatabaseContext
     */
    public function getModelDatabaseContextForModel($modelName) {
        $tableName = String::camelCaseToUnderscores($modelName);
        return $this->getModelDatabaseContextForTable($tableName);
    }

    /**
     *
     * @param string $tableName
     * @return ModelDatabaseContext
     */
    public function getModelDatabaseContextForTable($tableName) {
        $databaseContext = $this->getLocalDatabaseContext($tableName);
        if($databaseContext == null) {
            $databaseContext = $this->globalModelDatabaseContext;
        }
        return $databaseContext;
    }
}

?>
