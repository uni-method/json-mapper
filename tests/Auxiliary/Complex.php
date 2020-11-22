<?php declare(strict_types=1);

namespace Tests\Auxiliary;

/**
 * @deprecated only for tests
 */
class Complex
{
    public int $one;
    protected string $two;
    protected Dummy $dummy;

    /**
     * @return string
     */
    public function getTwo(): string
    {
        return $this->two;
    }

    /**
     * @param string $two
     */
    public function setTwo(string $two): void
    {
        $this->two = $two;
    }

    /**
     * @return Dummy
     */
    public function getDummy(): Dummy
    {
        return $this->dummy;
    }

    /**
     * @param Dummy $dummy
     */
    public function setDummy(Dummy $dummy): void
    {
        $this->dummy = $dummy;
    }
}
