<?php

namespace Nesk\Rialto\Tests;

use Monolog\Logger;
use ReflectionClass;
use Psr\Log\LogLevel;
use PHPUnit\Util\ErrorHandler;
use Symfony\Component\Process\Process;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\TestCase as BaseTestCase;
use PHPUnit\Framework\MockObject\Matcher\Invocation;

class TestCase extends BaseTestCase
{
    private $dontPopulateProperties = [];

    public function setUp(): void
    {
        parent::setUp();

        $methodName = \explode(' ', $this->getName())[0] ?? '';
        $testMethod = new \ReflectionMethod($this, $methodName);
        $docComment = $testMethod->getDocComment();

        if (\preg_match('/@dontPopulateProperties (.*)/', $docComment, $matches)) {
            $this->dontPopulateProperties = \array_values(\array_filter(\explode(' ', $matches[1])));
        }
    }

    public function canPopulateProperty(string $propertyName): bool
    {
        return !\in_array($propertyName, $this->dontPopulateProperties);
    }

    public function ignoreUserDeprecation(string $messagePattern, callable $callback)
    {
        \set_error_handler(
            function (int $errorNumber, string $errorString, string $errorFile, int $errorLine) use ($messagePattern) {
                if ($errorNumber !== E_USER_DEPRECATED || \preg_match($messagePattern, $errorString) !== 1) {
                    ErrorHandler::handleError($errorNumber, $errorString, $errorFile, $errorLine);
                }
            }
        );

        $value = $callback();

        \restore_error_handler();

        return $value;
    }

    public function getPidsForProcessName(string $processName)
    {
        $pgrep = new Process(['pgrep', $processName]);
        $pgrep->run();

        $pids = \explode("\n", $pgrep->getOutput());

        $pids = \array_filter($pids, function ($pid) {
            return !empty($pid);
        });

        $pids = \array_map(function ($pid) {
            return (int) $pid;
        }, $pids);

        return $pids;
    }
}
