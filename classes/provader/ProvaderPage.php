<?php

namespace Parser\Provader;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use nokogiri;

interface iProvaderPage
{
    const GETWEBPAGE_MASSEGE_ERROR = "\n<h3 style='color:red'>getWebPage не смог прочитать страницу товара!!!</h3>\n";

    public function getWebPage($cookieFile);

    public function createNokogiriObject();
}

class ProvaderPage implements iProvaderPage
{
    /**
     * url from create nokogiri
     * @var string
     */
    protected $url;

    /**
     * http-code response
     * @var int
     */
    public $statusCode;

    /**
     * massege response
     * @var string
     */
    protected $reason;

    /**
     * nokogiri jbject
     * @var object
     */
    public $nokogiriObject;

    /**
     * http body response
     * @var string
     */
    public $pageBody;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * getWebPage to all url
     * @param string $cookieFile
     */
    public function getWebPage($cookieFile)
    {
        $client = new Client([
            'timeout' => 120.0,
            'headers' => ['User-Agent' => 'Shacal makewear.com.ua']
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
     * create nokogiri object
     * @return object nokogiri
     */
    public function createNokogiriObject()
    {
        if (($this->statusCode !== 0 ) && ($this->statusCode !== 200)) {
            echo "<h4 style='color:red'>{$this->reason}</h4>\n";
            echo "<span style='color:red'>{$this->statusCode}</span> - код ошибки!!!\n";
            echo self::GETWEBPAGE_MASSEGE_ERROR;
            echo "URL: <a href={$this->url} target='_blank' >{$this->url}</a>\n";
            echo "-------------------------------------------------------------\n\n";
            $this->nokogiriObject = FALSE;
        } else {
            $this->nokogiriObject = (new nokogiri())->fromHtmlNoCharset($this->pageBody);
        }
        return $this->nokogiriObject;
    }
}