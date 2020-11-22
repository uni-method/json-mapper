<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Config;

use UniMethod\JsonapiMapper\Exception\ConfigurationException;

class ConfigStore
{
    /** @var EntityConfig[] */
    protected array $entities;

    public function __construct(array $entities = [])
    {
        $this->entities = $entities;
    }

    /**
     * @param string $alias
     * @return EntityConfig
     * @throws ConfigurationException
     */
    public function getEntityConfigByAlias(string $alias): EntityConfig
    {
        $filtered = array_values(
            array_filter($this->entities, static function (EntityConfig $entity) use ($alias) {
                return $entity->alias === $alias;
            })
        );
        if (count($filtered) === 1) {
            return $filtered[0];
        }
        throw new ConfigurationException('There are no configuration for this type');
    }

    /**
     * @param object $object
     * @return EntityConfig
     * @throws ConfigurationException
     */
    public function getEntityConfigByObject(object $object): EntityConfig
    {
        $filtered = array_values(
            array_filter($this->entities, static function (EntityConfig $entity) use ($object) {
                return $entity->class === get_class($object);
            })
        );
        if (count($filtered) === 1) {
            return $filtered[0];
        }
        throw new ConfigurationException('There are no configuration for this object');
    }
}
