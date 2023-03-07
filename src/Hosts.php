<?php

namespace pkg6\flysystem\github;

class Hosts
{
    /**
     * @var array
     */
    public static $hosts = [
        'github'   => "https://raw.githubusercontent.com/:username/:repository/:branch/:fullfilename",
        'fastgit'  => "https://hub.fastgit.org/:username/:repository/blob/:branch/:fullfilename?raw=true",
        'jsdelivr' => "https://cdn.jsdelivr.net/gh/:username/:repository@:branch/:fullfilename"
    ];

    /**
     * @param $index
     * @return mixed
     */
    public static function getCdn($index)
    {
        return Hosts::$hosts[$index] ?? Hosts::$hosts['github'];
    }
}