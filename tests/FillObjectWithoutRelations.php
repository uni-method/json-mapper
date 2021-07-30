<?php declare(strict_types=1);

namespace Tests;

use DateTime;

/**
 * @deprecated only for tests
 */
class FillObjectWithoutRelations
{
    public const DESC = 'All available types with protected id';

    protected string $id;
    protected float $floatVal;
    protected string $stringVal;
    protected int $intVal;
    protected DateTime $dateTimeVal;
    protected bool $boolVal;

    public function getId(): string
    {
        return $this->id;
    }

    public function getFloatVal(): float
    {
        return $this->floatVal;
    }

    public function getStringVal(): string
    {
        return $this->stringVal;
    }

    public function getIntVal(): int
    {
        return $this->intVal;
    }

    public function getDateTimeVal(): DateTime
    {
        return $this->dateTimeVal;
    }

    public function isBoolVal(): bool
    {
        return $this->boolVal;
    }

    public function setFloatVal(float $floatVal): void
    {
        $this->floatVal = $floatVal;
    }

    public function setStringVal(string $stringVal): void
    {
        $this->stringVal = $stringVal;
    }

    public function setIntVal(int $intVal): void
    {
        $this->intVal = $intVal;
    }

    public function setDateTimeVal(DateTime $dateTimeVal): void
    {
        $this->dateTimeVal = $dateTimeVal;
    }

    public function setBoolVal(bool $boolVal): void
    {
        $this->boolVal = $boolVal;
    }
}
