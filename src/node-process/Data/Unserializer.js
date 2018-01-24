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
     * @param  {Object} value
     * @return {*}
     */
    unserialize(value)
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

module.exports = Unserializer;
