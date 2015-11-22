# Laravel Service

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.org/czim/laravel-service.svg?branch=master)](https://travis-ci.org/czim/laravel-service)
[![Latest Stable Version](http://img.shields.io/packagist/v/czim/laravel-service.svg)](https://packagist.org/packages/czim/laravel-service)

Basic framework for making standardized but flexible webservice classes.


## Install

Via Composer

``` bash
$ composer require czim/laravel-service
```

## Usage

... 

## To Do

- make it easy to set up loggers (before/after)
    - make it easier by providing some extensions in the package that will log with some log configuration
- make it easy to 'prepareResponse' through a standard interface
    - so we can fix broken XML before interpreting it
    - this should be part of the interpreters! keep the services themselves clean
        - make interpreters get injectable pre-interpreter array
        - and parse response through the array before interpreting (abstract class)

### Consider:

- authorisation errors as CouldNotAuthorizeException?


## Notes

When using the `DomDocumentBasedXmlParser`, note that this will not return a *SimpleXml*-type object, but a `DOMElement`.
To sensibly use this, convert it to an array using the `DomObjectToArrayConverter`.
This means that when rebinding or injecting one of these, treat them as a pair. 

## Requirements

To use the `SshFileService`, you will require the `libssh2` PHP extension.


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Credits

- [Coen Zimmerman][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/czim/laravel-service.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/czim/laravel-service.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/czim/laravel-service
[link-downloads]: https://packagist.org/packages/czim/laravel-service
[link-author]: https://github.com/czim
[link-contributors]: ../../contributors
