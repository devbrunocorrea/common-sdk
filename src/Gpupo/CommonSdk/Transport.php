<?php

namespace Gpupo\CommonSdk;

use Gpupo\CommonSdk\Entity\Collection;
use Gpupo\CommonSdk\Exception\RuntimeException;

class Transport extends Collection
{
    protected $curl;

    public function setOption($option, $value)
    {
        return curl_setopt($this->curl, $option, $value);
    }

    public function getInfo($option)
    {
        return curl_getinfo($this->curl, $option);
    }

    public function __construct(Collection $options)
    {
        $this->curl = curl_init();
        $this->setOption(CURLOPT_SSLVERSION, 3);
        $this->setOption(CURLOPT_RETURNTRANSFER, true );
        $this->setOption(CURLOPT_VERBOSE, $options->get('verbose'));
        
        parent::__construct([]);
    }
    
    public function setUrl($url)
    {
        $this->set('url', $url);
        $this->setOption(CURLOPT_URL, $url);
        
        return $this;
    }
    
    public function getMethod()
    {
        return strtoupper($this->get('method', 'GET'));
    }
    
    public function exec()
    {
        switch ($this->getMethod()) {
            case 'POST':
                $this->setOption(CURLOPT_POST, true);
                $this->setOption(CURLOPT_POSTFIELDS, $this->getBody());
        
                break;
            case 'PUT':
                $this->setOption(CURLOPT_PUT, true);
                $pointer = fopen('php://temp/maxmemory:512000', 'w+');

                if (!$pointer) {
                    throw new RuntimeException('Could not open temp memory data');
                }
                
                fwrite($pointer, $this->getBody());
                fseek($pointer, 0);

                $this->setOption(CURLOPT_BINARYTRANSFER, true);
                $this->setOption(CURLOPT_INFILE, $pointer);
                $this->setOption(CURLOPT_INFILESIZE, strlen($this->getBody()));
                //curl_setopt($request, CURLOPT_POSTFIELDS, $body);
                //curl_setopt($request, CURLOPT_CUSTOMREQUEST, "PUT");
                
                break;   
        }
     
        $data = [
            'responseRaw'       => curl_exec($this->curl),
            'httpStatusCode'    => $this->getInfo(CURLINFO_HTTP_CODE),
        ];
        
        curl_close($this->curl);
        
        return $data;
    }
    
    public function toLog()
    {
        
    }
}
