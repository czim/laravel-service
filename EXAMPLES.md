# Examples

## Setting service configuration as array

```php

    // Can pass in NULL as the defaults, since they will be set by a config later
    $service = new SoapService(
        null,
        new \Czim\Service\Interpreters\BasicSoapXmlAsArrayInterpreter()
    );

    // You can pass in an array with settings, which will be set as the defaults for the service.
    // The contents of this config will be validated, but unknown keys will be ignored without
    // warning.
    
    $service->config([
        'location' => 'http://some.wsdl.url.com/?WSDL',
        'port'     => 8000,
        'options'  => [
            'trace' => false,
        ],
    ]);


```


## Soap

```php

    $defaults = new \Czim\Service\Requests\ServiceSoapRequestDefaults();

    $defaults->setLocation('http://www.webservicex.net/globalweather.asmx?WSDL');

    $service = new SoapService(
        $defaults,
        new \Czim\Service\Interpreters\BasicSoapXmlAsArrayInterpreter()
    );

    $request = new ServiceSoapRequest();

    $request->setBody([
        'CityName'    => 'Tokyo',
        'CountryName' => 'Japan',
    ]);
    
    $response = $service->call('GetWeather', $request);

```

## Rest

```php

    $defaults = new \Czim\Services\Requests\ServiceRestRequestDefaults();
    $defaults->setLocation('http://localhost')
             ->setPort(1234)
             ->setHttpMethod(\Czim\Service\Services\RestService::METHOD_GET);
    
    $service = new \Czim\Service\Services\RestService($defaults);
    
    $request = new \Czim\Service\Requests\ServiceRestRequest();
    $request->setHttpMethod($service::METHOD_PATCH)
            ->setBody([
                 'name' => 'New Name'
            ]);
    
    $response = $service->call('user/2', $request);
    
```

## File

```php

    $service = new \Czim\Service\Services\FileService(
        null,
        \Czim\Service\Interpreters\BasicRawXmlInterpreter()
    );

    $file = storage_path('some_file_with_xml_content.xml');
    
    $response = $service->call( $file );
        
```

## SSH Files

```php

    // Set up config for SSH connection and files location
    $config = [
        'host'        => 'www.someserver.com',
        'port'        => 22,
        'user'        => 'admin',
        'password'    => 'h0pl0p0p0p@p',
        'fingerprint' => '4E9D000ACB6DC9D683E06EAE26E1DCDE',
        'base_path'   => '/./home/admin/domains/yourdomain.com/public_ftp/',
    ];

    $request = new ServiceSshRequest();

    // Prepare request, set to download all .xml files from a folder and
    // download them to the /tmp dir in Laravels storage path
    $request->setLocation($config['host'])
            ->setPort($config['port'])
            ->setCredentials($config['user'], $config['password'])
            ->setFingerprint($config['fingerprint'])
            ->setPath($config['base_path'] . '/downloads')
            ->setLocalPath(storage_path('tmp'))
            ->setPattern('*.xml');

    $service = new SshFileService(
        null,
        \Czim\Service\Interpreters\BasicRawXmlAsArrayInterpreter()
    );

    $response = $service->call(null, $request);
        
```
