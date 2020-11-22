<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Config;

interface Scalar
{
    public const TYPE_STRING = 'string';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_FLOAT = 'float';
}
