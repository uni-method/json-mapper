<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Config;

class AttributeConfig
{
    public string $name;
    public string $type;
    public ?string $setter;
    public ?string $getter;

    public function __construct(string $name, string $type, ?string $setter, ?string $getter)
    {
        $this->name = $name;
        $this->type = $type;
        $this->setter = $setter;
        $this->getter = $getter;
    }
}
