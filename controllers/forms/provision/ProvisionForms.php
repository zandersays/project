<?php
class ProvisionForms {

    function provision($formValues) {
        $response = array();
        //$response['failureNoticeHtml'] = Json::encode($formValues); return $response;

        // Get the site directory
        $instancePath = $formValues->instancePath;
        if(!String::endsWith('/', $instancePath)) {
            $instancePath = $instancePath.'/';
        }

        // Conditionally remove the instance path
        if(!empty($formValues->deleteInstancePath)) {
            Dir::delete($instancePath);
        }

        // Check if the directory already exists
        if(Dir::exists($instancePath)) {
            $response['failureNoticeHtml'] = $instancePath.' already exists.'; return $response;
        }
        else {
            // Create the directory
            Dir::create($instancePath);

            // Set the permissions on the new directory
            Dir::chmod($instancePath, 0777);

            // Make sure the directory was created
            if(!Dir::exists($instancePath)) {
                $response['failureNoticeHtml'] = $instancePath.' could not be created. Please check your file permissions.'; return $response;
            }
            else if(Dir::chmod($instancePath) != 16895) {
                $response['failureNoticeHtml'] = $instancePath.' was created successfully but the appropriate permissions could not be set.'; return $response;
            }
        }

        // Copy the templated source files to the directory
        Dir::copy(Project::getProjectPath().'_template', $instancePath);

        // Adjust the htaccess file
        if(isset($formValues->instanceAccessPath)) {
            $instanceAccessPath = $formValues->instanceAccessPath;

            if($instanceAccessPath == '/') {
                $instanceAccessPath = '';
            }
        }
        else {
            $instanceAccessPath = '';
        }

        $htaccess = File::content($instancePath.'.htaccess');
        $htaccess = String::replace('[instanceAccessPath]', $instanceAccessPath, $htaccess);
        File::write($instancePath.'.htaccess', $htaccess);

        $instanceAccessPath = '/'.$instanceAccessPath.'/';
        $instanceAccessPath = String::replace('//', '/', $instanceAccessPath);

        // Adjust the settings file
        include($instancePath.'project/settings.php');
        chdir('../');
        $settings = array();

        $settings['instances'] = array();
        $settings['instances'][0] = array();
        $settings['instances'][0]['projectPath'] = getcwd().'/';
        $settings['instances'][0]['path'] = $instancePath;
        $settings['instances'][0]['accessPath'] = $instanceAccessPath;
        $settings['instances'][0]['host'] = $formValues->instanceHost;

        File::write($instancePath.'project/settings.php', "<?php".
            "\n".'$settings = '.Arr::php($settings)."\n".
            "?>"
        );

        $response['failureNoticeHtml'] = 'Project instance successfully provisioned at '.$instancePath.'.'."\n".'Setup a virtual host and visit the base directory of your site to customize your installation.';

        return $response;
    }

}
?>