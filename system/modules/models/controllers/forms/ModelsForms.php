<?php
class ModelsForms {

    function deleteModelFile($formValues) {
        //return array('failureNoticeHtml' => Json::encode($formValues));
        
        $fileName = Project::getInstancePath().'models/'.$formValues->databaseName.'/'.$formValues->modelName.'.php';
        if(!File::exists($fileName)) {
            return array('failureNoticeHtml' => 'Model file '.$fileName.' does not exist.');
        }
        else {
            File::delete($fileName);
            $response = array();
            $response['successPageHtml'] = '<h2>Successfully Deleted Model File for <b>'.$formValues->modelName.' on '.$formValues->databaseName.'</b></h2><p>Model file removed: '.$fileName.'</p>';
            return $response;
        }
    }

    function generateModelFile($formValues) {
        $response = array();
        //return array('failureNoticeHtml' => Json::encode($formValues));

        // Create the model file
        $generateModel = ModelGenerator::generateModel($formValues->databaseName, String::camelCaseToUnderscores($formValues->modelName), Project::getInstancePath().'models/');

        // Add the model to the classes array in settings
        $modelsSettings = Project::getModuleSettings('Models');
        if(!isset($modelsSettings['classes'])) {
            $modelsSettings['classes'] = array();
        }
        $modelClass = String::replace(Project::getInstancePath().'models/', '', $generateModel);
        $modelsSettings['classes'][$formValues->modelName] = $modelClass;
        Project::setModuleSettings('Models', $modelsSettings);
        Project::saveSettings();

        $response['successPageHtml'] = '<h2>Successfully Generated Model Class File for <b>'.$formValues->modelName.' on '.$formValues->databaseName.'</b></h2><p>'.$generateModel.'</p>';
        //print_r(Project::$instance->settings->databases);

        return $response;
    }

    function generateModelFiles($formValues) {
        $response = array();
        //return array('failureNoticeHtml' => Json::encode($formValues));
        
        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriverByDatabaseName($formValues->databaseName);
        $modelsPath = ModelManager::getModelPathFromDatabaseName($databaseDriver->getDatabaseName());
        $relativeModelsPath = ModelManager::getModelPathFromDatabaseName($databaseDriver->getDatabaseName(), true);
        $modelManager = new ModelManager($databaseDriver, $modelsPath);        
                
        // Check to see if the models path exists
        if(!Dir::exists($modelsPath)) {
            Dir::create($modelsPath, 0777, true);
        }
        
        // Get the models settings
        $modelsSettings = Project::getModuleSettings('Models');
        if(!isset($modelsSettings['classes'])) {
            $modelsSettings['classes'] = array();
        }
        
        // Generate all of the models for a database
        foreach($modelManager->getModels() as $modelName => $modelMeta) {
            $modelManager->createModelPhpClassFileFromDatabaseTable($modelName);
            $modelsSettings['models'][$modelName] = array(
                'file' => $relativeModelsPath.'/'.$modelName.'.php',
                'context' => $databaseDriver->getDatabaseName(),
            );
        }        
                
        Project::setModuleSettings('Models', $modelsSettings);
        Project::saveSettings();
        
        //return array('failureNoticeHtml' => $modelsPath);

        $response['successPageHtml'] = '<h2>Successfully Generated Model Files for <b>'.$databaseDriver->getDatabaseName().'</b></h2>';

        return $response;
    }

    function regenerateClassFile($formValues) {

    }
    
}
?>