<?php

namespace Nesk\Rialto\Tests;

use Monolog\Logger;
use Psr\Log\LogLevel;
use PHPUnit\Util\ErrorHandler;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    private const PSR_LOG_LEVELS = [
        LogLevel::DEBUG,
        LogLevel::INFO,
        LogLevel::NOTICE,
        LogLevel::WARNING,
        LogLevel::ERROR,
        LogLevel::CRITICAL,
        LogLevel::ALERT,
        LogLevel::EMERGENCY,
    ];

    private const MONOLOG_LEVELS = [
        Logger::DEBUG,
        Logger::INFO,
        Logger::NOTICE,
        Logger::WARNING,
        Logger::ERROR,
        Logger::CRITICAL,
        Logger::ALERT,
        Logger::EMERGENCY,
    ];

    private $dontPopulateProperties = [];

    public function setUp(): void
    {
        parent::setUp();

        $testMethod = new \ReflectionMethod($this, $this->getName());
        $docComment = $testMethod->getDocComment();

        if (preg_match('/@dontPopulateProperties (.*)/', $docComment, $matches)) {
            $this->dontPopulateProperties = array_values(array_filter(explode(' ', $matches[1])));
        }
    }

    public function canPopulateProperty(string $propertyName): bool
    {
        return !in_array($propertyName, $this->dontPopulateProperties);
    }

    public function ignoreUserDeprecation(string $messagePattern, callable $callback) {
        set_error_handler(
            function (int $errorNumber, string $errorString, string $errorFile, int $errorLine) use ($messagePattern) {
                if ($errorNumber !== E_USER_DEPRECATED || preg_match($messagePattern, $errorString) !== 1) {
                    ErrorHandler::handleError($errorNumber, $errorString, $errorFile, $errorLine);
                }
            }
        );

        $value = $callback();

        restore_error_handler();

        return $value;
    }

    public function isLogLevel(): Callback {
        return $this->callback(function ($level) {
            if (is_string($level)) {
                return in_array($level, self::PSR_LOG_LEVELS, true);
            } else if (is_int($level)) {
                return in_array($level, self::MONOLOG_LEVELS, true);
            }

            return false;
        });
    }
}
