<?php

namespace Czim\Service\Contracts;

interface SftpConnectionInterface
{
    /**
     * Lists files in the given path.
     *
     * @param string $path
     * @return string[]
     */
    public function listFiles(string $path = '/./'): array;

    /**
     * Downloads files via SFTP.
     *
     * @param string $pathFrom path for download
     * @param string $pathTo   where to store the files
     * @return int bytes written
     */
    public function downloadFile(string $pathFrom, string $pathTo): int;

    /**
     * Uploads files over SFTP.
     *
     * @param string $pathFrom path for file to upload
     * @param string $pathTo   where to store the files
     * @return bool
     */
    public function uploadFile(string $pathFrom, string $pathTo): bool;

    /**
     * Renames a file over SFTP.
     *
     * @param string $pathFrom
     * @param string $pathTo
     * @return bool
     */
    public function renameFile(string $pathFrom, string $pathTo): bool;

    /**
     * Deletes file over SFTP.
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool;
}
