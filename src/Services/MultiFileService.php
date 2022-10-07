<?php

declare(strict_types=1);

namespace Czim\Service\Services;

use Closure;
use Czim\Service\Contracts\ResponseMergerInterface;
use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestDefaultsInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Contracts\ServiceResponseInterface;
use Czim\Service\Contracts\ServiceSshRequestInterface;
use Czim\Service\Exceptions\CouldNotConnectException;
use Czim\Service\Exceptions\EmptyRetrievedDataException;
use Czim\Service\Requests\ServiceSshRequest;
use Czim\Service\Requests\ServiceSshRequestDefaults;
use Czim\Service\Responses\ServiceResponseInformation;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

/**
 * Retrieve multiple files and combine the results.
 * Uses same request setup as SshFileService, only gets the files locally.
 * Remote path/credentials etc. are ignored.
 */
class MultiFileService extends AbstractService
{
    /**
     * @var class-string<ServiceRequestDefaultsInterface>
     */
    protected string $requestDefaultsClass = ServiceSshRequestDefaults::class;

    /**
     * @var ServiceSshRequestDefaults
     */
    protected ServiceRequestDefaultsInterface $defaults;

    /**
     * @var ServiceSshRequestInterface
     */
    protected ServiceRequestInterface $request;

    protected Filesystem $files;
    protected ResponseMergerInterface $responseMerger;


    public function __construct(
        Filesystem $files = null,
        ServiceInterpreterInterface $interpreter = null,
        ResponseMergerInterface $responseMerger = null
    ) {
        if ($files === null) {
            $files = app(Filesystem::class);
        }

        $this->files = $files;

        if ($responseMerger === null) {
            $responseMerger = app(ResponseMergerInterface::class);
        }

        $this->responseMerger = $responseMerger;

        parent::__construct(null, $interpreter);
    }


    /**
     * Applies mass configuration to default request.
     *
     * @param array<string, mixed> $config
     */
    public function config(array $config): void
    {
        parent::config($config);

        if (array_key_exists('fingerprint', $config)) {
            $this->defaults->setFingerprint($config['fingerprint']);
        }

        if (array_key_exists('path', $config)) {
            $this->defaults->setPath($config['path']);
        }

        if (array_key_exists('local_path', $config)) {
            $this->defaults->setLocalPath($config['local_path']);
        }

        if (array_key_exists('pattern', $config)) {
            $this->defaults->setPattern($config['pattern']);
        }

        if (array_key_exists('files_callback', $config)) {
            $this->defaults->setFilesCallback($config['files_callback']);
        }

        if (array_key_exists('do_cleanup', $config)) {
            $this->defaults->setDoCleanup($config['do_cleanup']);
        }
    }

    /**
     * Returns the rules to validate the config against.
     *
     * @return array<string, mixed>
     */
    protected function getConfigValidationRules(): array
    {
        return array_merge(
            parent::getConfigValidationRules(),
            [
                'fingerprint' => 'string',
                'path'        => 'string',
                'local_path'  => 'string',
                'pattern'     => 'string',
            ]
        );
    }

    /**
     * Takes the current request and supplements it with the service's defaults
     * to merge them into a complete request.
     */
    protected function supplementRequestWithDefaults(): void
    {
        parent::supplementRequestWithDefaults();

        if (empty($this->request->getFingerprint())) {
            $this->request->setFingerprint(
                $this->defaults->getFingerprint()
            );
        }

        if (empty($this->request->getPath())) {
            $this->request->setPath(
                $this->defaults->getPath()
            );
        }

        if (empty($this->request->getLocalPath())) {
            $this->request->setLocalPath(
                $this->defaults->getLocalPath()
            );
        }

        if (empty($this->request->getPattern())) {
            $this->request->setPattern(
                $this->defaults->getPattern()
            );
        }

        if (empty($this->request->getFilesCallback())) {
            $this->request->setFilesCallback(
                $this->defaults->getFilesCallback()
            );
        }

        if (empty($this->request->getDoCleanup())) {
            $this->request->setDoCleanup(
                $this->defaults->getDoCleanup()
            );
        }
    }


    /**
     * @param ServiceSshRequestInterface $request
     * @return mixed
     * @throws CouldNotConnectException
     */
    protected function callRaw(ServiceRequestInterface $request): mixed
    {
        if ($this->request !== $request) {
            $this->request = $request;
        }

        $files = $this->retrieveFiles();

        $responseParts = [];

        // if more than one, combine through a responseMergerInterface
        foreach ($files as $file) {
            $responseParts[] = $this->parseFileContents($file);
        }

        return $this->responseMerger->merge($responseParts);
    }


    /**
     * Override to prevent normal interpretation from taking place.
     * Do not interpret, response is already a combination of interpreted responses at this point.
     */
    protected function interpretResponse(): void
    {
        // just copy over the 'raw' response
        $this->response = $this->rawResponse;
    }


    /**
     * Loads data from a local file and parses it through the interpreter.
     *
     * @param string $file
     * @return ServiceResponseInterface
     * @throws CouldNotConnectException
     */
    protected function parseFileContents(string $file): ServiceResponseInterface
    {
        try {
            $data = $this->files->get($file);
        } catch (FileNotFoundException) {
            throw new CouldNotConnectException("Local file could not be found: '{$file}'");
        } catch (Exception) {
            throw new CouldNotConnectException("Local file unreadable or unopenable: '{$file}'");
        }

        // store the file name as the request method, so the interpreter can know what the source was;
        $this->request->setMethod(basename($file));

        // add information to mark reading the file a success
        $information = new ServiceResponseInformation();
        $information->setStatusCode(200);

        return $this->interpreter->interpret($this->request, $data, $information);
    }


    /**
     * Retrieves files from external (or local) source and returns the
     * paths to all of the files as an array.
     *
     * @return array<string, string> filename => full path
     * @throws EmptyRetrievedDataException
     */
    protected function retrieveFiles(): array
    {
        $localFiles = [];

        $pattern   = $this->getFilePattern();
        $localPath = rtrim($this->request->getLocalPath() ?? '', DIRECTORY_SEPARATOR);
        $callback  = $this->request->getFilesCallback();

        // get local files based on given path and pattern
        $files = $this->files->files($localPath);

        if ($callback instanceof Closure) {
            $files = $callback($files);
        }

        foreach ($files as $file) {
            // File::files returns full pathname to file, so get basename
            $filename = basename((string) $file);

            if (! empty($pattern) && ! fnmatch($pattern, $filename)) {
                continue;
            }

            $localFiles[ $filename ] = (string) $file;
        }


        if (! count($localFiles)) {
            throw new EmptyRetrievedDataException(
                "No local files read for pattern '{$this->getFilePattern()}' for path: '{$localPath}'."
            );
        }

        return $localFiles;
    }

    /**
     * Returns the pattern to check for (remote and/or local) files.
     *
     * @return string|null
     */
    protected function getFilePattern(): ?string
    {
        return $this->request->getPattern() ?: $this->request->getMethod();
    }

    /**
     * Checks the request to be used in the next/upcoming call.
     */
    protected function checkRequest(): void
    {
        parent::checkRequest();

        if (! $this->request instanceof ServiceSshRequest) {
            throw new InvalidArgumentException('Request class is not a ServiceSshRequest');
        }
    }
}
