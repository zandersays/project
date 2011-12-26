<?php

class Json {

    public static function is($string) {
        return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
    }

    public static function encode($value) {
        return json_encode($value);
    }

    public static function decode($json, $returnArray = false) {
        if(!String::is($json)) {
            echo 'Json::decode requires a string, received a '.gettype($json).'.';
            print_r(debug_backtrace());
        }
        
        return json_decode($json, $returnArray);
    }

    // This doesn't work
    public static function html($object) {
        if(Json::is($object)) {
            $object = self::decode($object);
        }

        $ul = HtmlElement::ul();
        foreach($object as $key => $value) {
            $li = HtmlElement::li(array());

            if(Arr::is($value) || Object::is($value)) {
                $li->append($key.self::html($value));
            }
            else {
                $li->append($key);
            }
            
            $ul->append($li);
        }

        return $ul;
    }

    public static function indent($json) {
        $result = '';
        $pos = 0;
        $strLen = strlen($json);
        $indentStr = "    ";
        $newLine = "\r\n";

        for ($i = 0; $i <= $strLen; $i++) {

            // Grab the next character in the string
            $char = substr($json, $i, 1);

            // If this character is the end of an element,
            // output a new line and indent the next line
            if ($char == '}' || $char == ']') {
                $result .= $newLine;
                $pos--;
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }

            // Add the character to the result string
            $result .= $char;

            // If the last character was the beginning of an element,
            // output a new line and indent the next line
            if ($char == ',' || $char == '{' || $char == '[') {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos++;
                }
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
        }
        return $result;
    }

}

?>