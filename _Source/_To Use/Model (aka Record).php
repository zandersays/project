<?php
class Record {
    var $database;
    var $table;
    var $changed = false;

    function __construct($database) {
        $this->database = $database;
        $this->table = Utility::fromCamelCasetoUnderscore(get_class($this));
    }

    function create($columnValuesArray) {
        $record = Database::createRecord($this->database, $this->table, $columnValuesArray);
        return $this->processRecord($record);
    }

    function read($id, $originTable = false) {
        $record = Database::readRecord($this->database, $this->table, 'id', $id);
        return $this->processRecord($record, $originTable);
    }
    
    function readByKey($key, $value, $clause = '') {
        $record = Database::readRecord($this->database, $this->table, $key, $value, $clause);
        return $this->processRecord($record);
    }

    function processRecord($record, $originTable = false) {
        if($record['status'] != 'success') {
            $response = array('status' => 'failure', 'response' => strtolower(get_class($this)).' record not found.');
        }
        else {
            foreach($record['response'] as $key => $value) {
                // If a foreign key exists (ends with an id)
                if(strrpos($key, '_id') === strlen($key)-strlen('_id')) {

                    $key = str_replace('_id', '', $key);
                    // Do not do another read to the origin table, if $originTable is set we don't need to read it again
                    // Also, do not read a foreign key relationship if the foreign key is NULL
                    if($originTable == $key || empty($value)) {
                        $this->{$key.'_id'} = $value;
                    }
                    else {
                        //echo 'We have a foreign key: '.$key;
                        // Populate the key with the foreign object
                        $class = Utility::toCamelCase($key, true);
                        if(class_exists($class)) {
                            $this->{$key} = new $class();
                        }
                        // Wipe out the first string_ piece to see if it is a self referential primary key, like commenter_user_id
                        else {
                            $class = preg_replace('/([A-Z][a-z]+)/', '', $class, 1);
                            $this->{$key} = new $class();
                        }
                        
                        $this->{$key}->read($value, $this->table);
                    }
                }
                else {
                    $this->{$key} = $value;
                }
            }
            $response = array('status' => 'success', 'response' => strtolower(get_class($this)).' record found.');
        }

        return $response;
    }

    function readRelationship($table) {
        if(!isset($this->id)) {
            $response = array('status' => 'failure', 'response' => 'Cannot read a relationship without having the origin record id.');
        }
        else {
            $originTable = Utility::fromCamelCasetoUnderscore(get_class($this));
            $record = Database::select($this->database, '* FROM '.$table.' WHERE `'.$originTable.'_id` = \''.$this->id.'\'');
            if($record['status'] != 'success') {
                $response = array('status' => 'failure', 'response' => 'No records found in '.$table.' containing a foreign key for a record in '.$originTable.'.');
                $this->{$table.'_array'} = array();
            }
            else {
                $class = Utility::toCamelCase($table, true);
                foreach($record['response'] as $object) {
                    $newObject = new $class();
                    $newObject->read($object->id, $originTable);
                    $this->{$table.'_array'}[] = $newObject;
                }
                $response = array('status' => 'success', 'response' => 'Successfully read '.sizeof($this->{$table.'_array'}).' relating records from '.$table.' to '.$originTable.'.');
            }
        }

        return $response;
        
    }

    function update($columnValuesArray) {
        $record = Database::updateRecord($this->database, $this->table, 'id', $this->id, $columnValuesArray, '');
        return $this->processRecord($record);
    }

    function delete() {

    }
}
?>