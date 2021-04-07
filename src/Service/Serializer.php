<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Service;

use Exception;
use UniMethod\JsonapiMapper\Config\ConfigStore;
use UniMethod\JsonapiMapper\Config\EntityConfig;
use UniMethod\JsonapiMapper\Config\IncludedHelper;
use UniMethod\JsonapiMapper\Config\MethodHelper;
use UniMethod\JsonapiMapper\Config\Scalar;
use UniMethod\JsonapiMapper\Exception\ConfigurationException;
use DateTime;
use IteratorAggregate;
use UniMethod\JsonapiMapper\External\Error;

class Serializer
{
    use MethodHelper, IncludedHelper;

    protected ConfigStore $configStore;

    public function __construct(ConfigStore $configStore)
    {
        $this->configStore = $configStore;
    }

    /**
     * @param object|null $object
     * @param string $included
     * @return mixed[]
     * @throws ConfigurationException
     */
    public function handleObject(?object $object, string $included = ''): array
    {
        $result = [];
        if ($object === null) {
            $result['data'] = null;
        } else {
            $parsedInclude = $this->parseIncluded($included);
            $data = $this->extractData($object, $parsedInclude);
            $result['data'] = $data;
            $result['included'] = $this->filterIncluded($this->getIncluded($object, $parsedInclude));
        }
        return $result;
    }

    /**
     * Check is synthetic model or not
     *
     * @param string $alias
     * @return bool
     * @throws ConfigurationException
     */
    public function isSynthetic(string $alias): bool
    {
        return $this->configStore->getEntityConfigByAlias($alias)->type === EntityConfig::TYPE_SYNTHETIC;
    }

    /**
     * @param object[] $objects
     * @param string $included
     * @param mixed[] $meta
     * @return mixed[]
     * @throws ConfigurationException
     */
    public function handleCollection(array $objects, string $included = '', array $meta = []): array
    {
        $result = [];
        $data = [];
        $_included = [];
        foreach ($objects as $object) {
            $parsedIncluded = $this->parseIncluded($included);
            $data[] = $this->extractData($object, $parsedIncluded);
            $_included[] = $this->getIncluded($object, $parsedIncluded);
        }
        if ($meta !== []) {
            $result['meta'] = $meta;
        }
        $result['data'] = $data;
        $result['included'] = $this->filterIncluded(array_merge(...$_included));
        return $result;
    }

    /**
     * Output errors
     *
     * @param Error[] $errors
     * @return mixed[]
     */
    public function handleErrors(array $errors): array
    {
        return ['errors' => array_map(static fn(Error $error) => [
            'status' => $error->getStatus(),
            'source' => ['pointer' => $error->getPointer()],
            'title' => $error->getTitle(),
            'detail' => $error->getDetail(),
        ], $errors)];
    }

    /**
     * @param object $entity
     * @param mixed[] $included
     * @return mixed[]
     * @throws ConfigurationException
     */
    protected function extractData(object $entity, array $included = []): array
    {
        $config = $this->configStore->getEntityConfigByObject($entity);
        $entity = $this->applyPostLoad($entity, $config, $included);

        return array_merge($this->extractTypeAndId($entity, $config), [
            'attributes' => $this->getAttributes($entity, $config),
            'relationships' => $this->getRelationships($entity, $config, array_keys($included)),
        ]);
    }

    /**
     * @param object $entity
     * @param EntityConfig $config
     * @param array $included
     * @return object
     */
    protected function applyPostLoad(object $entity, EntityConfig $config, array $included): object
    {
        foreach ($config->getPostLoadHandlers() as $handler) {
            $handler->processObject($entity, $included);
        }
        return $entity;
    }

    /**
     * @param object $entity
     * @param EntityConfig $config
     * @return array
     * @throws ConfigurationException
     */
    protected function extractTypeAndId(object $entity, EntityConfig $config): ?array
    {
        if ($config->getPrimaryAttribute() !== null && $config->getPrimaryAttribute()->getter !== null) {
            $id = $this->getValue($entity, $config->getPrimaryAttribute()->getter);
        } elseif (method_exists($entity, 'getId')) {
            $id = $entity->getId();
        } else {
            throw new ConfigurationException('There is no getter for ID');
        }
        return $entity !== null ? [
            'type' => $config->alias,
            'id' => (string) $id,
        ] : null;
    }

    protected function filterIncluded(array $included): array
    {
        $result = [];
        foreach (array_filter($included) as $one) {
            $result[$one['type'] . '_' . $one['id']] = $one;
        }
        return array_values($result);
    }

    /**
     * Output attributes according to the config
     *
     * @param object $obj
     * @param EntityConfig $config
     * @return array
     */
    protected function getAttributes(object $obj, EntityConfig $config): array
    {
        $result = [];
        foreach ($config->getAttributesWithGetters() as $attributeConfig) {
            if ($config->getPrimaryAttribute() === $attributeConfig) {
                continue;
            }
            $type = $attributeConfig->type;

            $value = $this->getValue($obj, $attributeConfig->getter);
            if ($value !== null) {
                if ($type === Scalar::TYPE_STRING) {
                    $value = (string) $value;
                } elseif ($type === Scalar::TYPE_INTEGER) {
                    $value = (int) $value;
                } elseif ($type === Scalar::TYPE_FLOAT) {
                    $value = (double) $value;
                } elseif ($type === Scalar::TYPE_DATETIME) {
                    if ($value instanceof DateTime) {
                        /** @var DateTime $value */
                        $value = $value->format('c');
                    } else {
                        $value = null;
                    }
                } elseif ($type === Scalar::TYPE_BOOLEAN) {
                    $value = (bool) $value;
                }
            }
            $result[$attributeConfig->name] = $value;
        }
        return $result;
    }

    /**
     * Output relations according to the config
     *
     * @param object $obj
     * @param EntityConfig $config
     * @param mixed[] $relations
     * @return mixed[]
     * @throws ConfigurationException
     * @throws Exception
     */
    protected function getRelationships(object $obj, EntityConfig $config, array $relations): array
    {
        $result = [];
        foreach ($config->getRelationshipsWithGetters($relations) as $relationshipConfig) {
            $relation = $this->getValue($obj, $relationshipConfig->getter);
            if ($relation === null) {
                continue;
            }
            if ($relation instanceof IteratorAggregate) {
                foreach ($relation->getIterator() as $item) {
                    $result[$relationshipConfig->name]['data'][] = $this->extractTypeAndId($item, $this->configStore->getEntityConfigByObject($item));
                }
            } elseif (is_array($relation)) {
                foreach ($relation as $item) {
                    $result[$relationshipConfig->name]['data'][] = $this->extractTypeAndId($item, $this->configStore->getEntityConfigByObject($item));
                }
            } else {
                $result[$relationshipConfig->name]['data'] = $this->extractTypeAndId($relation, $this->configStore->getEntityConfigByObject($relation));
            }
        }
        return $result;
    }

    /**
     * @param object $obj
     * @param array $included
     * @return array
     * @throws ConfigurationException
     * @throws Exception
     */
    protected function getIncluded(object $obj, array $included): array
    {
        $result = [];
        $config = $this->configStore->getEntityConfigByObject($obj);
        foreach ($config->getRelationshipsWithGetters(array_keys($included)) as $relationshipConfig) {
            $relation = $this->getValue($obj, $relationshipConfig->getter);
            if ($relation === null) {
                continue;
            }
            if ($relation instanceof IteratorAggregate) {
                foreach ($relation->getIterator() as $item) {
                    $result[] = $this->extractData($item, $included[$relationshipConfig->name]);
                    foreach ($this->getIncluded($item, $included[$relationshipConfig->name]) as $value) {
                        $result[] = $value;
                    }
                }
            } elseif (is_array($relation)) {
                foreach ($relation as $item) {
                    $result[] = $this->extractData($item, $included[$relationshipConfig->name]);
                    foreach ($this->getIncluded($item, $included[$relationshipConfig->name]) as $value) {
                        $result[] = $value;
                    }
                }
            } else {
                $result[] = $this->extractData($relation, $included[$relationshipConfig->name]);
                foreach ($this->getIncluded($relation, $included[$relationshipConfig->name]) as $value) {
                    $result[] = $value;
                }
            }
        }
        return $result;
    }
}
