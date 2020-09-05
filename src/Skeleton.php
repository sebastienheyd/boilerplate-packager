<?php

namespace Sebastienheyd\BoilerplatePackager;

class Skeleton
{
    public $assign = [];
    /**
     * @var FileHandler
     */
    protected $fileHandler;

    public function __construct(FileHandler $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    public function assign($name, $value = null)
    {
        if (is_string($name)) {
            $values[$name] = $value;
        } elseif (is_array($name)) {
            $values = $name;
        }

        foreach ($values as $k => $v) {
            $this->assign[':'.ltrim($k, ':')] = $v;
        }

        return $this;
    }

    public function download()
    {
        $this->recurse_copy($this->fileHandler->packagesDir('sebastienheyd/boilerplate-package-skeleton'),
            $this->fileHandler->tempDir());
    }

    public function build()
    {
        $iterator = (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->fileHandler->tempDir())));
        foreach ($iterator as $item) {
            /** @var \SplFileInfo $item */
            if (!$item->isFile()) {
                continue;
            }

            $content = file_get_contents($item->getPathname());
            $content = str_replace(array_keys($this->assign), array_values($this->assign), $content);
            file_put_contents($item->getPathname(), $content);
        }

        $this->moveFiles();
        $this->buildLicense();
    }

    private function moveFiles()
    {
        if (!is_file($this->fileHandler->tempDir('package.json'))) {
            return false;
        }

        $rules = json_decode(file_get_contents($this->fileHandler->tempDir('package.json')));

        foreach ($rules as $orig => $dest) {
            if (!is_readable($this->fileHandler->tempDir($dest))) {
                rename($this->fileHandler->tempDir($orig), $this->fileHandler->tempDir($dest));
            } else {
                $this->fileHandler->removeDir($this->fileHandler->tempDir($orig));
            }
        }

        unlink($this->fileHandler->tempDir('package.json'));
    }

    private function buildLicense()
    {
        if (!is_file($this->fileHandler->tempDir('license.md'))) {
            return false;
        }

        $license = strtolower($this->assign[':license']);
        $url = 'https://raw.githubusercontent.com/licenses/license-templates/master/templates/%s.txt';
        $license = @file_get_contents(sprintf($url, $license));

        if (empty($license)) {
            return false;
        }

        $replace = [
            '{{ year }}'         => date('Y'),
            '{{ organization }}' => $this->assign[':author_name'],
            '{{ projet }}'       => $this->assign[':vendor'].'/'.$this->assign[':package'],
        ];

        $content = str_replace(array_keys($replace), array_values($replace), $license);
        file_put_contents($this->fileHandler->tempDir('license.md'), $content);
    }

    private function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {
                    $this->recurse_copy($src.'/'.$file, $dst.'/'.$file);
                } else {
                    copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }
        closedir($dir);
    }
}
