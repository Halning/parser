<?php

use Modules\MySQLi;
use Parser\InterfaceAdmin;
use Parser\PHPExcelParser;
use Parser\Provader\ProvaderPageFactory;
use Parser\Report\ReportParser;

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
//                      Первая интерация step = 0                           
//==============================================================================

    $step      = filter_input(INPUT_GET, 'step', FILTER_SANITIZE_NUMBER_INT);
    $domenName = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING);
    if (isset($step) && $step == 0 && $step != "") {

        if (44 == $_SESSION["id"]) {
            require 'brands_parsers/FashionLook/fashion_look_44_312.php';
            exit;
        }

        //Сохраняем массив ссылок на товар
        $linkArray  = explode(" ", trim($_SESSION["links"]));
        $linkArray  = array_values(array_unique($linkArray));
        $linkArray  = array_combine(array_merge(array_slice(array_keys($linkArray),
                    1), array(count($linkArray))), array_values($linkArray));
        $countLinks = count($linkArray);
        //var_dump($countLinks);
        //die;
        //Если ссылок нет заканчиваем работу скрипта, есть создаем файл отчета
        if ($linkArray[1] == "" || $countLinks == 0) {
            $_SESSION = array();
            $mysqli->close();
            //setcookie("PHPSESSID", null);
            die("НЕТУ ССЫЛОК НА ТОВАР!!!");
        } else {
            $report = new ReportParser($_SESSION["cat_id"], 0, $step, "",
                $countLinks);
            $report->createFileReport();
        }

        //сохраняем количество ссылок в сессии выводим начало работы
        $_SESSION["linkArray"]  = $linkArray;
        $_SESSION["countLinks"] = $countLinks;

        //начальная запись отчета
        $report->reportStart();
        $report->echoStart($countLinks);
        $content = $report->reportEnd();

        InterfaceAdmin::init($_SESSION["id"], $countLinks)->setInterfaceParser($step,
            $content, FALSE);
        ?><meta http-equiv="refresh" content="5;URL=http://<?php echo $domenName ?>/parser/parser_main.php?step=1"><?php
    }

//==============================================================================
//           Следующие инттерации еслии есть ссылки на товар
//==============================================================================
    //Подготовка переменных для работы скрипта
    if (isset($step) && $step >= 1) {
        $insert       = FALSE;
        $idBrand      = $_SESSION["id"];
        $catId        = $_SESSION["cat_id"];
        $countLinks   = $_SESSION["countLinks"];
        $remeindLinks = $_SESSION["countLinks"] - $step;
        $nextStep     = $step + 1;
        $requestUrl   = $domenName."/parser/parser_main.php?step={$nextStep}";
        $curLink      = filterLink($_SESSION["linkArray"][$step]);
        //$curLink      = 'http://ghazel.com.ua/plate_dve_zvezdi.html';
        //Подготовка переменных для парсинга XML(Glem FasUP) и exel
//==============================================================================
//Проверяем есть ли ссылки для парсинга если нет заканчиваем,
// да парсим ссылку товара                                                 
//==============================================================================
        if ($step == $countLinks + 1) {
            $_SESSION = array();
            /* Закрываем соединение */
            $mysqli->close();
            die("Скрипт закочил Работу!!!");
        }

        //Проверяем есть ли уже товар по текущей ссылке
        $comExistId = FALSE;
        if (isset($_SESSION['linkArrayCom'][$curLink])) {
            $comExistId = $_SESSION['linkArrayCom'][$curLink];
        }

        //начало записи отчета
        $report = new ReportParser($_SESSION["cat_id"], $remeindLinks, $step,
            $curLink, $countLinks);
        $report->reportStart();
        if ($comExistId == FALSE) {
//==============================================================================
//                  Подготовка обьекта для парсинга 
//==============================================================================
//
            //усли есть exeles прайс
            if (($idBrand == 35 || $idBrand == 34 || $idBrand == 29 || $idBrand == 25)
                && $step == 1) {
                //try {
                $exelDoc = new PHPExcelParser($idBrand);
                $exelDoc->writeJsonFile();
                //} catch (Exception $ex) {
                //    $ex->getMessage();
                //}
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

            if ($saw == FALSE) {

                //записываем данные в файл отчета и в интерфейс
                $content = $report->reportEnd();
                InterfaceAdmin::init($idBrand, $countLinks)->setInterfaceParser($step,
                    $content, FALSE);
                ?><meta http-equiv="refresh" content="5;URL=http://<?php echo $requestUrl ?>"><?php
                die;
            }

            //запускаем парсинг
            $verify          = "import";
            $resultParsArray = selectAndParserBrend($idBrand, $curLink, $saw,
                $verify, $_SESSION["duplicateArray"], $statusCode, $pageBody);

//==============================================================================
//                              Запись в БД
//==============================================================================
            //Подготовка переменных товара из парсинга для записи в бд
            $comExist         = $resultParsArray['exist'];
            $price            = $resultParsArray["price"];
            $price2           = $resultParsArray["price2"];
            $comSizes         = $resultParsArray["sizes"];
            $comOptions       = $resultParsArray["options"];
            $comCount         = $resultParsArray['count'];
            $code             = $resultParsArray['cod'];
            $comName          = $resultParsArray['name'];
            $comFullDesc      = $resultParsArray['desc'];
            $comDuplicate     = $resultParsArray['existDub'][1];
            $commodityAddDate = date("Y-m-d h:i:s");
            $catId;
            $step;
            $curLink;
            if ($idBrand == 24) {
                $catId = $resultParsArray['catId'];
            }

            //Проверяем есть ли цена если нету то не записываем товар в БД
            if ($price == 0) {
                $report::STRING_NOPRICE;
            }

            //если товара в бд нет и он есть в наличии то инсертим
            //если есть уже товар или его не в наличии-ничего не делаем
            if ($comExist == TRUE) {
                $stmt = $mysqli->stmt_init();
                if (!($stmt = $mysqli->prepare("INSERT INTO shop_commodity(from_url, cod,
                                                                       com_name, commodity_price,
                                                                       commodity_price2, commodity_add_date,
                                                                       commodity_order, size_count,
                                                                       com_fulldesc, com_sizes,
                                                                       brand_id, commodity_select,
                                                                       duplicate) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)"))) {
                    die('Insert shop_commodity s Error ('.$mysqli->errno.') '.$mysqli->error);
                }
                if (!$stmt->bind_param("sssddsisssiss", $curLink, $code,
                        $comName, $price, $price2, $commodityAddDate, $step,
                        $comCount, $comFullDesc, $comSizes, $catId, $comOptions,
                        $comDuplicate)) {
                    die('Insert shop_commodity Error ('.$stmt->errno.') '.$stmt->error);
                }
                if (!$stmt->execute() || !$stmt->close()) {
                    die('Insert shop_commodity Error ('.$stmt->errno.') '.$stmt->error);
                }

                $commodityID = $mysqli->insert_id;

                //end cardo
                //записываем привязку категории к id товара
                $mysqli->query("INSERT INTO `shop_commodities-categories` SET
                      commodityID='{$commodityID}',
                      categoryID={$catId}");
                if ($mysqli->errno) {
                    die('Insert shop_commodities-categories Error ('.$mysqli->errno.') '.$mysqli->error);
                }

                //записываем алиас в бд
                $alias = transliterate($comName).'_'.transliterate($commodityID);
                $mysqli->query("UPDATE shop_commodity SET
                      alias='{$alias}' WHERE commodity_ID='{$commodityID}'");
                if ($mysqli->errno) {
                    die('Update shop_commodity SET alias Error ('.$mysqli->errno.') '.$mysqli->error);
                }

                //вызываем обработку и запись фоток
                $resultImageArray = writeImage($idBrand, $curLink, $saw,
                    $commodityID, $mysqli, $verify);

                //выводим отчет записи в БД
                $report->echoInsertProd($commodityID, $code, $comName, $price,
                    $price2, $resultImageArray['mainSrcImg'],
                    $resultImageArray['dopSrcImg'], $comSizes, $comOptions,
                    $comCount, $comFullDesc);

                $_SESSION['linkArrayCom'][$curLink] = $commodityID;
                $insert                             = TRUE;
            } else {

                //Выводим отчет если дубликат или нет в наличие
                $report->echoDublicatOrNotExist($resultParsArray['existDub'][0],
                    $remeindLinks, $step, $curLink, $catId, $code, $comName);
            }

            //записываем данные в файл отчета и в интерфейс
            $content = $report->reportEnd();

            InterfaceAdmin::init($idBrand, $countLinks)->setInterfaceParser($step,
                $content, $insert);
            //die("end");
            //Рендирим на новыю ссылку товара
            ?><meta http-equiv="refresh" content="3;URL=http://<?php echo $requestUrl ?>"><?php
        } else {

            //Если товар уже есть выводим мини отчет, рендирим на новыю ссылку товара
            $report->echoExistProd($remeindLinks, $step, $curLink, $comExistId,
                $catId);

            //записываем данные в файл отчета и в интерфейс
            $insert  = FALSE;
            $content = $report->reportEnd();

            InterfaceAdmin::init($idBrand, $countLinks)->setInterfaceParser($step,
                $content, $insert);
            //die("end");
            //Рендирим на новыю ссылку товара
            ?><meta http-equiv="refresh" content="0;URL=http://<?php echo $requestUrl ?>"><?php
        }
    }
    ?>
</pre>
<?php
//==============================================================================
//                              Функции
//==============================================================================
//==============================================================================
//            Функции для запуска парсинга и записи картинок
//==============================================================================

/**
 *
 * @param type $idBrand
 * @param type $curLink
 * @param type $saw
 * @param type $verify
 * @param type $duplicate
 * @param type $statusCode
 * @param type $pageBody
 * @return type
 */
function selectAndParserBrend($idBrand, $curLink, $saw, $verify, $duplicate,
                              $statusCode, $pageBody)
{
    switch ($idBrand) {
        case 1:
            require 'brands_parsers/Fashionup/fashionup_1_2.php';
            break;
        case 4:
            require 'brands_parsers/SwirlBySwirl/swirlbyswirl_4_3.php';
            break;
        case 5:
            require 'brands_parsers/Cardo/cardo_5_1.php';
            break;
        case 6:
            require 'brands_parsers/Glem/glem_6_15.php';
            break;
        case 7:
            require 'brands_parsers/Lenida/lenida_7_16.php';
            break;
        case 9:
            require 'brands_parsers/Sellin/sellin_9_23.php';
            break;
        case 10:
            require 'brands_parsers/Meggi/meggi_10_42.php';
            break;
        case 11:
            require 'brands_parsers/Alva/alva_11_43.php';
            break;
        case 13:
            require 'brands_parsers/Flfashion/flfashion_13_46.php';
            break;
        case 14:
            require 'brands_parsers/SKHouse/skhouse_14_49.php';
            break;
        case 16:
            require 'brands_parsers/Seventeen/seventeen_16_47.php';
            break;
        case 17:
            require 'brands_parsers/SandL/sandl_17_48.php';
            break;
        case 19:
            require 'brands_parsers/OlisStyle/olisstyle_19_58.php';
            break;
        case 20:
            require 'brands_parsers/Nelli-Co/nellico_20_62.php';
            break;
        case 21:
            require 'brands_parsers/FStyle/fstyle_21_63.php';
            break;
        case 24:
            require 'brands_parsers/B1/b1_artmilano_24(64,66).php';
            return $resultParsArray = array("cod" => $codProd, "name" => $nameProd,
                "exist" => $existProd,
                "price" => $price,
                "price2" => $price2, "sizes" => $sizesProd, "options" => $optionsProd,
                'count' => $comCount, 'desc' => $descProd,
                'existDub' => array($existDub, $duplicateProd), "catId" => $catId);
            break;
        case 25:
            require 'brands_parsers/Majaly/majaly_25_65.php';
            break;
        case 26:
            require 'brands_parsers/SergioTorri/sergio_torri_26_85.php';
            break;
        case 27:
            require 'brands_parsers/Aliya/aliya_27_86.php';
            break;
        case 29:
            require 'brands_parsers/Crisma/crisma_29_87.php';
            break;
        case 31:
        case 30:
            require 'brands_parsers/VitalityAll/vitality_31;30_205;88.php';
            break;
        case 32:
            require 'brands_parsers/HelenLaven/helen_laven_32_217.php';
            break;
        case 33:
            require 'brands_parsers/Dajs/dajs_33_215.php';
            break;
        case 34:
            require 'brands_parsers/DemboHouse/dembo_house_34_218.php';
            break;
        case 35:
            require 'brands_parsers/Jhiva/jhiva_35_219.php';
            break;
        case 36:
            require 'brands_parsers/Zdes/zdes_36_239.php';
            break;
        case 38:
            require 'brands_parsers/Vidoli/vidoli_38_241.php';
            break;
        case 39:
            require 'brands_parsers/LavanaFashion/lavanaFashion_39_242.php';
            break;
        case 40:
            require 'brands_parsers/Reform/reform_40_300.php';
            break;
        case 41:
            require 'brands_parsers/TaliTtet/tali_ttet_41_300.php';
            break;
        case 43:
            require 'brands_parsers/Ghazel/ghazel_43_311.php';
            break;
        default:
            break;
    }
    return $resultParsArray = array("cod" => $codProd, "name" => $nameProd, "exist" => $existProd,
        "price" => $price,
        "price2" => $price2, "sizes" => $sizesProd, "options" => $optionsProd, 'count' => $comCount,
        'desc' => $descProd,
        'existDub' => array($existDub, $duplicateProd));
}
//=========================Конец================================================


    
