# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Support passing Node resources in JS functions

## [1.0.2] - 2018-06-18
### Fixed
- Fix an issue where the socket port couldn't be retrieved

## [1.0.1] - 2018-06-12
### Fixed
- Fix `false` values being parsed as `null` by the unserializer
- Fix Travis tests

## [1.0.0] - 2018-06-05
### Changed
- Change PHP's vendor name from `extractr-io` to `nesk`
- Change NPM's scope name from `@extractr-io` to `@nesk`

## [0.1.2] - 2018-04-09
### Added
- Support PHPUnit v7
- Add Travis integration

### Changed
- Improve the conditions to throw `ReadSocketTimeoutException`

### Fixed
- Support heavy socket payloads containing non-ASCII characters

## [0.1.1] - 2018-01-29
### Fixed
- Fix an issue on an internal type check

## 0.1.0 - 2018-01-29
First release


[Unreleased]: https://github.com/nesk/rialto/compare/1.0.2...HEAD
[1.0.2]: https://github.com/nesk/rialto/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/nesk/rialto/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/nesk/rialto/compare/0.1.2...1.0.0
[0.1.2]: https://github.com/nesk/rialto/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/nesk/rialto/compare/0.1.0...0.1.1
