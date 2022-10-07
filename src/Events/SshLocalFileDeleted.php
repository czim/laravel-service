<?php

declare(strict_types=1);

namespace Czim\Service\Events;

class SshLocalFileDeleted extends AbstractServiceEvent
{
    /**
     * @param string $filename
     * @param string $localPath Full path including file name
     */
    public function __construct(
        protected readonly string $filename,
        protected readonly string $localPath,
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
}
