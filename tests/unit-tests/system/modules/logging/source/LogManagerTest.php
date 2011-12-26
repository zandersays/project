<?php

/**
 * Description of LogManagerTest
 *
 * @author Kam Sheffield
 * @version 08/30/2011
 */
class LogManagerTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var DatabaseDriver
     */
    protected $databaseDriver;

    protected function setUp() {
        // mock settings for the logging module
        $settings = array(
            'modules' => array(
                'Logging' => array(
                    'rollingLog' => array(
                        'isBuffering' => false,
                        'fileName' => 'project.log',
                        'fileLocation' => getcwd().'/temp/',
                        'maxFileSize' => 2,
                        'maxFileCount' => 4
                    ),
                    'databaseLog' => array(
                        'isBuffering' => false,
                        'databaseName' => 'project_unit_test',
                        'logTableName' => 'project_log'
                    ),
                    'echoMessages' => false,
                    'lineEnding' => "\n"
                )
            )
        );

        Project::setSettings($settings);

        $this->databaseDriver = new DatabaseDriverMySql('project_unit_test', 'localhost', 'test', 'password');
        Database::addDatabaseDriver($this->databaseDriver);
    }

    protected function tearDown() {
        $this->databaseDriver->closeConnection();
        Database::removeDatabaseDriver($this->databaseDriver->getId());
    }

    public function testWriteToLogsLogFileOnly() {
        // setup
        $logManager = LogManager::getInstance();
        $message = 'this is a test log message';
        $className = 'LogManagerTest';
        $tag = 'project_unit_test';
        $logToFile = true;
        $logToDatabase = false;
        $logLevel = LogLevel::Information;

        // prepare assertions database
        $beforeDatabaseCount = Database::query('SELECT COUNT(*) FROM project_log');
        $beforeDatabaseCount = $beforeDatabaseCount[0]['COUNT(*)'];

        // prepare assertions log file
        $logFile = Project::getProjectPath().'tests/temp/project.log';
        $beforeLogFileExists = File::exists($logFile);
        $beforeLogFileSize = 0;
        if($beforeLogFileExists) {
            $beforeLogFileSize = File::size($logFile);
        }

        // test
        $result = $logManager->writeToLogs($message, $className, $logLevel, $tag, $logToFile, $logToDatabase);
        $this->assertTrue($result);

        $afterDatabaseCount = Database::query('SELECT COUNT(*) FROM project_log');
        $afterDatabaseCount = $afterDatabaseCount[0]['COUNT(*)'];

        $this->assertEquals($beforeDatabaseCount, $afterDatabaseCount);

        $afterLogFileExists = File::exists($logFile);
        $this->assertTrue($afterLogFileExists);

        $afterLogFileSize = File::size($logFile);
        $this->assertTrue($afterLogFileSize > $beforeLogFileSize);
    }

    public function testWriteToLogsLogDatabaseOnly() {
        // setup
        $logManager = LogManager::getInstance();
        $message = 'this is a test log message';
        $className = 'LogManagerTest';
        $tag = 'project_unit_test';
        $logToFile = false;
        $logToDatabase = true;
        $logLevel = LogLevel::Information;

        // prepare assertions database
        $beforeDatabaseCount = Database::query('SELECT COUNT(*) FROM project_log');
        $beforeDatabaseCount = $beforeDatabaseCount[0]['COUNT(*)'];

        // prepare assertions log file
        $logFile = Project::getProjectPath().'tests/temp/project.log';
        $beforeLogFileExists = File::exists($logFile);
        $beforeLogFileSize = 0;
        if($beforeLogFileExists) {
            $beforeLogFileSize = File::size($logFile);
        }

        // test
        $result = $logManager->writeToLogs($message, $className, $logLevel, $tag, $logToFile, $logToDatabase);
        $this->assertTrue($result);

        $afterDatabaseCount = Database::query('SELECT COUNT(*) FROM project_log');
        $afterDatabaseCount = $afterDatabaseCount[0]['COUNT(*)'];

        $this->assertEquals($beforeDatabaseCount + 1, $afterDatabaseCount);

        $afterLogFileExists = File::exists($logFile);
        $this->assertEquals($beforeLogFileExists, $afterLogFileExists);

        $afterLogFileSize = 0;
        if($afterLogFileExists) {
            $afterLogFileSize = File::size($logFile);
        }
        $this->assertEquals($beforeLogFileSize, $afterLogFileSize);
    }
}

?>
