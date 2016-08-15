<?php

namespace Parser\Provader;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\RequestException;

class ProvaderPageCardo extends ProvaderPage
{
    /**
     * path to cookie
     * @var string
     */
    private $cookieFile;

    /**
     * getWebPAgeFromCardo extends ProvaderPage
     * @param string $cookieFile
     */
    public function getWebPage($cookieFile)
    {
        $this->cookieFile = $cookieFile;
        $this->getCurrensyCardo("http://cardo.com.ua/changecurrency.php?rand=1457527032864");
        $cookieJar        = new FileCookieJar($this->cookieFile);
        $client           = new Client([
            'timeout' => 2.0,
            'cookies' => $cookieJar
        ]);
        try {
            $response = $client->request('GET', $this->url);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            }
        }
        $this->pageBody   = $response->getBody()->getContents();
        $this->statusCode = $response->getStatusCode();
        $this->reason     = $response->getReasonPhrase();
    }

    /**
     * write cookie file from currency
     * @param string $url
     */
    private function getCurrensyCardo($url)
    {
        $cookieJar = new FileCookieJar($this->cookieFile);
        $client    = new Client([
            'timeout' => 2.0,
            'cookies' => $cookieJar
        ]);
        try {
            $client->request('POST', $url,
                [
                'cookies' => $cookieJar,
                'form_params' => [
                    'id_currency' => '1'
                ]
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            echo $response->getStatusCode();
            echo $response->getReasonPhrase();
        }
    }

    /**
     * Auth Cardo site
     * @param string $url
     * @param string $cookieFileAuth path to cookie auth
     */
    public function loginCardo($url, $cookieFileAuth)
    {
        $this->cookieFile = $cookieFileAuth;
        $this->getCurrensyCardo("http://cardo-ua.com/changecurrency.php?rand=1457530350103");
        $cookieJar        = new FileCookieJar($this->cookieFile);
        $client           = new Client([
            'timeout' => 2.0,
            'cookies' => $cookieJar
        ]);
        try {
            $client->request('POST', $url,
                [
                'cookies' => $cookieJar,
                'form_params' => [
                    'email' => 'v.chupovsky@makewear.com.ua', //'email' => 'a.homenko@makewear.com.ua',
                    'passwd' => 'qqqqqqqqq', //'passwd' => '80983522900a',
                    'back' => 'my-account.php',
                    'SubmitLogin' => 'Вход'
                ]
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            echo $response->getStatusCode();
            echo $response->getReasonPhrase();
        }
    }
}