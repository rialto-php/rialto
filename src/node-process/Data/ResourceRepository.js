'use strict';

class ResourceRepository {
    /**
     * Constructor.
     */
    constructor()
    {
        this.resources = new Map;
    }

    /**
     * Retrieve a resource with its identifier.
     *
     * @param  {string} resourceId
     * @return {*}
     */
    retrieve(resourceId)
    {
        for (let [resource, id] of this.resources) {
            if (resourceId === id) {
                return resource;
            }
        }

        return null;
    }

    /**
     * Store a resource and return its unique identifier.
     *
     * @param  {*} resource
     * @return {string}
     */
    store(resource)
    {
        const {resources} = this;

        if (resources.has(resource)) {
            return resources.get(resource);
        }

        const id = String(Date.now() + Math.random());

        resources.set(resource, id);

        return id;
    }
}

module.exports = ResourceRepository;
