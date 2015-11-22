<?php
namespace Czim\Service\Test;

use Czim\DataObject\Test\Helpers\TestMockInterpreter;
use Czim\Service\Requests\ServiceRequest;
use Czim\Service\Requests\ServiceSoapRequest;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Services\SoapService;
use SoapClient;

class SoapServiceTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_mocked_data_as_service_response()
    {
        $soapMock = $this->getMockBuilder(SoapClient::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        //$soapMock->method('testMethod')->willReturn('some test content');
        $soapMock->expects($this->any())
                 ->method('__call')
                 ->with($this->logicalOr('testMethod', []))
                 ->will($this->returnCallback(function() {
                     return 'some test content';
                 }));

        app()->bind(SoapClient::class, function() use ($soapMock) { return $soapMock; });

        $interpreter = new TestMockInterpreter();
        $service     = new SoapService(null, $interpreter);
        $request     = new ServiceSoapRequest();

        $response = $service->call('testMethod', $request);

        $this->assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        $this->assertEquals('some test content', $response->getData(), "Mocked service should return fixed data");
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    function it_throws_an_exception_if_an_incorrect_service_request_is_used()
    {
        $service = new SoapService();
        $request = new ServiceRequest();

        $service->call(null, $request);
    }

    /**
     * Disabled this test to prevent unstoppable binding resolution fatal error
     *
     * @test
     * @expectedException \Czim\Service\Exceptions\CouldNotConnectException
     */
    //function it_throws_a_normalized_exception_if_soap_connect_fails()
    //{
    //    app()->bind(SoapClient::class, function($app, $parameters) {
    //
    //        return @new SoapClient($parameters[0], $parameters[1]);
    //    });
    //
    //    $interpreter = new TestMockInterpreter();
    //    $service     = new SoapService(null, $interpreter);
    //    $request     = new ServiceSoapRequest();
    //
    //    $request->setLocation('http://does_not_exist_anywhere.org/?WSDL');
    //    $request->setOptions(['trace' => false]);
    //
    //    $service->call('nothing_here', $request);
    //}

}
