'use strict';

const Server = require('./Server'),
    ConnectionDelegate = require('./ConnectionDelegate'),
    DataSerializer = require('./Data/Serializer');

// Instanciate the custom connection delegate
const connectionDelegate = new (require(process.argv.slice(2)[0]));

// Retrieve the options
let options = process.argv.slice(2)[1];
options = options !== undefined ? JSON.parse(options) : {};

// Start the server with the custom connection delegate
const server = new Server(connectionDelegate, options);

// Write the server port to the process output
server.writePortToOutput();

// Throw unhandled rejections
process.on('unhandledRejection', error => {
    throw error;
});

// Output the exceptions in JSON format
process.on('uncaughtException', error => {
    process.stderr.write(JSON.stringify(DataSerializer.serializeError(error)));

    process.exit(1);
});
