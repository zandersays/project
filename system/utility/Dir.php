<?php
class Dir {

    static function read($path, $recurse = true, $baseDirectory = false) {
        $array = array();
        if(!self::exists($path)) {
            return $array;
        }
        if($handle = opendir($path)) {
            while(false !== ($file = readdir($handle))) {
                if($file != "." && $file != "..") {
                    if(is_dir($path. "/" . $file) && $recurse) {
                        $array = array_merge($array, self::read($path. "/" . $file, $recurse, $baseDirectory));
                    }
                    else {
                        $file = $path . "/" . $file;
                        if($baseDirectory) {
                            $file = str_replace($baseDirectory, "/", $file);
                        }
                        $array[] = preg_replace("/\/\//si", "/", $file);
                    }
                }
            }
            closedir($handle);
        }

        return $array;
    }

    static function create($path, $mode = null, $recursive = null) {
        if(is_dir($path)) {
            return true;
        }
        else {
            return mkdir($path, $mode, $recursive);
        }
    }

    static function copy($originalDirectory, $destination) {
        $dir = opendir($originalDirectory);
        @mkdir($destination);
        while(false !== ($file = readdir($dir))) {
            if(($file != '.') && ($file != '..')) {
                if(is_dir($originalDirectory . '/' . $file)) {
                    self::copy($originalDirectory . '/' . $file,$destination . '/' . $file);
                }
                else {
                    copy($originalDirectory . '/' . $file,$destination . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    static function copyUsingExec($originalDirectory, $destination) {
        if(!String::endsWith('/', $originalDirectory)) {
            $originalDirectory = $originalDirectory.'/';
        }
        if(!String::endsWith('/', $destination)) {
            $destination = $destination.'/';
        }
        $output = shell_exec('cp -r -a '.$originalDirectory.'/* '.$destination.' 2>&1');
        echo $output;
    }

    static function delete($path) {
        if(is_dir($path)) {
            $objects = scandir($path);
            foreach($objects as $object) {
                if($object != "." && $object != "..") {
                    if(filetype($path . "/" . $object) == "dir") {
                        self::delete($path . "/" . $object);
                    }
                    else {
                        unlink($path . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($path);
        }
    }

    static function name($path) {
        return dirname($path);
    }

    static function chmod($path, $mode = null) {
        if($mode == null) {
            return fileperms($path);
        }
        else {
            return chmod($path, $mode);
        }
    }

    static function exists($path) {
        return is_dir($path);
    }

}
?>