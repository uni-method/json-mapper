<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\External;

interface ObjectHandler
{
    public function processObject(object $object): void;
}
