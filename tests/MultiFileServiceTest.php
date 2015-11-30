<?php
namespace Czim\Service\Test;

use Czim\DataObject\Test\Helpers\TestMockInterpreter;
use Czim\Service\Exceptions\ServiceConfigurationException;
use Czim\Service\Requests\ServiceRequest;
use Czim\Service\Requests\ServiceSshRequest;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Services\MultiFileService;
use Illuminate\Filesystem\Filesystem;

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
                  ->will($this->returnCallback(function($file) {
                      if ($file == 'test1.txt') return 'some test content';
                      return 'some more test content';
                  }));

        $filesMock->method('files')
                  ->willReturn(['test1.txt', 'test2.txt']);

        // mocking through service container because passing it to the
        // constructor makes it 'null' for some glitchy reason
        app()->bind(Filesystem::class, function() use ($filesMock) { return $filesMock; });

        $interpreter = new TestMockInterpreter();
        $service     = new MultiFileService(null, $interpreter);
        $request     = new ServiceSshRequest();

        $response = $service->call(null, $request);

        $this->assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        $this->assertArraySubset(
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
                  ->will($this->returnCallback(function($file) {
                      if ($file == 'test1.txt') return 'some test content';
                      return 'some more test content';
                  }));

        $filesMock->method('files')
                  ->willReturn(['test1.txt', 'test2.txt']);

        // mocking through service container because passing it to the
        // constructor makes it 'null' for some glitchy reason
        app()->bind(Filesystem::class, function() use ($filesMock) { return $filesMock; });

        $interpreter = new TestMockInterpreter();
        $service     = new MultiFileService(null, $interpreter);
        $request     = new ServiceSshRequest();

        $response = $service->call('test2.txt', $request);

        $this->assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        $this->assertEquals(
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

        $this->assertInstanceOf(ServiceResponse::class, $response, "Service should return ServiceResponse object");
        $this->assertRegExp(
            '#\s*testing content in here\s*#',
            $response->getData(),
            "File service should return data from test.txt"
        );
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    function it_throws_an_exception_if_an_incorrect_service_request_is_used()
    {
        $service = new MultiFileService();
        $request = new ServiceRequest();

        $service->call(null, $request);
    }

    /**
     * @test
     * @expectedException \Czim\Service\Exceptions\EmptyRetrievedDataException
     */
    function it_throws_an_exception_if_no_files_are_matched()
    {
        $filesMock = $this->getMockBuilder(Filesystem::class)
                          ->getMock();

        $filesMock->method('get')
                  ->willReturn('some test content');

        $filesMock->method('files')
                  ->willReturn(['test1.txt', 'test2.txt']);

        $service = new MultiFileService();
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

        $this->assertEquals('089370823740237480239', $defaults->getFingerprint());
        $this->assertEquals('test/tmp', $defaults->getPath());
        $this->assertEquals('local/test', $defaults->getLocalPath());
        $this->assertEquals('*.txt', $defaults->getPattern());
    }

    /**
     * @test
     */
    function it_throws_an_exception_on_invalid_config()
    {
        $service = new MultiFileService();

        try {

            $service->config([
                'fingerprint' => [ 'not a string' ],
                'path'        => false,
                'local_path'  => true,
                'pattern'     => [ 'not a string' ],
            ]);

            $this->fail('Expecting ServiceConfigurationException');

        } catch (ServiceConfigurationException $e) {

            $errors = $e->getErrors();

            foreach (
                [
                    'fingerprint',
                    'path',
                    'local_path',
                    'pattern',
                ]
                as $key
            ) {
                $this->assertArrayHasKey($key, $errors, 'Missing validation error for: ' . $key);
            }
        }
    }
}
