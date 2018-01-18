'use strict';

class Instruction
{
    /**
     * Constructor.
     *
     * @param  {Object} serializedInstruction
     * @param  {ResourceRepository} resources
     */
    constructor(serializedInstruction, resources)
    {
        this.instruction = serializedInstruction;
        this.resources = resources;
        this.defaultResource = process;
    }

    /**
     * Return the type of the instruction.
     *
     * @return {instructionTypeEnum}
     */
    type()
    {
        return this.instruction.type;
    }

    /**
     * Override the type of the instruction.
     *
     * @param  {instructionTypeEnum} type
     * @return {this}
     */
    overrideType(type)
    {
        this.instruction.type = type;

        return this;
    }

    /**
     * Return the name of the instruction.
     *
     * @return {string}
     */
    name()
    {
        return this.instruction.name;
    }

    /**
     * Override the name of the instruction.
     *
     * @param  {string} name
     * @return {this}
     */
    overrideName(name)
    {
        this.instruction.name = name;

        return this;
    }

    /**
     * Return the value of the instruction.
     *
     * @return {*}
     */
    value()
    {
        const {value} = this.instruction;

        return value !== undefined ? value : null;
    }

    /**
     * Override the value of the instruction.
     *
     * @param  {*} value
     * @return {this}
     */
    overrideValue(value)
    {
        this.instruction.value = value;

        return this;
    }

    /**
     * Return the resource of the instruction.
     *
     * @return {Object|null}
     */
    resource()
    {
        const {resource} = this.instruction;

        return resource
            ? this.resources.retrieve(resource)
            : null;
    }

    /**
     * Override the resource of the instruction.
     *
     * @param  {Object|null} resource
     * @return {this}
     */
    overrideResource(resource)
    {
        if (resource !== null) {
            this.instruction.resource = this.resources.store(resource);
        }

        return this;
    }

    /**
     * Set the default resource to use.
     *
     * @param  {Object} resource
     * @return {this}
     */
    setDefaultResource(resource)
    {
        this.defaultResource = resource;

        return this;
    }

    /**
     * Whether errors thrown by the instruction should be catched.
     *
     * @return {boolean}
     */
    shouldCatchErrors()
    {
        return this.instruction.catched;
    }

    /**
     * Execute the instruction.
     *
     * @return {*}
     */
    execute()
    {
        const type = this.type(),
            name = this.name(),
            value = this.value(),
            resource = this.resource() || this.defaultResource;

        let output = null;

        switch (type) {
            case Instruction.TYPE_CALL:
                output = this.callResourceMethod(resource, name, value || []);
                break;
            case Instruction.TYPE_GET:
                output = resource[name];
                break;
            case Instruction.TYPE_SET:
                output = resource[name] = this.unserializeValue(value);
                break;
        }

        return output;
    }

    /**
     * Call a method on a resource.
     *
     * @protected
     * @param  {Object} resource
     * @param  {string} methodName
     * @param  {array} args
     * @return {*}
     */
    callResourceMethod(resource, methodName, args)
    {
        try {
            return resource[methodName](...args.map(this.unserializeValue));
        } catch (error) {
            if (error.message === 'resource[methodName] is not a function') {
                const resourceName = resource.constructor.name === 'Function'
                    ? resource.name
                    : resource.constructor.name;

                throw new Error(`"${resourceName}.${methodName} is not a function"`);
            }

            throw error;
        }
    }

    /**
     * Unserialize a value.
     *
     * @protected
     * @param  {Object} value
     * @return {*}
     */
    unserializeValue(value)
    {
        if (value.type === 'json') {
            return value.value;
        }

        if (value.type === 'function') {
            const scopedVariables = [];

            for (let [varName, varValue] of Object.entries(value.scope)) {
                scopedVariables.push(`var ${varName} = ${JSON.stringify(varValue)};`);
            }

            const parameters = [];

            for (let [paramKey, paramValue] of Object.entries(value.parameters)) {
                if (!isNaN(parseInt(paramKey, 10))) {
                    parameters.push(paramValue);
                } else {
                    parameters.push(`${paramKey} = ${JSON.stringify(paramValue)}`);
                }
            }

            return new Function(`
                return function (${parameters.join(', ')}) {
                    ${scopedVariables.join('\n')}
                    ${value.body}
                };
            `)();
        }

        return null;
    }
}

/**
 * Instruction types.
 *
 * @enum {instructionTypeEnum}
 * @readonly
 */
Object.assign(Instruction, {
    TYPE_CALL: 'call',
    TYPE_GET: 'get',
    TYPE_SET: 'set',
});

module.exports = Instruction;
