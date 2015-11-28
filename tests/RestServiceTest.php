<?php
namespace Czim\Service\Test;

use Czim\DataObject\Test\Helpers\TestMockInterpreter;
use Czim\Service\Requests\ServiceRequest;
use Czim\Service\Requests\ServiceRestRequest;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Services\RestService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\StreamInterface;

class RestServiceTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_mocked_data_as_service_response()
    {
        $guzzleMock = $this->getMockBuilder(Client::class)
                           ->getMock();

        $responseMock = $this->getMockBuilder(Response::class)
                             ->getMock();

        $responseBodyMock = $this->getMockBuilder(StreamInterface::class)
                                 ->getMock();

        $responseBodyMock->method('getContents')->willReturn('some test content');

        $responseMock->method('getStatusCode')->willReturn(200);
        $responseMock->method('getReasonPhrase')->willReturn('OK');
        $responseMock->method('getHeaders')->willReturn([]);
        $responseMock->method('getBody')->willReturn($responseBodyMock);


        $guzzleMock->method('request')
                   ->willReturn($responseMock);

        // mocking through service container because passing it to the
        // constructor makes it 'null' for some glitchy reason
        app()->bind(Client::class, function() use ($guzzleMock) { return $guzzleMock; });

        $interpreter = new TestMockInterpreter();
        $service     = new RestService(null, $interpreter);
        $request     = new ServiceRestRequest();

        $response = $service->call('testing', $request);

        $this->assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        $this->assertEquals('some test content', $response->getData(), "Mocked service should return fixed data");
    }

    /**
     * @test
     * @expectedException \Czim\Service\Exceptions\CouldNotConnectException
     */
    function it_throws_a_normalized_exception_if_guzzle_connect_fails()
    {
        $interpreter = new TestMockInterpreter();
        $service     = new RestService(null, $interpreter);
        $request     = new ServiceRestRequest();

        $service->call('nothing_here', $request);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    function it_throws_an_exception_if_an_incorrect_service_request_is_used()
    {
        $service = new RestService();
        $request = new ServiceRequest();

        $service->call(null, $request);
    }

}
