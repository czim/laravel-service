<?php
namespace Czim\Service;

use Czim\Service\Contracts\ResponseMergerInterface;
use Czim\Service\Contracts\Ssh2SftpConnectionInterface;
use Czim\Service\Responses\Mergers\ResponseMerger;
use Czim\Service\Services\Ssh\Ssh2SftpConnection;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }

    public function register()
    {

        $this->app->bind(ResponseMergerInterface::class, ResponseMerger::class);


        // add bindings for SSH2 / SFTP services

        $this->app->bind(Ssh2SftpConnectionInterface::class, function ($app, array $parameters) {
            /** @var Container $app */

            $host        = $parameters[0];
            $user        = $parameters[1];
            $password    = $parameters[2];
            $port        = isset($parameters[3]) ? $parameters[3] : 22;
            $fingerprint = isset($parameters[4]) ? $parameters[4] : null;

            $filesystem  = isset($parameters[5]) ? $parameters[5] : $app->make('files');

            return new Ssh2SftpConnection($host, $user, $password, $port, $fingerprint, $filesystem);
        });
    }

}
