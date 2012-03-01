<?php
class InstancesForms {

    function addAnInstance($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Check to see if the instance already exists
        if(Project::getInstance($formValues->instance->instanceId)) {
            $response['failureNoticeHtml'] = 'Instance with that ID already exists.';
            return $response;
        }

        // Add the instance to project
        Project::addInstance(array(
            'id' => $formValues->instance->instanceId,
            'type' => $formValues->instance->instanceType,
            'host' => $formValues->instance->instanceHost,
            'accessPath' => $formValues->instance->instanceAccessPath,
            'path' => $formValues->instance->instancePath,
            'projectPath' => $formValues->instance->projectPath,
            'setupTime' => time(),
        ));
        //$response['failureNoticeHtml'] = Json::encode(Project::$instance->settings['instances']); return $response;

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Added Instance <b>'.$formValues->instance->instanceId.'</b></h2><p>Visit the <a href="/project/settings/instances/">instances section</a> to see the addition.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save new instance to settings.php file.';
        }

        return $response;
    }

    function deleteInstances($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        $instance = Project::getInstance($formValues->instanceId);

        // Check to see if the current instance is the one they are trying to delete
        if($formValues->instanceId == Project::getInstanceId()) {
            $response['failureNoticeHtml'] = 'You may not remove the current instance.';
            return $response;
        }

        // Make sure there is at least one instance
        if(Arr::size(Project::getInstances()) == 1) {
            $response['failureNoticeHtml'] = 'You may not remove the only instance.';
            return $response;
        }

        // Remove the instance from the settings
        Project::deleteInstance($formValues->instanceId);
        //$response['failureNoticeHtml'] = Json::encode(Project::$instance->settings['instances']); return $response;

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Removed Instance <b>'.$formValues->instanceId.'</b></h2><p>Visit the <a href="/project/settings/instances/">instances section</a> to see the change.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to remove instance from settings.php file.';
        }

        return $response;
    }

    function editInstance($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Check to see if the instance ID is already taken
        if($_GET['instanceId'] != $formValues->instance->instanceId && Project::getInstance($formValues->instance->instanceId)) {
            $response['failureNoticeHtml'] = 'Instance ID already taken.';
            return $response;
        }

        // Save the changes to instance in project settings
        Project::updateInstance($_GET['instanceId'], array(
            'id' => $formValues->instance->instanceId,
            'type' => $formValues->instance->instanceType,
            'host' => $formValues->instance->instanceHost,
            'accessPath' => $formValues->instance->instanceAccessPath,
            'path' => $formValues->instance->instancePath,
            'projectPath' => $formValues->instance->projectPath,
        ));

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Edited Instance <b>'.$formValues->instance->instanceId.'</b></h2><p>Visit the <a href="/project/settings/instances/">instances section</a> to see the addition.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save instance changes to settings.php file.';
        }

        return $response;
    }

}
?>