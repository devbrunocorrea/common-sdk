<?php

namespace Gpupo\CommonSdk\Entity;

use Gpupo\CommonSdk\Traits\FactoryTrait;

abstract class EntityAbstract extends CollectionAbstract
{
    use FactoryTrait;

    public function __construct(array $data = null)
    {
        if (!$this instanceof EntityInterface) {
            throw new \Exception('EntityInterface deve ser implementada');
        }

        $schema = $this->getSchema();

        if (!empty($schema)) {
            parent::__construct($this->initSchema($this->getSchema(), $data));
        }
    }

    protected function initSchema(array $schema, $data)
    {
        $get = function ($key, $default = '') use ($data) {
            $fill = $default;
            
            if (array_key_exists($key, $data)) {
                $fill = $data[$key];
            }
            
            return $fill;
        };
        
        foreach ($schema as $key => $value) {
            if ($value == 'collection') {
                $schema[$key] = $this->factoryCollection();
            } elseif ($value == 'object') {
                $schema[$key] = $this->factoryNeighborObject(ucfirst($key),
                    $get($key, []));
            } elseif ($value == 'array') {
                $schema[$key] = $get($key, []);
            } elseif (in_array($value, ['string', 'integer'])) {
                $schema[$key] = $get($key);
            }
        }

        return $schema;
    }

    protected function factoryCollection()
    {
        return new Collection;
    }

    public function toArray()
    {
        if ($this->validate()) {
            return parent::toArray();
        }
    }

    protected function validate()
    {
        foreach ($this->getSchema() as $key => $value) {
            $current = $this->get($key);
            if ($value == 'integer') {
                if (intval($current) !== $current) {
                    throw new \InvalidArgumentException($key . 'should have value of type Integer valid');
                }
            } elseif ($value == 'number') {
                if (floatval($current) != $current) {
                    throw new \InvalidArgumentException($key . 'should have value of type Number valid');
                }
            }
        }

        return true;
    }
}
