'use strict';

const Instruction = require('./Instruction');

/**
 * @callback responseHandler
 * @param  {*} value
 */

/**
 * @callback errorHandler
 * @param  {Error} error
 */

/**
 * Handle the requests of a connection.
 */
class ConnectionDelegate
{
    /**
     * Constructor.
     *
     * @param  {Object} options
     */
    constructor(options)
    {
        this.options = options;
    }

    /**
     * Prepare and return the instruction before handling it.
     *
     * @param  {Instruction} instruction
     * @return {Instruction}
     */
    prepareInstruction(instruction)
    {
        return instruction;
    }

    /**
     * Handle the provided instruction and respond to it.
     *
     * @param  {Instruction} instruction
     * @param  {responseHandler} responseHandler
     * @param  {errorHandler} errorHandler
     */
    async handleInstruction(instruction, responseHandler, errorHandler)
    {
        try {
            const executionType = instruction.executionType(true);
            const value = instruction.execute();

            if (executionType === Instruction.EXECUTION_EAGER) {
                responseHandler(await value);
            } else if (executionType === Instruction.EXECUTION_LAZY) {
                responseHandler(value);
            } else {
                throw new Error(`Unknow execution type "${executionType}".`);
            }
        } catch (error) {
            if (instruction.shouldCatchErrors()) {
                return errorHandler(error);
            }

            throw error;
        }
    }
}

module.exports = ConnectionDelegate;
