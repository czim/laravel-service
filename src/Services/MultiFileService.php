<?php
namespace Czim\Service\Services;

use Czim\Service\Contracts\ResponseMergerInterface;
use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Contracts\ServiceSshRequestInterface;
use Czim\Service\Exceptions\CouldNotConnectException;
use Czim\Service\Exceptions\EmptyRetrievedDataException;
use Czim\Service\Responses\ServiceResponse;
use Czim\Service\Responses\ServiceResponseInformation;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

/**
 * Retrieve multiple files and combine the results
 * Uses same request setup as SshFileService, only gets the files locally.
 * Remote path/credentials etc. are ignored.
 */
class MultiFileService extends AbstractService
{

    /**
     * @var ServiceSshRequestInterface
     */
    protected $request;

    /**
     * @var ResponseMergerInterface
     */
    protected $responseMerger;

    /**
     * @var Filesystem
     */
    protected $files;


    /**
     * @param Filesystem                  $files
     * @param ServiceInterpreterInterface $interpreter
     * @param ResponseMergerInterface     $responseMerger
     */
    public function __construct(Filesystem $files = null,
                                ServiceInterpreterInterface $interpreter = null,
                                ResponseMergerInterface $responseMerger = null)
    {
        if (is_null($files)) {
            $this->files = app(Filesystem::class);
        }

        if (is_null($responseMerger)) {
            $this->responseMerger = app(ResponseMergerInterface::class);
        }

        parent::__construct(null, $interpreter);
    }


    /**
     * @param ServiceRequestInterface $request
     * @return mixed
     * @throws CouldNotConnectException
     * @throws Exception
     */
    protected function callRaw(ServiceRequestInterface $request)
    {
        if ($this->request !== $request) $this->request = $request;

        $files = $this->retrieveFiles();

        $responseParts = [];

        // if more than one, combine through a responseMergerInterface
        foreach ($files as $file) {

            $responseParts[] = $this->parseFileContents($file);
        }

        return $this->responseMerger->merge($responseParts);
    }


    /**
     * Override to prevent normal interpretation from taking place
     * Do not interpret, response is already a combination of interpreted responses at this point
     */
    protected function interpretResponse()
    {
        // just copy over the 'raw' response
        $this->response = $this->rawResponse;
    }


    /**
     * Loads data from a local file and parses it through the interpreter
     *
     * @param string $file
     * @return ServiceResponse
     * @throws CouldNotConnectException
     */
    protected function parseFileContents($file)
    {
        try {

            $data = $this->files->get($file);

        } catch (FileNotFoundException $e) {

            throw new CouldNotConnectException("Local file could not be found: '{$file}'");

        } catch (Exception $e) {

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
     * paths to all of the files as an array
     *
     * @return array assoc filename => full path
     * @throws EmptyRetrievedDataException
     */
    protected function retrieveFiles()
    {
        $localFiles = [];

        $pattern   = $this->getFilePattern();
        $localPath = rtrim($this->request->getLocalPath(), '/');

        // get local files based on given path and pattern
        $files = $this->files->files($localPath);

        foreach ($files as $file) {

            // File::files returns full pathname to file, so get basename
            $filename = basename($file);

            if ( ! empty($pattern) && ! fnmatch($pattern, $filename)) continue;

            $localFiles[ $filename ] = $file;
        }


        if ( ! count($localFiles)) {

            throw new EmptyRetrievedDataException(
                "No local files read for pattern '{$this->getFilePattern()}' for path: '{$localPath}'."
            );
        }

        return $localFiles;
    }

    /**
     * Returns the pattern to check for (remote and/or local) files
     *
     * @return string
     */
    protected function getFilePattern()
    {
        return $this->request->getPattern() ?: $this->request->getMethod();
    }

}
