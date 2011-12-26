<?php
//require_once('HtmlElement.php');
//require_once('Image.php');
//require_once('Mail.php');
//require_once('Network.php');
//require_once('Record.php');
//require_once('Url.php');

abstract class Utility {
    public static function getPercentageScoreString($percentile) {
        $scoreArray = array('Excellent' => .8, 'Good' => .6, 'Average' => .4, 'Poor' => .2, 'Terrible' => 0);

        foreach($scoreArray as $scoreString => $scoreValue) {
            if($percentile >= $scoreValue) {
                return $scoreString;
            }
        }
    }

    public static function autoRequireOnce($path, $fileArray) {
        foreach($fileArray as $file) {
            require_once($path.$file);
        }
    }

    public static function isJson($string) {
    // Check argument is a json or not.
        return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
    }

    public static function stripTrailingComma($string) {
        return substr($string, 0, -2);
    }

    public static function stringToUrlPath($string){
	$string = preg_replace("`\[.*\]`U","",$string);
        $string = str_replace("-","--",$string);
	$string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$string);
	$string = htmlentities($string, ENT_COMPAT, 'utf-8');
	$string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $string );
	$string = preg_replace( array("`[^a-z0-9\.']`i","`[-]`") , "-", $string);
	return strtolower(trim($string, '-'));
    }

    public static function reverseStringToUrlPath($string){
        $string = str_replace('--', '[dash]', $string);
	$string = str_replace('-', ' ', $string);
        $string = str_replace('[dash]', '-', $string);
	return $string;
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -> firstName)
     * @param    string   $string                     String in underscore format
     * @param    bool     $capitaliseFirstCharacter   If true, capitalise the first char in $str
     * @return   string                            $str translated into camel caps
     */
    public static function toCamelCase($string, $capitaliseFirstCharacter = false) {
        if($capitaliseFirstCharacter) {
            $string[0] = strtoupper($string[0]);
        }
        return preg_replace_callback('/_([a-z0-9])/', create_function('$c', 'return strtoupper($c[1]);'), $string);
    }

    public static function fromCamelCasetoUnderscore($string) {
        $string[0] = strtolower($string[0]);
        $string = preg_replace_callback('/([A-Z])/', create_function('$c', 'return "_".strtolower($c[1]);'), $string);
        return $string;
    }

    public static function fromCamelCasetoDashes($string) {
        $string[0] = strtolower($string[0]);
        $string = preg_replace_callback('/([A-Z])/', create_function('$c', 'return "-".strtolower($c[1]);'), $string);
        return $string;
    }

    public static function toHtmlId($string) {
        $string = str_replace(" ", "_", $string);
        $string = str_replace("!", "", $string);
        $string = str_replace("+", "", $string);
        return $string;
    }

    public static function toColumnName($string) {
        return strtolower(Utility::toHtmlId($string));
    }

    public static function toRegularText($string) {
        $string = toCamelCase($string, true);
        return preg_replace('/(?<=[a-z])(?=[A-Z])/',' ',$string);
    }

    /**
     * A bubble sort that will sort an array of objects based on any one of the values contained in them
     *
     * usage: objectSort($details, 'percent');
     */
    public static function objectSort(&$data, $key) {
        for($i = count($data) - 1; $i >= 0; $i--) {
            $swapped = false;
            for($j = 0; $j < $i; $j++) {
                if($data[$j]->$key > $data[$j + 1]->$key) {
                    $tmp = $data[$j];
                    $data[$j] = $data[$j + 1];
                    $data[$j + 1] = $tmp;
                    $swapped = true;
                }
            }
            if(!$swapped) return;
        }
    }

    public static function objectToArray($object) {
        $array = array();
        foreach($object as $key => $value) {
            if(is_object($value)) {
                $value = objectToArray($value);
            }
            $array[$key] = $value;
        }
        return $array;
    }

    public static function arrayToObject($array = array()) {
        if(!empty($array)) {
            $data = false;
            foreach ($array as $akey => $aval) {
                if(is_array($aval)) {
                    $aval = arrayToObject($aval);
                }
                $data -> {$akey} = $aval;
            }
            return $data;
        }
        return false;
    }

    public static function printHtml(&$var, $var_name = NULL, $indent = NULL, $reference = NULL) {
        $print_html_indent = "<span style='color:#eeeeee;'>|</span> &nbsp;&nbsp; ";
        $reference = $reference.$var_name;
        $keyvar = 'the_print_html_recursion_protection_scheme'; $keyname = 'referenced_object_name';

        if (is_array($var) && isset($var[$keyvar])) {
            $real_var = &$var[$keyvar];
            $real_name = &$var[$keyname];
            $type = ucfirst(gettype($real_var));
            echo "$indent$var_name <span style='color:#a2a2a2'>$type</span> = <span style='color:#e87800;'>&amp;$real_name</span><br>";
        }
        else {
            $var = array($keyvar => $var, $keyname => $reference);
            $avar = &$var[$keyvar];

            $type = ucfirst(gettype($avar));
            if($type == "String") $type_color = "<span style='color:green'>";
            elseif($type == "Integer") $type_color = "<span style='color:red'>";
            elseif($type == "Double") { $type_color = "<span style='color:#0099c5'>"; $type = "Float"; }
            elseif($type == "Boolean") $type_color = "<span style='color:#92008d'>";
            elseif($type == "NULL") $type_color = "<span style='color:black'>";

            if(is_array($avar)) {
                $count = count($avar);
                echo "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:#a2a2a2'>$type ($count)</span><br>$indent(<br>";
                $keys = array_keys($avar);
                foreach($keys as $name) {
                    $value = &$avar[$name];
                    Utility::printHtml($value, "['$name']", $indent.$print_html_indent, $reference);
                }
                echo "$indent)<br>";
            }
            elseif(is_object($avar)) {
                echo "$indent$var_name <span style='color:#a2a2a2'>$type</span><br>$indent(<br>";
                foreach($avar as $name=>$value) Utility::printHtml($value, "$name", $indent.$print_html_indent, $reference);
                echo "$indent)<br>";
            }
            elseif(is_int($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color$avar</span><br>";
            elseif(is_string($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color\"$avar\"</span><br>";
            elseif(is_float($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color$avar</span><br>";
            elseif(is_bool($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $type_color".($avar == 1 ? "TRUE":"FALSE")."</span><br>";
            elseif(is_null($avar)) echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> {$type_color}NULL</span><br>";
            else echo "$indent$var_name = <span style='color:#a2a2a2'>$type(".strlen($avar).")</span> $avar<br>";

            $var = $var[$keyvar];
        }
    }

    public static function mergeObjects($objectA, $objectB) {
        $objectC = new stdclass();
        if(!empty($objectA) && $objectA != "null") {
            foreach($objectA as $key => $value) {
                $objectC->$key = $value;
            }
        }

        if(!empty($objectB) && $objectB != "null") {
            foreach($objectB as $key => $value) {
                $objectC->$key = $value;
            }
        }

        return $objectC;
    }

    /**
     * Get a (recursive) directory listing in an array.
     *
     * @param <type> $directory
     * @param <type> $recursive
     * @return <type>
     */
    public static function directoryToArray($directory, $baseDirectory = false) {
        $arrayItems = array();
        if($handle = opendir($directory)) {
            while(false !== ($file = readdir($handle))) {
                if($file != "." && $file != "..") {
                    if(is_dir($directory. "/" . $file)) {
                        $arrayItems = array_merge($arrayItems, Utility::directoryToArray($directory. "/" . $file, $baseDirectory));
                    }
                    else {
                        $file = $directory . "/" . $file;
                        if($baseDirectory) {
                            $file = str_replace($baseDirectory, "/", $file);
                        }
                        $arrayItems[] = preg_replace("/\/\//si", "/", $file);
                    }
                }
            }
            closedir($handle);
        }

        return $arrayItems;
    }

    public static function stringToTitle($string) {
        $smallwordsarray = array('of','a','the','and','an','or','nor','but','is','if','then','else','when', 'at','from','by','on','off','for','in','out','over','to','into','with');
        $words = explode(' ', $string);
        foreach($words as $key => $word) {
            if($key == 0 or !in_array($word, $smallwordsarray)) $words[$key] = ucwords($word);
        }
        $newtitle = implode(' ', $words);
        return $newtitle;
    }

    public static function addHtmlLinksToString($string) {
        $pattern = "!(http://|https://|ftp://|mailto:|smb://|afp://|file://|gopher://|news://|ssl://|sslv2://|sslv3://|tls://|tcp://|udp://)*([a-zA-Z0-9@:%_+*~#?&=.,/;-]*\.\w\w+/?[a-zA-Z0-9@:%_+*?~#&=/;-]*)!i";
        $string = preg_replace_callback($pattern, 'addHtmlLinksToStringMatcher', $string);
        return $string;
    }

    public static function addHtmlLinksToStringMatcher($matches) {
        if(empty($matches[1])) {
            $link = "http://".$matches[0];
        }
        else {
            $link = $matches[1].$matches[2];
        }

        return "<a href=\"".$link."\" target=\"_blank\">".$matches[0]."</a>";
    }

    public static function readFile($location, $filename, $mimeType='application/octet-stream') {
        if(!file_exists($location)) { header ("HTTP/1.0 404 Not Found");
            return;
        }

        $size=filesize($location);
        $time=date('r',filemtime($location));

        $fm=@fopen($location,'rb');
        if(!$fm) { header ("HTTP/1.0 505 Internal server error");
            return;
        }

        $begin=0;
        $end=$size;

        if(isset($_SERVER['HTTP_RANGE'])) { if(preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) { $begin=intval($matches[0]);
                if(!empty($matches[1]))
                    $end=intval($matches[1]);
            }
        }

        if($begin>0||$end<$size)
            header('HTTP/1.0 206 Partial Content');
        else
            header('HTTP/1.0 200 OK');

        header("Content-Type: $mimeType");
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Accept-Ranges: bytes');
        header('Content-Length:'.($end-$begin));
        header("Content-Range: bytes $begin-$end/$size");
        header("Content-Disposition: inline; filename=$filename");
        header("Content-Transfer-Encoding: binary\n");
        header("Last-Modified: $time");
        header('Connection: close');

        $cur=$begin;
        fseek($fm,$begin,0);

        while(!feof($fm)&&$cur<$end&&(connection_status()==0)) { print fread($fm,min(1024*16,$end-$cur));
            $cur+=1024*16;
        }
    }

    // Convert a string to a 32-bit integer
    public static function stringToNumber($string, $check, $Magic) {
        $int32Unit = 4294967296;  // 2^32
        $length = strlen($string);
        for($i = 0; $i < $length; $i++) {
            $check *= $Magic;
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

    public static function getRandomString($length = 32) {
        $characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $randomString = '';
        $maxValue = strlen($characters)-1;
        for($i=0; $i < $length; $i++) {
            $randomString .= substr($characters, rand(0, $maxValue), 1);
        }
        return $randomString;
    }

    // Genearate a hash for a URL
    public static function hashURL($string) {
        $check1 = Utility::stringToNumber($string, 0x1505, 0x21);
        $check2 = Utility::stringToNumber($string, 0, 0x1003F);

        $check1 >>= 2;
        $check1 = (($check1 >> 4) & 0x3FFFFC0 ) | ($check1 & 0x3F);
        $check1 = (($check1 >> 4) & 0x3FFC00 ) | ($check1 & 0x3FF);
        $check1 = (($check1 >> 4) & 0x3C000 ) | ($check1 & 0x3FFF);

        $t1 = (((($check1 & 0x3C0) << 4) | ($check1 & 0x3C)) <<2 ) | ($check2 & 0xF0F );
        $t2 = (((($check1 & 0xFFFFC000) << 4) | ($check1 & 0x3C00)) << 0xA) | ($check2 & 0xF0F0000 );

        return ($t1 | $t2);
    }

    // Genearate a checksum for a hash string
    public static function checkHash($hash) {
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

    public static function getPagination($startItemOffset, $itemsPerPage, $totalItemCount, $url, $className = 'pagination', $pageNumbers = false) {
        $ul = new HtmlElement('ul', array('class' => $className));

        $currentPageNumber = ceil(($startItemOffset) / $itemsPerPage);
        //echo 'start item'.$startItemOffset;
        //echo 'items per page'.$itemsPerPage;
        //echo 'current page'.$currentPageNumber;
        $nextPageNumber = $currentPageNumber + 1;
        $currentPageFirstItemOffset = ($currentPageNumber * $itemsPerPage) - $itemsPerPage + 1;

        $totalPageCount = ceil($totalItemCount / $itemsPerPage);

        //echo $totalItemCount;
        //echo $itemsPerPage;
        //echo $totalPageCount;

        $lastPageNumber = $totalPageCount;
        $lastPageFirstItemOffset = $totalItemCount - ($totalItemCount % $itemsPerPage) + 1;

        // Previous button
        if($currentPageNumber > 1) {
            $previousPageFirstItemOffset = $currentPageFirstItemOffset - $itemsPerPage;
            if($pageNumbers) {
                $ul->insert('<li class="previousPage"><a href="'.(str_replace('[offset]', $currentPageNumber - 1, $url)).'">Prev</a></li>');
            }
            else {
                $ul->insert('<li class="previousPage"><a href="'.(str_replace('[offset]', $previousPageFirstItemOffset, $url)).'">Prev</a></li>');
            }
            
        }

        // Less than the fifth page
        if($currentPageNumber < 10) {
            for($tempPageNumber = 1; $tempPageNumber <= $totalPageCount && $tempPageNumber <= 10; $tempPageNumber++) {
                if($tempPageNumber == $currentPageNumber) {
                    $class = ' active';
                }
                else {
                    $class = '';
                }
                $tempPageFirstItemOffset = ($tempPageNumber * $itemsPerPage) - $itemsPerPage + 1;
                if($pageNumbers) {
                    $ul->insert('<li class="pageNumber'.$class.'"><a href="'.(str_replace('[offset]', $tempPageNumber, $url)).'">'.$tempPageNumber.'</a></li>');
                }
                else {
                    $ul->insert('<li class="pageNumber'.$class.'"><a href="'.(str_replace('[offset]', $tempPageFirstItemOffset, $url)).'">'.$tempPageNumber.'</a></li>');
                }
            }

            if($lastPageNumber > 10) {
                $ul->insert('<li class="pageSeparator">...</li>');
                if($pageNumbers) {
                    $ul->insert('<li class="pageNumber"><a href="'.(str_replace('[offset]', $lastPageNumber, $url)).'">'.$lastPageNumber.'</a></li>');
                }
                else {
                    $ul->insert('<li class="pageNumber"><a href="'.(str_replace('[offset]', $lastPageFirstItemOffset, $url)).'">'.$lastPageNumber.'</a></li>');
                }
            }
        }
        // Inbetween the fifth and last five pages
        else if($currentPageNumber >= 10 && $currentPageNumber <= $totalPageCount - 10) {
            $ul->insert('<li class="pageNumber"><a href="'.(str_replace('[offset]', '1', $url)).'">1</a></li>');
            $ul->insert('<li class="pageSeparator">...</li>');

            $tempPageLowerLimit = $currentPageNumber - 3;
            $tempPageUpperLimit = $currentPageNumber + 3;
            for($tempPageNumber = $tempPageLowerLimit; $tempPageNumber <= $tempPageUpperLimit; $tempPageNumber++) {
                if($tempPageNumber == $currentPageNumber) {
                    $class = ' active';
                }
                else {
                    $class = '';
                }
                $tempPageFirstItemOffset = ($tempPageNumber * $itemsPerPage) - $itemsPerPage + 1;
                if($pageNumbers) {
                    $ul->insert('<li class="pageNumber'.$class.'"><a href="'.(str_replace('[offset]', $tempPageNumber, $url)).'">'.$tempPageNumber.'</a></li>');
                }
                else {
                    $ul->insert('<li class="pageNumber'.$class.'"><a href="'.(str_replace('[offset]', $tempPageFirstItemOffset, $url)).'">'.$tempPageNumber.'</a></li>');
                }
                
            }

            $ul->insert('<li class="pageSeparator">...</li>');

            if($pageNumbers) {
                $ul->insert('<li class="pageNumber"><a href="'.(str_replace('[offset]', $lastPageNumber, $url)).'">'.$lastPageNumber.'</a></li>');
            }
            else {
                $ul->insert('<li class="pageNumber"><a href="'.(str_replace('[offset]', $lastPageFirstItemOffset, $url)).'">'.$lastPageNumber.'</a></li>');
            }
        }
        // Within the last five pages
        else if($currentPageNumber > $totalPageCount - 10) {
            if($currentPageNumber > 10) {
                $ul->insert('<li class="pageNumber"><a href="'.(str_replace('[offset]', '1', $url)).'">1</a></li>');
                $ul->insert('<li class="pageSeparator">...</li>');
            }

            for($tempPageNumber = $totalPageCount - 9; $tempPageNumber <= $totalPageCount; $tempPageNumber++) {
                if($tempPageNumber == $currentPageNumber) {
                    $class = ' active';
                }
                else {
                    $class = '';
                }
                $tempPageFirstItemOffset = ($tempPageNumber * $itemsPerPage) - $itemsPerPage + 1;

                if($pageNumbers) {
                    $ul->insert('<li class="pageNumber'.$class.'"><a href="'.(str_replace('[offset]', $tempPageNumber, $url)).'">'.$tempPageNumber.'</a></li>');
                }
                else {
                    $ul->insert('<li class="pageNumber'.$class.'"><a href="'.(str_replace('[offset]', $tempPageFirstItemOffset, $url)).'">'.$tempPageNumber.'</a></li>');
                }
            }
        }

        // Next button
        if($currentPageNumber < $totalPageCount) {
            $nextPageFirstItemOffset = $currentPageFirstItemOffset + $itemsPerPage;
            if($pageNumbers) {
                $ul->insert('<li class="nextPage"><a href="'.(str_replace('[offset]', $currentPageNumber + 1, $url)).'">Next</a></li>');
            }
            else {
                $ul->insert('<li class="nextPage"><a href="'.(str_replace('[offset]', $nextPageFirstItemOffset, $url)).'">Next</a></li>');
            }
        }

        return $ul;
    }
    /* Works out the time since the entry post, takes a an argument in unix time (seconds) */
    public static function getTimeSince($original) {
    // array of time period chunks
        $chunks = array(
            array(60 * 60 * 24 * 365 , 'year'),
            array(60 * 60 * 24 * 30 , 'month'),
            array(60 * 60 * 24 * 7, 'week'),
            array(60 * 60 * 24 , 'day'),
            array(60 * 60 , 'hour'),
            array(60 , 'min'),
            array(1 , 'sec'),
        );

        $today = time(); /* Current unix time  */
        $since = $today - $original;

        // $j saves performing the count function each time around the loop
        for ($i = 0, $j = count($chunks); $i < $j; $i++) {

            $seconds = $chunks[$i][0];
            $name = $chunks[$i][1];

            // finding the biggest chunk (if the chunk fits, break)
            if (($count = floor($since / $seconds)) != 0) {
            // DEBUG print "<!-- It's $name -->\n";
                break;
            }
        }

        $print = ($count == 1) ? '1 '.$name : "$count {$name}s";

        if ($i + 1 < $j) {
        // now getting the second item
            $seconds2 = $chunks[$i + 1][0];
            $name2 = $chunks[$i + 1][1];

            // add second item if it's greater than 0
            if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0) {
                //$print .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 {$name2}s";
            }
        }
        return $print;
    }
}

?>
