<?php

namespace ExtractrIo\Rialto\Traits;

use ExtractrIo\Rialto\{Instruction, Process};
use ExtractrIo\Rialto\Interfaces\ShouldIdentifyResource;

trait CommunicatesWithProcess
{
    /**
     * The process to communicate with.
     *
     * @var \ExtractrIo\Rialto\Process
     */
    protected $process;

    /**
     * Whether the current resource should catch instruction errors.
     *
     * @var boolean
     */
    protected $catchInstructionErrors = false;

    /**
    * Get the process.
    */
    protected function getProcess(): Process
    {
        return $this->process;
    }

    /**
     * Set the process.
     *
     * @throws \RuntimeException if the process has already been set.
     */
    public function setProcess(Process $process): void
    {
        if ($this->process !== null) {
            throw new RuntimeException('The process has already been set.');
        }

        $this->process = $process;
    }

    /**
     * Clone the resource and catch its instruction errors.
     */
    protected function createCatchingResource()
    {
        $resource = clone $this;

        $resource->catchInstructionErrors = true;

        return $resource;
    }

    /**
     * Proxy an action.
     */
    protected function proxyAction(string $actionType, string $name, $value = null)
    {
        switch ($actionType) {
            case Instruction::TYPE_CALL:
                $instruction = Instruction::withCall($name, ...$value);
                break;
            case Instruction::TYPE_GET:
                $instruction = Instruction::withGet($name);
                break;
            case Instruction::TYPE_SET:
                $instruction = Instruction::withSet($name, $value);
                break;
        }

        $identifiesResource = $this instanceof ShouldIdentifyResource;

        $instruction->linkToResource($identifiesResource ? $this : null);

        if ($this->catchInstructionErrors) {
            $instruction->shouldCatchErrors(true);
        }

        return $this->getProcess()->executeInstruction($instruction);
    }

    /**
     * Proxy the method call to the process.
     */
    public function __call(string $name, array $arguments)
    {
        return $this->proxyAction(Instruction::TYPE_CALL, $name, $arguments);
    }

    /**
     * Proxy the property reading to the process.
     */
    public function __get(string $name)
    {
        if ($name === 'tryCatch' && !$this->catchInstructionErrors) {
            return $this->createCatchingResource();
        }

        return $this->proxyAction(Instruction::TYPE_GET, $name);
    }

    /**
     * Proxy the property writing to the process.
     */
    public function __set(string $name, $value)
    {
        return $this->proxyAction(Instruction::TYPE_SET, $name, $value);
    }
}
