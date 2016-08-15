<?php

namespace Parser\Provader;

class ProvaderXML
{
    const PATH_FASIONUP       = "brands_parsers/Fashionup/fUp.xml";
    const PATH_FASIONUP_ALL   = "brands_parsers/Fashionup/fUpAll.xml";
    const PATH_FASIONUP_TOAPI = 'http://fashionup.com.ua/api.php?brand=1';
    const PATH_GLEM           = "brands_parsers/Glem/glem.xml";
    const PATH_GLEM_ALL       = "brands_parsers/Glem/glem.xml";
    const PATH_GLEM_TOAPI     = 'http://www.glem.com.ua/eshop/ym4.php';
    const PATH_GHAZEL         = "brands_parsers/Ghazel/ghazel.xml";
    const PATH_GHAZEL_ALL     = "brands_parsers/Ghazel/ghazel.xml";
    const PATH_GHAZE_TOAPI    = 'http://ghazel.com.ua/ymn_uah_3500-20760,2721,23645,40721,40726,9630,40722,3656,2867,40723,40725,40724,40719,40727,40728,40720,2209,40729,6067,2226,2405,3426,4293,2324,21379.xml';

    /**
     * id's brend
     * @var int
     */
    private $idBrand;

    /**
     * step parsing
     * @var int
     */
    private $step;

    /**
     * object XML
     * @var object
     */
    public $xmlObject;

    /**
     * http-code response
     * @var int
     */
    public $statusCode;

    /**
     * http body response
     * @var string
     */
    public $pageBody;

    public function __construct($idBrand, $step)
    {
        $this->idBrand    = $idBrand;
        $this->step       = $step;
        $this->statusCode = "";
        $this->pageBody   = "";
    }

    /**
     * select and create xml object
     * @return object
     */
    public function createXMLObject()
    {
        if ($this->idBrand == 1 && $this->step == 1) {
            copy(self::PATH_FASIONUP_TOAPI, self::PATH_FASIONUP);
            $saw             = simplexml_load_file(self::PATH_FASIONUP);
            $this->xmlObject = $this->createXmlFileAll($saw);
        } else if ($this->idBrand == 6 && $this->step == 1) {
            copy(self::PATH_GLEM_TOAPI, self::PATH_GLEM);
            $this->xmlObject = simplexml_load_file(self::PATH_GLEM);
        } else if ($this->idBrand == 43 && $this->step == 1) {
            copy(self::PATH_GHAZE_TOAPI, self::PATH_GHAZEL);
            $this->xmlObject = simplexml_load_file(self::PATH_GHAZEL);
        } else if ($this->idBrand == 1 && $this->step !== 1) {
            $this->xmlObject = simplexml_load_file(self::PATH_FASIONUP_ALL);
        } else if ($this->idBrand == 6 && $this->step !== 1) {
            $this->xmlObject = simplexml_load_file(self::PATH_GLEM_ALL);
        } else if ($this->idBrand == 43 && $this->step !== 1) {
            $this->xmlObject = simplexml_load_file(self::PATH_GHAZEL_ALL);
        }
    }

    /**
     * contact xml files
     * @param object $saw
     * @return object
     */
    private function createXmlFileAll($saw)
    {
        $xmlstr = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<contentMain>
    <title>Обьединение xml</title>
XML;
        foreach ($saw->link as $value) {
            $area = $this->xmlnewObjload((string) $value);
            $xmlstr .= $this->xmlpattern($area->asXML());
        }
        $xmlstr .= <<<XML
</contentMain>
XML;
        $wovels = array("<data>", "</data>");
        $xmlstr = str_replace($wovels, "", $xmlstr);

        if (file_exists(self::PATH_FASIONUP_ALL)) {
            file_put_contents(self::PATH_FASIONUP_ALL, $xmlstr);
        } else {
            $fp = fopen(self::PATH_FASIONUP_ALL, "w");
            fwrite($fp, $xmlstr);
            fclose($fp);
        }
        $this->xmlObject = simplexml_load_file(self::PATH_FASIONUP_ALL);
        return $this->xmlObject;
    }

    /**
     * Создаем обькт XML
     * @param type $xml
     * @return type
     */
    private function xmlnewObjload($xml)
    {
        $area = simplexml_load_file($xml);
        return $area;
    }

    /**
     * Паттерн XML
     * @param type $xml
     * @return type
     */
    private function xmlpattern($xml)
    {
        $pattern = "'<\?xml.*?>'si";
        $xmlstr  = preg_replace($pattern, '', $xml);
        return $xmlstr;
    }
}