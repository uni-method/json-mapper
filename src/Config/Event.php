<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Config;

interface Event
{
    public const POST_LOAD = 'postLoad';
    public const PRE_CREATE = 'preCreate';
}
