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

namespace Gpupo\CommonSdk\Entity;

use Closure;
use Gpupo\Common\Entity\CollectionInterface;
use Gpupo\CommonSdk\Map;

class GenericManager extends ManagerAbstract
{
    public function factorySimpleMap(array $route, array $parameters = null)
    {
        $pars = array_merge($this->fetchDefaultParameters(), (array) $parameters);
        $map = new Map($route, $pars);

        return $map;
    }

    public function getFromRoute(array $route, array $parameters = null, $body = null)
    {
        $map = $this->factorySimpleMap($route, $parameters);

        $perform = $this->perform($map, $body);

        return $perform->getData();
    }

    public function requestWithCache(array $route, string $identifier, $body = null, bool $renew = false, Closure $normalizer = null): CollectionInterface
    {
        $cacheId = $this->getClient()->simpleCacheGenerateId($identifier);

        if (false === $renew && $this->getClient()->getSimpleCache()->has($cacheId)) {
            $this->log('info', 'Using cached response', [
                'route' => $route,
                'cacheId' => $cacheId,
            ]);

            return $this->getClient()->getSimpleCache()->get($cacheId);
        }

        $response = $this->getFromRoute($route, $this->getClient()->getOptions()->toArray(), $body);

        if ($normalizer) {
            $response = $normalizer($response);
        }

        $this->getClient()->getSimpleCache()->set($cacheId, $response, $this->getClient()->getOptions()->get('cacheTTL', 3600));
        $this->log('info', 'Saving response to cache', [
            'route' => $route,
            'cacheId' => $cacheId,
            'renew' => $renew,
        ]);

        return $response;
    }
}
