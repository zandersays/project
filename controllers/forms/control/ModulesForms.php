<?php
class ModulesForms {

    function activateModule($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Move the module from settings['modules'] to settings ['inactiveModules']
        Project::activateModule($formValues->moduleKey);
        //$response['failureNoticeHtml'] = Json::encode(Project::$instance->settings); return $response;

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Activated Module <b>'.$formValues->moduleKey.'</b></h2><p>Visit the <a href="/project/settings/modules/">modules section</a> to see the change.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to activate module in settings.php file.';
        }

        return $response;
    }

    function deactivateModule($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Move the module from settings['modules'] to settings ['inactiveModules']
        Project::deactivateModule($formValues->moduleKey);
        //$response['failureNoticeHtml'] = Json::encode(Project::$instance->settings); return $response;

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Deactivated Module <b>'.$formValues->moduleKey.'</b></h2><p>Visit the <a href="/project/settings/modules/">modules section</a> to see the change.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to deactivate module in settings.php file.';
        }

        return $response;
    }

    function installModule($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        $checkDependencies = Module::checkDependencies($formValues->moduleKey);
        if($checkDependencies['status'] == 'failure') {
            $failureHtml = '';
            // Modules
            if(Arr::size($checkDependencies['missingModules']) > 0) {
                $failureHtml = '<p>Must install or activate these <b>modules</b> first:</p><p>'.Arr::implode(', ', $checkDependencies['missingModules']).'</p>';
            }
            // Models
            if(Arr::size($checkDependencies['missingModels']) > 0) {
                $failureHtml = '<p>Must install these <b>models</b> first:</p><p>'.Arr::implode(', ', $checkDependencies['missingModels']).'</p>';
            }
            $response['failureHtml'] = $failureHtml;
            return $response;
        }

        // Add the module to the settings file
        Project::setModuleSettings($formValues->moduleKey, array());
        //$response['failureNoticeHtml'] = Json::encode(Project::$instance->settings); return $response;

        // Call the modules install function
        call_user_func(array(Module::getModuleClass($formValues->moduleKey), 'install'));

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Installed Module <b>'.$formValues->moduleKey.'</b></h2><p>Visit the <a href="'.Project::getInstanceAccessPath().'project/settings/modules/">modules section</a> to see the change.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to install module in settings.php file.';
        }

        return $response;
    }

    function reinstallModule($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        $uninstallModule = $this->uninstallModule($formValues);
        //if(isset($uninstallModule['failureNoticeHtml']) || isset($uninstallModule['failureHtml'])) {
        //    return $uninstallModule;
        //}

        $installModule = $this->installModule($formValues);
        if(isset($installModule['failureNoticeHtml']) || isset($installModule['failureHtml'])) {
            return $installModule;
        }
        
        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Reinstalled Module <b>'.$formValues->moduleKey.'</b></h2><p>Visit the <a href="/project/settings/modules/">modules section</a> to see the change.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to reinstall module in settings.php file.';
        }

        return $response;
    }

    function uninstallModule($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // See if the module has any dependecies
        $checkExternalDependencies = Module::checkExternalDependencies($formValues->moduleKey);
        if($checkExternalDependencies['status'] == 'failure') {
            $response['failureHtml'] = '<p>The following module(s) depend this module and must be uninstalled or deactivated first:</p><p>'.Arr::implode(', ', $checkExternalDependencies['externallyDependentModules']).'</p>';
            return $response;
        }

        // Remove the module from the settings file
        Project::uninstallModule($formValues->moduleKey);
        //$response['failureNoticeHtml'] = Json::encode(Project::$instance->settings); return $response;

        // Call the modules uninstall function
        call_user_func(array(Module::getModuleClass($formValues->moduleKey), 'uninstall'));

        if(Project::saveSettings()) {
            $response['successPageHtml'] = '<h2>Successfully Uninstalled Module <b>'.$formValues->moduleKey.'</b></h2><p>Visit the <a href="'.Project::getInstanceAccessPath().'project/settings/modules/">modules section</a> to see the change.</p>';
        }
        else {
            $response['failureNoticeHtml'] = 'Unable to uninstall module in settings.php file.';
        }

        return $response;
    }

}
?>