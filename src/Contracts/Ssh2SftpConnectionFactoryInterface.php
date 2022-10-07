<?php

namespace Czim\Service\Contracts;

interface Ssh2SftpConnectionFactoryInterface
{
    /**
     * Makes an SSH2SFTP connection instance.
     *
     * @param string      $class the connection class to use
     * @param string      $hostname
     * @param string      $user
     * @param string      $password
     * @param int         $port
     * @param string|null $fingerprint
     * @return Ssh2SftpConnectionInterface
     */
    public function make(
        string $class,
        string $hostname,
        string $user,
        string $password,
        int $port = 22,
        ?string $fingerprint = null,
    ): Ssh2SftpConnectionInterface;
}
