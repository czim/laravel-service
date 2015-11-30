<?php
namespace Czim\Service\Test;

use Czim\DataObject\Test\Helpers\TestMockInterpreter;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Exceptions\CouldNotValidateResponseException;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Test\Helpers\TestPostValidator;

class ValidationPostDecoratorTest extends TestCase
{

    /**
     * @test
     */
    function it_allows_valid_data_after_interpreting()
    {
        $decorator = new TestPostValidator( new TestMockInterpreter() );

        $mockRequest = $this->getMockBuilder(ServiceRequestInterface::class)
                            ->getMock();

        /** @var ServiceRequestInterface $mockRequest */
        $result = $decorator->interpret($mockRequest, 'correct data');

        $this->assertInstanceOf(ServiceResponse::class, $result, "Interpreter should return ServiceResponse object");
        $this->assertEquals('correct data', $result->getData(), "Incorrect returned data");
    }

    /**
     * @test
     */
    function it_throws_an_exception_on_invalid_data_after_interpreting()
    {
        $decorator = new TestPostValidator( new TestMockInterpreter() );

        $mockRequest = $this->getMockBuilder(ServiceRequestInterface::class)
                            ->getMock();

        try {

            /** @var ServiceRequestInterface $mockRequest */
            $decorator->interpret($mockRequest, 'wrong');

            $this->fail('Expecting CouldNotValidateResponseException');

        } catch (CouldNotValidateResponseException $e) {

            // check if errors are present
            $this->assertArraySubset([ 'wrong response' ], $e->getErrors(), "Errors not present in exception instance");
        }
    }

}
