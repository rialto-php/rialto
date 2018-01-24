<?php

namespace ExtractrIo\Rialto\Tests;

use PHPUnit\Framework\TestCase;
use ExtractrIo\Rialto\Instruction;
use ExtractrIo\Rialto\Data\ResourceIdentity;
use ExtractrIo\Rialto\Traits\IdentifiesResource;
use ExtractrIo\Rialto\Interfaces\ShouldIdentifyResource;

class InstructionTest extends TestCase
{
    /** @test */
    public function call_action_builds_appropriate_json()
    {
        $instruction = Instruction::withCall('methodName', 'arg1', ['arg2' => 'value']);

        $this->assertEquals([
            'type' => 'call',
            'name' => 'methodName',
            'value' => [
                'arg1',
                ['arg2' => 'value'],
            ],
            'catched' => false,
        ], $instruction->jsonSerialize());
    }

    /** @test */
    public function get_action_builds_appropriate_json()
    {
        $instruction = Instruction::withGet('propertyName');

        $this->assertEquals([
            'type' => 'get',
            'name' => 'propertyName',
            'catched' => false,
        ], $instruction->jsonSerialize());
    }

    /** @test */
    public function set_action_builds_appropriate_json()
    {
        $instruction = Instruction::withSet('propertyName', ['a' => 'b']);

        $this->assertEquals([
            'type' => 'set',
            'name' => 'propertyName',
            'value' => ['a' => 'b'],
            'catched' => false,
        ], $instruction->jsonSerialize());
    }

    /** @test */
    public function resource_linking_builds_appropriate_json()
    {
        $resource = new class implements ShouldIdentifyResource {
            use IdentifiesResource;
        };

        $id = uniqid('', true);
        $resource->setResourceIdentity(new ResourceIdentity('Fake', $id));

        $instruction = Instruction::withGet('propertyName')->linkToResource($resource);

        $this->assertEquals([
            'type' => 'get',
            'name' => 'propertyName',
            'resource' => $id,
            'catched' => false,
        ], $instruction->jsonSerialize());
    }

    /** @test */
    public function error_catching_builds_appropriate_json()
    {
        $instruction = Instruction::withGet('propertyName')->shouldCatchErrors(true);

        $this->assertEquals([
            'type' => 'get',
            'name' => 'propertyName',
            'catched' => true,
        ], $instruction->jsonSerialize());
    }
}
