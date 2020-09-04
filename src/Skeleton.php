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
        $this->recurse_copy($this->fileHandler->packagesDir('sebastienheyd/boilerplate-package-skeleton'), $this->fileHandler->tempDir());
    }

    public function parse()
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
    }


    private function recurse_copy($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
