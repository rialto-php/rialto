# Rialto

[![PHP Version](https://img.shields.io/packagist/php-v/extractr-io/rialto.svg?style=flat-square)](http://php.net/)
[![Composer Version](https://img.shields.io/packagist/v/extractr-io/rialto.svg?style=flat-square&label=Composer)](https://packagist.org/packages/extractr-io/rialto)
[![Node Version](https://img.shields.io/node/v/@extractr-io/rialto.svg?style=flat-square&label=Node)](https://nodejs.org/)
[![NPM Version](https://img.shields.io/npm/v/@extractr-io/rialto.svg?style=flat-square&label=NPM)](https://www.npmjs.com/package/@extractr-io/rialto)
[![Build Status](https://img.shields.io/travis/extractr-io/rialto.svg?style=flat-square&label=Build%20Status)](https://travis-ci.org/extractr-io/rialto)

A package to manage Node resources from PHP. It can be used to create bridges to interact with Node librairies in PHP, like [PuPHPeteer](https://github.com/extractr-io/puphpeteer/).

It works by creating a Node process and communicates with it through sockets.

## Requirements and installation

Rialto requires PHP >= 7.1 and Node >= 8.

Install it in your project:

```shell
composer require extractr-io/rialto
npm install @extractr-io/rialto
```

## Usage

See our tutorial to [create your first bridge with Rialto](docs/tutorial.md).

An [API documentation](docs/api.md) is also available.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
