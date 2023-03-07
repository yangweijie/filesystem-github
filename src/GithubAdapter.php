<?php

namespace pkg6\flysystem\github;

use Exception;
use Github\Api\Repository\Contents;
use Github\Client;
use Github\Exception\MissingArgumentException;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\Config;

class GithubAdapter extends AbstractAdapter
{
    use NotSupportingVisibilityTrait;

    /**
     * @var string
     */
    public $cdn;
    /**
     * @var string
     */
    protected $repository;
    /**
     * @var string
     */
    protected $username;
    /**
     * @var string
     */
    protected $branch;
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Contents
     */
    private $repoContents;

    /**
     * GithubAdapter constructor.
     *
     * @param $token
     * @param $username
     * @param $repository
     * @param string $branch
     * @param string $hostIndex
     */
    public function __construct($token, $username, $repository, $branch = 'master', $hostIndex = 'fastgit')
    {
        $this->username   = $username;
        $this->repository = $repository;
        $this->branch     = $branch;
        $this->cdn        = Hosts::getCdn($hostIndex);
        $this->client     = new Client();
        $this->client->authenticate($token, '', Client::AUTH_ACCESS_TOKEN);
        $this->repoContents = $this->client->repo()->contents();
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|bool false on failure file meta data on success
     * @throws MissingArgumentException
     *
     */
    public function write($path, $contents, Config $config)
    {
        try {
            return $this->repoContents->create(
                $this->username,
                $this->repository,
                $path,
                $contents,
                __FUNCTION__ . $this->commitMessage(),
                $this->branch
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Write a new file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     * @throws MissingArgumentException
     *
     */
    public function writeStream($path, $resource, Config $config)
    {
        $contents = '';
        while (!feof($resource)) {
            $contents .= fread($resource, 1024);
        }
        $response = $this->write($path, $contents, $config);
        if (false === $response) {
            return $response;
        }

        return compact('path');
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     * @throws MissingArgumentException
     *
     */
    public function update($path, $contents, Config $config)
    {
        try {
            $sha = $this->getMetadata($path);
            if (false === $sha) {
                return $sha;
            }

            return $this->repoContents->update(
                $this->username,
                $this->repository,
                $path,
                $contents,
                __FUNCTION__ . $this->commitMessage(),
                $sha,
                $this->branch
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update a file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     * @throws MissingArgumentException
     *
     */
    public function updateStream($path, $resource, Config $config)
    {
        $contents = '';
        while (!feof($resource)) {
            $contents .= fread($resource, 1024);
        }
        $response = $this->update($path, $contents, $config);
        if (false === $response) {
            return $response;
        }

        return compact('path');
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool | array
     */
    public function rename($path, $newpath)
    {
        $show = $this->read($path);
        if (empty($show['sha']) || empty($show['contents']) || $show === false) {
            return $show;
        }

        try {
            $this->repoContents->rm(
                $this->username,
                $this->repository,
                $path,
                __FUNCTION__ . $this->commitMessage(),
                $show['sha'],
                $this->branch
            );
            $this->write($newpath, $show['contents'], new Config());

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        return $this->rename($path, $newpath);
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool | array
     * @throws MissingArgumentException
     *
     */
    public function delete($path)
    {
        $show = $this->read($path);
        if (empty($show['sha']) || $show === false) {
            return false;
        }

        try {
            return $this->repoContents->rm(
                $this->username,
                $this->repository,
                $path,
                'delete:' . date('D M j G:i:s T Y'),
                $show['sha'],
                $this->branch
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        return true;
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        return true;
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        return $this->repoContents->exists(
            $this->username,
            $this->repository,
            $path,
            $this->branch
        );
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        if ($resp = $this->show($path)) {
            $contents = base64_decode($resp['content']);
            $sha      = $resp['sha'];

            return compact('sha', 'contents', 'path');
        }

        return false;
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        if (ini_get('allow_url_fopen')) {
            if ($result = fopen($this->getUrl($path), 'r')) {
                return $result;
            }
        }

        return false;
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        $list   = [];
        $result = $this->show($directory);
        if (empty($result) || $result === false) {
            return $list;
        }
        foreach ($result as $item) {
            $list[] = $item['path'];
        }

        return $list;
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        if ($resp = $this->show($path)) {
            return $resp;
        }

        return false;
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        if ($resp = $this->show($path)) {
            return $resp['size'];
        }

        return false;
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        if ($resp = $this->show($path)) {
            return $resp['type'];
        }

        return false;
    }

    /**
     * Get the last modified time of a file as a timestamp.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        return true;
    }

    /**
     * @param $path
     *
     * @return array|bool|string
     */
    public function show($path)
    {
        try {
            return $this->repoContents->show($this->username, $this->repository, $path, $this->branch);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param $path
     *
     * @return string
     */
    public function getUrl($path)
    {
        return str_replace(
            [':username', ':repository', ':branch', ":fullfilename"],
            [$this->username, $this->repository, $this->branch, $path],
            $this->cdn);
    }

    /**
     * @return false|string
     */
    protected function commitMessage()
    {
        return ':' . date('D M j G:i:s T Y');
    }
}
