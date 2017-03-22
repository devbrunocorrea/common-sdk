<?php

/*
 * This file is part of gpupo/common-sdk
 * Created by Gilmar Pupo <contact@gpupo.com>
 * For the information of copyright and license you should read the file
 * LICENSE which is distributed with this source code.
 * Para a informação dos direitos autorais e de licença você deve ler o arquivo
 * LICENSE que é distribuído com este código-fonte.
 * Para obtener la información de los derechos de autor y la licencia debe leer
 * el archivo LICENSE que se distribuye con el código fuente.
 * For more information, see <https://www.gpupo.com/>.
 */

namespace Gpupo\Tests\CommonSdk\Entity;

use Gpupo\CommonSdk\Entity\Entity;
use Gpupo\CommonSdk\Entity\EntityInterface;
use Gpupo\Tests\CommonSdk\TestCaseAbstract;
use Gpupo\Tests\CommonSdk\Traits\EntityTrait;

/**
 * @covers \Gpupo\CommonSdk\Entity\EntityAbstract
 */
class EntityTest extends TestCaseAbstract
{
    use EntityTrait;

    public static function setUpBeforeClass()
    {
        static::setFullyQualifiedObject('\Gpupo\CommonSdk\Entity\Entity');
        static::setUpEntityTest();
        parent::setUpBeforeClass();
    }

    public function dataProviderObject()
    {
        $expected = [
            'foo' => 'hello',
            'bar' => 1.2,
        ];

        return [[
            new Entity($expected),
            $expected,
        ]];
    }

    protected function factory()
    {
        return new Entity(['foo' => 'hello']);
    }

    public function testAcessoAIdentificadorPadraoDaEntidade()
    {
        $entity = $this->factory();

        $this->assertSame('hello', $entity->getId());
    }

    public function testAcessoAoNomeDaEntidadeAtual()
    {
        $entity = $this->factory();
        $this->assertSame('Entity', $entity->getCalledEntityName());
        $this->assertSame('Gpupo\CommonSdk\Entity\Entity', $entity->getCalledEntityName(true));
    }

    /**
     * @expectedException \Gpupo\CommonSdk\Exception\SchemaException
     */
    public function testValidaDadosObrigatórios()
    {
        $entity = new Entity(['foo' => '']);
        $entity->toJson();
    }

    /**
     * @dataProvider dataProviderObject
     */
    public function testPossuiGetterParaAcessoAFoo(EntityInterface $object, $expected = null)
    {
        $this->assertSchemaGetter('foo', 'string', $object, $expected);
    }

    /**
     * @dataProvider dataProviderObject
     */
    public function testPossuiSetterParaDefinirFoo(EntityInterface $object, $expected = null)
    {
        $this->assertSchemaSetter('foo', 'string', $object);
    }

    /**
     * @dataProvider dataProviderObject
     */
    public function testPossuiGetterParaAcessoABar(EntityInterface $object, $expected = null)
    {
        $this->assertSchemaGetter('bar', 'number', $object, $expected);
    }

    /**
     * @dataProvider dataProviderObject
     */
    public function testPossuiSetterParaDefinirBar(EntityInterface $object, $expected = null)
    {
        $this->assertSchemaSetter('bar', 'number', $object);
    }
}
