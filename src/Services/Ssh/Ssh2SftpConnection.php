<?php
namespace Czim\Service\Services\Ssh;

use Czim\Service\Events\SshFileDownloaded;
use Czim\Service\Events\SshFileUploaded;
use Czim\Service\Contracts\Ssh2SftpConnectionInterface;
use Czim\Service\Exceptions\SftpLocalFileException;
use Czim\Service\Exceptions\SftpRemoteFileException;
use Illuminate\Filesystem\Filesystem;

class Ssh2SftpConnection extends Ssh2Connection implements Ssh2SftpConnectionInterface
{
    const DIRECTORY_CREATE_MODE = 0666;

    /**
     * ssh2 sftp2 connection
     *
     * @var resource
     */
    protected $ftpConnection;

    /**
     * Whether data should be received in chunks
     *
     * @var bool
     */
    protected $chunking = false;

    /**
     * If chunking is enabled, the chunk size in bytes
     *
     * @var int
     */
    protected $chunkSize = 8192;

    /**
     * @var Filesystem
     */
    protected $files;


    /**
     * @param string     $hostname
     * @param string     $user
     * @param string     $password
     * @param int        $port
     * @param string     $fingerprint
     * @param Filesystem $files
     */
    public function __construct($hostname, $user, $password, $port = 22, $fingerprint = null, Filesystem $files = null)
    {
        parent::__construct($hostname, $user, $password, $port, $fingerprint);

        if (is_null($files)) {
            $files = app(Filesystem::class);
        }

        $this->files = $files;

        $this->connectSftp();
    }

    /**
     * Make SFTP connection over SSH2
     *
     * @return boolean
     */
    protected function connectSftp()
    {
        if ( ! $this->connected) return false;

        $this->ftpConnection = ssh2_sftp($this->connection);

        return true;
    }


    /**
     * Lists files in the given path
     *
     * @param string $path
     * @return array
     */
    public function listFiles($path = '/./')
    {
        $handle = opendir('ssh2.sftp://' . (string) $this->ftpConnection . $path);

        $files = [];

        while (($file = readdir($handle)) !== false) {

            if ($file === '.' || $file === '..') continue;

            $files[] = $file;
        }

        closedir($handle);

        return $files;
    }


    /**
     * Downloads files via SFTP
     *
     * @param string $pathFrom  path for download
     * @param string $pathTo    where to store the files
     * @return int bytes written
     * @throws SftpLocalFileException
     * @throws SftpRemoteFileException
     */
    public function downloadFile($pathFrom, $pathTo)
    {
        if ( ! $remoteStream = @fopen("ssh2.sftp://{$this->ftpConnection}/{$pathFrom}", 'r')) {
            throw new SftpRemoteFileException("Unable to open remote: '{$pathFrom}'.");
        }

        // ensure that local file may be created
        if ( ! is_dir(dirname($pathTo))) {

            $this->files->makeDirectory(dirname($pathTo), static::DIRECTORY_CREATE_MODE, true, true);
        }

        if ( ! $localStream = @fopen($pathTo, 'w')) {

            throw new SftpLocalFileException("Unable to open local file: '{$pathTo}'.");
        }

        // write from our remote stream to our local stream

        if ($this->chunking) {

            $bytesRead = 0;

            while ( ! feof($remoteStream)) {

                $buffer = fread($remoteStream, $this->chunkSize);
                $bytesRead += strlen($buffer);

                if (fwrite($localStream, $buffer) === false) {

                    throw new SftpLocalFileException("Unable to write to local file: '{$pathTo}'.");
                }
            }

        } else {
            // just read the entire stream at once

            $buffer    = stream_get_contents($remoteStream);
            $bytesRead = strlen($buffer);

            if (fwrite($localStream, $buffer) === false) {

                throw new SftpLocalFileException("Unable to write to local file: '{$pathTo}'.");
            }

        }


        unset($buffer);

        fclose($localStream);
        fclose($remoteStream);

        event( new SshFileDownloaded(basename($pathFrom), $pathTo, $bytesRead) );

        return $bytesRead;
    }


    /**
     * Uploads files over SFTP
     *
     * @param string $pathFrom  path for file to upload
     * @param string $pathTo    where to store the files
     * @return bool
     * @throws SftpLocalFileException
     * @throws SftpRemoteFileException
     */
    public function uploadFile($pathFrom, $pathTo)
    {
        if ( ! $localStream = @fopen($pathFrom, 'r')) {

            throw new SftpLocalFileException("Unable to open local file: '{$pathFrom}'.");
        }

        if ( ! $remoteStream = @fopen("ssh2.sftp://{$this->ftpConnection}/{$pathTo}", 'w')) {

            throw new SftpRemoteFileException("Unable to open remote: '{$pathTo}'.");
        }

        // write from our remote stream to our local stream
        $buffer       = stream_get_contents($localStream);
        $bytesWritten = strlen($buffer);

        if (fwrite($remoteStream, $buffer) === false) {

            throw new SftpLocalFileException("Unable to write to remote file: '{$pathTo}'.");
        }

        fclose($localStream);
        fclose($remoteStream);

        event( new SshFileUploaded(basename($pathFrom), $pathTo, $bytesWritten) );

        return $bytesWritten;
    }

}
