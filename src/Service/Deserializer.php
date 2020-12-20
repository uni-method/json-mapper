<?php declare(strict_types=1);

namespace UniMethod\JsonapiMapper\Service;

use UniMethod\JsonapiMapper\Config\ConfigStore;
use UniMethod\JsonapiMapper\Config\EntityConfig;
use UniMethod\JsonapiMapper\Config\IncludedHelper;
use UniMethod\JsonapiMapper\Config\Method;
use UniMethod\JsonapiMapper\Config\MethodHelper;
use UniMethod\JsonapiMapper\Config\Scalar;
use UniMethod\JsonapiMapper\External\ObjectManagerInterface;
use UniMethod\JsonapiMapper\Exception\BrokenInputException;
use UniMethod\JsonapiMapper\Exception\ConfigurationException;
use DateTime;
use Exception;

class Deserializer
{
    use MethodHelper, IncludedHelper;

    protected ConfigStore $configStore;
    protected ObjectManagerInterface $objectManager;

    public function __construct(ConfigStore $configStore, ObjectManagerInterface $objectManager)
    {
        $this->configStore = $configStore;
        $this->objectManager = $objectManager;
    }

    /**
     * @param mixed[] $data
     * @param string $method
     * @param string $included
     * @return object
     * @throws BrokenInputException
     * @throws ConfigurationException
     */
    public function handle(array $data, string $method, string $included = ''): object
    {
        $objectConfig = $this->configStore->getEntityConfigByAlias($data['data']['type']);
        $class = $objectConfig->class;
        $isNew = true;
        $parseIncluded = $this->parseIncluded($included);
        if ($method === Method::CREATE) {
            $object = new $class;
        } elseif ($method === Method::UPDATE && !empty($data['data']['id'])) {
            if ($objectConfig->type === EntityConfig::TYPE_SYNTHETIC) {
                $object = new $class;
            }  else  {
                $object = $this->objectManager->loadByClassAndId($class, $data['data']['id']);
            }

            if ($object === null) {
                throw new BrokenInputException('Non valid input data');
            }

            $isNew = false;
        } else {
            throw new BrokenInputException('Non valid input data');
        }
        return $this->resolveObject($object, $objectConfig, $data, $isNew, $parseIncluded);
    }

    /**
     * @param object $object
     * @param EntityConfig $objectConfig
     * @param mixed[] $data
     * @param bool $isNew
     * @param array $included
     * @return object
     * @throws ConfigurationException
     */
    protected function resolveObject(object $object, EntityConfig $objectConfig, array $data, bool $isNew, array $included) {
        $object = $this->setAttributes($object, $objectConfig, $data['data'] ?? []);
        $object = $this->fillRelationships($object, $objectConfig, $included, $data['data'], $data['included'] ?? []);
        if ($isNew) {
            $object = $this->applyPreCreate($object, $objectConfig);
        }
        return $object;
    }

    /**
     * @param object $entity
     * @param EntityConfig $config
     * @return object
     */
    protected function applyPreCreate(object $entity, EntityConfig $config): object
    {
        foreach ($config->getPreCreateHandlers() as $handler) {
            $handler->processObject($entity);
        }
        return $entity;
    }

    /**
     * @param object $object
     * @param EntityConfig $config
     * @param mixed[] $data
     * @return object
     */
    protected function setAttributes(object $object, EntityConfig $config, array $data): object
    {
        foreach ($config->getAttributesWithSetters() as $attribute) {
            $value = $data['attributes'][$attribute->name] ?? null;
            if ($value === null) {
                continue;
            }
            $type = $attribute->type;
            if ($type === Scalar::TYPE_STRING) {
                $value = (string) $value;
            } elseif ($type === Scalar::TYPE_DATETIME) {
                try {
                    $value = new DateTime($value);
                } catch (Exception $e) {
                    $value = null;
                }
            } elseif ($type === Scalar::TYPE_BOOLEAN) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);;
            } elseif ($type === Scalar::TYPE_INTEGER) {
                $value = (int) $value;
            } elseif ($type === Scalar::TYPE_FLOAT) {
                $value = (double) $value;
            }
            $object = $this->setValue($object, $attribute->setter, $value);
        }
        return $object;
    }

    /**
     * @param object $object
     * @param EntityConfig $config
     * @param mixed[] $links
     * @param mixed[] $data
     * @param mixed[] $included
     * @return object
     * @throws ConfigurationException
     */
    protected function fillRelationships(object $object, EntityConfig $config, array $links, array $data, array $included): object
    {
        if (!empty($data)) {
            $keys = array_keys($links);
            foreach ($config->getRelationshipsWithSetters($keys) as $relationship) {
                $relationLink = $data['relationships'][$relationship->name]['data'];
                if ($this->isAssoc($relationLink)) {
                     $value = $this->getInnerObject($relationLink, $links[$relationship->name], $included);
                } else {
                    $value = [];
                    foreach ($relationLink as $localLink) {
                        $value[] = $this->getInnerObject($localLink, $links[$relationship->name], $included);
                    }
                }
                $object = $this->setValue($object, $relationship->setter, $value);
            }
        }
        return $object;
    }

    /**
     * @param mixed[] $included
     * @param string $type
     * @param string $id
     * @return mixed[]
     */
    protected function filterIncluded(array $included, string $type, string $id): array
    {
        return array_values(
            array_filter($included, static function ($value) use ($type, $id) {
                return isset($value['type'], $value['id']) && $value['type'] === $type && $value['id'] === $id;
            })
        );
    }

    /**
     * @param mixed[] $relationLink
     * @param mixed[] $links
     * @param mixed[] $included
     * @return object
     * @throws ConfigurationException
     */
    protected function getInnerObject(array $relationLink, array $links, array $included): object
    {
        $internalData = $this->filterIncluded($included, $relationLink['type'], $relationLink['id'])[0] ?? [];
        $config = $this->configStore->getEntityConfigByAlias($relationLink['type']);
        $internalObject = $this->setAttributes(
            $this->getOuterObject($config->class, $relationLink['id']),
            $config,
            $internalData
        );
        return $this->fillRelationships($internalObject, $config, $links, $internalData, $included);
    }

    /**
     * Extract data from external sources, like DB
     *
     * @param string $class
     * @param string|null $id
     * @return object
     * @throws ConfigurationException
     */
    protected function getOuterObject(string $class, ?string $id): object
    {
        $config = $this->configStore->getEntityConfigByClass($class);

        if ($config->type === EntityConfig::TYPE_SYNTHETIC) {
            $object = new $class;
            if ($id !== null) {
                $this->setId($object, $id);
            }
            return $object;
        }

        if ($id === null) {
            return new $class;
        }

        $object = $this->objectManager->loadByClassAndId($class, $id);
        if ($object === null) {
            $object = new $class;
        }
        return $object;
    }

    protected function isAssoc(array $array): bool {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}
