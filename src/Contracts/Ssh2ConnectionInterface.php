<?php

namespace Czim\Service\Contracts;

use Czim\Service\Exceptions\Ssh2ConnectionException;

interface Ssh2ConnectionInterface
{
    /**
     * Connects to SSH2 server.
     *
     * @param string $url
     * @param string $user
     * @param string $password
     * @throws Ssh2ConnectionException  if cannot connect
     */
    public function __construct(string $url, string $user, string $password);

    /**
     * Reconnects if not already connected.
     *
     * @return bool
     * @throws Ssh2ConnectionException  if cannot connect
     */
    public function reconnect(): bool;

    /**
     * Disconnects open connection.
     *
     * @return bool
     */
    public function disconnect(): bool;

    /**
     * Executes command over connection.
     *
     * @param string $command
     * @return mixed
     */
    public function exec(string $command);
}
