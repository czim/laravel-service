<?php
namespace Czim\Service\Contracts;

use Closure;

interface ServiceSshRequestInterface extends ServiceRequestInterface
{

    /**
     * Returns the path on the SSH server to use as a base
     *
     * @return string
     */
    public function getPath();

    /**
     * Sets the path on the SSH server
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path);

    /**
     * Returns the path to locally store SSH-retrieved files (if any)
     *
     * @return string
     */
    public function getLocalPath();

    /**
     * Sets the localPath
     *
     * @param string $localPath
     * @return $this
     */
    public function setLocalPath($localPath);

    /**
     * Returns the (glob) pattern to apply when picking files for download
     *
     * @return string
     */
    public function getPattern();

    /**
     * Sets the pattern for selection of external files
     *
     * @param string $pattern
     * @return $this
     */
    public function setPattern($pattern);

    /**
     * Returns the expected server fingerprint
     *
     * @return string
     */
    public function getFingerprint();

    /**
     * Sets the fingerprint
     * This is optional, and only used when set to perform a security check to verify the host
     *
     * @param string $fingerprint
     * @return $this
     */
    public function setFingerprint($fingerprint);

    /**
     * Returns the closure to run over the files array to retrieve/parse
     * This should be a function that takes an array of strings and returns an array of strings
     *
     * @return null|Closure
     */
    public function getFilesCallback();

    /**
     * Sets the closure to run over the files array for retrieval and/or parsing (if local)
     * This should be a function that takes an array of strings and returns an array of strings
     *
     * @param null|Closure $callback
     * @return $this
     */
    public function setFilesCallback(Closure $callback = null);

    /**
     * Returns whether old (local) files should be deleted after downloading new
     * ones. Cleanup function, only used for ssh file service.
     *
     * @return boolean
     */
    public function getDoCleanup();

    /**
     * Sets whether old files cleanup should be done after retrieval
     *
     * @param boolean $enable
     * @return $this
     */
    public function setDoCleanup($enable);

}
