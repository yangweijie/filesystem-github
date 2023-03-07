<?php

namespace pkg6\flysystem\github\Plugins;

use League\Flysystem\Plugin\AbstractPlugin;

class FileUrl extends AbstractPlugin
{
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'getUrl';
    }

    public function handle($path)
    {
        return $this->filesystem->getAdapter()->getUrl($path);
    }
}
