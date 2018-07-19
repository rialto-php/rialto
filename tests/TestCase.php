<?php

namespace Nesk\Rialto\Tests;

use PHPUnit\Util\ErrorHandler;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
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
}
