'use strict';

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
        let value = null;

        try {
            value = await instruction.execute();
        } catch (error) {
            if (instruction.shouldCatchErrors()) {
                return errorHandler(error);
            }

            throw error;
        }

        responseHandler(value);
    }
}

module.exports = ConnectionDelegate;
