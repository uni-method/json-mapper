<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Exception;

use RuntimeException;
use UniMethod\JsonapiMapper\External\Error;

class ValidationException extends RuntimeException
{
    /** @var Error[] */
    public array $errors;
}
