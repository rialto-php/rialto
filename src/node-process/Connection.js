'use strict';

const EventEmitter = require('events'),
    _ = require('lodash'),
    ConnectionDelegate = require('./ConnectionDelegate'),
    ResourceRepository = require('./ResourceRepository'),
    Instruction = require('./Instruction'),
    ErrorSerializer = require('./ErrorSerializer');

/**
 * Handle a connection interacting with this process.
 */
class Connection extends EventEmitter
{
    /**
     * Constructor.
     *
     * @param  {net.Socket} socket
     * @param  {ConnectionDelegate} delegate
     */
    constructor(socket, delegate)
    {
        super();

        this.socket = this.configureSocket(socket);

        if (delegate instanceof ConnectionDelegate) {
            this.delegate = delegate;
        } else {
            throw new Error('The connection delegate must extend the ConnectionDelegate class.')
        }

        this.resources = new ResourceRepository;
    }

    /**
     * Configure the socket for communication.
     *
     * @param  {net.Socket} socket
     * @return {net.Socket}
     */
    configureSocket(socket)
    {
        socket.setEncoding('utf8');

        socket.on('data', data => {
            this.emit('activity');

            this.handleSocketData(data);
        });

        return socket;
    }

    /**
     * Handle data received on the socket.
     *
     * @param  {string} data
     */
    handleSocketData(data)
    {
        const instruction = new Instruction(JSON.parse(data), this.resources),
            {responseHandler, errorHandler} = this.createInstructionHandlers();

        this.delegate.handleInstruction(instruction, responseHandler, errorHandler);
    }

    /**
     * Generate response and errors handlers.
     *
     * @return {Object}
     */
    createInstructionHandlers()
    {
        let handlerHasBeenCalled = false;

        const updateHandlerCallStatus = () => {
            if (handlerHasBeenCalled) {
                throw new Error('You can call only once the response/error handler.');
            }

            handlerHasBeenCalled = true;
        };

        const responseHandler = data => {
            updateHandlerCallStatus();

            this.writeToSocket(JSON.stringify(this.serializeValue(data)));
        };

        const errorHandler = error => {
            updateHandlerCallStatus();

            this.writeToSocket(JSON.stringify(this.serializeError(error)));
        };

        return {responseHandler, errorHandler};
    }

    /**
     * Write a string to the socket by slitting it in packets of fixed length.
     *
     * @param  {string} str
     */
    writeToSocket(str)
    {
        const bodySize = Connection.SOCKET_PACKET_SIZE - Connection.SOCKET_HEADER_SIZE,
            chunkCount = Math.ceil(str.length / bodySize);

        const packets = [];

        for (let i = 0 ; i < chunkCount ; i++) {
            const chunk = str.substr(i * bodySize, bodySize);

            let chunksLeft = String(chunkCount - 1 - i);
            chunksLeft = chunksLeft.padStart(Connection.SOCKET_HEADER_SIZE - 1, '0');

            packets.push(`${chunksLeft}:${chunk}`);
        }

        this.socket.write(packets.join(''));
    }

    /**
     * Serialize a value to return to the client.
     *
     * @param  {*} value
     * @return {Object}
     */
    serializeValue(value)
    {
        value = value === undefined ? null : value;

        if (this.isValueContainer(value)) {
            return this.mapContainerValues(value, this.serializeValue.bind(this));
        } else if (_.isString(value) || _.isNumber(value) || _.isBoolean(value) || _.isNull(value)) {
            return value;
        } else {
            return {
                __node_communicator_resource__: true,
                class_name: value.constructor.name,
                id: this.resources.store(value),
            };
        }
    }

    /**
     * Determine if the value is a container.
     *
     * @param  {*} value
     * @return {boolean}
     */
    isValueContainer(value)
    {
        return _.isArray(value) || _.isPlainObject(value);
    }

    /**
     * Map the values of a container.
     *
     * @param  {*} container
     * @param  {callback} mapper
     * @return {array}
     */
    mapContainerValues(container, mapper)
    {
        if (_.isArray(container)) {
            return container.map(mapper);
        } else if (_.isPlainObject(container)) {
            return Object.entries(container).reduce((finalObject, [key, value]) => {
                finalObject[key] = mapper(value);

                return finalObject;
            }, {});
        } else {
            return container;
        }
    }

    /**
     * Serialize an error to return to the client.
     *
     * @param  {Error} error
     * @return {Object}
     */
    serializeError(error)
    {
        return ErrorSerializer.serialize(error);
    }
}

/**
 * The size of a packet sent through the sockets.
 *
 * @constant
 * @type {number}
*/
Connection.SOCKET_PACKET_SIZE = 1024;

/**
 * The size of the header in each packet sent through the sockets.
 *
 * @constant
 * @type {number}
 */
Connection.SOCKET_HEADER_SIZE = 5;

module.exports = Connection;
