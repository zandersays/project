<?php
class CookiesForms {

    function editCookiesSettings($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        $cookies = Object::arr($formValues);
        $cookies['expiration'] = intval($cookies['expiration']);
        $cookies['httpsOnly'] = $cookies['httpsOnly'] == 'Yes' ? true : false;
        $cookies['httpProtocolOnly'] = $cookies['httpProtocolOnly'] == 'Yes' ? true : false;
        $cookies['signing'] = Arr::contains('Yes', $cookies['signing']) ? true : false;
        //$response['failureNoticeHtml'] = Json::encode($cookies); return $response;

        // Set the cookie settings
        Project::setModuleSettings('Cookies', $cookies);

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Saved Cookie Settings</h2><p>Visit the <a href="'.Project::getInstanceAccessPath().'project/modules/cookies/settings/cookies/">cookies section</a> to see your changes.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save cookie settings to settings.php file.';
        }

        return $response;
    }

}
?>