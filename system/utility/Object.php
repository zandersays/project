<?php
class Object {

    // Turn an object into an associative array
    public static function arr($object) {
        $array = array();
        // The !Arr::is check allows you to pass an array of objects and get them converted
        if(!Object::is($object) && !Arr::is($object)) {
            return false;
        }

        foreach($object as $key => $value) {
            // Handle objects
            if(Object::is($value)) {
                $value = Object::arr($value);
            }
            // Handle arrays of objects
            else if(Arr::is($value)) {
                foreach($value as $arrayIndex => $arrayValue) {
                    if(Object::is($arrayValue)) {
                        $value[$arrayIndex] = Object::arr($arrayValue);
                    }
                }
            }

            $array[$key] = $value;
        }
        return $array;
    }

    public static function className($object) {
        return get_class($object);
    }

    public static function findKeyValue($key, $object) {
        //echo 'looking for '.$key.'<br />';
        foreach($object as $currentKey => $currentValue) {
            //echo $currentKey.' vs. '.$key.'<br />';
            if($currentKey === $key) {
                //echo 'FOUND '.$key.'<br />';
                return $currentValue;
            }
            else if(Arr::is($currentValue) || Object::is($currentValue)) {
                $response = self::findKeyValue($key, $currentValue);
                if($response != null) {
                    return $response;
                }
            }
        }

        return null;
    }

    // This code is having issues
    public static function equals($objectA, $objectB) {
        if(is_object($objectA) && is_object($objectB)) {
            if(get_class($objectA) != get_class($objectB))
                return false;
            foreach($objectA as $key => $val) {
                if(!self::equals($val, $objectB->$key))
                    return false;
            }
            return true;
        }
        else if(is_array($objectA) && is_array($objectB)) {
            while(!is_null(key($objectA) && !is_null(key($objectB)))) {
                if(key($objectA) !== key($objectB) || !self::equals(current($objectA), current($objectB)))
                    return false;
                next($objectA);
                next($objectB);
            }
            return true;
        }
        else {
            return $objectA === $objectB;
        }
    }

    public static function propertyExists($propertyName, $className) {
        return property_exists($className, $propertyName);
    }

    public static function methodExists($methodName, $object) {
        return method_exists($object, $methodName);
    }

    public static function is($variable) {
        return is_object($variable);
    }

    public static function json($object) {
        return Json::encode($object);
    }

    /*static function sortByKey(&$data, $key) {
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

    static function array($object) {
        $array = array();
        foreach($object as $key => $value) {
            if(is_object($value)) {
                $value = objectToArray($value);
            }
            $array[$key] = $value;
        }
        return $array;
    }

    static function merge($objectA, $objectB) {
        $objectC = new stdClass();
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
    }*/

    /**
     * Converts a JSON encoded string into a PHP object.
     *
     * @param string $json The JSON string you want to create an object from.
     * @param boolean $asArray Whether or not you actually want the output as an array instead of an object.
     * @return mixed
     */
    public static function fromJson($json, $asArray = false) {
        $jsonArray = json_decode($json, true);

        // is there typing information?
        $objectToReturn = new stdClass();
        if($jsonArray != null) {
            if(array_key_exists('___php__type___', $jsonArray)) {
                if(!$asArray) {
                    $objectToReturn = self::deserializeArrayToTypedObjects($jsonArray);
                }
                else {
                    $objectToReturn = $jsonArray;
                }
            }
            else {
                if(!$asArray) {
                    $objectToReturn = json_decode($json);
                }
                else {
                    $objectToReturn = $jsonArray;
                }
            }
        }
        return $objectToReturn;
    }

    private static function deserializeArrayToTypedObjects(Array $typedArray) {
        $objectToReturn;

        // get the type and get it out of our way
        $type = $typedArray['___php__type___'];
        unset($typedArray['___php__type___']);

        // instance the correct type
        if($type == 'array' || $type == 'associative_array') {
            $objectToReturn = array();
        }
        else {
            $objectToReturn = new $type();
        }

        // set the values for this type
        foreach($typedArray as $key => $value) {
            // if the value is another object go down and handle it first before setting the value
            if(is_array($value)) {
                $value = self::deserializeArrayToTypedObjects($value);
            }

            if($type == 'array') {
                $objectToReturn[] = $value;
            }
            else if($type == 'associative_array') {
                $objectToReturn[$key] = $value;
            }
            else {
                if($value != null) {
                    // figure out what the setter function is called
                    $setter = 'set'.String::upperFirstCharacter($key);

                    // get the reflected class for this object
                    $reflectionClass = new ReflectionClass($objectToReturn);

                    // does the setter actually exist?  if is does set the value
                    if($reflectionClass->hasMethod($setter)) {
                        $reflectionMethod = new ReflectionMethod($objectToReturn, $setter);
                        $reflectionMethod->invoke($objectToReturn, $value);
                    }
                }
            }
        }

        return $objectToReturn;
    }

    /**
     * Converts an object to a JSON string.
     *
     * @param Object $object The object you want to convert.
     * @param boolean $isRecursive Whether or not you want to apply this operation recursivly.
     * @param boolean $preserveTyping Whether or not you want this function to add typeing information flags to the array.
     * @return string
     */
    public static function toJson($object, $isRecursive = false, $preserveTyping = false) {
        return json_encode(self::toArray($object, $isRecursive, $preserveTyping));
    }

    /**
     * Convert a user-defined, or stdClass object into an array.
     *
     * @param Object $object The object to convert.
     * @param boolean $isRecursive Whether or not you want to apply this operation recursivly.
     * @param boolean $preserveTyping Whether or not you want this function to add typeing information flags to the array.
     * @return array
     */
    public static function toArray($object, $isRecursive = false, $preserveTyping = false) {
        $array = array();

        if(is_array($object) || $object instanceof stdClass) {
            if($preserveTyping) {
                if(Arr::isAssociative($object)) {
                    $array['___php__type___'] = 'associative_array';
                }
                else {
                    $array['___php__type___'] = 'array';
                }
            }

            foreach($object as $key => $value) {
                if(!is_object($value) && !is_array($value)) {
                    // if the value happens to be json, decode it and turn it into an array as well
                    if(Json::is($value)) {
                        $value = Object::arr(Json::decode($value));
                    }
                    $array[$key] = $value;
                }
                else if($isRecursive) {
                    $array[$key] = self::toArray($value, true, $preserveTyping);
                }
                else {
                    $array[$key] = null;
                }
            }
        }
        else if(is_object($object)) {
            if($preserveTyping) {
                $array['___php__type___'] = get_class($object);
            }

            $reflectionClass = new ReflectionClass($object);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach($methods as $method) {
                if(String::startsWith('get', $method->getName())) {
                    // create the method object
                    $reflectedMethod = new ReflectionMethod($object, $method->getName());

                    // invoke non-static getters that require no arguments
                    if(!$reflectedMethod->isStatic() && $reflectedMethod->getNumberOfParameters() == 0) {
                        // figure out what the field name should be based on convention
                        $fieldName = String::lowerFirstCharacter((String::sub($method->getName(), 3, String::length($method->getName()))));
                        $fieldValue = $reflectedMethod->invoke($object);

                        if(!is_object($fieldValue) && !is_array($fieldValue)) {
                            // if the value happens to be json, decode it and turn it into an array as well
                            if(Json::is($fieldValue)) {
                                $fieldValue = Object::arr(Json::decode($fieldValue));
                            }
                            $array[$fieldName] = $fieldValue;
                        }
                        else if($isRecursive) {
                            $array[$fieldName] = self::toArray($fieldValue, true, $preserveTyping);
                        }
                        else {
                            $array[$fieldName] = null;
                        }
                    }
                }
            }
        }
        else {
            // if we don't know what this is, add it to an array and return the result
            $array[] = $object;
        }

        return $array;
    }
}

?>