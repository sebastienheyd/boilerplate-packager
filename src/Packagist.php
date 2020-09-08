<?php

namespace Sebastienheyd\BoilerplatePackager;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use RuntimeException;

class Packagist
{
    public function getPackageInformation($name)
    {
        if (! $this->exists($name)) {
            throw new RuntimeException('Package does not exists on packagist');
        }

        [$vendor, $name] = explode('/', strtolower($name));

        $client = new Client();
        try {
            $response = $client->get(sprintf('https://packagist.org/packages/%s/%s.json', $vendor, $name));
            $json = json_decode($response->getBody()->getContents());

            return $json->package;
        } catch (ClientException $e) {
            throw new RuntimeException('Error in package information');
        }
    }

    public function exists($name)
    {
        if (! $this->checkFormat($name)) {
            throw new RuntimeException('Package format is invalid');
        }

        [$vendor, $name] = explode('/', strtolower($name));

        $client = new Client();
        try {
            $client->head(sprintf('https://packagist.org/packages/%s/%s.json', $vendor, $name));

            return true;
        } catch (ClientException $e) {
            return false;
        }
    }

    public function checkFormat($name)
    {
        return preg_match('`^([a-z0-9\-]*)/([a-z0-9\-]*)$`', $name);
    }
}
