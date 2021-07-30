<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Config;

class IdAttributeConfig extends AttributeConfig
{
    public function __construct(string $type, ?string $setter, ?string $getter, ?string $description = null)
    {
        parent::__construct(EntityConfig::DEFAULT_PRIMARY_ATTRIBUTE, $type, $setter, $getter, $description);
    }
}
