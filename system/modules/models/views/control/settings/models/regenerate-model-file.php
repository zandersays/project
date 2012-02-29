<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/../project/system/core/Project.php');

if(!isset($modelName)) {
    $modelName = $_GET['modelName'];
}

$regenerateModels = new Form('regenerateModels', array(
    'action' => Project::getInstanceAccessPath().'project/control/settings/models/regenerate-models.php?modelName='.$modelName,
    'submitButtonText' => 'Confirm Regeneration',
    'style' => 'width: 600px;',
));

$regenerateModels->addFormSection(
    new FormSection('regenerateModelsSection', array(
        'description' => '
            <p>You have specified these models for regeneration:</p>
            <ul style="margin: 0 0 0 1.5em;">
                <li>'.$modelName.'</li>
            </ul>
        '
    ))
);

$regenerateModels->addFormComponent(
    new FormComponentHidden('modelName', $modelName)
);

$regenerateModels->processRequest(true);

function onSubmit($formValues) {

    $response = array();

    //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;
    //print_r(Project::$instance->settings->models);

    // Check to see if the model already exists
    foreach(Project::$instance->settings->models as $modelName => $modelOptions) {
        if($formValues->model->modelName == $modelName) {
            $response['failureNoticeHtml'] = 'Model <b>'.$formValues->model->modelName.'</b> does not exist.';
            return $response;
        }
    }

    // Remove the model from the settings
    unset(Project::$instance->settings->models->{$formValues->modelName});
    //$response['failureNoticeHtml'] = Json::encode(Project::$instance->settings->models); return $response;

    if(Project::saveSettings()) {
        $response['successPageHtml'] = '<h2>Successfully Removed Model <b>'.$formValues->modelName.'</b></h2><p>Visit the <a href="/project/settings/models/">models section</a> to see the change.</p>';
    }
    else {
        $response['failureNoticeHtml'] = 'Unable to save remove model from settings.php file.';
    }

    return $response;
}
?>