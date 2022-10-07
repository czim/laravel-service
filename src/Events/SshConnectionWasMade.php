<?php

declare(strict_types=1);

namespace Czim\Service\Events;

class SshConnectionWasMade
{
    /**
     * @param string $address     Full address: user@hostname:port.
     * @param string $fingerprint Last known server fingerprint.
     */
    public function __construct(
        protected readonly string $address,
        protected readonly string $fingerprint
    ) {
    }

    /**
     * @return string[]
     */
    public function broadcastOn(): array
    {
        return [];
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getFingerprint(): string
    {
        return $this->fingerprint;
    }
}
