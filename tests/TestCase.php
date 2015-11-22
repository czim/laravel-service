<?php
namespace Czim\Service\Test;

use Czim\Service\ServiceServiceProvider;
use Czim\Service\Test\Helpers\XmlDataProvider;
use Mockery;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @var XmlDataProvider
     */
    protected $xml;


    public function setUp()
    {
        parent::setUp();

        $this->xml = new XmlDataProvider();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application   $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app->register(ServiceServiceProvider::class);
    }

}
