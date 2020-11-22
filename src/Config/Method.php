<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Config;

interface Method
{
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const LIST = 'list';
    public const VIEW = 'view';
    public const DELETE = 'delete';
}
