<?php
class EmailControl extends Controller {

    function index($data) {
        return $this->$data['function']($data);
    }
    
    function settingsEmail($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }

}