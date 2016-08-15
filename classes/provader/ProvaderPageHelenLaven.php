<?php

namespace Parser\Provader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ProvaderPageHelenLaven extends ProvaderPage
{

    /**
     * getWebPAgeFromSkHouse extends ProvaderPage
     * @param string $cookieFile
     */
    public function getWebPage($cookieFile = '')
    {
        $client = new Client([
            'timeout' => 120.0,
            'headers' => ['User-Agent' => 'Shacal makewear.com.ua']
        ]);
        try {
            $response = $client->request('GET', $this->url/*,
                ['proxy' => 'tcp://109.207.61.134',
                'debug' => true]*/);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            }
        }
        $this->pageBody   = $response->getBody()->getContents();
        $this->statusCode = $response->getStatusCode();
        $this->reason     = $response->getReasonPhrase();
    }
}