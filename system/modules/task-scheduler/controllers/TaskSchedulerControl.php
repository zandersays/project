<?php
class TaskSchedulerControl extends Controller {

    function index($data) {
        return $this->$data['function']($data);
    }

    function settingsTaskScheduler($data) {
        return $this->getHtmlElement('Module:'.$data['modulePath'].'/'.String::camelCaseToDashes($data['path']), $data);
    }

}
?>