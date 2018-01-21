'use strict';

class ErrorSerializer
{
    /**
     * Serialize an error to JSON.
     *
     * @param  {Error} error
     * @return {Object}
     */
    static serialize(error)
    {
        return {
            __node_communicator_error__: true,
            message: error.message,
            stack: error.stack,
        };
    }
}

module.exports = ErrorSerializer;
