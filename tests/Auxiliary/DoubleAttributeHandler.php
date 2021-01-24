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
     * @param array $included
     */
    public function processObject(object $object, array $included = []): void
    {
        $object->setTwo($object->getTwo() . $object->getTwo());
    }
}
