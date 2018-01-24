<?php

namespace ExtractrIo\Rialto\Data;

class JsFunction implements \JsonSerializable
{
    /**
    * The parameters of the function.
    *
    * @var array
    */
    protected $parameters;

    /**
     * The body of the function.
     *
     * @var string
     */
    protected $body;

    /**
     * The scope of the function.
     *
     * @var array
     */
    protected $scope;

    /**
     * Create a new JS function. Function parameters can be omitted.
     */
    public static function create(...$arguments)
    {
        if (isset($arguments[0]) && is_string($arguments[0])) {
            return new static([], $arguments[0], $arguments[1] ?? []);
        }

        return new static(...$arguments);
    }

    /**
     * Constructor.
     */
    public function __construct(array $parameters, string $body, array $scope = [])
    {
        $this->parameters = $parameters;
        $this->body = $body;
        $this->scope = $scope;
    }

    /**
     * Serialize the object to a value that can be serialized natively by {@see json_encode}.
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => 'function',
            'parameters' => (object) $this->parameters,
            'body' => $this->body,
            'scope' => (object) $this->scope,
        ];
    }
}
