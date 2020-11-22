<?php declare(strict_types=1);

namespace Tests\Auxiliary;

use UniMethod\JsonapiMapper\External\ObjectHandler;

/**
 * @deprecated only for tests
 */
class DoubleAttributeHandler implements ObjectHandler
{
    /**
     * @param object|Dummy $object
     */
    public function processObject(object $object): void
    {
        $object->setTwo($object->getTwo() . $object->getTwo());
    }
}
