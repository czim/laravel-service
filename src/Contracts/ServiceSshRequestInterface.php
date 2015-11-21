<?php
namespace Czim\Service\Contracts;

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

}
