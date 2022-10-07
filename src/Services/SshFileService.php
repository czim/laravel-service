<?php

declare(strict_types=1);

namespace Czim\Service\Services;

use Closure;
use Czim\Service\Contracts\ResponseMergerInterface;
use Czim\Service\Contracts\ServiceInterpreterInterface;
use Czim\Service\Contracts\Ssh2SftpConnectionFactoryInterface;
use Czim\Service\Contracts\Ssh2SftpConnectionInterface;
use Czim\Service\Events\SshLocalFileDeleted;
use Czim\Service\Exceptions\CouldNotConnectException;
use Czim\Service\Exceptions\EmptyRetrievedDataException;
use Czim\Service\Exceptions\Ssh2ConnectionException;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;

/**
 * For retrieving content from (combined) files served on an SSH2 server.
 * This service retrieves the file(s) from a specific path, for an optional file pattern match,
 * downloads them to a localPath.
 *
 * If more than one file is parsed, it interprets the content and combines the results in a
 * single response object.
 */
class SshFileService extends MultiFileService
{
    protected Ssh2SftpConnectionInterface $ssh;


    /**
     * @param Filesystem|null                  $files
     * @param ServiceInterpreterInterface|null $interpreter
     * @param ResponseMergerInterface|null     $responseMerger
     * @param Ssh2SftpConnectionInterface|null $sshConnection
     */
    public function __construct(
        Filesystem $files = null,
        ServiceInterpreterInterface $interpreter = null,
        ResponseMergerInterface $responseMerger = null,
        Ssh2SftpConnectionInterface $sshConnection = null
    ) {
        if ($sshConnection !== null) {
            $this->ssh = $sshConnection;
        }

        parent::__construct($files, $interpreter, $responseMerger);
    }


    /**
     * Retrieves files from external (or local) source and returns the
     * paths to all of the files as an array.
     *
     * The pattern will be used if non-empty; the fallback value will be the method,
     * which might be used to indicate an exact file match.
     *
     * @return array<string, string> filename => full path
     * @throws CouldNotConnectException
     * @throws EmptyRetrievedDataException
     */
    protected function retrieveFiles(): array
    {
        $localFiles = [];

        // Connect
        if (! isset($this->ssh)) {
            $this->initializeSsh();
        }

        $pattern   = $this->getFilePattern();
        $path      = rtrim($this->request->getPath() ?? '', DIRECTORY_SEPARATOR);
        $localPath = rtrim($this->request->getLocalPath() ?? '', DIRECTORY_SEPARATOR);
        $callback  = $this->request->getFilesCallback();

        // List all files in path
        $files = $this->ssh->listFiles($path);

        if ($callback instanceof Closure) {
            $files = $callback($files);
        }

        // Retrieve files that match the pattern (or all if no pattern given)
        foreach ($files as $file) {
            // skip files not matching pattern, IF pattern is set
            if (! empty($pattern) && ! fnmatch($pattern, $file)) {
                continue;
            }

            $this->ssh->downloadFile($path . DIRECTORY_SEPARATOR . $file, $localPath . DIRECTORY_SEPARATOR . $file);

            $localFiles[ $file ] = $localPath . DIRECTORY_SEPARATOR . $file;
        }


        if (! count($localFiles)) {
            throw new EmptyRetrievedDataException(
                "No files retrieved for pattern '{$pattern}' in local path: '{$localPath}', "
                . "retrieved from remote path: '{$path}'."
            );
        }


        // Do cleanup by deleting any files in the local directory that were not downloaded
        if ($this->request->getDoCleanup()) {
            $this->cleanupLocalFiles($localFiles);
        }

        return $localFiles;
    }

    /**
     * Removes locally files that are not newly downloaded.
     *
     * @param string[] $newFiles
     */
    protected function cleanupLocalFiles(array $newFiles): void
    {
        $localPath = rtrim($this->request->getLocalPath() ?? '', DIRECTORY_SEPARATOR);

        // get local files not in newly downloaded files
        $deleteFiles = array_diff(
            array_map(
                fn (string|\Stringable $file): string => (string) $file,
                $this->files->files($localPath)
            ),
            $newFiles
        );

        foreach ($deleteFiles as $file) {
            if (! $this->files->delete($file)) {
                throw new RuntimeException("Failed to delete local file for cleanup: {$file}");
            }

            event(
                new SshLocalFileDeleted($file, $localPath)
            );
        }
    }

    /**
     * Initializes the SSH connection.
     *
     * @throws CouldNotConnectException
     */
    protected function initializeSsh(): void
    {
        try {
            $this->ssh = $this->getSsh2SftpConnectionFactory()
                ->make(
                    Ssh2SftpConnectionInterface::class,
                    $this->request->getLocation(),
                    $this->request->getCredentials()['name'],
                    $this->request->getCredentials()['password'],
                    $this->request->getPort() ?: 22,
                    $this->request->getFingerprint()
                );
        } catch (Ssh2ConnectionException $e) {
            throw new CouldNotConnectException($e->getMessage(), 0, $e);
        }
    }

    protected function getSsh2SftpConnectionFactory(): Ssh2SftpConnectionFactoryInterface
    {
        return app(Ssh2SftpConnectionFactoryInterface::class);
    }
}
