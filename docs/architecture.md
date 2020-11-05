# Understanding the code architecture behind Rialto

This guide is here to help you understand how Rialto works behind the scene. Before reading it, make sure you understand the basic usage of Rialto [by reading the tutorial](https://github.com/rialto-php/rialto/blob/dev/docs/tutorial.md).

## Process communication

Basically, when you instanciate the entrypoint of a Rialto implementation, a Node process is spawned with some configuration provided by PHP. Once running, the Node process opens a socket on a random port (provided by the operating system) and outputs the port on the stdout stream, which will be retrieved by the PHP process. Once the Node port is retrieved, the PHP process connects to the socket with the provided port. The processes are now communicating!

## The entrypoint

When you write a Rialto implementation, you start by creating an entrypoint, it's a simple PHP class inheriting the [`AbstractEntryPoint`](https://github.com/rialto-php/rialto/blob/architecture-guide/src/AbstractEntryPoint.php) class. It has 2 roles:

- Once instanciated, it starts the Node process (via the [`ProcessSupervisor`](https://github.com/rialto-php/rialto/blob/architecture-guide/src/ProcessSupervisor.php) class) and opens a connection with it.
- All instructions made on the entrypoint (property read/write, method call, etc…) will be intercepted and send to the default resource set in the connection handler.

## The connection delegate

The connection delegate is required and its main tasks are:

- defining the default resource;
- executing the instructions;
- handling errors.

### The default resource

The default resource is the underlying JavaScript object you will use when calling instructions on the PHP entrypoint.

For example, [PuPHPeteer defines its default resource](https://github.com/rialto-php/puphpeteer/blob/f9a9c17d62076e5e5652df38d38fe26fc565b6f8/src/PuppeteerConnectionDelegate.js#L31) with the result of `require('puppeteer')`:

```js
async handleInstruction(instruction, responseHandler, errorHandler) {
    const puppeteer = require('puppeteer')
    instruction.setDefaultResource(puppeteer)
    
    // ...
}
```

That means, when you instanciate the `Nesk\Puphpeteer\Puppeteer` class and call a method on it, on the JS side the method will be called on the default resource.

When you write:

```php
$puppeteer = new Nesk\Puphpeteer\Puppeteer;
$puppeteer->launch();
```

Node will execute:

```js
const puppeteer = require('puppeteer')
puppeteer.launch()
```

### Instruction execution and error handling

Instructions are not automatically executed by Rialto, instead it provides the necessary objects to let the connection delegate execute the instructions the way it wants. For example, [here's how PuPHPeteer implements this whole process](https://github.com/rialto-php/puphpeteer/blob/f9a9c17d62076e5e5652df38d38fe26fc565b6f8/src/PuppeteerConnectionDelegate.js#L35-L43):

```js
async handleInstruction(instruction, responseHandler, errorHandler) {
    // ...

    try {
        // The "instruction" object has a simple "execute()" method which will run the code sent by PHP.
        value = await instruction.execute()
    } catch (error) {
        // We always catch the errors, however we rethrow them if the code
        // sent by PHP doesn't explicitly require the errors to be catched.
        // See: https://github.com/rialto-php/rialto/blob/3f3420ad/docs/api.md#node-errors
        if (instruction.shouldCatchErrors()) {
            return errorHandler(error)
        }

        throw error
    }

    responseHandler(value)
}
```

### Other usages 

The connection delegate can also be used to track some resources for various tasks. For example, PuPHPeteer tracks [the `Page` objects](https://pptr.dev/#?product=Puppeteer&version=v5.3.1&show=api-class-page) to [log the console messages](https://github.com/rialto-php/puphpeteer/blob/f9a9c17d62076e5e5652df38d38fe26fc565b6f8/src/PuppeteerConnectionDelegate.js#L54-L56) and output them in the PHP logger [if the user asked for it](https://github.com/rialto-php/puphpeteer/tree/f9a9c17d62076e5e5652df38d38fe26fc565b6f8#puppeteers-class-must-be-instantiated).

## Instruction flow

When you execute an instruction on the PHP side, it will be:

- intercepted by the [`CommunicatesWithProcessSupervisor` trait](https://github.com/rialto-php/rialto/blob/df5a6b1b2c15a742773f48baaf1ac763664591de/src/Traits/CommunicatesWithProcessSupervisor.php);
- converted to a [PHP `Instruction`](https://github.com/rialto-php/rialto/blob/df5a6b1b2c15a742773f48baaf1ac763664591de/src/Instruction.php#L10) and serialized;
- sent to the Node process;
- [unserialized](https://github.com/rialto-php/rialto/blob/df5a6b1b2c15a742773f48baaf1ac763664591de/src/node-process/Data/Unserializer.js) and converted to a [JS `Instruction`](https://github.com/rialto-php/rialto/blob/df5a6b1b2c15a742773f48baaf1ac763664591de/src/node-process/Instruction.js);
- sent to the connection delegate.

Once the connection delegate produces a return value, it will be:

- [serialized](https://github.com/rialto-php/rialto/blob/df5a6b1b2c15a742773f48baaf1ac763664591de/src/node-process/Data/Serializer.js);
- sent back to the PHP process, which is in a blocking state until a response is provided (or until [`read_timeout`](https://github.com/rialto-php/rialto/blob/df5a6b1b2c15a742773f48baaf1ac763664591de/docs/api.md#options) is reached);
- [unserialized](https://github.com/rialto-php/rialto/blob/df5a6b1b2c15a742773f48baaf1ac763664591de/src/Data/UnserializesData.php);
- provided as the return value of the original instruction.

>describe resources and how they are tracked

# TODO

- instruction flow
- basic resource, specific resources, and resource repository
- php delegate
