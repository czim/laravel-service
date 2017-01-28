<?php
namespace Czim\Service\Factories;

use Czim\Service\Contracts\Ssh2SftpConnectionFactoryInterface;
use Czim\Service\Contracts\Ssh2SftpConnectionInterface;
use Czim\Service\Services\Ssh\Ssh2SftpConnection;

class Ssh2SftpConnectionFactory implements Ssh2SftpConnectionFactoryInterface
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
    public function make($class, $hostname, $user, $password, $port = 22, $fingerprint = null)
    {
        if ($class === Ssh2SftpConnectionInterface::class) {
            $class = $this->getDefaultConnectionClass();
        }

        return $class($hostname, $user, $password, $port, $fingerprint, app('files'));
    }

    /**
     * @return string
     */
    protected function getDefaultConnectionClass()
    {
        return Ssh2SftpConnection::class;
    }

}
