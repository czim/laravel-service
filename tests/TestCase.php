<?php

declare(strict_types=1);

namespace Czim\Service\Test;

use ArrayAccess;
use Czim\Service\Providers\ServiceServiceProvider;
use Czim\Service\Test\Helpers\XmlDataProvider;
use Illuminate\Foundation\Application;
use Illuminate\Testing\Constraints\ArraySubset;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected ?XmlDataProvider $xml = null;


    public function setUp(): void
    {
        parent::setUp();

        $this->xml = new XmlDataProvider();
    }

    /**
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app->register(ServiceServiceProvider::class);
    }

    /**
     * Asserts that an array has a specified subset.
     *
     * @param array|ArrayAccess|mixed[] $subset
     * @param array|ArrayAccess|mixed[] $array
     * @param bool                      $checkForObjectIdentity
     * @param string                    $message
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public static function assertArraySubset(
        mixed $subset,
        mixed $array,
        bool $checkForObjectIdentity = false,
        string $message = '',
    ): void {
        if (! (is_array($subset) || $subset instanceof ArrayAccess)) {
            throw InvalidArgumentException::create(
                1,
                'array or ArrayAccess'
            );
        }
        if (! (is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentException::create(
                2,
                'array or ArrayAccess'
            );
        }
        $constraint = new ArraySubset($subset, $checkForObjectIdentity);

        Assert::assertThat($array, $constraint, $message);
    }
}
