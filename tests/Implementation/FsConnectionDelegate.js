'use strict';

const fs = require('fs'),
    {ConnectionDelegate} = require('../../src/node-process');

/**
 * Handle the requests of a connection to control the "fs" module.
 */
class FsConnectionDelegate extends ConnectionDelegate
{
    async handleInstruction(instruction, responseHandler, errorHandler)
    {
        instruction.setDefaultResource(this.extendFsModule(fs));

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

    extendFsModule(fs)
    {
        fs.multipleStatSync = (...args) => args.map(fs.statSync);

        fs.wait = ms => new Promise(resolve => setTimeout(resolve, ms));

        fs.runCallback = cb => cb(fs);

        return fs;
    }
}

module.exports = FsConnectionDelegate;
