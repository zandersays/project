<?php
class DatabasesForms {

    function addADatabase($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;
        
        // Get the current databases, make sure it is an array
        $databasesSettings = Project::getModuleSettings('Databases');
        if(!Arr::is($databasesSettings)) {
            $databasesSettings = array();
            $databasesSettings['databases'] = array();
        }
        
        // Check to see if any other database has the global context
        if(!empty($formValues->database->globalContext)) {
            foreach($databasesSettings['databases'] as $database) {
                if($database['globalContext']) {
                    return array('failureNoticeHtml' => 'Could not create database because <b>'.$database['name'].'</b> is currently set as the global context.');
                }
            }    
        }        

        // This database is for all instances
        if(Arr::contains('Yes', $formValues->database->allInstances)) {
            $instances = 'all';
        }
        // Handle instance specific databases
        else {
            $instances = $formValues->database->instances;
        }

        // Add the instance to the database
        $databasesSettings['databases'][] = array(
            'type' => $formValues->database->databaseType,
            'name' => $formValues->database->databaseName,
            'host' => $formValues->database->databaseHost,
            'port' => $formValues->database->databasePort,
            'username' => $formValues->database->databaseUsername,
            'password' => $formValues->database->databasePassword,
            'modelPath' => $formValues->database->modelPath,
            'globalContext' => !empty($formValues->database->globalContext),
            'instances' => $instances,
        );

        // Save the database settings
        Project::setModuleSettings('Databases', $databasesSettings);

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Added Database <b>' . $formValues->database->databaseName . '</b></h2><p>Visit the <a href="' . Project::getInstanceAccessPath() . 'project/modules/databases/settings/databases/">databases section</a> to see the addition.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save new database to settings.php file.';
        }

        return $response;
    }

    function deleteDatabases($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;
        // Get the databases
        $databasesSettings = Project::getModuleSettings('Databases');
        $databaseName = $databasesSettings['databases'][$formValues->databaseIndex]['name'];

        // Remove the requested index
        $databasesSettings['databases'][$formValues->databaseIndex] = null;
        $databasesSettings['databases'] = Arr::filter($databasesSettings['databases']);
        Project::setModuleSettings('Databases', $databasesSettings);

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Removed Database <b>' . $databaseName . '</b></h2><p>Visit the <a href="' . Project::getInstanceAccessPath() . 'project/modules/databases/settings/databases/">databases section</a> to see the change.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save remove database from settings.php file.';
        }

        return $response;
    }

    function editDatabase($formValues) {
        $response = array();

        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Get the current databases, make sure it is an array
        $databasesSettings = Project::getModuleSettings('Databases');
        if(!Arr::is($databasesSettings)) {
            $databasesSettings = array();
            $databasesSettings['databases'] = array();
        }
        
        // Check to see if any other database has the global context
        if(!empty($formValues->database->globalContext)) {
            $databaseIndex = 0;
            foreach($databasesSettings['databases'] as $database) {
                if($database['globalContext'] && $databaseIndex != $formValues->database->databaseIndex) {
                    return array('failureNoticeHtml' => 'Could not create database because <b>'.$database['name'].'</b> is currently set as the global context.');
                }
                $databaseIndex++;
            }    
        }
        

        // This database is for all instances
        if(Arr::contains('Yes', $formValues->database->allInstances)) {
            $instances = 'all';
        }
        // Handle instance specific databases
        else {
            $instances = $formValues->database->instances;
        }

        // Add the instance to the database
        $databasesSettings['databases'][$formValues->database->databaseIndex] = array(
            'type' => $formValues->database->databaseType,
            'name' => $formValues->database->databaseName,
            'host' => $formValues->database->databaseHost,
            'port' => $formValues->database->databasePort,
            'username' => $formValues->database->databaseUsername,
            'password' => $formValues->database->databasePassword,
            'modelPath' => $formValues->database->modelPath,
            'globalContext' => !empty($formValues->database->globalContext),
            'instances' => $instances,
        );

        // Save the database settings
        Project::setModuleSettings('Databases', $databasesSettings);

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Edited Database <b>'.$formValues->database->databaseName.'</b></h2><p>Visit the <a href="'.Project::getInstanceAccessPath().'project/modules/databases/settings/databases/">databases section</a> to see the change.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save database changes to settings.php file.';
        }

        return $response;
    }

}
?>