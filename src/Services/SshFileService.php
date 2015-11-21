<?php
namespace Czim\Service\Services;

use Czim\Service\Contracts\ResponseMergerInterface;
use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\ServiceRequestInterface;
use Czim\Service\Contracts\Ssh2SftpConnectionInterface;
use Czim\Service\Contracts\ServiceSshRequestInterface;
use Czim\Service\Exceptions\CouldNotConnectException;
use Czim\Service\Exceptions\Ssh2ConnectionException;
use Czim\Service\Responses\ServiceResponse;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

/**
 * For retrieving content from (combined) files served on an SSH2 server.
 * This service retrieves the file(s) from a specific path, for an optional file pattern match,
 * downloads them to a localPath.
 *
 * If more than one file is parsed, it interprets the content and combines the results in a
 * single response object.
 */
class SshFileService extends AbstractService
{

    /**
     * @var ServiceSshRequestInterface
     */
    protected $request;

    /**
     * @var Ssh2SftpConnectionInterface
     */
    protected $ssh;

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
     * @param Ssh2SftpConnectionInterface $sshConnection        optional
     */
    public function __construct(Filesystem $files = null,
                                ServiceInterpreterInterface $interpreter = null,
                                ResponseMergerInterface $responseMerger = null,
                                Ssh2SftpConnectionInterface $sshConnection = null)
    {
        if (is_null($files)) {
            $this->files = app(Filesystem::class);
        }

        if (is_null($responseMerger)) {
            $this->responseMerger = app(ResponseMergerInterface::class);
        }

        if ( ! is_null($sshConnection)) {
            $this->ssh = $sshConnection;
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
     */
    protected function interpretResponse()
    {
        // do not interpret, response is already a combination of interpreted responses at this point
    }


    /**
     * Returns the contents from a locally stored file
     *
     * @param string $file
     * @return string
     * @throws CouldNotConnectException
     */
    protected function getLocalFileContent($file)
    {
        $path = rtrim($this->request->getLocalPath(), '/') . '/' . $file;

        try {

            $response = $this->files->get($path);

        } catch (FileNotFoundException $e) {

            throw new CouldNotConnectException($e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

    /**
     * Retrieves files from external (or local) source and returns the
     * paths to all of the files as an array
     *
     * @return array    assoc filename => full path
     */
    protected function retrieveFiles()
    {
        $localFiles = [];

        // connect
        if (empty($this->ssh)) {
            $this->initializeSsh();
        }

        $pattern   = $this->request->getPattern();
        $path      = rtrim($this->request->getPath(), '/');
        $localPath = rtrim($this->request->getLocalPath(), '/');

        // list all files in path
        $files = $this->ssh->listFiles($path);


        // retrieve files that match the pattern (or all if no pattern given)
        foreach ($files as $file) {

            // skip files not matching pattern, IF pattern is set
            if ( ! empty($pattern) && ! fnmatch($pattern, $file)) continue;

            $this->ssh->downloadFile($path . '/' . $file, $localPath . '/' . $file);

            $localFiles[ $file ] = $localPath . '/' . $file;
        }

        return $localFiles;
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

        } catch (Exception $e) {

            throw new CouldNotConnectException("Local file unreadable or unopenable: '{$file}'");
        }

        return $this->interpreter->interpret($this->request, $data);
    }

    /**
     * Initializes the SSH connection
     *
     * @throws CouldNotConnectException
     */
    protected function initializeSsh()
    {
        try {

            $this->ssh = app(Ssh2SftpConnectionInterface::class, [
                $this->request->getLocation(),
                $this->request->getCredentials()['name'],
                $this->request->getCredentials()['password'],
                $this->request->getPort(),
                $this->request->getFingerprint(),
            ]);

        } catch (Ssh2ConnectionException $e) {

            throw new CouldNotConnectException($e->getMessage(), 0, $e);
        }
    }

}
