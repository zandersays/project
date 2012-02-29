<?php

/**
 * Description of SqlFunction
 *
 * @author Kam Sheffield
 * @version 08/19/2011
 */
class Sql {

    /**
     * NOW()
     *
     * @return string
     */
    public static function now() {
        return ':NOW()';
    }

    /**
     * INET_ATON()
     *
     * @param string $ipAddress
     * @return string
     */
    public static function inetAton($ipAddress) {
        return ':INET_ATON(\''.$ipAddress.'\')';
    }

    /**
     * STRCMP()
     *
     * @param string $string1
     * @param string $string2
     * @return string
     */
    public static function strcmp($string1, $string2) {
        return ':STRCMP(\''.$string1.'\', \''.$string2.'\')';
    }
}

?>
