'use strict';

const net = require('net'),
    Connection = require('./Connection');

/**
 * Listen for new socket connections.
 */
class Server
{
    /**
     * Constructor.
     *
     * @param  {ConnectionDelegate} connectionDelegate
     * @param  {Object} options
     */
    constructor(connectionDelegate, options = {})
    {
        this.options = options;

        this.start(connectionDelegate);

        this.resetIdleTimeout();
    }

    /**
     * Start the server and listen for new connections.
     *
     * @return {Server}
     */
    start(connectionDelegate)
    {
        this.server = net.createServer(socket => {
            const connection = new Connection(socket, connectionDelegate);

            connection.on('activity', () => this.resetIdleTimeout());

            this.resetIdleTimeout();
        });

        this.server.listen();
    }

    /**
     * Write the listening port on the process output.
     */
    writePortToOutput()
    {
        process.stdout.write(`${this.server.address().port}\n`);
    }

    /**
     * Reset the idle timeout.
     *
     * @protected
     */
    resetIdleTimeout()
    {
        clearTimeout(this.idleTimer);

        const {idle_timeout: idleTimeout} = this.options;

        if (idleTimeout) {
            this.idleTimer = setTimeout(() => {
                throw new Error('The idle timeout has been reached.');
            }, idleTimeout * 1000);
        }
    }
}

module.exports = Server;
