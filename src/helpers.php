<?php

if (! function_exists('packages_path')) {
    function packages_path($path = '')
    {
        $pathPrefix = rtrim(Storage::disk('packages')->getAdapter()->getPathPrefix(), DIRECTORY_SEPARATOR);

        if (! empty($path)) {
            $pathPrefix .= DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR);
        }

        return rtrim($pathPrefix, DIRECTORY_SEPARATOR);
    }
}

if (! function_exists('run_process')) {
    function run_process(array $args = [])
    {
        $process = new Symfony\Component\Process\Process($args, base_path());
        $process->setTimeout(config('packager.timeout', 300));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new Symfony\Component\Process\Exception\ProcessFailedException($process);
        }

        return $process->getExitCode() === 0;
    }
}
