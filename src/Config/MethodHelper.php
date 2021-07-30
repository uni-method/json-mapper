<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Config;

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
     * @return mixed
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
}
