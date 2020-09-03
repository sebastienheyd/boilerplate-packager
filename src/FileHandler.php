<?php

namespace Sebastienheyd\BoilerplatePackager;

use RuntimeException;

class FileHandler
{
    public function tempDir($path = '')
    {
        return $this->packagesDir('.temp').(empty($path) ? '' : '/'.$path);
    }

    public function packagesDir($path = '')
    {
        return base_path('packages').(empty($path) ? '' : '/'.$path);
    }

    public function moveDir($source, $destination)
    {
        if(!is_dir($source)) {
            throw new RuntimeException('Source directory does not exists');
        }

        if(!is_dir($destination)) {
            mkdir($destination, 0775, true);
        }

        rename($source, $destination);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function removeDir($path)
    {
        if(!preg_match('#^'.$this->packagesDir().'#', $path)) {
            throw new RuntimeException('Folder deletion forbidden');
        }

        if(!is_dir($path)) {
            return true;
        }

        foreach (array_diff(scandir($path), ['.', '..']) as $file) {
            if (is_dir("$path/$file")) {
                $this->removeDir("$path/$file");
            } else {
                @chmod("$path/$file", 0777);
                @unlink("$path/$file");
            }
        }

        return rmdir($path);
    }
}
