'use strict';

class ResourceIdentity
{
    /**
     * Constructor.
     *
     * @param  {string} className
     * @param  {string} uniqueIdentifier
     */
    constructor(className, uniqueIdentifier)
    {
        this.resource = {className, uniqueIdentifier};
    }

    /**
     * Return the class name of the resource.
     *
     * @return {string}
     */
    className()
    {
        return this.resource.className;
    }

    /**
     * Return the unique identifier of the resource.
     *
     * @return {string}
     */
    uniqueIdentifier()
    {
        return this.resource.uniqueIdentifier;
    }

    /**
     * Unserialize a resource identity.
     *
     * @param  {Object} identity
     * @return {ResourceIdentity}
     */
    static unserialize(identity)
    {
        return new ResourceIdentity(identity.class_name, identity.id);
    }

    /**
     * Serialize the resource identity.
     *
     * @return {Object}
     */
    serialize()
    {
        return {
            __node_communicator_resource__: true,
            class_name: this.className(),
            id: this.uniqueIdentifier(),
        };
    }
}

module.exports = ResourceIdentity;
