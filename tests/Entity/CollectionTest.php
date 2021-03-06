<?php

declare(strict_types=1);

/*
 * This file is part of gpupo/common-sdk
 * Created by Gilmar Pupo <contact@gpupo.com>
 * For the information of copyright and license you should read the file
 * LICENSE which is distributed with this source code.
 * Para a informação dos direitos autorais e de licença você deve ler o arquivo
 * LICENSE que é distribuído com este código-fonte.
 * Para obtener la información de los derechos de autor y la licencia debe leer
 * el archivo LICENSE que se distribuye con el código fuente.
 * For more information, see <https://opensource.gpupo.com/>.
 *
 */

namespace Gpupo\CommonSdk\Tests\Entity;

use Gpupo\Common\Entity\CollectionInterface;
use Gpupo\CommonSdk\Entity\Collection;
use Gpupo\CommonSdk\Entity\Entity;
use Gpupo\CommonSdk\Tests\TestCaseAbstract;

/**
 * @covers \Gpupo\CommonSdk\Entity\CollectionAbstract
 */
class CollectionTest extends TestCaseAbstract
{
    public function dataProviderObject()
    {
        $expected = [
            'key' => 'foo',
            'value' => 'bar',
        ];

        return [
            [new Collection([$expected]), $expected],
        ];
    }

    /**
     * @dataProvider dataProviderObject
     *
     * @param null|mixed $expected
     */
    public function testPossuiSetterParaDefinirBar(CollectionInterface $object, array $expected)
    {
        $this->assertInstanceof(Entity::class, $object->first());
    }
}
