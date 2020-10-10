# Understanding the code architecture behind Rialto

This guide is here to help you understand how Rialto works behind the scene. Before reading it, make sure you understand the basic usage of Rialto [by reading the tutorial](https://github.com/rialto-php/rialto/blob/dev/docs/tutorial.md).

## Process communication

Basically, when you instanciate the entrypoint of a Rialto implementation, a Node process is spawned with some configuration provided by PHP. Once running, the Node process opens a socket on a random port (provided by the operating system) and outputs the port on the stdout stream, which will be retrieved by the PHP process. Once the Node port is retrieved, the PHP process connects to the socket with the provided port. The processes are now communicating!

## The entrypoint

When you write a Rialto implementation, you start by creating an entrypoint, it's a simple PHP class inheriting the [`AbstractEntryPoint`](https://github.com/rialto-php/rialto/blob/architecture-guide/src/AbstractEntryPoint.php) class. It has 2 roles:

- Once instanciated, it starts the Node process (via the [`ProcessSupervisor`](https://github.com/rialto-php/rialto/blob/architecture-guide/src/ProcessSupervisor.php) class) and opens a connection with it.
- All instructions made on the entrypoint (property read/write, method call, etc…) will be intercepted and send to the default resource set in the connection handler.

## The connection delegate

- default resource

# TODO

- instruction flow
- basic resource, specific resources, and resource repository
- php delegate
