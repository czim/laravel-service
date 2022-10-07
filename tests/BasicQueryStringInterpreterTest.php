<?php

declare(strict_types=1);

namespace Czim\Service\Test;

use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Interpreters\BasicQueryStringInterpreter;
use Czim\Service\Responses\ServiceResponse;

class BasicQueryStringInterpreterTest extends TestCase
{
    /**
     * @test
     */
    function it_decodes_a_query_string_as_array()
    {
        $interpreter = new BasicQueryStringInterpreter(true);

        $mockRequest = $this->getMockBuilder(ServiceRequestInterface::class)
            ->getMock();

        /** @var ServiceRequestInterface $mockRequest */
        $result = $interpreter->interpret($mockRequest, 'test=1&tosti[0]=piet&tosti[1]=paaltjens&taster[test]=tosti');

        static::assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        static::assertEquals(
            [ 'test' => '1', 'tosti' => [ 'piet', 'paaltjens' ], 'taster' => [ 'test' => 'tosti' ] ],
            $result->getData(),
            "Incorrect parsed data"
        );
    }

    /**
     * @test
     */
    function it_decodes_a_query_string_as_object()
    {
        $interpreter = new BasicQueryStringInterpreter(false);

        $mockRequest = $this->getMockBuilder(ServiceRequestInterface::class)
            ->getMock();

        /** @var ServiceRequestInterface $mockRequest */
        $result = $interpreter->interpret($mockRequest, 'test=1&tosti[0]=piet&tosti[1]=paaltjens&taster[test]=tosti');

        static::assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        static::assertIsObject($result->getData(), "Incorrect json-decoded data: should be an object");
        static::assertArraySubset(
            [ 'test' => '1', 'tosti' => [ 'piet', 'paaltjens' ], 'taster' => [ 'test' => 'tosti' ] ],
            (array) $result->getData(),
            false,
            "Incorrect parsed data"
        );
    }
}
