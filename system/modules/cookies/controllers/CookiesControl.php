<?php
class CookiesControl extends Controller {

    function index($data) {
        return $this->$data['function']($data);
    }

    function settingsCookies($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }

}