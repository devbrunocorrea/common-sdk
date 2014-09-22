<?php

namespace Gpupo\CommonSdk;

abstract class ClientAbstract
{
    use Traits\LoggerTrait;
    use Traits\SingletonTrait;
    use Traits\OptionsTrait;
    
    public function factoryRequest($resource, $post = false)
    {
        $curlClient = curl_init();
        curl_setopt($curlClient, CURLOPT_SSLVERSION, 3);
        curl_setopt( $curlClient , CURLOPT_POST, $post);
        curl_setopt( $curlClient , CURLOPT_RETURNTRANSFER, true );
        curl_setopt($curlClient, CURLOPT_VERBOSE, $this->getOptions()->get('verbose'));
        curl_setopt($curlClient, CURLOPT_URL, $this->getResourceUri($resource));

        return $curlClient;
    }

    public function __construct($options = [])
    {
        $this->setOptions($options);
    }

    protected function exec($request)
    {
        $data = [];
        $data['responseRaw'] = curl_exec($request);
        $data['httpStatusCode'] = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);

        $this->debug('exec',$data);
        
        return new Response($data);
    }

    public function get($resource)
    {
        $request = $this->factoryRequest($resource);

        return $this->exec($request);
    }

    public function post($resource, $body)
    {
        $request = $this->factoryRequest($resource, true);

        curl_setopt($request, CURLOPT_POSTFIELDS, $body);

        return $this->exec($request);
    }

    public function put($resource, $body)
    {
        $request = $this->factoryRequest($resource, true);

        curl_setopt($request, CURLOPT_PUT, true);

        $pointer = fopen('php://temp/maxmemory:512000', 'w+');
        //$pointer = tmpfile();
        if (!$pointer) {
            throw new \Exception('could not open temp memory data');
        }
        fwrite($pointer, $body);
        fseek($pointer, 0);

        curl_setopt($request, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($request, CURLOPT_INFILE, $pointer);
        curl_setopt($request, CURLOPT_INFILESIZE, strlen($body));

        //curl_setopt($request, CURLOPT_POSTFIELDS, $body);
        //curl_setopt($request, CURLOPT_CUSTOMREQUEST, "PUT");
        return $this->exec($request);
    }
}
