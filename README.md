# Laravel Service

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.org/czim/laravel-service.svg?branch=master)](https://travis-ci.org/czim/laravel-service)
[![Latest Stable Version](http://img.shields.io/packagist/v/czim/laravel-service.svg)](https://packagist.org/packages/czim/laravel-service)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b916d372-6e69-4179-9e76-513f1ecda9ed/mini.png)](https://insight.sensiolabs.com/projects/b916d372-6e69-4179-9e76-513f1ecda9ed)


Basic framework for making standardized but flexible webservice classes.


## Install

Via Composer

``` bash
$ composer require czim/laravel-service
```


## Requirements

To use the `SshFileService`, you will require the `libssh2` PHP extension.


## Usage

### Services

The basic approach to using a Service in three steps:

1. Instantiate a Service
    - passing in the defaults (*optional*)
    - passing in an interpreter instance (*optional*)
2. Perform a `call()` on the service instance

For a simple SOAP service, this might be as follows:

```php

    // Set up defaults
    $defaults = new \Czim\Service\Requests\ServiceSoapRequestDefaults();

    $defaults->setLocation('http://www.webservicex.net/globalweather.asmx?WSDL')
             ->setOptions([
                 'trace'      => true,
                 'exceptions' => true,
                 'features'   => SOAP_SINGLE_ELEMENT_ARRAYS,
             ]);

    // Instantiate service, with a to-array interpreter
    $service = new SoapService(
        $defaults,
        new \Czim\Service\Interpreters\BasicSoapXmlAsArrayInterpreter()
    );

    // Prepare a specific request
    $request = new ServiceSoapRequest();

    $request->setBody([
        'CityName'    => 'Tokyo',
        'CountryName' => 'Japan',
    ]);
    
    // Perform the call, which will return a ServiceReponse object
    $response = $service->call('GetWeather', $request);

```


#### Available Services

- `RestService`: for HTTP REST requests, requires a `ServiceRestRequest` object for defaults/requests.
    - See the section about RestServices below.
- `RestCurlService`: same as above, but uses cURL directly instead of guzzle
- `SoapService`: for SOAP requests, requires a `ServiceSoapRequest` object for defaults/requests.
    - See the note about BeSimple below.
- `FileService`: for retrieving the contents of a single file, requires a `ServiceSshRequest` object for defaults/requests.
- `MultiFileService`: for retrieving the contents of multiple files and merging their interpretations, requires a `ServiceSshRequest` object for defaults/requests.
- `SshFileService`: like the MultiFileService, but retrieves files by logging into an SSH server. See requirements above.

See this [list of examples](EXAMPLES.md) for more information.


#### RestServices and HTTP methods

One thing to look out for is a potential confusion over the 'method' when using the `RestService`.
The `$method` parameter in the service's `call()` does **not** refer to the HTTP Method (`GET`, `POST`, etc).
For cross-services compatibility purposes it is used to set the URI path instead, since that best corresponds to the 'method' for SOAP calls.
The HTTP Method is to be separately set on the service before making the call, with `setHttpMethod()`.
 
So making a `DELETE` call to `/comments/13` may be set up as follows:

```php
    
    // Set up the default and base URI for the service
    $defaults = new \Czim\Services\Requests\ServiceRestRequestDefaults();
    $defaults->setLocation('http://base.service.url.com/v1');
    $defaults->setHttpMethod($service::METHOD_DELETE);
    
    // Instantiate a new service with the defaults
    $service = new \Czim\Service\Services\RestService($defaults);
    
    // Perform a call to delete comment 13
    $result = $service->call('comments/13');

```

If not set, the default HTTP method is `POST`.

Alternatively, the HTTP method may be configured per-call without affecting the default, by setting it in a `ServiceRequest` parameter:

```php

    // Set up the default and base URI for the service
    $defaults = new \Czim\Services\Requests\ServiceRestRequestDefaults();
    $defaults->setLocation('http://base.service.url.com/v1');
    
    // Instantiate a new service with the defaults
    $service = new \Czim\Service\Services\RestService($defaults);
    
    // Create a request object with an HTTP method
    $request = new \Czim\Service\Requests\ServiceRestRequest();
    $request->setHttpMethod($service::METHOD_DELETE);
    
    // Perform a call to delete comment 13
    $result = $service->call('comments/13', $request);

```

The order of precedence is:

1. HTTP method set in request for call
2. HTTP method set in default request for service
3. HTTP method defined in service directly


### Interpreters

Services always return a `ServiceResponse` for each call.
The content of the response (mainly the data accessible through `getData()`, but also its other properties) is determined by the `ServiceInterpreter` instance set for the service.
ServiceInterpreters are responsible for interpreting and converting the raw data retrieved to whatever (standardized) format and response content you prefer. 

Standard available interpreters:

- `BasicDefaultInterpreter`: sets up response, but does not manipulate the raw data in any way
- `BasicJsonInterpreter`: converts a string with raw JSON to an object or associative array
- `BasicQueryStringInterpreter`: converts a query string to an object or array
- `BasicRawXmlInterpreter`: converts a string with raw XML to SimpleXML objects
- `BasicRawXmlAsArrayInterpreter`: same, but converts to an associative array
- `BasicSoapXmlInterpreter`: makes response for SimpleXML object returned by a SoapClient
- `BasicSoapXmlAsArrayInterpreter`: same, but converts to an associative array


It is recommended to extend these or roll your own interpreter for complex services.
I often build interpreters that use [`czim/laravel-dataobject`](https://github.com/czim/laravel-dataobject) to convert raw data into validatable, specific data objects.
See the [AbstractServiceInterpreter source](https://github.com/czim/laravel-service/blob/master/src/Interpreters/AbstractInterpreter.php) for hints and useful methods.


#### Interpreter Decorators

Some decorators for interpreters are provided:

- `FixXmlNamespacesDecorator`: rewrites relative path namespaces to absolute in raw XML, since some XML may be uninterpretable otherwise (hacky).
- `RemoveXmlNamespacesDecorator`: removes XML namespaces from raw XML entirely (hacky).


They follow the standard decorator pattern for interpreters, so they may be used as follows:

```php

    // Decorate a raw XML interpreter with a namespace removal decorator
    $interpreter = new \Czim\Service\Interpreters\Decorators\RemoveXmlNamespacesDecorator(
        new \Czim\Service\Interpreters\Decorators\BasicRawXmlInterpreter()
    );
    
    // Inject the interpreter into a new service
    $service = new \Czim\Service\Services\FileService(null, $interpreter);

```

The `AbstractValidationPreDecorator` may be extended to easily set up validation of raw data before the decorated interpreter does its business.
Likewise the `AbstractValidationPostDecorator` may be extended to validate the `ServiceResponse` object returned *after* the interpreter has treated the response.


### Service Collection

By way of wrapping multiple services, this package offers a `ServiceCollection`.
This is a simple extension of the `Illuminate\Support\Collection`, which may only be used to store `ServiceInterface` objects.

You can set one up just as you would a normal Collection:

```php

    $services = new \Czim\Service\Collections\ServiceCollection();
    
    // Adding services
    $services->put('service name', $service);

    // Performing calls on a service 
    $service->get('service name')
            ->call('someMethod', [ 'param' => 'value' ]);

```

Note that calling `get()` on the collection for a non-existant service will throw an exception; as will trying to store something other than a `ServiceInterface` in it.


## Notes

### BeSimple SoapClient

The `BeSimpleSoapService` service class is set up to use the BeSimple SoapClient.

The only thing required to use it is to include the package:

```
composer require "besimple/soap-client"
```

### DOMDocument parsing as an alternative to SimpleXml

When using the `DomDocumentBasedXmlParser`, note that this will not return a *SimpleXml*-type object, but a `DOMElement`.
To sensibly use this, convert it to an array using the `DomObjectToArrayConverter`.
This means that when rebinding or injecting one of these, treat them as a pair. 

Note that the DOMDocument method is slower and uses significantly more memory. If you have no specific reason to use this, stick to the default.


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
