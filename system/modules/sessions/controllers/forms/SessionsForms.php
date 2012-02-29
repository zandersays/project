<?php
class SessionsForms {

    function editSessionsSettings($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Set the session settings
        $sessionsSettings = Object::arr($formValues);
        $sessionsSettings['regenerate'] = $sessionsSettings['regenerate'] == 'Yes' ? true : false;

        Project::setModuleSettings('Sessions', $sessionsSettings);

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Saved Session Settings</h2><p>Visit the <a href="'.Project::getInstanceAccessPath().'project/modules/sessions/settings/sessions/">sessions section</a> to see your changes.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save session settings to settings.php file.';
        }

        return $response;
    }

}
?>