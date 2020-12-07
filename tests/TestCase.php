<?php

namespace Nesk\Rialto\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PHPUnit\Util\ErrorHandler;
use Symfony\Component\Process\Process;

use function Safe\preg_match;

class TestCase extends BaseTestCase
{
    private $dontPopulateProperties = [];

    public function setUp(): void
    {
        parent::setUp();

        $methodName = \explode(' ', $this->getName())[0] ?? '';
        $testMethod = new \ReflectionMethod($this, $methodName);
        $docComment = $testMethod->getDocComment();
        $docComment = $docComment !== false ? $docComment : '';

        if (preg_match('/@dontPopulateProperties (.*)/', $docComment, $matches) !== 0) {
            $this->dontPopulateProperties = \array_values(\array_filter(\explode(' ', $matches[1])));
        }
    }

    public function canPopulateProperty(string $propertyName): bool
    {
        return !\in_array($propertyName, $this->dontPopulateProperties, true);
    }

    public function ignoreUserDeprecation(string $messagePattern, callable $callback)
    {
        \set_error_handler(
            function (
                int $errorNumber,
                string $errorString,
                string $errorFile,
                int $errorLine
            ) use ($messagePattern): bool {
                if ($errorNumber !== E_USER_DEPRECATED || preg_match($messagePattern, $errorString) !== 1) {
                    ErrorHandler::handleError($errorNumber, $errorString, $errorFile, $errorLine);
                }

                return true;
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

        $pids = \array_filter($pids, function ($pid): bool {
            return \strlen(\trim($pid)) > 0;
        });

        $pids = \array_map(function ($pid): int {
            return (int) \trim($pid);
        }, $pids);

        return $pids;
    }
}
