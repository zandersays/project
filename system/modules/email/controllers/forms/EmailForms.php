<?php
class EmailForms {

    function editEmailSettings($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        $email = Object::arr($formValues);
        $email['defaultMailType'] = $email['defaultMailType'];
        $email['defaultCharacterSet'] = $email['defaultCharacterSet'];
        $email['defaultUserAgent'] = $email['defaultUserAgent'];
        $email['defaultSendMailPath'] = $email['defaultSendMailPath'];
        $email['defaultProtocol'] = $email['defaultProtocol'];
        $email['defaultSmtpHost'] = $email['defaultSmtpHost'];
        $email['defaultSmtpPort'] = Number::integerValue($email['defaultSmtpPort']);
        $email['defaultSmtpUsername'] = $email['defaultSmtpUsername'];
        $email['defaultSmtpPassword'] = $email['defaultSmtpPassword'];
        $email['defaultSmtpTimeout'] = Number::integerValue($email['defaultSmtpTimeout']);
        $email['defaultWordWrap'] = Arr::contains('yes', $email['defaultWordWrap']) ? true : false;
        $email['defaultWordWrapCharacters'] = Number::integerValue($email['defaultWordWrapCharacters']);

        //$response['failureNoticeHtml'] = Json::encode($email); return $response;

        // Set the settings
        Project::setModuleSettings('Email', $email);

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Saved E-mail Settings</h2><p>Visit the <a href="'.Project::getInstanceAccessPath().'project/modules/email/settings/email/">e-mail settings section</a> to see your changes.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to save e-mail settings to settings.php file.';
        }

        return $response;
    }

}
?>