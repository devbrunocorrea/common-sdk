<?php

namespace Gpupo\CommonSdk;

use Gpupo\CommonSdk\Entity\Collection;

class Request extends Collection
{
    public function setTransport(Transport $transport)
    {
        $this->set('transport', $transport);
        
        return $this;
    }
    
    public function getTransport()
    {
        return $this->get('transport');
    }
    
    public function exec()
    {        
        $transport =  $this->getTransport()->setUrl($this->get('url'))
            ->setMethod($this->get('method', 'GET'));
        
        if ($this->get('body', false)) {
            $transport->setOption(CURLOPT_POSTFIELDS, $this->get('body'));
        }
        
        return $transport->exec();
    }
}