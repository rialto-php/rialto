<?php

namespace Nesk\Rialto\Data;

class JsFunction implements \JsonSerializable
{
    /**
    * The parameters of the function.
    *
    * @var array<int|string, int|float|string|bool|null>
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
     * @var array<int, int|float|string|bool|null>
     */
    protected $scope;

    /**
     * The async state of the function.
     *
     * @var bool
     */
    protected $async = false;

    /**
     * Create a new JS function.
     *
     * @deprecated 2.0.0 Chaining methods should be used instead.
     */
    public static function create(...$arguments)
    {
        \trigger_error(__METHOD__ . '() has been deprecated and will be removed from v2.', E_USER_DEPRECATED);

        if (isset($arguments[0]) && \is_string($arguments[0])) {
            return new static([], $arguments[0], $arguments[1] ?? []);
        }

        return new static(...$arguments);
    }

    /**
     * Constructor.
     *
     * @param array<int|string, int|float|string|bool|null> $parameters
     * @param array<int, int|float|string|bool|null> $scope
     */
    public function __construct(array $parameters = [], string $body = '', array $scope = [])
    {
        $this->parameters = $parameters;
        $this->body = $body;
        $this->scope = $scope;
    }

    /**
     * Return a new instance with the specified parameters.
     *
     * @param array<int|string, int|float|string|bool|null> $parameters
     */
    public function parameters(array $parameters): self
    {
        $clone = clone $this;
        $clone->parameters = $parameters;
        return $clone;
    }

    /**
     * @param array<int|string, int|float|string|bool|null> $parameters
     */
    public static function createWithParameters(array $parameters): self
    {
        return (new self())->parameters($parameters);
    }

    /**
     * Return a new instance with the specified body.
     */
    public function body(string $body): self
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    public static function createWithBody(string $body): self
    {
        return (new self())->body($body);
    }

    /**
     * Return a new instance with the specified scope.
     *
     * @param array<int, int|float|string|bool|null> $scope
     */
    public function scope(array $scope): self
    {
        $clone = clone $this;
        $clone->scope = $scope;
        return $clone;
    }

    /**
     * @param array<int, int|float|string|bool|null> $scope
     */
    public static function createWithScope(array $scope): self
    {
        return (new self())->scope($scope);
    }

    /**
     * Return a new instance with the specified async state.
     */
    public function async(bool $isAsync = true): self
    {
        $clone = clone $this;
        $clone->async = $isAsync;
        return $clone;
    }

    public static function createWithAsync(bool $isAsync = true): self
    {
        return (new self())->async($isAsync);
    }

    /**
     * Serialize the object to a value that can be serialized natively by {@see json_encode}.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            '__rialto_function__' => true,
            'parameters' => (object) $this->parameters,
            'body' => $this->body,
            'scope' => (object) $this->scope,
            'async' => $this->async,
        ];
    }
}
