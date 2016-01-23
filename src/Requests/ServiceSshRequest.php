<?php
namespace Czim\Service\Requests;

use Closure;
use Czim\Service\Contracts\ServiceSshRequestInterface;

/**
 * Request for SshFileService and MultiFileService
 *
 * @property string        $path
 * @property string        $local_path
 * @property string        $pattern
 * @property string        $fingerprint
 * @property null|\Closure $files_callback
 */
class ServiceSshRequest extends ServiceRequest implements ServiceSshRequestInterface
{
    protected $attributes = [
        'location'       => null,
        'port'           => null,
        'method'         => null,
        'parameters'     => null,
        'headers'        => [],
        'body'           => null,
        'credentials'    => [
            'name'     => null,
            'password' => null,
            'domain'   => null,
        ],
        'options'        => [],
        'path'           => null,
        'local_path'     => null,
        'pattern'        => null,
        'fingerprint'    => null,
        'files_callback' => null,
    ];


    /**
     * Returns the path on the SSH server to use as a base
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getAttribute('path');
    }

    /**
     * Sets the path on the SSH server
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->setAttribute('path', (string) $path);

        return $this;
    }

    /**
     * Returns the path to locally store SSH-retrieved files (if any)
     *
     * @return string
     */
    public function getLocalPath()
    {
        return $this->getAttribute('local_path');
    }

    /**
     * Sets the localPath
     *
     * @param string $localPath
     * @return $this
     */
    public function setLocalPath($localPath)
    {
        $this->setAttribute('local_path', (string) $localPath);

        return $this;
    }

    /**
     * Returns the (glob) pattern to apply when picking files for download
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->getAttribute('pattern');
    }

    /**
     * Sets the pattern for selection of external files
     *
     * @param string $pattern
     * @return $this
     */
    public function setPattern($pattern)
    {
        $this->setAttribute('pattern', (string) $pattern);

        return $this;
    }

    /**
     * Returns the expected server fingerprint
     *
     * @return string
     */
    public function getFingerprint()
    {
        return $this->getAttribute('fingerprint');
    }

    /**
     * Sets the fingerprint
     * This is optional, and only used when set to perform a security check to verify the host
     *
     * @param string $fingerprint
     * @return $this
     */
    public function setFingerprint($fingerprint)
    {
        $this->setAttribute('fingerprint', (string) $fingerprint);

        return $this;
    }

    /**
     * Returns the closure to run over the files array to retrieve/parse
     * This should be a function that takes an array of strings and returns an array of strings
     *
     * @return Closure
     */
    public function getFilesCallback()
    {
        return $this->getAttribute('files_callback');
    }

    /**
     * Sets the closure to run over the files array for retrieval and/or parsing (if local)
     * This should be a function that takes an array of strings and returns an array of strings
     *
     * @param Closure $callback
     * @return $this
     */
    public function setFilesCallback(Closure $callback)
    {
        $this->setAttribute('files_callback', $callback);

        return $this;
    }

}
