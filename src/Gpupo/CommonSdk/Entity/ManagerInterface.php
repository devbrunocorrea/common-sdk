<?php

namespace Gpupo\CommonSdk\Entity;

interface ManagerInterface
{
    public function save(EntityInterface $entity);
    public function findById($id);
    public function fetch($offset = 1, $limit = 50);
}
