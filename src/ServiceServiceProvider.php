<?php
namespace Czim\Service;

use Czim\Service\Collections\ServiceCollection;
use Czim\Service\Contracts\ResponseMergerInterface;
use Czim\Service\Contracts\ServiceCollectionInterface;
use Czim\Service\Contracts\Ssh2SftpConnectionInterface;
use Czim\Service\Contracts\XmlObjectConverterInterface;
use Czim\Service\Contracts\XmlParserInterface;
use Czim\Service\Interpreters\Xml\SimpleXmlParser;
use Czim\Service\Interpreters\Xml\XmlObjectToArrayConverter;
use Czim\Service\Responses\Mergers\ResponseMerger;
use Czim\Service\Services\Ssh\Ssh2SftpConnection;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }

    public function register()
    {

        $this->app->bind(ServiceCollectionInterface::class, ServiceCollection::class);

        $this->app->bind(ResponseMergerInterface::class, ResponseMerger::class);

        $this->app->bind(XmlParserInterface::class, SimpleXmlParser::class);
        $this->app->bind(XmlObjectConverterInterface::class, XmlObjectToArrayConverter::class);


        // add bindings for SSH2 / SFTP services

        $this->app->bind(Ssh2SftpConnectionInterface::class, function (Container $app, array $parameters) {

            $host        = $parameters[0];
            $user        = $parameters[1];
            $password    = $parameters[2];
            $port        = isset($parameters[3]) ? $parameters[3] : 22;
            $fingerprint = isset($parameters[4]) ? $parameters[4] : null;

            $filesystem  = isset($parameters[5]) ? $parameters[5] : $app->make('files');

            return new Ssh2SftpConnection($host, $user, $password, $port, $fingerprint, $filesystem);
        });
    }

}
