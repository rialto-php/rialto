# Understanding the code architecture behind Rialto

This guide is here to help you understand how Rialto works behind the scene. Before reading it, make sure you understand the basic usage of Rialto [by reading the tutorial](https://github.com/rialto-php/rialto/blob/dev/docs/tutorial.md).

## Process communication

Basically, when you instanciate the entrypoint of a Rialto implementation, a Node process is spawned with some configuration provided by PHP. Once running, the Node process opens a socket on a random port (provided by the operating system) and outputs the port on the stdout stream, which will be retrieved by the PHP process. Once the Node port is retrieved, the PHP process connects to the socket with the provided port. The processes are now communicating!

## The entrypoint

When you write a Rialto implementation, you start by creating an entrypoint, it's a simple PHP class inheriting the [`AbstractEntryPoint`](https://github.com/rialto-php/rialto/blob/architecture-guide/src/AbstractEntryPoint.php) class. It has 2 roles:

- Once instanciated, it starts the Node process (via the [`ProcessSupervisor`](https://github.com/rialto-php/rialto/blob/architecture-guide/src/ProcessSupervisor.php) class) and opens a connection with it.
- All instructions made on the entrypoint (property read/write, method call, etc…) will be intercepted and send to the default resource set in the connection handler.

## The connection delegate

The connection delegate is required and its main task is to define the default resource used by Rialto.

### The default resource

The default resource is the underlying JavaScript object you will use when calling instructions on the PHP entrypoint.

For example, [PuPHPeteer defines its default resource](https://github.com/rialto-php/puphpeteer/blob/f9a9c17d62076e5e5652df38d38fe26fc565b6f8/src/PuppeteerConnectionDelegate.js#L31) with the result of `require('puppeteer')`:

```js
const puppeteer = require('puppeteer')
instruction.setDefaultResource(puppeteer)
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

### Other usages 

- useful things, like closing the puppeteer browsers

## Instruction flow

- describe basic resources and the way they are used in the whole communication

# TODO

- instruction flow
- basic resource, specific resources, and resource repository
- php delegate
