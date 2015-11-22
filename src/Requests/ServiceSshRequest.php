<?php
namespace Czim\Service\Requests;

use Czim\Service\Contracts\ServiceSshRequestInterface;

/**
 * Request for SshFileService and MultiFileService
 *
 * @property string   $path
 * @property string   $localPath
 * @property string   $pattern
 * @property string   $fingerprint
 */
class ServiceSshRequest extends ServiceRequest implements ServiceSshRequestInterface
{
    protected $attributes = [
        'location'    => null,
        'port'        => null,
        'method'      => null,
        'parameters'  => null,
        'headers'     => [],
        'body'        => null,
        'credentials' => [
            'name'     => null,
            'password' => null,
            'domain'   => null,
        ],
        'options'     => [],

        'path'        => null,
        'localPath'   => null,
        'pattern'     => null,
        'fingerprint' => null,
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
        return $this->getAttribute('localPath');
    }

    /**
     * Sets the localPath
     *
     * @param string $localPath
     * @return $this
     */
    public function setLocalPath($localPath)
    {
        $this->setAttribute('localPath', (string) $localPath);

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

}
