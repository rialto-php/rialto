<?php

namespace ExtractrIo\Rialto\Exceptions\Node;

class Exception extends \RuntimeException
{
    /**
     * The original error as a string.
     *
     * @var string
     */
    protected $originalError;

    /**
     * Constructor.
     */
    public function __construct(string $originalError)
    {
        $this->originalError = $originalError;

        parent::__construct($this->getOriginalMessage());
    }

    /**
     * Return the error output splitted by lines.
     */
    protected function getExplodedOutput(): array
    {
        $output = explode("\n", $this->originalError);

        return array_values(array_filter($output));
    }

    /**
     * Return the original error message.
     */
    protected function getOriginalMessage(): string
    {
        $output = $this->getExplodedOutput();

        $start = array_keys(preg_grep('/^[a-zA-Z]*Error(: .*)?$/', $output))[0];
        $end = array_keys(preg_grep('/^    at /', $output))[0];

        $message = implode("\n", array_slice($output, $start, $end - $start));

        $message = preg_match('/^[a-zA-Z]*Error: "(\X*)"/', $message, $matches)
            ? $matches[1]
            : '';

        return $message;
    }

    /**
     * Return the original stack trace.
     */
    public function getOriginalTrace(): array
    {
        $output = $this->getExplodedOutput();

        $start = array_keys(preg_grep('/^    at /', $output))[0];

        $trace = array_slice($output, $start);

        $trace = array_map(function ($line) {
            return substr($line, strlen('    at '));
        }, $trace);

        return $trace;
    }

    /**
     * Return the original stack trace as a string.
     */
    public function getOriginalTraceAsString(): string
    {
        $trace = $this->getOriginalTrace();

        $trace = array_map(function ($line, $index) {
            return "#$index $line";
        }, $trace, range(0, count($trace) - 1));

        $trace = implode("\n", $trace);

        return $trace;
    }
}
