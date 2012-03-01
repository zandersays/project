<?php
class UsersForms {

    function login($formValues) {
        //sleep(1);
        $response = array();
        //echo Url::decode($formValues->loginRedirect);
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;
        
        try {
            $login = UserApi::login($formValues->loginIdentifier, $formValues->loginPassword, Arr::contains('yes', $formValues->loginRememberMe) ? true : false);    
        }
        catch(Exception $exception) {
            //print_r($exception); exit();
        }
        //$response['failureNoticeHtml'] = Json::encode($login); return $response;

        if($login['status'] == 'failure') {
            $response['failureJs'] = "$('#loginPassword').val('').focus();";
            $response['failureNoticeHtml'] = $login['response'];
            return $response;
        }
        else {
            if(!empty($formValues->loginRedirect)) {
                $response['redirect'] = Url::decode($formValues->loginRedirect);
            }
            else {
                $response['successJs'] = 'location.reload(true);';
            }
        }

        return $response;
    }

    function loginDialog($formValues) {
        return $this->login($formValues);
    }

    function addAUser($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Register the user
        $user = UserApi::register($formValues->username, $formValues->email, $formValues->reformedPassword, $formValues->status);
        //print_r($user);

        // TODO: Conditionally send the new user a message which may be custom

        $response['failureNoticeHtml'] = $user['response'];

        return $response;
    }

    function editUser($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        $user = User::readById($formValues->userId)->execute();
        $user->setUsername($formValues->username);
        $user->setStatus($formValues->status);

        // Todo: Change user password
        // Todo: Update user e-mails

        $user->save();

        $response['failureNoticeHtml'] = 'Changes successfully saved.';

        return $response;
    }

    function deleteUsers($formValues) {
        $response['failureNoticeHtml'] = 'Deleting users is not allowed.'; return $response;

        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        $userIdArray = Json::decode($formValues->userIdArray);
        foreach($userIdArray as $userId) {
            echo $userId;
            $user = User::readById($userId)->execute();
            $user->delete();
        }

        $response['successPageHtml'] = '
            <h2>Successfully Deleted Users</h2><p>Visit the <a href="' . Project::getInstanceAccessPath() . 'project/modules/users/users/">users section</a> to see the change.</p>
        ';

        return $response;
    }

    function register($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Register the user
        UserApi::register('unverified', $formValues->username, $formValues->reformedPassword, $formValues->email);

        // TODO: Conditionally send the new user a message which may be custom

        $response['failureNoticeHtml'] = 'Added user '.$formValues->username.'.';

        return $response;
    }

    function registerDialog($formValues) {
        //return Controller::getVariable('Module:users/forms/register-success', array(
        //    'username' => 'testing a long name!',
        //    'email' => 'test@test.com',
        //));
        //return array('failureNoticeHtml' => Json::encode($formValues));

        // Register the user
        $register = UserApi::register($formValues->registerUsername, $formValues->registerEmail, $formValues->registerReformedPassword);

        // If the registration is successful
        if($register['status'] == 'success') {
            return Controller::getVariable('Module:users/forms/register-success', array(
                'username' => $register['username'],
                'email' => $register['email'],
            ));
        }
        // If registration fails
        else {
            return array('failureNoticeHtml' => $register['response']);
        }
    }

    function editRegistrationEmailSettings($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        $formValues = Object::arr($formValues);
        $registrationEmailSettings['emailOptions']['mailType'] = $formValues['mailType'];
        $registrationEmailSettings['emailOptions']['characterSet'] = $formValues['characterSet'];
        $registrationEmailSettings['emailOptions']['userAgent'] = $formValues['userAgent'];
        $registrationEmailSettings['emailOptions']['sendMailPath'] = $formValues['sendMailPath'];
        $registrationEmailSettings['emailOptions']['protocol'] = $formValues['protocol'];
        $registrationEmailSettings['emailOptions']['smtpHost'] = $formValues['smtpHost'];
        $registrationEmailSettings['emailOptions']['smtpPort'] = Number::integerValue($formValues['smtpPort']);
        $registrationEmailSettings['emailOptions']['smtpUsername'] = $formValues['smtpUsername'];
        $registrationEmailSettings['emailOptions']['smtpPassword'] = $formValues['smtpPassword'];
        $registrationEmailSettings['emailOptions']['smtpTimeout'] = Number::integerValue($formValues['smtpTimeout']);
        $registrationEmailSettings['emailOptions']['wordWrap'] = Arr::contains('yes', $formValues['wordWrap']) ? true : false;
        $registrationEmailSettings['emailOptions']['wordWrapCharacters'] = Number::integerValue($formValues['wordWrapCharacters']);

        //$response['failureNoticeHtml'] = Json::encode($registrationEmailSettings); return $response;

        // Set the settings
        $usersSettings = Project::getModuleSettings('Users');
        if(!isset($usersSettings['users'])) {
            $usersSettings['users'] = array();
        }
        $usersSettings['users']['registrationEmail'] = $registrationEmailSettings;
        Project::setModuleSettings('Users', $usersSettings);

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Saved Registration E-mail Settings</h2><p>Visit the <a href="'.Project::getInstanceAccessPath().'project/modules/users/settings/users/users/registration-email/">user registration e-mail settings section</a> to see your changes.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save settings to settings.php file.';
        }

        return $response;
    }
    
}
?>