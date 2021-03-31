<?php

namespace Czim\Service\Providers;

use Czim\Service\Collections\ServiceCollection;
use Czim\Service\Contracts\GuzzleFactoryInterface;
use Czim\Service\Contracts\ResponseMergerInterface;
use Czim\Service\Contracts\ServiceCollectionInterface;
use Czim\Service\Contracts\SoapFactoryInterface;
use Czim\Service\Contracts\Ssh2SftpConnectionFactoryInterface;
use Czim\Service\Contracts\XmlObjectConverterInterface;
use Czim\Service\Contracts\XmlParserInterface;
use Czim\Service\Factories\GuzzleFactory;
use Czim\Service\Factories\SoapFactory;
use Czim\Service\Factories\Ssh2SftpConnectionFactory;
use Czim\Service\Interpreters\Xml\SimpleXmlParser;
use Czim\Service\Interpreters\Xml\XmlObjectToArrayConverter;
use Czim\Service\Responses\Mergers\ResponseMerger;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ServiceCollectionInterface::class, ServiceCollection::class);
        $this->app->bind(ResponseMergerInterface::class, ResponseMerger::class);
        $this->app->bind(XmlParserInterface::class, SimpleXmlParser::class);
        $this->app->bind(XmlObjectConverterInterface::class, XmlObjectToArrayConverter::class);
        $this->app->bind(GuzzleFactoryInterface::class, GuzzleFactory::class);
        $this->app->bind(SoapFactoryInterface::class, SoapFactory::class);
        $this->app->bind(Ssh2SftpConnectionFactoryInterface::class, Ssh2SftpConnectionFactory::class);
    }
}
