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
