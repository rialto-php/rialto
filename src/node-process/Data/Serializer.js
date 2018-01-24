'use strict';

const Value = require('./Value');

class Serializer
{
    /**
     * Serialize an error to JSON.
     *
     * @param  {Error} error
     * @return {Object}
     */
    static serializeError(error)
    {
        return {
            __node_communicator_error__: true,
            message: error.message,
            stack: error.stack,
        };
    }

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
     * Serialize a value.
     *
     * @param  {*} value
     * @return {*}
     */
    serialize(value)
    {
        value = value === undefined ? null : value;

        if (Value.isContainer(value)) {
            return Value.mapContainer(value, this.serialize.bind(this));
        } else if (Value.isScalar(value)) {
            return value;
        } else {
            return this.serializeResource(value);
        }
    }

    /**
     * Serialize a resource.
     *
     * @param  {Object} value
     * @return {Object}
     */
    serializeResource(value)
    {
        return {
            __node_communicator_resource__: true,
            class_name: value.constructor.name,
            id: this.resources.store(value),
        };
    }
}

module.exports = Serializer;
