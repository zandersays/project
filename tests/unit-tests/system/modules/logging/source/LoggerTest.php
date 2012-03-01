<?php

/**
 * Description of LoggerTest
 *
 * @author Kam Sheffield
 * @version 08/30/2011
 */
class LoggerTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Logger
     */
    protected $object;

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

        $this->object = new Logger('LoggerTest', LogLevel::Information, true, false);
    }

    protected function tearDown() {
        $this->databaseDriver->closeConnection();
        Database::removeDatabaseDriver($this->databaseDriver->getId());
    }

    public function testErrorLevel() {
        // prepare
        $logFile = Project::getProjectPath().'tests/temp/project.log';
        $beforeLogFileExists = File::exists($logFile);
        $beforeLogFileSize = 0;
        if($beforeLogFileExists) {
            $beforeLogFileSize = File::size($logFile);
        }

        // setup
        $this->object->setLogLevel(LogLevel::Error);

        // test
        $result = $this->object->error('testMessage');
        $this->assertTrue($result);

        // verify
        $afterLogFileExists = File::exists($logFile);
        $this->assertTrue($afterLogFileExists);

        $afterLogFileSize = File::size($logFile);
        $this->assertTrue($afterLogFileSize > $beforeLogFileSize);


        // setup
        $beforeLogFileSize = File::size($logFile);

        // test
        $result = $this->object->warning('testMessage');
        $this->assertFalse($result);

        // verify
        $afterLogFileSize = File::size($logFile);
        $this->assertEquals($beforeLogFileSize, $afterLogFileSize);


        // setup
        $beforeLogFileSize = File::size($logFile);

        // test
        $result = $this->object->information('testMessage');
        $this->assertFalse($result);

        // verify
        $afterLogFileSize = File::size($logFile);
        $this->assertEquals($beforeLogFileSize, $afterLogFileSize);


        // setup
        $beforeLogFileSize = File::size($logFile);

        // test
        $result = $this->object->verbose('testMessage');
        $this->assertFalse($result);

        // verify
        $afterLogFileSize = File::size($logFile);
        $this->assertEquals($beforeLogFileSize, $afterLogFileSize);
    }

    public function testWarningLevel() {
        // prepare
        $logFile = Project::getProjectPath().'tests/temp/project.log';
        $beforeLogFileExists = File::exists($logFile);
        $beforeLogFileSize = 0;
        if($beforeLogFileExists) {
            $beforeLogFileSize = File::size($logFile);
        }

        // setup
        $this->object->setLogLevel(LogLevel::Warning);

        // test
        $result = $this->object->error('testMessage');
        $this->assertTrue($result);

        // verify
        $afterLogFileExists = File::exists($logFile);
        $this->assertTrue($afterLogFileExists);

        $afterLogFileSize = File::size($logFile);
        $this->assertTrue($afterLogFileSize > $beforeLogFileSize);


        // setup
        $beforeLogFileSize = File::size($logFile);

        // test
        $result = $this->object->warning('testMessage');
        $this->assertTrue($result);

        // verify
        $afterLogFileSize = File::size($logFile);
        $this->assertTrue($afterLogFileSize > $beforeLogFileSize);


        // setup
        $beforeLogFileSize = File::size($logFile);

        // test
        $result = $this->object->information('testMessage');
        $this->assertFalse($result);

        // verify
        $afterLogFileSize = File::size($logFile);
        $this->assertEquals($beforeLogFileSize, $afterLogFileSize);


        // setup
        $beforeLogFileSize = File::size($logFile);

        // test
        $result = $this->object->verbose('testMessage');
        $this->assertFalse($result);

        // verify
        $afterLogFileSize = File::size($logFile);
        $this->assertEquals($beforeLogFileSize, $afterLogFileSize);
    }

    public function testInformationLevel() {
        // prepare
        $logFile = Project::getProjectPath().'tests/temp/project.log';
        $beforeLogFileExists = File::exists($logFile);
        $beforeLogFileSize = 0;
        if($beforeLogFileExists) {
            $beforeLogFileSize = File::size($logFile);
        }

        // setup
        $this->object->setLogLevel(LogLevel::Information);

        // test
        $result = $this->object->error('testMessage');
        $this->assertTrue($result);

        // verify
        $afterLogFileExists = File::exists($logFile);
        $this->assertTrue($afterLogFileExists);

        $afterLogFileSize = File::size($logFile);
        $this->assertTrue($afterLogFileSize > $beforeLogFileSize);


        // setup
        $beforeLogFileSize = File::size($logFile);

        // test
        $result = $this->object->warning('testMessage');
        $this->assertTrue($result);

        // verify
        $afterLogFileSize = File::size($logFile);
        $this->assertTrue($afterLogFileSize > $beforeLogFileSize);


        // setup
        $beforeLogFileSize = File::size($logFile);

        // test
        $result = $this->object->information('testMessage');
        $this->assertTrue($result);

        // verify
        $afterLogFileSize = File::size($logFile);
        $this->assertTrue($afterLogFileSize > $beforeLogFileSize);


        // setup
        $beforeLogFileSize = File::size($logFile);

        // test
        $result = $this->object->verbose('testMessage');
        $this->assertFalse($result);

        // verify
        $afterLogFileSize = File::size($logFile);
        $this->assertEquals($beforeLogFileSize, $afterLogFileSize);
    }

    public function testVerboseLevel() {
        // prepare
        $logFile = Project::getProjectPath().'tests/temp/project.log';
        $beforeLogFileExists = File::exists($logFile);
        $beforeLogFileSize = 0;
        if($beforeLogFileExists) {
            $beforeLogFileSize = File::size($logFile);
        }

        // setup
        $this->object->setLogLevel(LogLevel::Verbose);

        // test
        $result = $this->object->error('testMessage');
        $this->assertTrue($result);

        // verify
        $afterLogFileExists = File::exists($logFile);
        $this->assertTrue($afterLogFileExists);

        $afterLogFileSize = File::size($logFile);
        $this->assertTrue($afterLogFileSize > $beforeLogFileSize);


        // setup
        $beforeLogFileSize = File::size($logFile);

        // test
        $result = $this->object->warning('testMessage');
        $this->assertTrue($result);

        // verify
        $afterLogFileSize = File::size($logFile);
        $this->assertTrue($afterLogFileSize > $beforeLogFileSize);


        // setup
        $beforeLogFileSize = File::size($logFile);

        // test
        $result = $this->object->information('testMessage');
        $this->assertTrue($result);

        // verify
        $afterLogFileSize = File::size($logFile);
        $this->assertTrue($afterLogFileSize > $beforeLogFileSize);


        // setup
        $beforeLogFileSize = File::size($logFile);

        // test
        $result = $this->object->verbose('testMessage', 'test_tag');
        $this->assertTrue($result);

        // verify
        $afterLogFileSize = File::size($logFile);
        $this->assertTrue($afterLogFileSize > $beforeLogFileSize);
    }

    public function testLogToDatabaseActuallyLog() {
        $this->object->setLogToFile(false);
        $this->object->setLogToDatabase(true);
        $this->object->setLogLevel(LogLevel::Error);

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
        $result = $this->object->error('test message');
        $this->assertTrue($result);

        $afterDatabaseCount = Database::query('SELECT COUNT(*) FROM project_log');
        $afterDatabaseCount = $afterDatabaseCount[0]['COUNT(*)'];

        $this->assertEquals($beforeDatabaseCount + 1, $afterDatabaseCount);

        $afterLogFileExists = File::exists($logFile);
        $this->assertEquals($beforeLogFileExists, $afterLogFileExists);

        $afterLogFileSize = File::size($logFile);
        $this->assertEquals($beforeLogFileSize, $afterLogFileSize);
    }

    public function testLogToDatabaseDontLog() {
        $this->object->setLogToFile(false);
        $this->object->setLogToDatabase(true);
        $this->object->setLogLevel(LogLevel::Error);

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
        $result = $this->object->information('test message');
        $this->assertFalse($result);

        $afterDatabaseCount = Database::query('SELECT COUNT(*) FROM project_log');
        $afterDatabaseCount = $afterDatabaseCount[0]['COUNT(*)'];

        $this->assertEquals($beforeDatabaseCount, $afterDatabaseCount);

        $afterLogFileExists = File::exists($logFile);
        $this->assertEquals($beforeLogFileExists, $afterLogFileExists);

        $afterLogFileSize = File::size($logFile);
        $this->assertEquals($beforeLogFileSize, $afterLogFileSize);
    }
}

?>
