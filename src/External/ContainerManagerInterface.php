<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\External;

interface ContainerManagerInterface
{
    /**
     * @param string $classOrAlias
     * @return object|ObjectHandler
     */
    public function getService(string $classOrAlias): object;
}
