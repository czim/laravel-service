<?php
namespace Czim\Service\Contracts;

interface Ssh2SftpConnectionInterface extends Ssh2ConnectionInterface, SftpConnectionInterface
{

    /**
     * @param string     $hostname
     * @param string     $user
     * @param string     $password
     * @param int        $port
     * @param string     $fingerprint
     */
    public function __construct($hostname, $user, $password, $port = 22, $fingerprint = null);

}
