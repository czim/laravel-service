<?php
namespace Czim\Service\Test;

use Czim\DataObject\Test\Helpers\TestMockInterpreter;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Interpreters\BasicJsonInterpreter;
use Czim\Service\Requests\ServiceRequest;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Services\FileService;
use Illuminate\Filesystem\Filesystem;

class BasicJsonInterpreterTest extends TestCase
{

    /**
     * @test
     */
    function it_decodes_json_data_as_array()
    {
        $interpreter = new BasicJsonInterpreter(true);

        $mockRequest = $this->getMockBuilder(ServiceRequestInterface::class)
                            ->getMock();

        /** @var ServiceRequestInterface $mockRequest */
        $result = $interpreter->interpret($mockRequest, '{"test":"data","does":"it work?"}');

        $this->assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        $this->assertArraySubset(['test' => 'data', 'does' => 'it work?' ], $result->getData(), "Incorrect json-decoded data");
    }

    /**
     * @test
     */
    function it_decodes_json_data_as_object()
    {
        $interpreter = new BasicJsonInterpreter(false);

        $mockRequest = $this->getMockBuilder(ServiceRequestInterface::class)
                            ->getMock();

        /** @var ServiceRequestInterface $mockRequest */
        $result = $interpreter->interpret($mockRequest, '{"test":"data","does":"it work?"}');

        $this->assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        $this->assertInternalType('object', $result->getData(), "Incorrect json-decoded data: should be an object");
        $this->assertArraySubset(['test' => 'data', 'does' => 'it work?' ], (array) $result->getData(), "Incorrect json-decoded data");
    }
}
