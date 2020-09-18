<?php

namespace Sebastienheyd\BoilerplatePackager;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Skeleton
{
    /**
     * @var array
     */
    public $assign = [];

    /**
     * @var \Illuminate\Support\Facades\Storage
     */
    protected $storage;

    /**
     * Temporary folder name
     *
     * @var string
     */
    protected static $temp = '.temp';

    public function __construct()
    {
        $this->storage = Storage::disk('packages');
    }

    public function assign($name, $value = null)
    {
        if (is_string($name)) {
            $values[$name] = $value;
        } elseif (is_array($name)) {
            $values = $name;
        }

        foreach ($values as $k => $v) {
            $this->assign[ltrim($k, '~')] = $v;
        }

        return $this;
    }

    /**
     * Copy or download the skeleton content and put it in packages temporary folder.
     *
     * @param string $url
     * @param string $branch
     * @return bool
     */
    public function download($url, $branch)
    {
        if (is_dir($url)) {
            $this->recurse_copy($url, packages_path(self::$temp));

            return true;
        }

        $tempPath = packages_path(self::$temp);
        exec("git clone -b $branch -q $url $tempPath", $output, $exit_code);
        $this->storage->deleteDirectory(self::$temp.DIRECTORY_SEPARATOR.'.git');

        return true;
    }

    /**
     * Recursive copy of skeleton files.
     * This will not use Storage facade because skeleton folder can be outside of the packages directory.
     *
     * @param string $src
     * @param string $dst
     */
    private function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.DIRECTORY_SEPARATOR.$file)) {
                    $this->recurse_copy($src.DIRECTORY_SEPARATOR.$file, $dst.DIRECTORY_SEPARATOR.$file);
                } else {
                    copy($src.DIRECTORY_SEPARATOR.$file, $dst.DIRECTORY_SEPARATOR.$file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Replace content in skeleton files, rename files and build license
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function build()
    {
        $replacements = [];
        foreach ($this->assign as $k => $v) {
            $replacements['~uc:pl:'.$k] = Str::plural(mb_convert_case(Str::slug($v, ' '), MB_CASE_TITLE));
            $replacements['~uc:'.$k] = Str::studly($v);
            $replacements['~sc:'.$k] = mb_strtolower(Str::slug($v, '_'));
            $replacements['~wd:'.$k] = mb_convert_case(Str::slug($v, ' '), MB_CASE_TITLE);
            $replacements['~pl:'.$k] = Str::plural(Str::slug($v, ' '), MB_CASE_TITLE);
            $replacements['~'.$k] = $v;
        }

        foreach ($this->storage->allFiles(self::$temp) as $file) {
            $content = $this->storage->get($file);
            $content = str_replace(array_keys($replacements), array_values($replacements), $content);
            $this->storage->put($file, $content);
        }

        $this->moveFiles();
        $this->buildLicense();
    }

    /**
     * Rename files that are declared in packager.json
     *
     * @return false
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function moveFiles()
    {
        $packagerJson = self::$temp.DIRECTORY_SEPARATOR.'packager.json';

        if (! $this->storage->exists($packagerJson)) {
            return false;
        }

        $rules = json_decode($this->storage->get($packagerJson));

        foreach ($rules as $orig => $dest) {
            if ($this->storage->exists(self::$temp.DIRECTORY_SEPARATOR.$dest)) {
                $this->storage->delete(self::$temp.DIRECTORY_SEPARATOR.$dest);
            }
            $this->storage->move(self::$temp.DIRECTORY_SEPARATOR.$orig, self::$temp.DIRECTORY_SEPARATOR.$dest);
        }

        $this->storage->delete($packagerJson);
    }

    /**
     * Get and build license file
     *
     * @return false
     */
    private function buildLicense()
    {
        $licenseFile = self::$temp.DIRECTORY_SEPARATOR.'license.md';
        if (! $this->storage->exists($licenseFile)) {
            return false;
        }

        $license = strtolower($this->assign['license']);
        $url = 'https://raw.githubusercontent.com/licenses/license-templates/master/templates/%s.txt';
        $license = @file_get_contents(sprintf($url, $license));

        if (empty($license)) {
            return false;
        }

        $replace = [
            '{{ year }}' => date('Y'),
            '{{ organization }}' => $this->assign['author_name'],
            '{{ projet }}' => $this->assign['vendor'].'/'.$this->assign['package'],
        ];

        $content = str_replace(array_keys($replace), array_values($replace), $license);
        $this->storage->put($licenseFile, $content);
    }
}
