<?php

/**
 * Description of ModelDriverFactory
 *
 * @author Kam Sheffield
 * @version 08/03/2011
 */
class ModelDriverFactory {

    /**
     *
     * @param DatabaseDriver $databaseDriver
     * @return ModelDriver
     */
    public static function create(DatabaseDriver $databaseDriver) {
        if($databaseDriver instanceof DatabaseDriverMySql) {
            return new ModelDriverMySql($databaseDriver);
        }
        else {
            //TODO log me
            throw new ModelException('Unable to instance a ModelDriver for ' . get_class($databaseDriver) . ', no ModelDriver exists for this database type.');
        }
    }
}

?>
