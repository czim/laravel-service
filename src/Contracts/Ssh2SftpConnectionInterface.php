<?php

namespace Czim\Service\Contracts;

interface Ssh2SftpConnectionInterface extends Ssh2ConnectionInterface, SftpConnectionInterface
{
    /**
     * @param string      $hostname
     * @param string      $user
     * @param string      $password
     * @param int         $port
     * @param string|null $fingerprint
     */
    public function __construct(
        string $hostname,
        string $user,
        string $password,
        int $port = 22,
        ?string $fingerprint = null,
    );
}
