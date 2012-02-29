<?php
class Session {

    public static $sessionDriver;
    public static $data;

    public static function start($id = '') {
        return self::$sessionDriver->start($id);
    }

    public static function get($key) {
        return self::$sessionDriver->get($key);
    }

    public static function getId() {
        return self::$sessionDriver->getId();
    }

    public static function data() {
        return self::$sessionDriver->data;
    }

    public static function set($key, $value) {
        return self::$sessionDriver->set($key, $value);
    }

    public static function delete($keys) {
        return self::$sessionDriver->delete($keys);
    }

    public static function regenerate() {
        return self::$sessionDriver->regenerate();
    }

    public static function destroy() {
        return self::$sessionDriver->destroy();
    }

}
?>