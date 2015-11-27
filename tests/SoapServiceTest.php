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
        $soapMock = $this->createSimpleSoapMock();

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
     */
    function it_reinitializes_the_soapclient_when_settings_change()
    {
        $interpreter = new TestMockInterpreter();
        $service     = new SoapService(null, $interpreter);
        $request     = new ServiceSoapRequest();

        // set up mock and bind for first call

        $soapMock = $this->createSimpleSoapMock('first call');

        app()->bind(SoapClient::class, function() use ($soapMock) { return $soapMock; });

        $response = $service->call('testMethod', $request);

        $this->assertEquals('first call', $response->getData(), "First call has incorrect response");


        // set up for second call
        // change some non-default-included option, which should trigger re-initialization

        $soapMock = $this->createSimpleSoapMock('second call');

        app()->bind(SoapClient::class, function() use ($soapMock) { return $soapMock; });

        $request->setOptions(['version' => SOAP_1_1]);

        $response = $service->call('testMethod', $request);

        $this->assertEquals('second call', $response->getData(), "Second call has incorrect response");


        // do third call that should NOT change even though the client is rebound again
        // since its settings do not change

        $soapMock = $this->createSimpleSoapMock('third call');

        app()->bind(SoapClient::class, function() use ($soapMock) { return $soapMock; });

        $response = $service->call('testMethod', $request);

        $this->assertEquals('second call', $response->getData(), "Third call should have same response as second");
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


    /**
     * @param string $return
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createSimpleSoapMock($return = 'some test content')
    {
        $soapMock = $this->getMockBuilder(SoapClient::class)
                         ->disableOriginalConstructor()
                         ->getMock();

        $soapMock->expects($this->any())
                 ->method('__call')
                 ->with($this->logicalOr('testMethod', []))
                 ->will($this->returnCallback(
                     function() use ($return) {
                        return $return;
                     }
                 ));

        return $soapMock;
    }
}
