<?php
class AdministratorForms {

    function editAdministrator($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Set the session settings
        Project::setAdministratorUsername($formValues->username);
        Project::setAdministratorEmail($formValues->email);
        
        // Change the username immediately if they are logged in via a settings login
        if(UserApi::$user['authenticationMethod'] == 'settings') {
            UserApi::$user['username'] = $formValues->username;
        }

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Saved Administrator Settings</h2><p>Visit the <a href="'.Project::getInstanceAccessPath().'project/settings/administrator/">administrator section</a> to see your changes.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save administrator settings to settings.php file.';
        }

        return $response;
    }

    function changeAdministratorPassword($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Check the current password
        if(Project::getAdministratorPassword() != hash('sha512', Project::getAdministratorPasswordSalt().$formValues->currentPassword)) {
            $response['failureNoticeHtml'] = 'The current password you provided is incorrect.'; return $response;
        }

        // Set the session settings
        $passwordSalt = uniqid();
        Project::setAdministratorPasswordSalt($passwordSalt);
        Project::setAdministratorPassword(hash('sha512', $passwordSalt.$formValues->password));

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Saved Administrator Password</h2><p>You may now visit the <a href="'.Project::getInstanceAccessPath().'project/settings/administrator/change-password/">administrator password section</a>.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save new administrator password to settings.php file.';
        }

        return $response;
    }

}
?>