<?php
namespace Czim\Service\Test;

use Czim\Service\Contracts\ServiceResponseInterface;
use Czim\Service\Responses\Mergers\ResponseMerger;
use Czim\Service\Responses\ServiceResponse;

class ResponseMergerTest extends TestCase
{

    /**
     * @test
     */
    function it_merges_an_array_of_service_responses_into_one()
    {
        $merger = new ResponseMerger();

        // only mock additional response, first response is
        // used by the merger to combine everything in (for efficiency)
        // so we cannot use a mock to test the outcome

        $responseA = new ServiceResponse([ 'data' => 'first response' ]);

        $mockResponseB = $this->getMockBuilder(ServiceResponseInterface::class)
                              ->getMock();

        $mockResponseB->method('getData')
                      ->willReturn('second response');

        $result = $merger->merge([ $responseA, $mockResponseB ]);

        $this->assertInstanceOf(ServiceResponseInterface::class, $result, "Incorrect type for response");
        $this->assertEquals(
            [ 'first response', 'second response' ],
            $result->getData(),
            "Response data not correctly merged"
        );
    }

    /**
     * @test
     */
    function it_returns_the_response_unaltered_if_there_is_only_one()
    {
        $merger = new ResponseMerger();

        $responseA = new ServiceResponse([ 'data' => 'only response' ]);

        $result = $merger->merge([ $responseA ]);

        $this->assertSame($responseA, $result, "Response should use the same object reference");
    }

}
