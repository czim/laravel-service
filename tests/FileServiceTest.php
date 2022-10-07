<?php

declare(strict_types=1);

namespace Czim\Service\Test;

use Czim\Service\Requests\ServiceRequest;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Services\FileService;
use Czim\Service\Test\Helpers\TestMockInterpreter;
use Illuminate\Filesystem\Filesystem;

class FileServiceTest extends TestCase
{
    /**
     * @test
     */
    function it_returns_mocked_data_as_service_response()
    {
        $filesMock = $this->getMockBuilder(Filesystem::class)
            ->getMock();

        $filesMock->method('get')
            ->willReturn('some test content');

        $interpreter = new TestMockInterpreter();
        $service     = new FileService($filesMock, $interpreter);
        $request     = new ServiceRequest();

        $response = $service->call('does not matter', $request);

        static::assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        static::assertEquals('some test content', $response->getData(), "Mocked service should return fixed data");
    }

    /**
     * @test
     */
    function it_returns_file_data_as_service_response()
    {
        $interpreter = new TestMockInterpreter();
        $service     = new FileService(null, $interpreter);
        $request     = new ServiceRequest();

        $response = $service->call('tests/data/test.txt', $request);

        static::assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        static::assertMatchesRegularExpression(
            '#\s*testing content in here\s*#',
            $response->getData(),
            "File service should return data from test.txt"
        );
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_the_file_was_not_found()
    {
        $this->expectException(\Czim\Service\Exceptions\CouldNotConnectException::class);
        $this->expectErrorMessageMatches('#test_this_does_not_exist\.txt#i');

        $interpreter = new TestMockInterpreter();
        $service     = new FileService(null, $interpreter);
        $request     = new ServiceRequest();

        $service->call('tests/data/test_this_does_not_exist.txt', $request);
    }
}
