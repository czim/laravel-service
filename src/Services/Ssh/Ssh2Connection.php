<?php

namespace Czim\Service\Services\Ssh;

use Czim\Service\Contracts\Ssh2ConnectionInterface;
use Czim\Service\Events\SshConnectionWasMade;
use Czim\Service\Exceptions\Ssh2CommandException;
use Czim\Service\Exceptions\Ssh2ConnectionException;
use Throwable;

/**
 * Note that this requires libssh2.
 */
class Ssh2Connection implements Ssh2ConnectionInterface
{
    /**
     * Whether currently connected.
     *
     * @var bool
     */
    protected $connected = false;

    /**
     * ssh2_connect connection.
     *
     * @var resource|null
     */
    protected $connection;

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string|null
     */
    protected $fingerprint;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * @param string      $hostname
     * @param string      $user
     * @param string      $password
     * @param int         $port
     * @param string|null $fingerprint
     * @throws Ssh2ConnectionException
     */
    public function __construct(
        string $hostname,
        string $user,
        string $password,
        int $port = 22,
        ?string $fingerprint = null
    ) {
        $this->hostname    = $hostname;
        $this->port        = $port;
        $this->user        = $user;
        $this->password    = $password;
        $this->fingerprint = $fingerprint;

        $this->connect();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * {@inheritDoc}
     */
    public function reconnect(): bool
    {
        if ($this->connected) {
            return false;
        }

        $this->connect();

        return true;
    }


    public function disconnect(): bool
    {
        if ($this->connected) {
            return false;
        }

        $this->exec('exit;');

        $this->connection = null;
        $this->connected  = false;

        return true;
    }


    protected function connect(): bool
    {
        $this->connected  = false;
        $this->connection = ssh2_connect($this->hostname, $this->port);

        if (! $this->connection) {
            throw new Ssh2ConnectionException("Could not connect to {$this->hostname}:{$this->port}.");
        }

        // always ask for fingerprint for passing to event
        $fingerprint = ssh2_fingerprint($this->connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);

        if (! empty($this->fingerprint) && $this->fingerprint !== $fingerprint) {
            throw new Ssh2ConnectionException(
                "Fingerprint mismatch for {$this->hostname}:{$this->port} (server: '{$fingerprint}')"
            );
        }

        try {
            if (! ssh2_auth_password($this->connection, $this->user, $this->password)) {
                throw new Ssh2ConnectionException(
                    "Could not authorize as {$this->user} on {$this->hostname}:{$this->port}."
                );
            }
        } catch (Throwable $e) {
            throw new Ssh2ConnectionException(
                "Could not authorize as {$this->user} on {$this->hostname}:{$this->port}."
            );
        }

        $this->connected = true;

        event(
            new SshConnectionWasMade($this->user . '@' . $this->hostname . ':' . $this->port, $fingerprint)
        );

        return $this->connected;
    }

    /**
     * {@inheritDoc}
     */
    public function exec(string $command)
    {
        $stream = ssh2_exec($this->connection, $command);

        if (! $stream) {
            throw new Ssh2CommandException("Exec failed: '{$command}'.");
        }

        stream_set_blocking($stream, true);

        $data = '';

        while ($buf = fread($stream, 4096)) {
            $data .= $buf;
        }

        fclose($stream);

        return $data;
    }
}
