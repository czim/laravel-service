<?php
namespace Czim\Service\Test;

use Czim\Service\ServiceServiceProvider;
use Czim\Service\Test\Helpers\XmlDataProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @var XmlDataProvider
     */
    protected $xml;


    public function setUp(): void
    {
        parent::setUp();

        $this->xml = new XmlDataProvider;
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application   $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app->register(ServiceServiceProvider::class);
    }

}
