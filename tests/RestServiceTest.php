<?php

declare(strict_types=1);

namespace Czim\Service\Test;

use Czim\Service\Contracts\GuzzleFactoryInterface;
use Czim\Service\Exceptions\ServiceConfigurationException;
use Czim\Service\Requests\ServiceRequest;
use Czim\Service\Requests\ServiceRestRequest;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Services\RestService;
use Czim\Service\Test\Helpers\TestMockInterpreter;
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

        $factoryMock = $this->getMockBuilder(GuzzleFactoryInterface::class)
            ->getMock();

        $factoryMock->method('make')->willReturn($guzzleMock);

        app()->instance(GuzzleFactoryInterface::class, $factoryMock);

        $interpreter = new TestMockInterpreter();
        $service     = new RestService(null, $interpreter);
        $request     = new ServiceRestRequest();

        $response = $service->call('testing', $request);

        static::assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        static::assertEquals('some test content', $response->getData(), "Mocked service should return fixed data");
    }

    /**
     * @test
     */
    function it_throws_a_normalized_exception_if_guzzle_connect_fails()
    {
        $this->expectException(\Czim\Service\Exceptions\CouldNotConnectException::class);

        $interpreter = new TestMockInterpreter();
        $service     = new RestService(null, $interpreter);
        $request     = new ServiceRestRequest();

        $service->call('nothing_here', $request);
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_an_incorrect_service_request_is_used()
    {
        $this->expectException(\InvalidArgumentException::class);

        $service = new RestService();
        $request = new ServiceRequest();

        $service->call(null, $request);
    }

    /**
     * @test
     */
    function it_takes_an_array_config_and_stores_its_contents_as_defaults()
    {
        $service = new RestService();

        $service->config([
            // standard config
            'location'    => 'http://test.com',
            'port'        => 1234,
            'credentials' => [
                'name'     => 'piet',
                'password' => 'paaltjens',
            ],
            'method'      => 'comment/11',
            'headers'     => ['some' => 'header'],
            'parameters'  => ['some' => 'parameter'],
            'options'     => ['some' => 'option'],
            'body'        => 'test',

            // specific for rest
            'http_method' => 'DELETE',
        ]);

        $defaults = $service->getRequestDefaults();

        static::assertEquals('http://test.com', $defaults->getLocation());
        static::assertEquals(1234, $defaults->getPort());
        static::assertEquals('comment/11', $defaults->getMethod());
        static::assertEquals('test', $defaults->getBody());

        static::assertEquals(['some' => 'header'], $defaults->getHeaders());
        static::assertEquals(['some' => 'parameter'], $defaults->getParameters());
        static::assertEquals(['some' => 'option'], $defaults->getOptions());
        static::assertEquals([
            'name'     => 'piet',
            'password' => 'paaltjens',
            'domain'   => null,
        ], $defaults->getCredentials());

        static::assertEquals('DELETE', $defaults['http_method']);
    }

    /**
     * @test
     */
    function it_throws_an_exception_on_invalid_config()
    {
        $service = new RestService();

        try {
            $service->config([
                'location'    => true,
                'port'        => 'not a port',
                'credentials' => [
                    'name'     => ['not a string'],
                    'password' => false,
                ],
                'method'      => ['not a string'],
                'headers'     => 'not an array',
                'parameters'  => 'not an array',
                'options'     => 'not an array',

                'http_method' => 'FALSE_METHOD',
            ]);

            $this->fail('Expecting ServiceConfigurationException');
        } catch (ServiceConfigurationException $e) {
            $errors = $e->getErrors();

            $keys = [
                'location',
                'port',
                'credentials.name',
                'credentials.password',
                'method',
                'headers',
                'parameters',
                'options',
                'http_method',
            ];

            foreach ($keys as $key) {
                static::assertArrayHasKey($key, $errors, 'Missing validation error for: ' . $key);
            }
        }
    }
}
