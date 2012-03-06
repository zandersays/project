<?php
class Arr {

    static function unique($array) {
        return array_unique($array);
    }
    
    static function indexOf($value, $array) {
        return array_search($value, $array);
    }
    
    static function camelCaseKeysToUnderscores($array) {
        $newArray = array();

        foreach($array as $key => $value) {
            if(Arr::is($value)) {
                $newArray[String::camelCaseToUnderscores($key)] = Arr::camelCaseKeysToUnderscores($value);
            }
            else {
                $newArray[String::camelCaseToUnderscores($key)] = $value;
            }
        }

        return $newArray;
    }

    static function underscoreKeysToCamelCase($array) {
        $newArray = array();

        foreach($array as $key => $value) {
            if(Arr::is($value) || Object::is($value)) {
                $newArray[String::underscoresToCamelCase($key)] = Arr::underscoreKeysToCamelCase($value);
            }
            else {
                $newArray[String::underscoresToCamelCase($key)] = $value;
            }
        }

        return $newArray;
    }

    static function hasKey($key, $array) {
        return array_key_exists($key, $array);
    }

    static function difference($arrayA, $arrayB) {
        return array_diff($arrayA, $arrayB);
    }

    static function rekey($array) {
        return array_values($array);
    }

    static function filter($array) {
        return array_filter($array);
    }

    static function merge($arrayA, $arrayB) {
        return array_merge($arrayA, $arrayB);
    }
    
    static function reverse($array, $preserveKeys = null) {
        return array_reverse($array, $preserveKeys);
    }

    static function sort(&$array) {
        return sort($array);
    }

    static function sortByKey(&$array) {
        return ksort($array);
    }

    static function pop(&$array) {
        return array_pop($array);
    }

    static function unshift($item, &$array) {
        return array_unshift($array, $item);
    }

    static function shift(&$array) {
        return array_shift($array);
    }

    static function last($array) {
        if(!isset($array[Arr::size($array) - 1])) {
            return null;
        }
        else {
            return $array[Arr::size($array) - 1];   
        }
    }

    static function first($array) {
        if(isset($array[0])) {
            return $array[0];
        }
        else {
            reset($array);
            return current($array);
        }
    }

    static function out($array) {
        print_r($array);
    }

    static function contains($string, $array, $deep = false) {
        if(!Arr::is($array)) {
            throw new Exception('Not an array.');
        }

        if($deep) {
            $response = false;
            foreach($array as $key => $value) {
                if(Arr::is($value)) {
                    if($string == $key || Arr::contains($string, $value, $deep)) {
                        $response = true;
                        break;
                    }
                }
                else if($string == $value) {
                    $response = true;
                    break;
                }
            }
        }
        else {
            $response = in_array($string, $array);
        }

        return $response;
    }

    static function is($variable) {
        return is_array($variable);
    }

    static function isAssociative($array) {
        if (is_array($array)) {
            $keys = array_keys($array);
            foreach ($keys as $key) {
                if (!is_int($key)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    static function size($array) {
        return sizeof($array);
    }

    static function implode($glueString, $array) {
        return implode($glueString, $array);
    }

    static function object($array = array()) {
        if(!empty($array)) {
            $data = false;
            foreach ($array as $akey => $aval) {
                if(is_array($aval)) {
                    $aval = Arr::object($aval);
                }
                $data -> {$akey} = $aval;
            }
            return $data;
        }
        return false;
    }

    static function equals($arrayA, $arrayB) {
        return $arrayA == $arrayB;
    }

    public static function printFormatted($array) {
        print("<pre>".print_r($array, true)."</pre>");
    }


    public static function php($array, $recursiveCallLevel = 1) {
        $php = '';

        foreach($array as $key => $value) {
            if(!Number::isInteger($key)) {
                $php .= String::padLeft('', $recursiveCallLevel * 4, ' ').'\''.$key.'\' => ';
                $isAssociative = true;
            }
            else {
                $isAssociative = false;
            }

            if(Arr::is($value)) {
                if(!$isAssociative) {
                    $php .= String::padLeft('', $recursiveCallLevel * 4, ' ').'array('."\n";
                }
                else {
                    $php .= 'array('."\n";
                }
                $php .= Arr::php($value, $recursiveCallLevel + 1);
                $php .= String::padLeft('', $recursiveCallLevel * 4, ' ').'),'."\n";

            }
            // Handle booleans
            else if($value === true) {
                $php .= 'true,'."\n";
            }
            else if($value === false) {
                $php .= 'false,'."\n";
            }
            // Handle integers
            else if(is_numeric($value)) {
                $php .= $value.','."\n";
            }
            else {
                $php .= '\''.String::addSlashes($value).'\','."\n";
            }
        }

        if($recursiveCallLevel === 1) {
            $php = 'array('."\n".$php.');';
        }

        return $php;
    }

}

?>