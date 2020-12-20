<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Config;

use UniMethod\JsonapiMapper\External\ObjectHandler;

class EntityConfig
{
    public const TYPE_STORE = 'store';
    public const TYPE_SYNTHETIC = 'synthetic';

    public const DEFAULT_PRIMARY_ATTRIBUTE = 'id';

    public string $class;

    /**
     * self::TYPE_STORE - store and read data usually from db
     * self::TYPE_SYNTHETIC - read and write in runtime
     *
     * @var string
     */
    public string $type;

    public string $alias;

    public string $description;

    /** @var ObjectHandler[] */
    public array $postLoadHandlers = [];

    /**
     * @var ObjectHandler[]
     */
    public array $preCreateHandlers = [];

    /** @var AttributeConfig[] */
    public array $attributes = [];

    /** @var RelationshipConfig[] */
    public array $relationships = [];

    /**
     * @param string $class
     * @return EntityConfig
     */
    public function setClass(string $class): self
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param string $alias
     * @return EntityConfig
     */
    public function setAlias(string $alias): self
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @param string $description
     * @return EntityConfig
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param ObjectHandler[] $postLoadHandlers
     * @return EntityConfig
     */
    public function setPostLoadHandlers(array $postLoadHandlers): self
    {
        $this->postLoadHandlers = $postLoadHandlers;
        return $this;
    }

    /**
     * @param ObjectHandler[] $preCreateHandlers
     * @return EntityConfig
     */
    public function setPreCreateHandlers(array $preCreateHandlers): self
    {
        $this->preCreateHandlers = $preCreateHandlers;
        return $this;
    }

    /**
     * @param AttributeConfig[] $attributes
     * @return EntityConfig
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param RelationshipConfig[] $relationships
     * @return EntityConfig
     */
    public function setRelationships(array $relationships): self
    {
        $this->relationships = $relationships;
        return $this;
    }

    /**
     * @return AttributeConfig|null
     */
    public function getPrimaryAttribute(): ?AttributeConfig
    {
        $id = self::DEFAULT_PRIMARY_ATTRIBUTE;
        $filtered = array_values(
            array_filter($this->attributes, static function (AttributeConfig $attributeConfig) use ($id) {
                return $attributeConfig->name === $id;
            })
        );
        if (count($filtered) === 1) {
            return $filtered[0];
        }
        return null;
    }

    /**
     * @return AttributeConfig[]
     */
    public function getAttributesWithGetters(): array
    {
        return array_values(
            array_filter($this->attributes, static function (AttributeConfig $attributeConfig) {
                return $attributeConfig->getter !== null;
            })
        );
    }

    /**
     * @return AttributeConfig[]
     */
    public function getAttributesWithSetters(): array
    {
        return array_values(
            array_filter($this->attributes, static function (AttributeConfig $attributeConfig) {
                return $attributeConfig->setter !== null;
            })
        );
    }

    /**
     * @param string[] $filterRelations
     * @return RelationshipConfig[]
     */
    public function getRelationshipsWithGetters(array $filterRelations): array
    {
        return array_values(
            array_filter($this->relationships, static function (RelationshipConfig $relationshipConfig) use ($filterRelations) {
                return $relationshipConfig->getter !== null && in_array($relationshipConfig->name, $filterRelations, true);
            })
        );
    }

    /**
     * @param array $filterRelations
     * @return RelationshipConfig[]
     */
    public function getRelationshipsWithSetters(array $filterRelations): array
    {
        return array_values(
            array_filter($this->relationships, static function (RelationshipConfig $relationshipConfig) use ($filterRelations) {
                return $relationshipConfig->setter !== null && in_array($relationshipConfig->name, $filterRelations, true);
            })
        );
    }

    /**
     * @return ObjectHandler[]
     */
    public function getPostLoadHandlers(): array
    {
        return $this->postLoadHandlers;
    }

    /**
     * @return ObjectHandler[]
     */
    public function getPreCreateHandlers(): array
    {
        return $this->preCreateHandlers;
    }
}
