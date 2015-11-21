<?php
namespace Czim\Service\Events;

class SshConnectionWasMade
{

    /**
     * Full address: user@hostname:port
     *
     * @var string
     */
    protected $address;

    /**
     * Last known server fingerprint
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
    public function __construct($address, $fingerprint)
    {
        $this->address     = $address;
        $this->fingerprint = $fingerprint;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }


    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getFingerprint()
    {
        return $this->fingerprint;
    }

}
