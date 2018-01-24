'use strict';

const _ = require('lodash');

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

        if (this.isValueContainer(value)) {
            return this.mapContainerValues(value, this.serialize.bind(this));
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
     * @protected
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
     * @protected
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
}

module.exports = Serializer;
