<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\External;

final class Error
{
    protected int $status;
    protected ?string $pointer = null;
    protected string $title;
    protected string $detail;

    public function __construct(int $status, string $title, string $detail, ?string $pointer)
    {
        $this->status = $status;
        $this->title = $title;
        $this->detail = $detail;
        $this->pointer = $pointer;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getPointer(): ?string
    {
        return $this->pointer;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDetail(): string
    {
        return $this->detail;
    }
}
