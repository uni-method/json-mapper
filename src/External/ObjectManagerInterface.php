<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\External;

interface ObjectManagerInterface
{
    public function loadByClassAndId(string $className, string $id): ?object;
}
