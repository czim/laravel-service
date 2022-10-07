<?php

declare(strict_types=1);

namespace Czim\Service\Events;

class SshFileUploaded extends AbstractServiceEvent
{
    /**
     * @param string $filename
     * @param string $remotePath Full path including file name
     * @param int    $filesize
     */
    public function __construct(
        protected readonly string $filename,
        protected readonly string $remotePath,
        protected readonly int $filesize,
    ) {
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
