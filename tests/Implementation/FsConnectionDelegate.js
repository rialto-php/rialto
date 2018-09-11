'use strict';

const fs = require('fs'),
    {ConnectionDelegate} = require('../../src/node-process');

/**
 * Handle the requests of a connection to control the "fs" module.
 */
class FsConnectionDelegate extends ConnectionDelegate
{
    prepareInstruction(instruction)
    {
        return instruction.setDefaultResource(this.extendFsModule(fs));
    }

    extendFsModule(fs)
    {
        fs.multipleStatSync = (...paths) => paths.map(fs.statSync);

        fs.multipleResourcesIsFile = resources => resources.map(resource => resource.isFile());

        fs.getHeavyPayloadWithNonAsciiChars = () => {
            let payload = '';

            for (let i = 0 ; i < 1024 ; i++) {
                payload += 'a';
            }

            return `😘${payload}😘`;
        };

        fs.wait = ms => new Promise(resolve => setTimeout(resolve, ms));

        fs.runCallback = cb => cb(fs);

        fs.getOption = name => this.options[name];

        return fs;
    }
}

module.exports = FsConnectionDelegate;
