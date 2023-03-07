<?php


namespace pkg6\flysystem\github;


use League\Flysystem\Config;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use pkg6\flysystem\github\Plugins\FileUrl;

class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        app('filesystem')->extend('github', function ($app, $config) {
            $adapter   = new GithubAdapter($config['token'],
                $config['username'],
                $config['repository'],
                $config['branch'] ?? 'master',
                $config['hostIndex'] ?? 'fastgit'
            );
            $flysystem = new Filesystem($adapter, new Config(['disable_asserts' => true]));
            $flysystem->addPlugin(new FileUrl());
            return $flysystem;
        });
    }
}