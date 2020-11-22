<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\External;

use UniMethod\JsonapiMapper\Config\ConfigStore;

interface ConfigLoaderInterface
{
    public function load(string $path): ConfigStore;
}
