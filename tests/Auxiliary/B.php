<?php declare(strict_types=1);

namespace Tests\Auxiliary;

/**
 * @deprecated only for tests
 */
class B
{
    public string $id;
    public string $title;
    public C $c;

    /**
     * @param string $id
     * @return B
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $title
     * @return B
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param C $c
     * @return B
     */
    public function setC(C $c): self
    {
        $this->c = $c;
        return $this;
    }
}
