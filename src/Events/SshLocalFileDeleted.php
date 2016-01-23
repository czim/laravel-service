<?php
namespace Czim\Service\Events;

class SshLocalFileDeleted extends AbstractServiceEvent
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
     * Create a new event instance.
     *
     * @param string $filename
     * @param string $localPath
     */
    public function __construct($filename, $localPath)
    {
        $this->filename  = $filename;
        $this->localPath = $localPath;
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

}
