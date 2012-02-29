<?php
class MessagingControl extends Controller {

    function index($data) {
        return $this->$data['function']($data);
    }

}