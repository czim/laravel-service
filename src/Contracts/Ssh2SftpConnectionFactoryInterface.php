<?php
namespace Czim\Service\Contracts;

interface Ssh2SftpConnectionFactoryInterface
{

    /**
     * Makes an SSH2SFTP connection instance.
     *
     * @param string $class the connection class to use
     * @param string $hostname
     * @param string $user
     * @param string $password
     * @param int    $port
     * @param string $fingerprint
     * @return Ssh2SftpConnectionInterface
     */
    public function make($class, $hostname, $user, $password, $port = 22, $fingerprint = null);

}
