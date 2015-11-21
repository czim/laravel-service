<?php
namespace Czim\Service\Events;

class SshFileUploaded extends AbstractServiceEvent
{

    /**
     * @var string
     */
    protected $filename;

    /**
     * Full path including file name
     *
     * @var string
     */
    protected $remotePath;

    /**
     * @var int
     */
    protected $filesize;


    /**
     * Create a new event instance.
     *
     * @param string $filename
     * @param string $remotePath
     * @param int    $filesize
     */
    public function __construct($filename, $remotePath, $filesize)
    {
        $this->filename   = $filename;
        $this->remotePath = $remotePath;
        $this->filesize   = $filesize;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }


    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getRemotePath()
    {
        return $this->remotePath;
    }

    /**
     * @return int
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

}
