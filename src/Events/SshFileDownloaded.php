<?php

declare(strict_types=1);

namespace Czim\Service\Events;

class SshFileDownloaded extends AbstractServiceEvent
{
    /**
     * @param string $filename
     * @param string $localPath Full path including local file name.
     * @param int    $filesize
     */
    public function __construct(
        protected readonly string $filename,
        protected readonly string $localPath,
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

    public function getLocalPath(): string
    {
        return $this->localPath;
    }

    public function getFilesize(): int
    {
        return $this->filesize;
    }
}
