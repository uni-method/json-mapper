<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Config;

use UniMethod\JsonapiMapper\Exception\ConfigurationException;

trait MethodHelper
{
    /**
     * @param string $method
     * @return bool
     */
    protected function isMethod(string $method): bool
    {
        return substr($method, -2) === '()';
    }

    /**
     * @param string $method
     * @return string
     */
    protected function getMethod(string $method): string
    {
        return substr($method, 0, -2);
    }

    /**
     * @param object $object
     * @param string $prop
     * @return object
     */
    protected function getValue(object $object, string $prop)
    {
        if ($this->isMethod($prop)) {
            $value = $object->{$this->getMethod($prop)}();
        } else {
            $value = $object->$prop;
        }
        return $value;
    }

    /**
     * @param object $object
     * @param string $prop
     * @param mixed $value
     * @return object
     */
    protected function setValue(object $object, string $prop, $value): object
    {
        if ($this->isMethod($prop)) {
            $object->{$this->getMethod($prop)}($value);
        } else {
            $object->$prop = $value;
        }
        return $object;
    }

    /**
     * @param object $object
     * @param $value
     * @return void
     */
    protected function setId(object $object, $value): void
    {
        $hasMethod = method_exists($object, 'setId');
        if ($hasMethod) {
            $object->setId($value);
        }

        if (in_array('id', get_object_vars($object), true)) {
            $object->id = $value;
        }
    }
}
