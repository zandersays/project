<?php
class Validation {

    public static function email($value) {
        return preg_match('/^[A-Z0-9._%-\+]+@(?:[A-Z0-9\-]+\.)+[A-Z]{2,4}$/i', $value);
    }

}
?>