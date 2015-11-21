<?php
namespace Czim\Service\Contracts;

interface SftpConnectionInterface
{

    /**
     * Lists files in the given path
     *
     * @param  string $path
     * @return array
     */
    public function listFiles($path = '/./');


    /**
     * Downloads files via SFTP
     *
     * @param  string $pathFrom path for download
     * @param  string $pathTo   where to store the files
     * @return int  bytes written
     */
    public function downloadFile($pathFrom, $pathTo);

    /**
     * Uploads files over SFTP
     *
     * @param  string $pathFrom path for file to upload
     * @param  string $pathTo   where to store the files
     * @return boolean
     */
    public function uploadFile($pathFrom, $pathTo);

}
