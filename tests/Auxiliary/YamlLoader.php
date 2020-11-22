<?php declare(strict_types=1);

namespace Tests\Auxiliary;

use UniMethod\JsonapiMapper\Config\AttributeConfig;
use UniMethod\JsonapiMapper\Config\ConfigStore;
use UniMethod\JsonapiMapper\Config\EntityConfig;
use UniMethod\JsonapiMapper\Config\Event;
use UniMethod\JsonapiMapper\Config\RelationshipConfig;
use UniMethod\JsonapiMapper\External\ConfigLoaderInterface;
use UniMethod\JsonapiMapper\External\ContainerManagerInterface;
use Symfony\Component\Yaml\Yaml;

class YamlLoader implements ConfigLoaderInterface
{
    /** @var ContainerManagerInterface */
    protected ContainerManagerInterface $containerManager;

    public function __construct(ContainerManagerInterface $containerManager)
    {
        $this->containerManager = $containerManager;
    }

    public function load(string $path): ConfigStore
    {
        $value = Yaml::parseFile($path);
        $entities = [];
        foreach ($value['entities'] as $type => $item) {
            $entity = (new EntityConfig())
                ->setDescription($item['description'])
                ->setAlias($type)
                ->setClass($item['class']);

            $entity->setPostLoadHandlers(array_map(function ($classOrAlias) {
                return $this->containerManager->getService($classOrAlias);
            }, $item[Event::POST_LOAD] ?? []));

            $entity->setPreCreateHandlers(array_map(function ($classOrAlias) {
                return $this->containerManager->getService($classOrAlias);
            }, $item[Event::PRE_CREATE] ?? []));

            $attributes = [];
            $attributesRaw = $item['attributes'] ?? [];
            foreach ($attributesRaw as $internalType => $attribute) {
                $attributes[] = new AttributeConfig($internalType, $attribute['type'], $attribute['setter'], $attribute['getter']);
            }

            $relationships = [];
            $relationshipsRaw = $item['relationships'] ?? [];
            foreach ($relationshipsRaw as $internalType => $relationship) {
                $relationships[] = new RelationshipConfig($internalType, $relationship['type'], $relationship['setter'], $relationship['getter']);
            }

            $entity->setAttributes($attributes)->setRelationships($relationships);
            $entities[] = $entity;
        }
        return new ConfigStore($entities);
    }
}
