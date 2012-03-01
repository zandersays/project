<?php

/**
 * Description of ModelContext
 *
 * @author Kam Sheffield
 * @version 09/13/2011
 */
class ModelContext {

    /**
     *
     * @param DatabaseDriver $databaseDriver
     */
    public static function setGlobalContext(DatabaseDriver $databaseDriver) {
        ModelDatabaseContextManager::getInstance()->setGlobalModelDatabaseContext($databaseDriver);
    }

    /**
     *
     */
    public static function removeGlobalContext() {
        ModelDatabaseContextManager::getInstance()->removeGlobalModelDatabaseContext();
    }

    /**
     *
     * @return string
     */
    public static function getGlobalContextDatabaseId() {
        return ModelDatabaseContextManager::getInstance()->getGlobalModelDatabaseContextDatabaseId();
    }

    /**
     *
     * @param string $modelName
     * @param DatabaseDriver $databaseDriver
     */
    public static function setLocalContext($modelName, DatabaseDriver $databaseDriver) {
        ModelDatabaseContextManager::getInstance()->setLocalModelDatabaseContext($modelName, $databaseDriver);
    }

    /**
     *
     * @param array $modelNames
     * @param DatabaseDriver $databaseDriver
     */
    public static function setLocalContextArray(Array $modelNames, DatabaseDriver $databaseDriver) {
        $modelDatabaseContextManager = ModelDatabaseContextManager::getInstance();
        foreach($modelNames as $modelName) {
            $modelDatabaseContextManager->setLocalModelDatabaseContext($modelName, $databaseDriver);
        }
    }

    /**
     *
     * @param string $modelName
     */
    public static function removeLocalContext($modelName) {
        ModelDatabaseContextManager::getInstance()->removeLocalModelDatabaseContext($modelName);
    }

    /**
     *
     * @param string $modelName
     * @return string
     */
    public static function getLocalContextDatabaseId($modelName) {
        return ModelDatabaseContextManager::getInstance()->getLocalModelDatabaseContextDatabaseId($modelName);
    }

    /**
     *
     * @param string $modelName
     */
    public static function prepare($modelName = null) {
        if($modelName == null) {
            return ModelDatabaseContextManager::getInstance()->getGlobalModelDatabaseContext()->prepare();
        }
        else {
            return ModelDatabaseContextManager::getInstance()->getLocalDatabaseContext($modelName)->prepare();
        }
    }

    /**
     *
     * @param string $modelName
     */
    public static function commit($modelName = null) {
        if($modelName == null) {
            return ModelDatabaseContextManager::getInstance()->getGlobalModelDatabaseContext()->commit();
        }
        else {
            return ModelDatabaseContextManager::getInstance()->getLocalDatabaseContext($modelName)->commit();
        }
    }

    /**
     *
     * @param string $modelName
     * @return boolean
     */
    public static function isPreparing($modelName = null) {
        if($modelName == null) {
            return ModelDatabaseContextManager::getInstance()->getGlobalModelDatabaseContext()->isPreparing();
        }
        else {
            return ModelDatabaseContextManager::getInstance()->getLocalDatabaseContext($modelName)->isPreparing();
        }
    }

    /**
     *
     * @param string $modelName
     * @return boolean
     */
    public static function isDebug($modelName = null) {
        if($modelName == null) {
            return ModelDatabaseContextManager::getInstance()->getGlobalModelDatabaseContext()->isDebug();
        }
        else {
            return ModelDatabaseContextManager::getInstance()->getLocalDatabaseContext($modelName)->isDebug();
        }
    }

    /**
     *
     * @param string $modelName
     * @param boolean $isDebug
     */
    public static function setDebug($modelName = null, $isDebug = false) {
        if($modelName == null) {
            ModelDatabaseContextManager::getInstance()->getGlobalModelDatabaseContext()->setIsDebug($isDebug);
        }
        else {
            ModelDatabaseContextManager::getInstance()->getLocalDatabaseContext($modelName)->setIsDebug($isDebug);
        }
    }
}

?>
