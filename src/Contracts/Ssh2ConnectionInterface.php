<?php
namespace Czim\Service\Contracts;

use Czim\Service\Exceptions\Ssh2ConnectionException;

interface Ssh2ConnectionInterface
{

    /**
     * Connects to SSH2 server
     *
     * @param  string $url
     * @param  string $user
     * @param  string $password
     * @throws Ssh2ConnectionException  if cannot connect
     */
    public function __construct($url, $user, $password);

    /**
     * Reconnects if not already connected
     *
     * @return boolean
     * @throws Ssh2ConnectionException  if cannot connect
     */
    public function reconnect();

    /**
     * Disconnects open connection.
     *
     * @return boolean
     */
    public function disconnect();

    /**
     * Executes command over connection
     *
     * @param  string $command
     * @return mixed
     */
    public function exec($command);

}
