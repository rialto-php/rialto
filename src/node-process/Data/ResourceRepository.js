'use strict';

const ResourceIdentity = require('./ResourceIdentity');

class ResourceRepository {
    /**
     * Constructor.
     */
    constructor()
    {
        this.resources = new Map;
    }

    /**
     * Retrieve a resource with its identity.
     *
     * @param  {ResourceIdentity} identity
     * @return {*}
     */
    retrieve(identity)
    {
        for (let [resource, id] of this.resources) {
            if (identity.uniqueIdentifier() === id) {
                return resource;
            }
        }

        return null;
    }

    /**
     * Store a resource and return its identity.
     *
     * @param  {*} resource
     * @return {ResourceIdentity}
     */
    store(resource)
    {
        const {resources} = this;

        if (resources.has(resource)) {
            return this.generateResourceIdentity(resource, resources.get(resource));
        }

        const id = String(Date.now() + Math.random());

        resources.set(resource, id);

        return this.generateResourceIdentity(resource, id);
    }

    /**
     * Generate a resource identity.
     *
     * @param  {*} resource
     * @param  {string} uniqueIdentifier
     * @return {ResourceIdentity}
     */
    generateResourceIdentity(resource, uniqueIdentifier)
    {
        return new ResourceIdentity(resource.constructor.name, uniqueIdentifier);
    }
}

module.exports = ResourceRepository;
