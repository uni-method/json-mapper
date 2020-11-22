<?php declare(strict_types=1);

namespace Tests\Service;

use UniMethod\JsonapiMapper\Config\AttributeConfig;
use UniMethod\JsonapiMapper\Config\ConfigStore;
use UniMethod\JsonapiMapper\Config\EntityConfig;
use UniMethod\JsonapiMapper\Config\Method;
use Tests\Auxiliary\YamlLoader;
use UniMethod\JsonapiMapper\External\ContainerManagerInterface;
use UniMethod\JsonapiMapper\External\ObjectManagerInterface;
use UniMethod\JsonapiMapper\Service\Deserializer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Auxiliary\Dummy;

class DeserializeTest extends TestCase
{
    public function testAttributes(): void
    {
        $attr1 = 1400;
        $entity = (new EntityConfig())
            ->setClass(Dummy::class)
            ->setAlias('dummy')
            ->setDescription('Dummy class for example')
            ->setAttributes([
                new AttributeConfig('testOne', 'integer', 'one', null),
                new AttributeConfig('id', 'string', 'setTwo()', null),
            ])
        ;
        $config = new ConfigStore([$entity]);
        /** @var MockObject|ObjectManagerInterface $objectManager */
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $deserialize = new Deserializer($config, $objectManager);
        $json = '{"data": {"id": "new", "type": "dummy", "attributes": {"testOne": ' . $attr1 . ', "id": "wow"}}}';
        /** @var Dummy $object */
        $object = $deserialize->handle(
            json_decode($json, true, 512, JSON_THROW_ON_ERROR),
            Method::CREATE,
            'bS.c');

        self::assertIsObject($object);
        self::assertObjectHasAttribute('one', $object);
        self::assertEquals($object->one, $attr1);
        self::assertObjectHasAttribute('two', $object);
        self::assertEquals($object->getTwo(), 'wow');
    }

    public function testNestedObjects(): void
    {
        /** @var MockObject|ContainerManagerInterface $containerManager */
        $containerManager = $this->createMock(ContainerManagerInterface::class);
        /** @var MockObject|ObjectManagerInterface $objectManager */
        $objectManager = $this->createMock(ObjectManagerInterface::class);

        $loader = new YamlLoader($containerManager);
        $config = $loader->load('tests/Auxiliary/example.yml');
        $deserialize = new Deserializer($config, $objectManager);

        $json = '{"data":{"type":"a","id":"123","attributes":{"desc":"such wow"},"relationships":{"bS":{"data":[{"type":"b","id":"1"},{"type":"b","id":"2"}]}}},"included":[{"type":"b","id":"1","attributes":{"title":"wow1"},"relationships":{"c":{"data":{"type":"c","id":"11"}}}},{"type":"c","id":"11","attributes":{"count":3},"relationships":[]},{"type":"b","id":"2","attributes":{"title":"wow2"},"relationships":{"c":{"data":{"type":"c","id":"21"}}}},{"type":"c","id":"21","attributes":{"count":3},"relationships":[]}]}';
        $object = $deserialize->handle(
            json_decode($json, true, 512, JSON_THROW_ON_ERROR),
            Method::CREATE,
            'bS.c'
        );
        self::assertIsObject($object);
    }
}
