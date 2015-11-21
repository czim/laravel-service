<?php
namespace Czim\Service\Events;

class SshFileDownloaded extends AbstractServiceEvent
{

    /**
     * @var string
     */
    protected $filename;

    /**
     * Full path including local file name
     *
     * @var string
     */
    protected $localPath;

    /**
     * @var int
     */
    protected $filesize;


    /**
     * Create a new event instance.
     *
     * @param string $filename
     * @param string $localPath
     * @param int    $filesize
     */
    public function __construct($filename, $localPath, $filesize)
    {
        $this->filename  = $filename;
        $this->localPath = $localPath;
        $this->filesize  = $filesize;
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
    public function getLocalPath()
    {
        return $this->localPath;
    }

    /**
     * @return int
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

}
