<?php

/**
 * Description of FilterByFlags
 *
 * @author Kam Sheffield
 * @version 08/19/2011
 */
class FilterByFlags {

    /**
     *
     */
    const CaseInsensitive = 1;

    /**
     *
     * @param int $flags
     * @return boolean
     */
    public static function isCaseInsensitive($flags) {
        return ($flags & self::CaseInsensitive) > 0;
    }
}

?>
