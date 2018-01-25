<?php

namespace ExtractrIo\Rialto\Tests;

use Mockery as m;
use Monolog\Logger;
use Psr\Log\LogLevel;
use ExtractrIo\Rialto\Data\JsFunction;
use Symfony\Component\Process\Process;
use ExtractrIo\Rialto\Data\BasicResource;
use ExtractrIo\Rialto\Tests\Implementation\Fs;
use ExtractrIo\Rialto\Tests\Implementation\Resources\Stats;

class ImplementationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->dirPath = realpath(__DIR__.'/resources');
        $this->filePath = "{$this->dirPath}/file";

        $this->fs = $this->canPopulateProperty('fs') ? new Fs : null;
    }

    public function tearDown(): void
    {
        m::close();

        $this->fs = null;
    }

    /** @test */
    public function can_call_method_and_get_its_return_value()
    {
        $content = $this->fs->readFileSync($this->filePath, 'utf8');

        $this->assertEquals('Hello world!', $content);
    }

    /** @test */
    public function can_get_property()
    {
        $constants = $this->fs->constants;

        $this->assertInternalType('array', $constants);
    }

    /** @test */
    public function can_set_property()
    {
        $this->fs->foo = 'bar';

        $value = $this->fs->foo;

        $this->assertEquals('bar', $value);
    }

    /** @test */
    public function can_return_basic_resources()
    {
        $resource = $this->fs->readFileSync($this->filePath);

        $this->assertInstanceOf(BasicResource::class, $resource);
    }

    /** @test */
    public function can_return_specific_resources()
    {
        $resource = $this->fs->statSync($this->filePath);

        $this->assertInstanceOf(Stats::class, $resource);
    }

    /** @test */
    public function can_use_nested_resources()
    {
        $resources = $this->fs->multipleStatSync($this->dirPath, $this->filePath);

        $this->assertCount(2, $resources);
        $this->assertContainsOnlyInstancesOf(Stats::class, $resources);

        $isFile = $this->fs->multipleResourcesIsFile($resources);

        $this->assertFalse($isFile[0]);
        $this->assertTrue($isFile[1]);
    }

    /** @test */
    public function can_use_multiple_resources_without_confusion()
    {
        $dirStats = $this->fs->statSync($this->dirPath);
        $fileStats = $this->fs->statSync($this->filePath);

        $this->assertInstanceOf(Stats::class, $dirStats);
        $this->assertInstanceOf(Stats::class, $fileStats);

        $this->assertTrue($dirStats->isDirectory());
        $this->assertTrue($fileStats->isFile());
    }

    /** @test */
    public function can_return_multiple_times_the_same_resource()
    {
        $stats1 = $this->fs->Stats;
        $stats2 = $this->fs->Stats;

        $this->assertEquals($stats1, $stats2);
    }

    /** @test */
    public function can_create_and_pass_js_functions()
    {
        $value = $this->fs->runCallback(JsFunction::create("
            return 'Simple callback';
        "));

        $this->assertEquals('Simple callback', $value);

        $value = $this->fs->runCallback(JsFunction::create(['fs'], "
            return 'Callback using arguments: ' + fs.constructor.name;
        "));

        $this->assertEquals('Callback using arguments: Object', $value);

        $value = $this->fs->runCallback(JsFunction::create("
            return 'Callback using scope: ' + foo;
        ", ['foo' => 'bar']));

        $this->assertEquals('Callback using scope: bar', $value);

        $value = $this->fs->runCallback(JsFunction::create(['fs'], "
            return 'Callback using scope and arguments: ' + fs.readFileSync(path);
        ", ['path' => $this->filePath]));

        $this->assertEquals('Callback using scope and arguments: Hello world!', $value);
    }

    /**
     * @test
     * @expectedException \ExtractrIo\Rialto\Exceptions\Node\FatalException
     * @expectedExceptionMessage Object.__inexistantMethod__ is not a function
     */
    public function node_crash_throws_a_fatal_exception()
    {
        $this->fs->__inexistantMethod__();
    }

    /**
     * @test
     * @expectedException \ExtractrIo\Rialto\Exceptions\Node\Exception
     * @expectedExceptionMessage Object.__inexistantMethod__ is not a function
     */
    public function can_catch_errors()
    {
        $this->fs->tryCatch->__inexistantMethod__();
    }

    /** @test*/
    public function node_current_working_directory_is_the_same_as_php()
    {
        $result = $this->fs->accessSync('tests/resources/file');

        $this->assertNull($result);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Process\Exception\ProcessFailedException
     * @expectedExceptionMessageRegExp /Error Output:\n=+\n.*__inexistant_process__.*not found/
     */
    public function executable_path_option_changes_the_process_prefix()
    {
        new Fs(['executable_path' => '__inexistant_process__']);
    }

    /**
     * @test
     * @dontPopulateProperties fs
     */
    public function idle_timeout_option_closes_node_once_timer_is_reached()
    {
        $this->fs = new Fs(['idle_timeout' => 0.5]);

        $this->fs->constants;

        sleep(1);

        $this->expectException(\ExtractrIo\Rialto\Exceptions\IdleTimeoutException::class);
        $this->expectExceptionMessageRegExp('/^The idle timeout \(0\.500 seconds\) has been exceeded/');

        $this->fs->constants;
    }

    /**
     * @test
     * @dontPopulateProperties fs
     * @expectedException \ExtractrIo\Rialto\Exceptions\ReadSocketTimeoutException
     * @expectedExceptionMessageRegExp /^The timeout \(0\.010 seconds\) has been exceeded/
     */
    public function read_timeout_option_throws_an_exception_on_long_actions()
    {
        $this->fs = new Fs(['read_timeout' => 0.01]);

        $this->fs->wait(20);
    }

    /**
     * @test
     * @dontPopulateProperties fs
     */
    public function process_status_is_tracked()
    {
        if (PHP_OS === 'WINNT') {
            $this->markTestSkipped('This test is not supported on Windows.');
        }

        if ((new Process('which pgrep'))->run() !== 0) {
            $this->markTestSkipped('The "pgrep" command is not available.');
        }

        $pgrep = new Process('pgrep node');

        $pgrep->run();
        $oldPids = explode("\n", $pgrep->getOutput());

        $this->fs = new Fs;

        $pgrep->run();
        $newPids = explode("\n", $pgrep->getOutput());

        $newNodeProcesses = array_values(array_diff($newPids, $oldPids));

        $this->assertCount(1, $newNodeProcesses, 'Only one Node process should have been created. Try running again.');

        $processKilled = posix_kill((int) $newNodeProcesses[0], SIGKILL);

        $this->assertTrue($processKilled);

        $this->expectException(\ExtractrIo\Rialto\Exceptions\ProcessUnexpectedlyTerminatedException::class);
        $this->expectExceptionMessage('The process has been unexpectedly terminated.');

        $this->fs->foo;
    }

    /** @test */
    public function process_is_properly_shutdown_when_they_is_no_more_references()
    {
        if (!class_exists('WeakRef')) {
            $this->markTestSkipped('This test requires weak references: http://php.net/weakref/');
        }

        $ref = new \WeakRef($this->fs->getProcess());

        $resource = $this->fs->readFileSync($this->filePath);

        $this->assertInstanceOf(BasicResource::class, $resource);

        $this->fs = null;
        unset($resource);

        $this->assertFalse($ref->valid());
    }

    /**
     * @test
     * @dontPopulateProperties fs
     */
    public function logger_is_used_when_provided()
    {
        $mock = m::mock(new Logger('rialto'));

        $shouldLog = function ($level, $message) use ($mock) {
            $mock->shouldReceive('log')->with($level, $message)->ordered()->once();
        };

        $shouldLog(LogLevel::DEBUG, 'Starting process...');
        $shouldLog(LogLevel::DEBUG, m::pattern('/^\[PID \d+\] Process started$/'));

        $this->fs = new Fs(['logger' => $mock]);

        $shouldLog(LogLevel::DEBUG, m::pattern('/^\[PORT \d+\] \[sending\] \{.*\}$/'));
        $shouldLog(LogLevel::DEBUG, m::pattern('/^\[PORT \d+\] \[receiving\] null$/'));
        $shouldLog(LogLevel::NOTICE, m::pattern('/^\[PID \d+\] \[stdout\] Hello World!$/'));

        $this->fs->runCallback(JsFunction::create("
            process.stdout.write('Hello World!');
        "));

        $shouldLog(LogLevel::DEBUG, m::pattern('/^\[PORT \d+\] \[sending\] \{.*\}$/'));
        $shouldLog(LogLevel::DEBUG, m::pattern('/^\[PORT \d+\] \[receiving\] null$/'));
        $shouldLog(LogLevel::ERROR, m::pattern('/^\[PID \d+\] \[stderr\] Goodbye World!$/'));

        $this->fs->runCallback(JsFunction::create("
            process.stderr.write('Goodbye World!');
        "));

        $shouldLog(LogLevel::DEBUG, m::pattern('/^\[PID \d+\] Stopping process...$/'));
        $shouldLog(LogLevel::DEBUG, m::pattern('/^\[PID \d+\] Stopped process$/'));

        $this->assertNull($this->fs = null);
    }
}
