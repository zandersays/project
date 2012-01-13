<?php
class String {
    
    public static function queryStringToArray($string) {
        $response = array();
        parse_str($string, $response);
        
        $response = Arr::underscoreKeysToCamelCase($response);
        
        return $response;
    }

    /**
     *
     * @param string $string
     * @return boolean
     */
    public static function isNullOrEmpty($string) {
        return $string == null || empty($string);
    }

    /**
     *
     * @param string $string
     * @return boolean
     */
    public static function isNumeric($string) {
        return is_numeric($string);
    }

    /**
     *
     * @param string $string
     * @return boolean
     */
    public static function blank($string) {
        return String::isBlank($string);
    }

    /**
     *
     * @param string $string
     * @return boolean
     */
    public static function isBlank($string) {
        return empty($string);
    }

    public static function allCapsToRegular($string, $wordsToCapitalize = array()) {
        $sentences = array_map('ucfirst', array_map('trim', array_map('mb_strtolower', explode('.', $string))));
        $output = implode('. ', $sentences);

        foreach($wordsToCapitalize as $wordToCapitalize) {
            $output = String::replace(String::lower($wordToCapitalize), $wordToCapitalize, $output);
        }

        return $output;
    }

    public static function newLinesToBreakTags($string) {
        $string = nl2br($string);

        return $string;
    }

    public static function newLinesToParagraphTags($string, $lineBreaks = true, $xml = true) {
        $string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);

        // It is conceivable that people might still want single line-breaks without breaking into a new paragraph.
        if($lineBreaks == true) {
            return '<p>'.preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '$1<br'.($xml == true ? ' /' : '').'>$2'), trim($string)).'</p>';
        }            
        else {
            return '<p>'.preg_replace(array("/([\n]{2,})/i", "/([\r\n]{3,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", "</p>\n<p>", '$1<br'.($xml == true ? ' /' : '').'>$2'), trim($string)).'</p>';
        }
    }

    public static function htmlEntities($string) {
        return htmlentities($string);
    }

    public static function htmlEntitiesDecode($string) {
        return html_entity_decode($string);
    }

    public static function htmlSpecialCharacters($string) {
        return htmlspecialchars($string);
    }

    public static function date($timeFormat, $unixTime) {
        return date($timeFormat, $unixTime);
    }

    public static function is($variable) {
        return is_string($variable);
    }

    public static function padLeft($string, $length, $paddingString) {
        return str_pad($string, $length, $paddingString, STR_PAD_LEFT);
    }

    public static function padRight($string, $length, $paddingString) {
        return str_pad($string, $length, $paddingString, STR_PAD_RIGHT);
    }

    static function explode($delimiter, $string) {
        return explode($delimiter, $string);
    }

    static function trimLeft($string, $characterList = null) {
        if(isset($characterList)) {
            return ltrim($string, $characterList);
        }
        else {
            return ltrim($string);
        }
    }

    static function trim($string, $characterList = null) {
        if(isset($characterList)) {
            return trim($string, $characterList);
        }
        else {
            return trim($string);
        }
    }

    static function md5($string) {
        return md5($string);
    }

    static function urlEncode($string) {
        return urlencode($string);
    }

    static function urlDecode($string) {
        return urldecode($string);
    }

    static function lower($string) {
        return mb_strtolower($string);
    }

    static function upper($string) {
        return mb_strtoupper($string);
    }

    static function length($string) {
        return strlen($string);
    }

    static function replace($search, $replace, $string, $count = null) {
        // The function str_replace takes the option paramter 'count' and assigns the total number of
        // replaced occurences to the variable passed. This is dumb. So here, we use the count variable
        // to limit the number of replacements occuring using replaceOccurences.
        
        // If they want to replace occurrences
        if($count != null) {
            return self::replaceOccurences($search, $replace, $string, $count);
        }
        else {
            return str_replace($search, $replace, $string, $count);    
        }        
    }

    static function replaceOccurences($search, $replace, $string, $limit) {
        return preg_replace('/'.$search.'/', $replace, $string, $limit);
    }

    static function replaceLast($search, $replace, $string) {
        $pos = strrpos($string, $search);

        if($pos === false) {
            return $string;
        }
        else {
            return substr_replace($string, $replace, $pos, strlen($search));
        }
    }

    static function contains($search, $string, $caseSensitive = true) {
        if(!$caseSensitive) {
            $string = strtolower($string);
            $search = strtolower($search);
        }
        return strpos($string, $search) === false ? false : true;
    }

    static function stripSlashes($string) {
        return stripslashes($string);
    }
    
    static function stripTags($string, $allowedTags = null) {
        return strip_tags($string, $allowedTags);
    }
    
    static function stripSpaces($string) {
        return String::replace(' ', '', $string);
    }

    static function removeSlashes($string) {
        return self::stripSlashes($string);
    }

    static function addSlashes($string, $characterList = null) {
        if($characterList === null) {
            $string = addslashes($string);
        }
        else {
            $string = addcslashes($string, $characterList);
        }
        return $string;
    }

    static function lowerFirstCharacter($string) {
        $string[0] = strtolower($string[0]);
        return $string;
    }

    static function upperFirstCharacter($string) {
        $string = ucfirst($string);
        return $string;
    }

    /**
     *
     * @param string $string
     * @return boolean
     */
    public static function isCamelCase($string) {
        return !String::contains('_', $string);
    }

    /**
     *
     * @param string $string
     * @return boolean
     */
    public static function hasUnderscores($string) {
        return String::contains('_', $string);
    }

    static function camelCaseToDashes($string) {
        $string[0] = strtolower($string[0]);
        $string = preg_replace_callback('/([A-Z])/', create_function('$c', 'return "-".strtolower($c[1]);'), $string);
        return $string;
    }

    static function camelCaseToSpaces($string) {
        $string[0] = strtolower($string[0]);
        $string = preg_replace_callback('/([A-Z])/', create_function('$c', 'return " ".strtolower($c[1]);'), $string);
        return $string;
    }

    static function camelCaseToUnderscores($string) {
        // Don't mess with anything that is not a string
        if(!String::is($string)) {
            return $string;
        }
        
        $string[0] = strtolower($string[0]);
        $string = preg_replace_callback('/([A-Z])/', create_function('$c', 'return "_".strtolower($c[1]);'), $string);
        return $string;
    }

    static function underscoresToCamelCase($string, $capitalizeFirstCharacter = false) {
        if($capitalizeFirstCharacter) {
            $string = ucfirst($string);
        }
        return preg_replace_callback('/_([a-z0-9])/', create_function('$c', 'return strtoupper($c[1]);'), $string);
    }

    static function underscoresToTitle($string) {
        return String::title(str_replace('_', ' ', $string));
    }

    static function camelCaseToRegular($string) {
        $string = ucfirst($string);
        return preg_replace('/(?<=[a-z])(?=[A-Z])/',' ',$string);
    }

    static function camelCaseToTitle($string) {
        $string = ucfirst($string);
        return self::title(preg_replace('/(?<=[a-z])(?=[A-Z])/',' ',$string));
    }

    static function dashesToCamelCase($string, $capitalizeFirstCharacter = false) {
        $string = strtolower($string);
        $string = preg_replace_callback('/-(\w?)/', create_function('$c', 'return strtoupper($c[1]);'), $string);
        if($capitalizeFirstCharacter) {
            $string = ucfirst($string);
        }
        return $string;
    }

    static function regularToCamelCase($string, $capitalizeFirstCharacter = false) {
        return self::spacesToCamelCase($string, $capitalizeFirstCharacter);
    }

    static function titleToCamelCase($string, $capitalizeFirstCharacter = false) {
        return self::spacesToCamelCase($string, $capitalizeFirstCharacter);
    }

    static function spacesToCamelCase($string, $capitalizeFirstCharacter = false) {
        $string = strtolower($string);
        $string = preg_replace_callback('/ (\w?)/', create_function('$c', 'return strtoupper($c[1]);'), $string);
        if($capitalizeFirstCharacter) {
            $string = ucfirst($string);
        }
        return $string;
    }

    static function dashesToSpaces($string) {
        return str_replace('-', ' ', $string);
    }
    
    static function underscoresToSpaces($string) {
        return str_replace('_', ' ', $string);
    }

    static function dashesToUnderscores($string) {
        return str_replace('-', '_', $string);
    }

    static function dashesToTitle($string) {
        return String::title(str_replace('-', ' ', $string));
    }

    static function urlPath($string){
	$string = preg_replace("`\[.*\]`U","",$string);
        $string = str_replace("-","--",$string);
	$string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$string);
	$string = htmlentities($string, ENT_COMPAT, 'utf-8');
	$string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $string );
	$string = preg_replace( array("`[^a-z0-9\.']`i","`[-]`") , "-", $string);
	return strtolower(trim($string, '-'));
    }

     function htmlId($string) {
        $string = str_replace(' ', '_', $string);
        $string = str_replace('!', '', $string);
        $string = str_replace('+', '', $string);
        $string = String::lowerFirstCharacter($string);
        return $string;
    }

    static function columnName($string) {
        return strtolower(String::htmlId($string));
    }

    static function reverseUrlPath($string){
        $string = str_replace('--', '[dash]', $string);
	$string = str_replace('-', ' ', $string);
        $string = str_replace('[dash]', '-', $string);
	return $string;
    }

    static function startsWith($search, $string) {
        return strpos($string, $search) === 0;
    }

    static function endsWith($search, $string){
        return strrpos($string, $search) === strlen($string) - strlen($search);
    }

    static function hasAllCaps($string){
        return strtoupper($string) == $string;
    }

    static function isJson($string) {
        return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
    }

    static function stripTrailingCharacters($length, $string) {
        return $response = substr($string, 0, 0 - $length);
    }

    static function title($string) {
        $string = String::lower($string);
        $lowerCaseWords = array('of','a','the','and','an','or','nor','but','is','if','then','else','when', 'at','from','by','on','off','for','in','out','over','to','into','with');
        $upperCaseWords = array('qr', 'api');
        $words = explode(' ', $string);
        foreach($words as $key => $word) {
            if($key == 0 || !in_array($word, $lowerCaseWords)) {
                $words[$key] = ucwords($word);
            }

            if(in_array($word, $upperCaseWords)) {
                $words[$key] = strtoupper($word);
            }
        }
        $string = implode(' ', $words);
        return $string;
    }

    static function position($search, $string, $offset = 0) {
        return strpos($string, $search, $offset);
    }

    /**
     *
     * @param int $search
     * @param int $string
     * @param int $offset
     * @return string
     */
    public static function indexOf($search, $string, $offset = 0) {
        return strpos($string, $search, $offset);
    }

    /**
     *
     * @param int $search
     * @param int $string
     * @param int $offset
     * @return string
     */
    public static function lastIndexOf($search, $string, $offset = 0) {
        return strrpos($string, $search, $offset);
    }

    static function sub($string, $start, $length = null) {
        return self::subString($string, $start, $length);
    }

    /**
     *
     * @param int $string
     * @param int $start
     * @param int $length
     * @return string
     */
    public static function subString($string, $start, $length = null) {
        if($length == null) {
            $length = String::length($string);
        }
        return substr($string, $start, $length);
    }

    static function subCount($search, $string) {
        return substr_count($search, $string);
    }

    static function time($string) {
        if(Number::isInteger($string)) {
            return $string;
        }
        else {
            return strtotime($string);    
        }
    }

    /**
     *
     * @param string $string
     * @return string
     */
    public static function lastCharacter($string) {
        $length = String::length($string);
        return $string{$length - 1};
    }


    static function random($length = 32) {
        $characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $string = '';
        $maxValue = strlen($characters)-1;
        for($i=0; $i < $length; $i++) {
            $string .= substr($characters, rand(0, $maxValue), 1);
        }
        return $string;
    }

    static function parseInteger($string) {
        return intval($string);
    }

    // Convert a string to a 32-bit integer
    static function integer($string, $check, $magic) {
        $int32Unit = 4294967296;  // 2^32
        $length = strlen($string);
        for($i = 0; $i < $length; $i++) {
            $check *= $magic;
            // If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31) the result of converting to integer is undefined
            if($check >= $int32Unit) {
                $check = ($check - $int32Unit * (int) ($check / $int32Unit));
                // If the check less than -2^31
                $check = ($check < -2147483648) ? ($check + $int32Unit) : $check;
            }
            $check += ord($string{$i});
        }
        return $check;
    }

    static function addHtmlLinks($string) {
        $pattern = "!(http://|https://|ftp://|mailto:|smb://|afp://|file://|gopher://|news://|ssl://|sslv2://|sslv3://|tls://|tcp://|udp://)*([a-zA-Z0-9@:%_+*~#?&=.,/;-]*\.\w\w+/?[a-zA-Z0-9@:%_+*?~#&=/;-]*)!i";
        $string = preg_replace_callback($pattern, 'String::addHtmlLinksMatcher', $string);
        return $string;
    }

    static function addHtmlLinksMatcher($matches) {
        if(empty($matches[1])) {
            $link = "http://".$matches[0];
        }
        else {
            $link = $matches[1].$matches[2];
        }

        return "<a href=\"".$link."\" target=\"_blank\">".$matches[0]."</a>";
    }

    // Generate a hash
    public static function hash($string) {
        $check1 = self::integer($string, 0x1505, 0x21);
        $check2 = self::integer($string, 0, 0x1003F);

        $check1 >>= 2;
        $check1 = (($check1 >> 4) & 0x3FFFFC0 ) | ($check1 & 0x3F);
        $check1 = (($check1 >> 4) & 0x3FFC00 ) | ($check1 & 0x3FF);
        $check1 = (($check1 >> 4) & 0x3C000 ) | ($check1 & 0x3FFF);

        $t1 = (((($check1 & 0x3C0) << 4) | ($check1 & 0x3C)) <<2 ) | ($check2 & 0xF0F );
        $t2 = (((($check1 & 0xFFFFC000) << 4) | ($check1 & 0x3C00)) << 0xA) | ($check2 & 0xF0F0000 );

        return ($t1 | $t2);
    }

    // Generate a checksum for a hash string
    public static function checksumHash($hash) {
        $checkByte = 0;
        $flag = 0;

        $hashString = sprintf('%u', $hash) ;
        $length = strlen($hashString);

        for($i = $length - 1;  $i >= 0;  $i --) {
            $re = $hashString{$i};
            if(1 === ($flag % 2)) {
                $re += $re;
                $re = (int)($re / 10) + ($re % 10);
            }
            $checkByte += $re;
            $flag ++;
        }

        $checkByte %= 10;
        if(0 !== $checkByte) {
            $checkByte = 10 - $checkByte;
            if(1 === ($flag % 2) ) {
                if(1 === ($checkByte % 2)) {
                    $checkByte += 9;
                }
                $checkByte >>= 1;
            }
        }

        return '7'.$checkByte.$hashString;
    }

}
?>