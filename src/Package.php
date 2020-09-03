<?php

namespace Sebastienheyd\BoilerplatePackager;

class Package
{
    public $url;
    public $vendor;
    public $name;

    public function parseFromUrl($url)
    {
        // parse url
        $regex = [
            '`^https?://([^/]*)/([^/]*)/([^./]*).*$`',
            '`^git@([^:]*):([^/]*)/([^.]*)\.git$`',
        ];

        foreach ($regex as $rx) {
            if (preg_match($rx, $url, $m)) {
                $this->url = $this->origin;
                $this->vendor = $m[2];
                $this->name = $m[3];
                return $this;
            }
        }

        return false;
    }

    public function __get($name)
    {
        if($name === 'temp_path') {
            return base_path('packages/.temp').'/'.$this->vendor.'/'.$this->name;
        }

        return null;
    }
}
