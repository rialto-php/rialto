'use strict';

const Value = require('./Value'),
    ResourceIdentity = require('./ResourceIdentity');

class Unserializer
{
    /**
     * Constructor.
     *
     * @param  {ResourceRepository} resources
     */
    constructor(resources)
    {
        this.resources = resources;
    }

    /**
     * Unserialize a value.
     *
     * @param  {*} value
     * @return {*}
     */
    unserialize(value)
    {
        if (value.__rialto_resource__ === true) {
            return this.resources.retrieve(ResourceIdentity.unserialize(value));
        } else if (value.__rialto_function__ === true) {
            return this.unserializeFunction(value);
        } else if (Value.isContainer(value)) {
            return Value.mapContainer(value, this.unserialize.bind(this));
        } else {
            return value;
        }
    }

    /**
     * Unserialize a function.
     *
     * @param  {Object} value
     * @return {Function}
     */
    unserializeFunction(value)
    {
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
}

module.exports = Unserializer;
