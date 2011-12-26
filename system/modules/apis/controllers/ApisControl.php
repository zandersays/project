<?php
class ApisControl extends Controller {

    function index($data) {
        return $this->$data['function']($data);
    }

    function settingsApis($data) {
        return $this->getView('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }
    
    function settingsApisExternalApis($data) {
        $data['externalApis'] = Module::getLocalizedSettings('APIs');
        
        Network::webRequest($url, $options);
        
        return $this->getView('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }

    function settingsApisExternalApisAddAnExternalApi($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }
    
}
?>