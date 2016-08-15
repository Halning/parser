<?php

namespace Parser\Provader;

use Exception;

class ProvaderPageFactory
{

    /**
     * Factory PAttern
     * @param int $idBrand
     * @param string $step
     * @param string $url
     * @return \Parser\Provader\ProvaderPage
     */
    public static function build($idBrand, $step, $url)
    {
        if ($idBrand == 5 && $step == 1) {
            $provaderPage = new ProvaderPageCardo($url);
            $provaderPage->loginCardo("http://cardo-ua.com/authentication?back=my-account.php",
                "brands_parsers/Cardo/cookie.txt");
        }

        switch ($idBrand) {
            case 1:
            case 6:
            case 43:
                $provaderPage = new ProvaderXML($idBrand, $step);
                $provaderPage->createXMLObject();
                break;
            case 14:
                $provaderPage = new ProvaderPageSkHouse($url);
                $provaderPage->getWebPage("");
                $provaderPage->createNokogiriObject();
                break;
            case 5:
                $provaderPage = new ProvaderPageCardo($url);
                $provaderPage->getWebPage("brands_parsers/Cardo/cookie_cur.txt");
                $provaderPage->createNokogiriObject();
                break;
            case 32:
                $provaderPage = new ProvaderPageHelenLaven($url);
                $provaderPage->getWebPage();
                $provaderPage->createNokogiriObject();
                break;
            default:
                $provaderPage = new ProvaderPage($url);
                $provaderPage->getWebPage("");
                $provaderPage->createNokogiriObject();
        }
        self::objextExeption($provaderPage);
        return $provaderPage;
    }

    /**
     * Создаем исключение если не создан обьект $provaderPage
     * @param object $provaderPage
     * @throws Exception
     */
    private static function objextExeption($provaderPage)
    {
        if (!is_object($provaderPage)) {
            throw new Exception('Фабрика не создала обьект ProvaderPage');
        }
    }
}