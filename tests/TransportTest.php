<?php

/*
 * This file is part of gpupo/common-sdk
 * Created by Gilmar Pupo <g@g1mr.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * For more information, see <http://www.g1mr.com/>.
 */

namespace Gpupo\Tests\CommonSdk;

use Gpupo\Common\Entity\Collection;
use Gpupo\CommonSdk\Transport;

class TransportTest extends TestCaseAbstract
{
    public function testRecebeObjetoOptions()
    {
        $transport = new Transport(new Collection([]));

        return $transport;
    }

    /**
     * @testdox Executa uma requisição para url informada
     * @depends testRecebeObjetoOptions
     */
    public function testExec(Transport $transport)
    {
        $transport->setUrl('https://github.com/');
        $data = $transport->exec();
        $this->assertSame(200, $data['httpStatusCode']);

        return $transport;
    }

    /**
     * @testdox Possui informações sobre a última requisição
     * @depends testExecutaRequisiçãoAUmaUrlInformada
     */
    public function testLastTransfer($transport)
    {
        $lastTransfer = $transport->getLastTransfer();
        $this->assertInstanceof("\Gpupo\Common\Entity\Collection", $lastTransfer);
        $this->assertSame('https://github.com/', $lastTransfer->get('url'));
    }
}
