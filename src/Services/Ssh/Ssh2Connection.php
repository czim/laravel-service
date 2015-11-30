<?php
namespace Czim\Service\Services\Ssh;

use Czim\Service\Contracts\Ssh2ConnectionInterface;
use Czim\Service\Events\SshConnectionWasMade;
use Czim\Service\Exceptions\Ssh2CommandException;
use Czim\Service\Exceptions\Ssh2ConnectionException;

/**
 * Note that this requires libssh2
 */
class Ssh2Connection implements Ssh2ConnectionInterface
{

    /**
     * Whether currently connected
     *
     * @var boolean
     */
    protected $connected = false;

    /**
     * ssh2_connect connection
     *
     * @var resource
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
     * @var string
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
     * Connects to SSH2 server
     *
     * @param string  $hostname
     * @param  string $user
     * @param  string $password
     * @param int     $port
     * @param null    $fingerprint
     * @throws Ssh2ConnectionException
     */
    public function __construct($hostname, $user, $password, $port = 22, $fingerprint = null)
    {
        $this->hostname    = $hostname;
        $this->port        = $port;
        $this->user        = $user;
        $this->password    = $password;
        $this->fingerprint = $fingerprint;

        $this->connect();
    }

    /**
     * Disconnects on destruction
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Reconnects if not already connected
     *
     * @return boolean
     * @throws Ssh2ConnectionException  if cannot connect
     */
    public function reconnect()
    {
        if ($this->connected) return false;

        $this->connect();

        return true;
    }

    /**
     * Disconnects open connection.
     *
     * @return boolean
     */
    public function disconnect()
    {
        if ($this->connected) return false;

        $this->exec('exit;');

        $this->connection = null;
        $this->connected  = false;

        return true;
    }

    /**
     * Attempt connection
     *
     * @return boolean
     * @throws Ssh2ConnectionException  if cannot connect
     */
    protected function connect()
    {
        $this->connected  = false;
        $this->connection = ssh2_connect($this->hostname, $this->port);

        if ( ! $this->connection) {

            throw new Ssh2ConnectionException("Could not connect to {$this->hostname}:{$this->port}.");
        }

        // always ask for fingerprint for passing to event
        $fingerprint = ssh2_fingerprint($this->connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);

        if ( ! empty($this->fingerprint) && $this->fingerprint !== $fingerprint) {

            throw new Ssh2ConnectionException(
                "Fingerprint mismatch for {$this->hostname}:{$this->port} (server: '{$fingerprint}')"
            );
        }

        try {

            if ( ! ssh2_auth_password($this->connection, $this->user, $this->password)) {

                throw new Ssh2ConnectionException(
                    "Could not authorize as {$this->user} on {$this->hostname}:{$this->port}."
                );
            }

        } catch (\Exception $e) {

            throw new Ssh2ConnectionException(
                "Could not authorize as {$this->user} on {$this->hostname}:{$this->port}."
            );
        }

        $this->connected = true;

        event( new SshConnectionWasMade($this->user . '@' . $this->hostname . ':' . $this->port, $fingerprint) );

        return $this->connected;
    }


    /**
     * Executes command over connection
     *
     * @param  string $command
     * @return mixed
     * @throws Ssh2CommandException
     */
    public function exec($command)
    {
        if ( ! ($stream = ssh2_exec($this->connection, $command))) {

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
