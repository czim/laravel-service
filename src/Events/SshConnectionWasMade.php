<?php

namespace Czim\Service\Events;

class SshConnectionWasMade
{
    /**
     * Full address: user@hostname:port.
     *
     * @var string
     */
    protected $address;

    /**
     * Last known server fingerprint.
     *
     * @var string
     */
    protected $fingerprint;

    /**
     * Create a new event instance.
     *
     * @param string $address
     * @param string $fingerprint
     */
    public function __construct(string $address, string $fingerprint)
    {
        $this->address     = $address;
        $this->fingerprint = $fingerprint;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
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
