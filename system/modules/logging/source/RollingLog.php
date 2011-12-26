<?php

/**
 * Description of RollingLog
 *
 * @author Kam Sheffield
 * @version 08/30/2011
 */
class RollingLog {

    /**
     * The internal buffer used to hold
     * all log messages until the user
     * commits them to the log.
     *
     * @var array
     */
    protected $buffer;

    /**
     *
     * @var boolean
     */
    protected $isBuffering;

    /**
     * The name of the log file used
     * by the LoggerFile.  This is the
     * full path, including file name
     * to the log file.
     *
     * @var string
     */
    protected $fileName;

    /**
     * The directory to store the
     * log files in.
     *
     * @var string
     */
    protected $logFileDirectory;

    /**
     *
     * @var string
     */
    protected $fullPath;

    /**
     * The maximum size (in kB) the log file
     * should get before it is rolled.
     *
     * @var int
     */
    protected $maxFileSize;

    /**
     * The maximum number of log files
     * to roll.
     *
     * @var int
     */
    protected $maxFileCount;

    /**
     * Instantiates a new instance of the RollingLog.
     *
     * @param $isBuffereing Whether or not to buffer the output to the log file
     * @param string $fileName The base log file name
     * @param string $logFileDirectory The directory in which to place log files
     * @param int $maxFileSize The maximum size you want a log file to get in MB
     * @param int $maxFileCount The maximum number of files you want before files get overwritten
     */
    public function __construct($isBuffering, $fileName, $logFileDirectory, $maxFileSize, $maxFileCount) {
        $this->buffer = array();

        if(is_bool($isBuffering)) {
            $this->isBuffering = $isBuffering;
        }
        else {
            $this->isBuffering = false;
        }

        if(!String::isNullOrEmpty($fileName) && !String::isNullOrEmpty($logFileDirectory)) {
            $this->fileName = $fileName;
            $this->logFileDirectory = $logFileDirectory;
            if(String::lastCharacter($this->logFileDirectory) != '/') {
                $this->logFileDirectory .= '/';
            }
            $this->fullPath = $this->logFileDirectory.$this->fileName;
        }
        else {
            $this->logFileDirectory = Project::getInstancePath();
            if(String::lastCharacter($this->logFileDirectory) != '/') {
                $this->logFileDirectory .= '/';
            }
            $this->fileName = Project::getSiteTitle().'.log';
        }

        if(is_int($maxFileSize) && $maxFileSize > 0) {
            $this->maxFileSize = $maxFileSize * 1000000;
        }
        else {
            $this->maxFileSize = 1000000;
        }

        if(is_int($maxFileCount) && $maxFileCount > 0) {
            $this->maxFileCount = $maxFileCount;
        }
        else {
            $this->maxFileCount = 2;
        }
    }

    /**
     *
     * @param string $message
     * @return boolean
     */
    public function write($message) {
        $this->buffer[] = $message;
        if(!$this->isBuffering) {
            return $this->commit();
        }
        return true;
    }

    /**
     * Writes all data contained in the buffer to
     * the log file.
     */
    public function commit() {
        $fileWrittenToSuccessfully = false;

        // check to see if the logging directory exists and create if needed
        if(!Dir::exists($this->logFileDirectory)) {
            $fileWrittenToSuccessfully = Dir::create($this->logFileDirectory);
        }

        // check to see if the log files exists and create if needed
        if(!File::exists($this->fullPath)) {
            $fileWrittenToSuccessfully = File::create($this->fullPath);
        }

        // write out the data to the file
        foreach($this->buffer as $message) {
            $fileWrittenToSuccessfully = File::write($this->fullPath, $message."\n", true);
        }

        // check to see if we need to roll the log and roll it if needed
        if(File::size($this->fullPath) > $this->maxFileSize) {
            // get a list of all files in this folder, do not recurse
            $directoryContents = Dir::read($this->logFileDirectory, false);

            // delete any files that are greater or at the file limit
            $maxDigit = 0;
            foreach($directoryContents as $logFile) {
                // skip the current log file
                if($logFile == $this->fullPath) {
                    continue;
                }

                // pull out the appended number on the file
                $fileNameLength = String::length($logFile) - 1;
                $endingDigit = String::sub($logFile, $fileNameLength, 1);

                // delete the file if the digit is >= maxfiles
                if($endingDigit >= $this->maxFileCount) {
                    // delete the file
                    File::delete($logFile);

                    // remove the file from the array
                    unset($directoryContents[$logFile]);
                }
            }

            // roll the log files if required
            $size = count($directoryContents);
            for($i = 1; $i < $size; $i++) {
                $oldname = $this->fullPath . '.' . ($size - $i);
                $newname = $this->fullPath . '.' . ($size - $i + 1);
                rename($oldname, $newname);
            }

            // rename the current log file
            rename($this->fullPath, $this->fullPath.'.1');
        }

        // clear the buffer
        $this->buffer = array();

        return $fileWrittenToSuccessfully;
    }
}

?>
