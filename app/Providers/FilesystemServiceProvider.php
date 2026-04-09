<?php

namespace App\Providers;

use Icewind\Flysystem\SMBv3Adapter;
use Icewind\SMB\ServerFactory;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

class FilesystemServiceProvider extends ServiceProvider
{
    /**
     * Register the SMB filesystem driver.
     */
    public function boot(): void
    {
        $this->app['filesystem']->extend('smb', function ($app, $config) {
            try {
                $serverFactory = new ServerFactory;

                $server = $serverFactory->createServer(
                    $config['host'],
                    $config['username'],
                    $config['password']
                );

                $share = $server->getShare($config['share']);

                return new Filesystem(
                    new SMBv3Adapter(
                        $share,
                        $config['root'] ?? '/'
                    )
                );
            } catch (\Exception $e) {
                \Log::error('SMB connection error: '.$e->getMessage());
                throw $e;
            }
        });
    }
}
