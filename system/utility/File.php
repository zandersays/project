<?php

class File {
    var $name;
    var $nameWithoutExtension;
    var $extension;
    var $directory;
    var $file;
    var $fileWithoutExtension;
    var $sizeInBytes;
    var $sizeInKilobytes;
    var $sizeInMegabytes;
    var $sizeInGigabytes;
    var $sizeInTerabytes;

    /**
     * Initialize the file object with properties read from the file.
     *
     * @param string $file
     */
    function __construct($file) {
        if(!is_file($file)) {
            throw new Exception("\"".$file."\" is not a file.");
        }
        $pathInfo = pathinfo(realpath($file));
        $this->name = $pathInfo['basename'];
        $this->nameWithoutExtension = $pathInfo['filename'];
        $this->extension = isset($pathInfo['extension']) ? $pathInfo['extension']: '';
        $this->directory = str_replace("\\", "/", $pathInfo['dirname']);
        $this->file = $this->directory."/".$this->name;
        $this->fileWithoutExtension = $this->directory."/".$this->nameWithoutExtension;
        $this->sizeInBytes = filesize($this->file);
        $this->sizeInKilobytes = $this->sizeInBytes / 1024;
        $this->sizeInMegabytes = $this->sizeInKilobytes / 1024;
        $this->sizeInGigabytes = $this->sizeInMegabytes / 1024;
        $this->sizeInTerabytes = $this->sizeInGigabytes / 1024;
    }

    /**
     * Returns the full path to the file.
     *
     * @return string
     */
    function __toString() {
        return $this->file;
    }

    /**
     * Rename a file.
     *
     * @param string $newName
     * @param string $newDirectory
     * @return File
     */
    public function rename($newName, $newDirectory = '') {
        if(!empty($newDirectory)) {
            $newFile = $newDirectory.'/'.$newName;
        }
        else {
            $newFile = $this->directory.'/'.$newName;
        }
        // Delete any file that already exists with the new name
        if(file_exists($newFile)) {
            chmod($newFile, 0777);
            unlink($newFile);
        }
        if(rename($this->file, $newFile)) {
            $class = get_class($this);
            $file = new $class($newFile);
            return $file;
        }
        else {
            return false;
        }
    }

    /**
     * Rename a file with a GUID concatenated to the end of the file name.
     *
     * @return Image
     */
    public function guidRename($directory = "") {
        $guid = md5(uniqid(rand(), true));

        if(!empty($directory)) {
            $newFile = $directory."/".$this->nameWithoutExtension."-".$guid.".".$this->extension;
        }
        else {
            $newFile = $this->fileWithoutExtension."-".$guid.".".$this->extension;
        }

        // Delete any file that already exists with the new name
        if(file_exists($newFile)) {
            chmod($newFile, 0777);
            unlink($newFile);
        }
        if(rename($this->file, $newFile)) {
            $this->__construct($newFile);
            return $this;
        }
        else {
            return false;
        }
    }

    /**
     * Statically rename a file.
     *
     * @param string $originalFile
     * @param string $newFile
     * @return File
     */
    public static function staticRename($originalFile, $newFile) {
        if(rename($originalFile, $newFile)) {
            return new File($newFile);
        }
        else {
            return false;
        }
    }

    /**
     * Copy a file.
     *
     * @param string $newName
     * @param string $newDirectory
     * @return File
     */
    public function copy($newName, $newDirectory = "") {
        if(!empty($newDirectory)) {
            $newFile = $newDirectory."/".$newName;
        }
        else {
            $newFile = $this->directory."/".$newName;
        }
        if(copy($this->file, $newFile)) {
            $class = get_class($this);
            $newFile = new $class($newFile);

            return $newFile;
        }
        else {
            return false;
        }
    }

    static function mimeType($path) {
        $extension = String::lower(String::sub($path, String::lastIndexOf('.', $path) + 1));
        
        switch($extension) {
            case 'js' :
                return 'application/x-javascript';
            case 'json' :
                return 'application/json';
            case 'jpg' :
            case 'jpeg' :
            case 'jpe' :
                return 'image/jpg';
            case 'png' :
            case 'gif' :
            case 'bmp' :
            case 'tiff' :
                return 'image/'.$extension;
            case 'css' :
                return 'text/css';
            case 'xml' :
                return 'application/xml';
            case 'doc' :
            case 'docx' :
                return 'application/msword';
            case 'xls' :
            case 'xlt' :
            case 'xlm' :
            case 'xld' :
            case 'xla' :
            case 'xlc' :
            case 'xlw' :
            case 'xll' :
                return 'application/vnd.ms-excel';
            case 'ppt' :
            case 'pps' :
                return 'application/vnd.ms-powerpoint';
            case 'rtf' :
                return 'application/rtf';
            case 'pdf' :
                return 'application/pdf';
            case 'html' :
            case 'htm' :
            case 'php' :
                return 'text/html';
            case 'txt' :
                return 'text/plain';
            case 'mpeg' :
            case 'mpg' :
            case 'mpe' :
                return 'video/mpeg';
            case 'mp3' :
                return 'audio/mpeg3';
            case 'wav' :
                return 'audio/wav';
            case 'aiff' :
            case 'aif' :
                return 'audio/aiff';
            case 'avi' :
                return 'video/msvideo';
            case 'wmv' :
                return 'video/x-ms-wmv';
            case 'mov' :
                return 'video/quicktime';
            case 'zip' :
                return 'application/zip';
            case 'tar' :
                return 'application/x-tar';
            case 'swf' :
                return 'application/x-shockwave-flash';
            default :
                if(function_exists('mime_content_type')) {
                    return mime_content_type($path);
                }
                return 'unknown/' . trim($extension, '.');
        }
    }

    static function output($path) {
        // Check if the file exists
        if(!File::exists($path)) {
            header('HTTP/1.0 404 Not Found');
            exit();
        }

        // Set the content-type header
        header('Content-Type: '.File::mimeType($path));

        // Handle caching
        $fileModificationTime = gmdate('D, d M Y H:i:s', File::modificationTime($path)).' GMT';

        $headers = Project::getHeaders();

        if(isset($headers['If-Modified-Since']) && $headers['If-Modified-Since'] == $fileModificationTime) {
            header('HTTP/1.1 304 Not Modified');
            exit();
        }
        header('Last-Modified: '.$fileModificationTime);

        // Read the file
        readfile($path);

        exit();
    }

    static function modificationTime($path) {
        return filemtime($path);
    }
    
    public function base64Encode() {
        return base64_encode(File::content($this->file));
    }
    
    public function encode($options = array()) {
        return array(
            'name' => isset($options['name']) ? $options['name'] : $this->name,
            'nameWithoutExtension' => isset($options['name']) ? String::sub($options['name'], 0, String::lastIndexOf('.', $options['name'])) : $this->nameWithoutExtension,
            'extension' => isset($options['name']) ? String::sub($options['name'], String::lastIndexOf('.', $options['name']) + 1) : $this->extension,
            'sizeInKilobytes' => $this->sizeInKilobytes,
            'mimeType' => File::mimeType($this->file),
            'content' => base64_encode(File::content($this->file)),
        );
    }
    
    public function decode() {
        
    }
    
    public function getContent() {
        return file_get_contents($this->file);
    }

    static function content($path) {
        //print_r(debug_backtrace());
        return file_get_contents($path);
    }

    /*
     static function read($location, $fileName, $mimeType='application/octet-stream') {
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
        header("Content-Disposition: inline; filename=$fileName");
        header("Content-Transfer-Encoding: binary\n");
        header("Last-Modified: $time");
        header('Connection: close');

        $cur=$begin;
        fseek($fm,$begin,0);

        while(!feof($fm)&&$cur<$end&&(connection_status()==0)) { print fread($fm,min(1024*16,$end-$cur));
            $cur+=1024*16;
        }
    }
     *
     */
    
    static function create($fileName) {
        if(self::exists($fileName)) {
            return true;
        }

        $file = fopen($fileName, 'w');
        if($file) {
            return true;
        }
        else {
            return false;
        }
    }

    static function write($fileName, $string, $append = false) {
        // Check to see if the file is not writable
        if(!is_writable($fileName)) {
            return false;
        }
        else {
            //
            if($append) {
                $fileResource = fopen($fileName, 'a');
            }
            else {
                $fileResource = fopen($fileName, 'w');
            }

            fwrite($fileResource, $string);

            return true;
        }
    }

    static function delete($fileName) {
        return unlink($fileName);
    }

    static function chmod($fileName, $mode) {
        return chmod($fileName, $mode);
    }

    static function exists($fileName, $caseSensitive = true) {
        // Handle case insensitive requests
        if(!$caseSensitive) {
            if(file_exists($fileName)) {
                return true;
            }
            $directoryName = dirname($fileName);
            $fileArray = glob($directoryName . '/*');
            $fileNameLowerCase = strtolower($fileName);
            foreach($fileArray as $file) {
                if(strtolower($file) == $fileNameLowerCase) {
                    return $file;
                }
            }
            return false;
        }
        else {
            return file_exists($fileName);
        }
    }
    
    static function size($fileName) {
        return filesize($fileName);
    }

}
?>