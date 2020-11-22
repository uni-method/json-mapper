<?php declare(strict_types=1);

namespace Tests\Auxiliary;

/**
 * @deprecated only for tests
 */
class C
{
    public string $id;
    public int $count;

    /**
     * @param string $id
     * @return C
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param int $count
     * @return C
     */
    public function setCount(int $count): self
    {
        $this->count = $count;
        return $this;
    }
}
