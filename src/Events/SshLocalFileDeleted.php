<?php

namespace Czim\Service\Events;

class SshLocalFileDeleted extends AbstractServiceEvent
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * Full path including local file name
     *
     * @var string
     */
    protected $localPath;


    public function __construct(string $filename, string $localPath)
    {
        $this->filename  = $filename;
        $this->localPath = $localPath;
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
}
