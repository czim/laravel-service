<?php

namespace Czim\Service\Events;

class SshFileDownloaded extends AbstractServiceEvent
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * Full path including local file name.
     *
     * @var string
     */
    protected $localPath;

    /**
     * @var int
     */
    protected $filesize;


    public function __construct(string $filename, string $localPath, int $filesize)
    {
        $this->filename  = $filename;
        $this->localPath = $localPath;
        $this->filesize  = $filesize;
    }

    /**
     * @return string[]
     */
    public function broadcastOn(): array
    {
        return [];
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getLocalPath(): string
    {
        return $this->localPath;
    }

    public function getFilesize(): int
    {
        return $this->filesize;
    }
}
