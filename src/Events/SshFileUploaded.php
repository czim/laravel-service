<?php

namespace Czim\Service\Events;

class SshFileUploaded extends AbstractServiceEvent
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * Full path including file name
     *
     * @var string
     */
    protected $remotePath;

    /**
     * @var int
     */
    protected $filesize;


    public function __construct(string $filename, string $remotePath, int $filesize)
    {
        $this->filename   = $filename;
        $this->remotePath = $remotePath;
        $this->filesize   = $filesize;
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

    public function getRemotePath(): string
    {
        return $this->remotePath;
    }

    public function getFilesize(): int
    {
        return $this->filesize;
    }
}
