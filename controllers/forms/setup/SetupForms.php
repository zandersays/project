<?php
class SetupForms {

    function setup($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        Project::setSiteTitle($formValues->settings->siteTitle);

        // Instance settings
        $instance = array();
        $instanceId = String::htmlId($formValues->settings->siteTitle);
        $instance['id'] = String::htmlId(String::lower(String::replace(' ', '-', $formValues->settings->siteTitle.' '.$formValues->settings->instanceType)));
        $instance['type'] = $formValues->settings->instanceType;
        $instance['setupTime'] = time();
        Project::updateInstance($instance['id'], $instance);

        // Administrator settings
        $passwordSalt = uniqid();
        $administrator = array(
            'username' => $formValues->administrator->administratorUsername,
            'email' => $formValues->administrator->administratorEmail,
            'passwordSalt' => $passwordSalt,
            'password' => hash('sha512', $passwordSalt.$formValues->administrator->administratorPassword),
        );
        Project::setAdministrator($administrator);

        // Run each of the essential modules install() method
        foreach(Project::getEssentialModules() as $moduleName) {
            call_user_func(array($moduleName.'Module', 'install'));
        }

        // Remove the setup route
        $routesSettings = Project::getModuleSettings('Routes');
        $routesSettings['routes'] = Router::deleteRouteByRouteHash(Security::md5('/project/setup/'));
        Project::setModuleSettings('Routes', $routesSettings);

        Project::saveSettings();

        //$response['failureNoticeHtml'] = Json::encode(Project::getSettings()); return $response;
        $projectSettings = Project::getSettings();
        $response['failureNoticeHtml'] = 'Project instance successfully setup: <a href="'.Project::getInstanceAccessPath().'">'.$projectSettings['siteTitle'].'</a>';
        return $response;
    }

}
?>