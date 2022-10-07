<?php

declare(strict_types=1);

namespace Czim\Service\Test;

use Czim\Service\Exceptions\EmptyRetrievedDataException;
use Czim\Service\Exceptions\ServiceConfigurationException;
use Czim\Service\Requests\ServiceRequest;
use Czim\Service\Requests\ServiceSshRequest;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Services\MultiFileService;
use Czim\Service\Test\Helpers\TestMockInterpreter;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

class MultiFileServiceTest extends TestCase
{
    /**
     * @test
     */
    function it_returns_mocked_data_as_service_response()
    {
        $filesMock = $this->getMockBuilder(Filesystem::class)
            ->getMock();

        $filesMock->method('get')
            ->will(
                $this->returnCallback(
                    fn ($file) => $file == 'test1.txt' ? 'some test content' : 'some more test content'
                )
            );

        $filesMock->method('files')
            ->willReturn(['test1.txt', 'test2.txt']);

        // mocking through service container because passing it to the
        // constructor makes it 'null' for some glitchy reason
        app()->bind(Filesystem::class, fn () => $filesMock);

        $interpreter = new TestMockInterpreter();
        $service     = new MultiFileService(null, $interpreter);
        $request     = new ServiceSshRequest();

        $response = $service->call(null, $request);

        static::assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        static::assertEquals(
            ['some test content', 'some more test content'],
            $response->getData(),
            "Mocked service should return fixed data"
        );
    }

    /**
     * @test
     */
    function it_returns_data_for_a_specific_file_if_method_is_set()
    {
        $filesMock = $this->getMockBuilder(Filesystem::class)
            ->getMock();

        $filesMock->method('get')
            ->will(
                $this->returnCallback(
                    fn ($file) => $file == 'test1.txt' ? 'some test content' : 'some more test content'
                )
            );

        $filesMock->method('files')
            ->willReturn(['test1.txt', 'test2.txt']);

        // mocking through service container because passing it to the
        // constructor makes it 'null' for some glitchy reason
        app()->bind(Filesystem::class, fn () => $filesMock);

        $interpreter = new TestMockInterpreter();
        $service     = new MultiFileService(null, $interpreter);
        $request     = new ServiceSshRequest();

        $response = $service->call('test2.txt', $request);

        static::assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        static::assertEquals(
            'some more test content',
            $response->getData(),
            "Mocked service should return fixed data for just one file"
        );
    }

    /**
     * @test
     */
    function it_returns_combined_file_data_as_service_response()
    {
        $interpreter = new TestMockInterpreter();
        $service     = new MultiFileService(null, $interpreter);
        $request     = new ServiceSshRequest();

        $request->setLocalPath('tests/data');
        $request->setPattern('*est.txt');

        $response = $service->call(null, $request);

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
    function it_throws_an_exception_if_an_incorrect_service_request_is_used()
    {
        $this->expectException(InvalidArgumentException::class);

        $service = new MultiFileService();
        $request = new ServiceRequest();

        $service->call(null, $request);
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_no_files_are_matched()
    {
        $this->expectException(EmptyRetrievedDataException::class);

        $filesMock = $this->getMockBuilder(Filesystem::class)
            ->getMock();

        $filesMock->method('get')
            ->willReturn('some test content');

        $filesMock->method('files')
            ->willReturn(['test1.txt', 'test2.txt']);

        $service = new MultiFileService($filesMock);
        $request = new ServiceSshRequest();

        $request->setPattern('*.xml');

        $service->call(null, $request);
    }

    /**
     * @test
     */
    function it_takes_an_array_config_and_stores_its_contents_as_defaults()
    {
        $service = new MultiFileService();

        $service->config([
            // specific for multifile
            'fingerprint' => '089370823740237480239',
            'path'        => 'test/tmp',
            'local_path'  => 'local/test',
            'pattern'     => '*.txt',
        ]);

        $defaults = $service->getRequestDefaults();

        static::assertEquals('089370823740237480239', $defaults->getFingerprint());
        static::assertEquals('test/tmp', $defaults->getPath());
        static::assertEquals('local/test', $defaults->getLocalPath());
        static::assertEquals('*.txt', $defaults->getPattern());
    }

    /**
     * @test
     */
    function it_throws_an_exception_on_invalid_config()
    {
        $service = new MultiFileService();

        try {
            $service->config([
                'fingerprint' => ['not a string'],
                'path'        => false,
                'local_path'  => true,
                'pattern'     => ['not a string'],
            ]);

            $this->fail('Expecting ServiceConfigurationException');
        } catch (ServiceConfigurationException $e) {
            $errors = $e->getErrors();

            $keys = [
                'fingerprint',
                'path',
                'local_path',
                'pattern',
            ];

            foreach ($keys as $key) {
                static::assertArrayHasKey($key, $errors, 'Missing validation error for: ' . $key);
            }
        }
    }
}
