<?php

namespace Czim\Service\Requests;

use Closure;
use Czim\Service\Contracts\ServiceSshRequestInterface;

/**
 * Request for SshFileService and MultiFileService.
 *
 * @property string|null  $path
 * @property string|null  $local_path
 * @property string|null  $pattern
 * @property string|null  $fingerprint
 * @property Closure|null $files_callback
 * @property bool|null    $do_cleanup
 */
class ServiceSshRequest extends ServiceRequest implements ServiceSshRequestInterface
{
    /**
     * @var array<string, mixed>
     */
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
        'do_cleanup'     => null,
    ];


    /**
     * Returns the path on the SSH server to use as a base.
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->getAttribute('path');
    }

    /**
     * Sets the path on the SSH server.
     *
     * @param string|null $path
     */
    public function setPath(?string $path): void
    {
        $this->setAttribute('path', $path);
    }

    /**
     * Returns the path to locally store SSH-retrieved files (if any).
     *
     * @return string|null
     */
    public function getLocalPath(): ?string
    {
        return $this->getAttribute('local_path');
    }

    /**
     * Sets the localPath.
     *
     * @param string|null $localPath
     */
    public function setLocalPath(?string $localPath): void
    {
        $this->setAttribute('local_path', $localPath);
    }

    /**
     * Returns the (glob) pattern to apply when picking files for download.
     *
     * @return string|null
     */
    public function getPattern(): ?string
    {
        return $this->getAttribute('pattern');
    }

    /**
     * Sets the pattern for selection of external files.
     *
     * @param string|null $pattern
     */
    public function setPattern(?string $pattern): void
    {
        $this->setAttribute('pattern', $pattern);
    }

    /**
     * Returns the expected server fingerprint.
     *
     * @return string|null
     */
    public function getFingerprint(): ?string
    {
        return $this->getAttribute('fingerprint');
    }

    /**
     * Sets the fingerprint.
     * This is optional, and only used when set to perform a security check to verify the host.
     *
     * @param string|null $fingerprint
     */
    public function setFingerprint(?string $fingerprint): void
    {
        $this->setAttribute('fingerprint', (string) $fingerprint);
    }

    /**
     * Returns the closure to run over the files array to retrieve/parse.
     * This should be a function that takes an array of strings and returns an array of strings.
     *
     * @return Closure|null
     */
    public function getFilesCallback(): ?Closure
    {
        return $this->getAttribute('files_callback');
    }

    /**
     * Sets the closure to run over the files array for retrieval and/or parsing (if local).
     * This should be a function that takes an array of strings and returns an array of strings.
     *
     * @param Closure|null $callback
     */
    public function setFilesCallback(?Closure $callback = null): void
    {
        $this->setAttribute('files_callback', $callback);
    }

    /**
     * Returns whether old (local) files should be deleted after downloading new ones.
     * Cleanup function, only used for ssh file service.
     *
     * @return bool
     */
    public function getDoCleanup(): bool
    {
        return (bool) $this->getAttribute('do_cleanup');
    }

    /**
     * Sets whether old files cleanup should be done after retrieval.
     *
     * @param bool $enable
     */
    public function setDoCleanup(bool $enable): void
    {
        $this->setAttribute('do_cleanup', $enable);
    }
}
