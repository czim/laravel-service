<?php

declare(strict_types=1);

namespace Czim\Service\Test;

use Czim\Service\Contracts\SoapFactoryInterface;
use Czim\Service\Requests\ServiceRequest;
use Czim\Service\Requests\ServiceSoapRequest;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Services\SoapService;
use Czim\Service\Test\Helpers\TestMockInterpreter;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use SoapClient;

class SoapServiceTest extends TestCase
{
    /**
     * @test
     */
    function it_returns_mocked_data_as_service_response()
    {
        $soapMock = $this->createSimpleSoapMock();
        $factoryMock = $this->createMockSoapFactory($soapMock);

        app()->instance(SoapFactoryInterface::class, $factoryMock);

        $interpreter = new TestMockInterpreter();
        $service     = new SoapService(null, $interpreter);
        $request     = new ServiceSoapRequest();

        $response = $service->call('testMethod', $request);

        static::assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        static::assertEquals('some test content', $response->getData(), "Mocked service should return fixed data");
    }

    /**
     * @test
     */
    function it_reinitializes_the_soapclient_when_settings_change()
    {
        $interpreter = new TestMockInterpreter();
        $service     = new SoapService(null, $interpreter);
        $request     = new ServiceSoapRequest();

        // set up mock and bind for first call

        $soapMock = $this->createSimpleSoapMock('first call');
        $factoryMock = $this->createMockSoapFactory($soapMock);

        app()->instance(SoapFactoryInterface::class, $factoryMock);

        $response = $service->call('testMethod', $request);

        static::assertEquals('first call', $response->getData(), "First call has incorrect response");


        // set up for second call
        // change some non-default-included option, which should trigger re-initialization

        $soapMock = $this->createSimpleSoapMock('second call');
        $factoryMock = $this->createMockSoapFactory($soapMock);

        app()->instance(SoapFactoryInterface::class, $factoryMock);

        $request->setOptions(['version' => SOAP_1_1]);

        $response = $service->call('testMethod', $request);

        static::assertEquals('second call', $response->getData(), "Second call has incorrect response");


        // do third call that should NOT change even though the client is rebound again
        // since its settings do not change

        $soapMock = $this->createSimpleSoapMock('third call');
        $factoryMock = $this->createMockSoapFactory($soapMock);

        app()->instance(SoapFactoryInterface::class, $factoryMock);

        $response = $service->call('testMethod', $request);

        static::assertEquals('second call', $response->getData(), "Third call should have same response as second");
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_an_incorrect_service_request_is_used()
    {
        $this->expectException(InvalidArgumentException::class);

        $service = new SoapService();
        $request = new ServiceRequest();

        $service->call(null, $request);
    }

    /**
     * @param SoapClient $client
     * @return SoapFactoryInterface&MockObject
     */
    protected function createMockSoapFactory(SoapClient $client): MockObject
    {
        $factoryMock = $this->getMockBuilder(SoapFactoryInterface::class)
            ->getMock();

        $factoryMock->method('make')->willReturn($client);

        return $factoryMock;
    }

    /**
     * @param string $return
     * @return SoapClient&MockObject
     */
    protected function createSimpleSoapMock(string $return = 'some test content'): MockObject
    {
        $soapMock = $this->getMockBuilder(SoapClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $soapMock->expects($this->any())
            ->method('__call')
            ->with($this->logicalOr('testMethod', []))
            ->will($this->returnCallback(fn (): string => $return));

        return $soapMock;
    }
}
