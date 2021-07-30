<?php declare(strict_types=1);

namespace Tests\Service;

use PHPUnit\Framework\MockObject\MockObject;
use Tests\Auxiliary\A;
use Tests\Auxiliary\B;
use Tests\Auxiliary\C;
use UniMethod\JsonapiMapper\Config\AttributeConfig;
use UniMethod\JsonapiMapper\Config\ConfigStore;
use UniMethod\JsonapiMapper\Config\EntityConfig;
use Tests\Auxiliary\YamlLoader;
use UniMethod\JsonapiMapper\Config\IdAttributeConfig;
use UniMethod\JsonapiMapper\External\ContainerManagerInterface;
use UniMethod\JsonapiMapper\Service\Serializer;
use PHPUnit\Framework\TestCase;
use Tests\Auxiliary\Dummy;

class SerializeTest extends TestCase
{
    public function testEverythingFine(): void
    {
        $entity = (new EntityConfig())
            ->setClass(Dummy::class)
            ->setId(
                new AttributeConfig('id', 'string', null, 'getTwo()')
            )
            ->setAlias('dummy23')
            ->setDescription('Dummy class for example')
            ->setAttributes([
                new AttributeConfig('testOne', 'integer', null, 'one'),
            ])
        ;
        $config = new ConfigStore([$entity]);
        $dummy = new Dummy();
        $dummy->one = 5;
        $dummy->setTwo('wow');
        $service = new Serializer($config);
        self::assertEquals(
            json_encode($service->handleObject($dummy), JSON_THROW_ON_ERROR, 512),
            '{"data":{"type":"dummy23","id":"wow","attributes":{"testOne":5},"relationships":[]},"included":[]}'
        );
    }

    public function testNestedObjects(): void
    {
        /** @var MockObject|ContainerManagerInterface $containerManager */
        $containerManager = $this->createMock(ContainerManagerInterface::class);

        $loader = new YamlLoader($containerManager);
        $config = $loader->load('tests/Auxiliary/example.yml');

        $a = new A();
        $a->id = '123';
        $a->desc = 'such wow';
        $a->b_s = [
            (new B())->setId('1')->setTitle('wow1')->setC((new C())->setId('11')->setCount(3)),
            (new B())->setId('2')->setTitle('wow2')->setC((new C())->setId('21')->setCount(3)),
        ];
        $serializer = new Serializer($config);
        self::assertEquals(
            json_encode($serializer->handleObject($a, 'bS.c'), JSON_THROW_ON_ERROR, 512),
            '{"data":{"type":"a","id":"123","attributes":{"desc":"such wow"},"relationships":{"bS":{"data":[{"type":"b","id":"1"},{"type":"b","id":"2"}]}}},"included":[{"type":"b","id":"1","attributes":{"title":"wow1"},"relationships":{"c":{"data":{"type":"c","id":"11"}}}},{"type":"c","id":"11","attributes":{"count":3},"relationships":[]},{"type":"b","id":"2","attributes":{"title":"wow2"},"relationships":{"c":{"data":{"type":"c","id":"21"}}}},{"type":"c","id":"21","attributes":{"count":3},"relationships":[]}]}'
        );
    }

    public function testCollection(): void
    {
        $entity = (new EntityConfig())
            ->setClass(Dummy::class)
            ->setAlias('dummy23')
            ->setId(
                new IdAttributeConfig('integer', null, 'one')
            )
            ->setDescription('Dummy class for example')
            ->setAttributes([
                new AttributeConfig('id', 'string', null, 'getTwo()'),
            ])
        ;
        $config = new ConfigStore([$entity]);
        $dummy = new Dummy();
        $dummy->one = 5;
        $dummy->setTwo('wow');

        $dummy2 = new Dummy();
        $dummy2->one = 15;
        $dummy2->setTwo('woww2');

        $service = new Serializer($config);
        self::assertEquals(
            json_encode($service->handleCollection([$dummy, $dummy2]), JSON_THROW_ON_ERROR, 512),
            '{"data":[{"type":"dummy23","id":"5","attributes":{"id":"wow"},"relationships":[]},{"type":"dummy23","id":"15","attributes":{"id":"woww2"},"relationships":[]}],"included":[]}'
        );
    }
}
