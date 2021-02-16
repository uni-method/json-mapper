<?php declare(strict_types=1);

namespace Tests\Service;

use Tests\Auxiliary\A;
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
    /** @var MockObject|ContainerManagerInterface $containerManager */
    protected $containerManager;

    /** @var MockObject|ObjectManagerInterface $objectManager */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->containerManager = $this->createMock(ContainerManagerInterface::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
    }

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
        $deserialize = new Deserializer($config, $this->objectManager);
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
        $loader = new YamlLoader($this->containerManager);
        $config = $loader->load('tests/Auxiliary/example.yml');
        $deserialize = new Deserializer($config, $this->objectManager);

        $json = $this->getJsonIncluded();
        $object = $deserialize->handle(
            json_decode($json, true, 512, JSON_THROW_ON_ERROR),
            Method::CREATE,
            'bS.c'
        );
        self::assertIsObject($object);
        self::assertEquals(3, $object->b_s[0]->c->count);
    }

    public function testNestedInnerObjects(): void
    {
        $loader = new YamlLoader($this->containerManager);
        $config = $loader->load('tests/Auxiliary/example.yml');
        $deserialize = new Deserializer($config, $this->objectManager);

        $json = $this->getJsonInner();
        /** @var A $object */
        $object = $deserialize->handle(
            json_decode($json, true, 512, JSON_THROW_ON_ERROR),
            Method::CREATE,
            'bS.c'
        );
        self::assertIsObject($object);
        self::assertEquals(3, $object->b_s[0]->c->count);
        self::assertEquals("wow2", $object->b_s[1]->title);
    }

    public function testNotFilledRelation(): void
    {
        $loader = new YamlLoader($this->containerManager);
        $config = $loader->load('tests/Auxiliary/example.yml');
        $deserialize = new Deserializer($config, $this->objectManager);

        $json = $this->getJsonIncluded();
        $object = $deserialize->handle(
            json_decode($json, true, 512, JSON_THROW_ON_ERROR),
            Method::CREATE,
            'bS.c,d'
        );
        self::assertIsObject($object);
        self::assertEquals(3, $object->b_s[0]->c->count);
    }

    protected function getJsonIncluded(): string
    {
        return file_get_contents(dirname(__DIR__) . '/Auxiliary/body_included.json');
    }

    protected function getJsonInner(): string
    {
        return file_get_contents(dirname(__DIR__) . '/Auxiliary/body_inner.json');
    }
}
