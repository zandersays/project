<?php
class DatabasesControl extends Controller {

    function index($data) {
        return $this->$data['function']($data);
    }

    function settingsDatabases($data) {
        $data['databases'] = Project::getModuleSettings('Databases');

        return $this->getView('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }

    function settingsDatabasesAddADatabase($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }

    function settingsDatabasesEditDatabase($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

    function settingsDatabasesDeleteDatabases($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data['pathArguments']);
    }

}
?>