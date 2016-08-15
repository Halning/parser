<?php

namespace Parser\Provader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ProvaderPageSkHouse extends ProvaderPage
{

    /**
     * getWebPAgeFromSkHouse extends ProvaderPage
     * @param string $cookieFile
     */
    public function getWebPage($cookieFile)
    {
        $jar    = new \GuzzleHttp\Cookie\CookieJar;
        $client = new Client([
            'timeout' => 20.0,
            'cookies' => $jar
        ]);
        try {
            $response = $client->request('GET',
                "http://sk-house.ua/Products/SetCurrency?cur=%D0%93%D0%A0%D0%9D",
                [ "curl" => [
                    CURLOPT_REFERER => $this->url
            ]]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            }
        }
        $this->pageBody   = $response ? $response->getBody()->getContents() : null;
        $this->statusCode = $response ? $response->getStatusCode() : 'none';
        $this->reason     = $response ? $response->getReasonPhrase() : 'none';

    }
}