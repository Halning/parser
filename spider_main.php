<?php

use Modules\MySQLi;
use Parser\InterfaceAdmin;
use Parser\Provader\ProvaderPageFactory;
use Parser\Report\ReportSpider;

session_start();
?>
<meta charset="utf-8">
<pre>
    <?php
    $startTime = microtime(true);
    error_reporting(-1);

    require_once("../vendor/autoload.php");

    $mysqli = MySQLi::getInstance()->getConnect();

    include 'import_verify_functions.php';
//==============================================================================
//                      Первая интерация step = 0                           1
//==============================================================================
    $step      = filter_input(INPUT_GET, 'step', FILTER_SANITIZE_NUMBER_INT);
    $domenName = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING);
    if (isset($step) && $step == 0 && $step != "") {

        //Сохраняем массив ссылок на товар
        $linkArray  = explode(";", $_SESSION["im_url"]);
        array_pop($linkArray);
        $countLinks = count($linkArray);
        //var_dump($linkArray);
        //Если ссылок нет заканчиваем работу скрипта, есть создаем файл отчета
        if ($countLinks == 0) {
            $_SESSION = array();
            die("НЕТУ ССЫЛОК НА КАТЕГОРИИ!!!");
        } else {
            $report = new ReportSpider($_SESSION["cat_id"], $countLinks, 0,
                $step);
            $report->createFileReport();
        }

        //сохраняем количество ссылок и ссылки на товары которые уже есть в БД
        //в сессии выводим начало работы
        $_SESSION['linksArr']   = array();
        $_SESSION["linkArray"]  = $linkArray;
        $_SESSION["countLinks"] = $countLinks;
        $_SESSION["linkArray"]  = array_combine(array_merge(array_slice(array_keys($_SESSION["linkArray"]),
                    1), array(count($_SESSION["linkArray"]))),
            array_values($_SESSION["linkArray"]));

        //начальная запись отчета
        $report->reportStart();
        $report->echoSpiderStart($_SESSION["linkArray"]);
        $content = $report->reportEnd();
        ?><meta http-equiv="refresh" content="5;URL=http://<?php echo $domenName ?>/parser/spider_main.php?step=1"><?php
    }


//==============================================================================
//           Следующие инттерации еслии есть ссылки на товар
//==============================================================================
    //Подготовка переменных для работы скрипта
    if (isset($step) && $step >= 1) {
        $idBrand      = $_SESSION["id"];
        $catId        = $_SESSION["cat_id"];
        $countLinks   = $_SESSION["countLinks"];
        $remeindLinks = $_SESSION["countLinks"] - $step;
        $nextStep     = $step + 1;
        $requestUrl   = $domenName."/parser/spider_main.php?step={$nextStep}";

//==============================================================================
//Проверяем не последняя ли ссылка на товар если нету то проверяем 
//есть ли еще бренд если нету бренда для проверки то заканчиваем скрипт
//==============================================================================   
        if ($step == $countLinks + 1) {
            $report   = new ReportSpider($_SESSION["cat_id"], $countLinks,
                $remeindLinks, $step);
            $report->reportStart();
            insertNewLinksInDb($report, $_SESSION['linksArr'], $mysqli,
                $idBrand, $_SESSION['updateData']);
            $content  = $report->reportEnd();
            $_SESSION = array();
            //Закрываем соединение
            $mysqli->close();
            die("Скрипт закочил Работу!!!");
        }
        $curLinkCat = trim(filterLink($_SESSION["linkArray"][$step]));
        //$curLinkCat = "http://helenlaven.com.ua/boy_kolgoty/page/$";
//==============================================================================
//                          Парсим ссылки постранично
//==============================================================================
        $report     = new ReportSpider($_SESSION["cat_id"], $countLinks,
            $remeindLinks, $step);
        $report->reportStart();
        $report->echoNewLinks();

        $sellinI = 1;
        for ($i = 1; $i < 200; $i++) {

            //Готовим ссылку для парсинга
            $wovels        = array("$", "#");
            $wovelsReplace = array($i, ";");
            $curLink       = trim(str_replace($wovels, $wovelsReplace,
                    $curLinkCat));
            if ($idBrand == 9) {
                $curLink = trim(str_replace("**", $sellinI, $curLinkCat));
                $sellinI = $sellinI + 51;
            }

            //страница поставщика по URL
            try {
                $provaderPage = ProvaderPageFactory::build($idBrand, $step,
                        $curLink);
                if (property_exists($provaderPage, 'nokogiriObject')) {
                    $saw = $provaderPage->nokogiriObject;
                } else {
                    $saw = $provaderPage->xmlObject;
                }
                $statusCode = $provaderPage->statusCode;
                $pageBody   = $provaderPage->pageBody;
            } catch (Exception $ex) {
                var_dump($ex->getMessage());
            }

            //Проверяем работает ли ссылка если не работает то парсим следующюю
            if ($saw == FALSE) {
                ?><meta http-equiv="refresh" content="5;URL=http://<?php echo $requestUrl ?>"><?php
                die;
            }

            //Запускаем парсинг ссылки и проверяем не последняя ли страница
            $resultParsArray      = selectAndParserBrendLinks($idBrand,
                $curLink, $saw, $_SESSION['linksArr'], $i, $pageBody,
                $statusCode);
            $_SESSION['linksArr'] = $resultParsArray[0];
            $existUrl             = $resultParsArray[1];
            if ($existUrl == FALSE) {
                break;
            }
        }

        //записываем данные в файл отчета и в интерфейс
        echo count($_SESSION['linksArr'])."\n";
        $content = $report->reportEnd();
//----------------Рендирим на новыю ссылку товара-----------------------------7-        
        ?><meta http-equiv="refresh" content="3;URL=http://<?php echo $requestUrl ?>"><?php
        die;
    }
    ?>
</pre>
<?php
//==============================================================================
//                              Функции
//==============================================================================
//==============================================================================
//                   Функции для запуска парсинга
//==============================================================================

/**
 *
 * @param type $idBrand
 * @param type $curLink
 * @param type $saw
 * @param type $linksArr
 * @param type $i
 * @param type $pageBody
 * @param type $statusCode
 * @return type
 */
function selectAndParserBrendLinks($idBrand, $curLink, $saw, $linksArr, $i,
                                   $pageBody, $statusCode)
{
    switch ($idBrand) {
        case 1:
            require 'brands_parsers/Fashionup/fashionup_spider.php';
            break;
        case 4:
            require 'brands_parsers/SwirlBySwirl/swirlbyswirl_spider.php';
            break;
        case 5:
            require 'brands_parsers/Cardo/cardo_spider.php';
            break;
        case 6:
            require 'brands_parsers/Glem/glem_spider.php';
            break;
        case 7:
            require 'brands_parsers/Lenida/lenida_spider.php';
            break;
        case 9:
            require 'brands_parsers/Sellin/sellin_spider.php';
            break;
        case 10:
            require 'brands_parsers/Meggi/meggi_spider.php';
            break;
        case 11:
            require 'brands_parsers/Alva/alva_spider.php';
            break;
        case 13:
            require 'brands_parsers/Flfashion/flfashion_spider.php';
            break;
        case 14:
            require 'brands_parsers/SKHouse/skhouse_spider.php';
            break;
        case 16:
            require 'brands_parsers/Seventeen/seventeen_spider.php';
            break;
        case 17:
            require 'brands_parsers/SandL/sandl_spider.php';
            break;
        case 19:
            require 'brands_parsers/OlisStyle/olisstyle_spider.php';
            break;
        case 20:
            require 'brands_parsers/Nelli-Co/nellico_spider.php';
            break;
        case 21:
            require 'brands_parsers/FStyle/fstyle_spider.php';
            break;
        case 24:
            require 'brands_parsers/B1/b1_spider.php';
            break;
        case 25:
            require 'brands_parsers/Majaly/majaly_spider.php';
            break;
        case 26:
            require 'brands_parsers/SergioTorri/sergio_torri_spider.php';
            break;
        case 27:
            require 'brands_parsers/Aliya/aliya_spider.php';
            break;
        case 29:
            require 'brands_parsers/Crisma/crisma_spider.php';
            break;
        case 31:
        case 30:
            require 'brands_parsers/VitalityAll/vitality_spider.php';
            break;
        case 32:
            require 'brands_parsers/HelenLaven/helen_laven_spider.php';
            break;
        case 33:
            require 'brands_parsers/Dajs/dajs_spider.php';
            break;
        case 34:
            require 'brands_parsers/DemboHouse/dembo_house_spider.php';
            break;
        case 35:
            require 'brands_parsers/Jhiva/jhiva_spider.php';
            break;
        case 36:
            require 'brands_parsers/Zdes/zdes_spider.php';
            break;
        case 38:
            require 'brands_parsers/Vidoli/vidoli_spider.php';
            break;
        case 39:
            require 'brands_parsers/LavanaFashion/lavanaFashion_spider.php';
            break;
        case 40:
            require 'brands_parsers/Reform/reform_spider.php';
            break;
        case 41:
            require 'brands_parsers/TaliTtet/tali_ttet_spider.php';
            break;
        case 43:
            require 'brands_parsers/Ghazel/ghazel_spider.php';
            break;
        default:
            break;
    }
    return $resultParsArray = array($linksArr, $existUrl);
}

/**
 * Записываем новые ссылки в БД
 * @param type $linksArr
 * @param type $mysqli
 * @param type $idBrand
 * @param type $updateData
 */
function insertNewLinksInDb($report, $linksArr, $mysqli, $idBrand, $updateData)
{
    if ($updateData !== FALSE) {
        $updateData["from_url"] = array_flip(array_unique($updateData["from_url"]));
    }
    $linksArr = array_values(array_unique($linksArr));
    $linksNew = "";
    foreach ($linksArr as $value) {
        if (!isset($updateData["from_url"][$value])) {
            $linksNew .= trim($value)." ";
        }
    }
    $arrNewLinks   = explode(" ", $linksNew);
    array_pop($arrNewLinks);
    $countNewLinks = count($arrNewLinks);
    $couLinks      = count($linksArr);
    //var_dump($linksNew);
    //die;
    if (!($stmt          = $mysqli->prepare("UPDATE parser SET new_links=? WHERE id=?"))) {
        die('Update shop_commodity Error ('.$mysqli->errno.') '.$mysqli->error);
    }
    $stmt->bind_param("si", $linksNew, $idBrand);
    $stmt->execute();
    $stmt->close();

    //Финальный отчет паука
    $report->echoSpiderFinal($couLinks, $countNewLinks, $arrNewLinks);

    //записываем в интерфейс фдминки
    InterfaceAdmin::init($idBrand, $couLinks)->setInterfaceSpider($countNewLinks);
    var_dump($updateData["from_url"]);
}

/**
 * strrev с нормальной кодировкой
 * @param type $str
 * @return type
 */
function utf8_strrev($str)
{
    preg_match_all('/./us', $str, $ar);
    return join('', array_reverse($ar[0]));
}
//=========================Конец================================================