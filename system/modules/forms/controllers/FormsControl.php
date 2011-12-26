<?php
class FormsControl extends Controller {

    function index($data) {
        return $this->$data['function']($data);
    }

    function settingsForms($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }
    
}
?>