'use strict';

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
        if (value.__node_communicator_function__ === true) {
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

        return value;
    }
}

module.exports = Unserializer;
