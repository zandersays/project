<?php

require_once(Project::getProjectPath().'system/libraries/spyc/spyc.php');

/**
 * Description of Yaml
 *
 * @author Kam Sheffield
 * @version 09/19/2011
 */
class Yaml {
    
    /**
     * Create an array from a YAML file.
     * 
     * @param string $file The path to the YAML file.
     * @return array An array containing the data from the YAML string.
     */
    public static function parseFromFile($file) {        
        return Spyc::YAMLLoad($file);
    }
    
    /**
     * Create an array from a YAML string.
     * 
     * @param string $string A YAML string.
     * @return array An array containing the data from the YAML string.
     */
    public static function parseFromString($string) {
        return Spyc::YAMLLoadString($string);
    }
    
    /**
     * Create a YAML string from an array.
     * 
     * @param array $array The array to serialize to YAML.
     * @param int $indent The number of spaces to indent, or false to disable indenting.
     * @param int $wordwrap The number of characters before a word wrap occurs, or false to disable word wrapping.
     * @return string The new YAML string.
     */
    public static function toYaml($array, $indent = false, $wordwrap = false) {
        return Spyc::YAMLDump($array, $indent, $wordwrap);
    }
}

?>
