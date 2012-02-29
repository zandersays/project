<?php
abstract class SessionDriver {

    public $data = array();

    abstract public function start($id = '');

    abstract public function get($key);

    public function getId() {
        return Cookie::get('sessionId', false);
    }

    abstract public function set($key, $value);

    abstract public function delete($keys);

    abstract public function regenerate();

    abstract public function destroy($id = '');

}
?>