<?php
class SessionsControl extends Controller {

    function index($data) {
        return $this->$data['function']($data);
    }

    function settingsSessions($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }

}
