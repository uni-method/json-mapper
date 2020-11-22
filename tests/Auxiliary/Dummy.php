<?php declare(strict_types=1);

namespace Tests\Auxiliary;

/**
 * @deprecated only for tests
 */
class Dummy
{
    public ?int $one = null;
    protected string $two;

    public function setTwo(string $two): void
    {
        $this->two = $two;
    }

    public function getTwo(): string
    {
        return $this->two;
    }
}
