<?php
namespace Czim\Service\Test;

use Czim\Service\Collections\ServiceCollection;
use Czim\Service\Contracts\ServiceInterface;
use Czim\Service\Contracts\ServiceInterpreterInterface;

class ServiceCollectionTest extends TestCase
{

    /**
     * @test
     */
    function it_stores_services()
    {
        $mockServiceA = $this->getMockBuilder(ServiceInterface::class)
                             ->getMock();

        $mockServiceB = $this->getMockBuilder(ServiceInterface::class)
                             ->getMock();

        $collection = new ServiceCollection([ 'a' => $mockServiceA ]);

        $this->assertTrue($collection->has('a'), "Collection should have constructor set service");
        $this->assertSame($mockServiceA, $collection->get('a'));

        $collection->put('b', $mockServiceB);

        $this->assertTrue($collection->has('b'), "Collection should have added service");
        $this->assertSame($mockServiceB, $collection->get('b'));
    }


    /**
     * @test
     */
    function it_throws_an_exception_when_constructing_with_incorrect_content()
    {
        $this->expectException(\Czim\Service\Exceptions\InvalidCollectionContentException::class);

        new ServiceCollection([ 'not_a_service' ]);
    }

    /**
     * @test
     */
    function it_throws_an_exception_when_storing_incorrect_content()
    {
        $this->expectException(\Czim\Service\Exceptions\InvalidCollectionContentException::class);

        $collection = new ServiceCollection();

        $mockObject = $this->getMockBuilder(ServiceInterpreterInterface::class)
                           ->getMock();

        $collection->put('wrong_type', $mockObject);
    }

    /**
     * @test
     */
    function it_throws_an_exception_when_retrieving_unset_service_by_name()
    {
        $this->expectException(\Czim\Service\Exceptions\ServiceNotFoundInCollectionException::class);
        $this->expectErrorMessageMatches('#does_not_exist#');

        $collection = new ServiceCollection();

        $collection->get('does_not_exist');
    }

}
