# Understanding the code architecture behind Rialto

This guide is here to help you understand how Rialto works behind the scene. Before reading it, make sure you understand the basic usage of Rialto [by reading the tutorial](https://github.com/rialto-php/rialto/blob/dev/docs/tutorial.md).

## Process communication

Basically, when you instanciate the entrypoint of a Rialto implementation, a Node process is spawned with some configuration provided by PHP. Once running, the Node process opens a socket on a random port (provided by the operating system) and outputs the port on the stdout stream, which will be retrieved by the PHP process. Once the Node port is retrieved, the PHP process connects to the socket with the provided port. The processes are now communicating!

# TODO

- entrypoint and js delegate
- instruction flow
- resource repository
- php delegate
