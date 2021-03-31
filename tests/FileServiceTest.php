<?php
namespace Czim\Service\Test;

use Czim\DataObject\Test\Helpers\TestMockInterpreter;
use Czim\Service\Requests\ServiceRequest;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Services\FileService;
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

        $this->assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        $this->assertEquals('some test content', $response->getData(), "Mocked service should return fixed data");
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

        $this->assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        $this->assertRegExp(
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
<<<<<<< Updated upstream
        $this->expectExceptionMessageRegExp('#test_this_does_not_exist\.txt#i');
=======
        $this->expectErrorMessageMatches('#test_this_does_not_exist\.txt#i');
>>>>>>> Stashed changes

        $interpreter = new TestMockInterpreter();
        $service     = new FileService(null, $interpreter);
        $request     = new ServiceRequest();

        $service->call('tests/data/test_this_does_not_exist.txt', $request);
    }

}
