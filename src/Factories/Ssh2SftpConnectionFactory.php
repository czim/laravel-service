<?php

declare(strict_types=1);

namespace Czim\Service\Factories;

use Czim\Service\Contracts\Ssh2SftpConnectionFactoryInterface;
use Czim\Service\Contracts\Ssh2SftpConnectionInterface;
use Czim\Service\Services\Ssh\Ssh2SftpConnection;

class Ssh2SftpConnectionFactory implements Ssh2SftpConnectionFactoryInterface
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
    ): Ssh2SftpConnectionInterface {
        if ($class === Ssh2SftpConnectionInterface::class) {
            $class = $this->getDefaultConnectionClass();
        }

        return new $class($hostname, $user, $password, $port, $fingerprint, app('files'));
    }

    protected function getDefaultConnectionClass(): string
    {
        return Ssh2SftpConnection::class;
    }
}
