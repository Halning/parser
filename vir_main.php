<?php

use Modules\MySQLi;
use Parser\InterfaceAdmin;
use Parser\PHPExcelParser;
use Parser\Provader\ProvaderPageFactory;
use Parser\Report\ReportVerify;

session_start();
?>
<meta charset="utf-8">
<pre>
    <?php
    $startTime = microtime(true);

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
        $linkArray  = $_SESSION['updateData']['from_url'];
        $countLinks = count($linkArray);

        //Если ссылок нет заканчиваем работу скрипта, есть создаем файл отчета
        if ($linkArray[1] == "" || $countLinks == 0) {
            $idsBrendComplite[]           = $_SESSION["id"];
            $_SESSION['idsBrendComplite'] = $idsBrendComplite;

            //Проверяем есть ли еще бренды для проверкм
            $_SESSION['idsBrend'] = array_values(array_diff($_SESSION['idsBrend'],
                    $_SESSION['idsBrendComplite']));
            //var_dump($_SESSION['idsBrend']);
            if (count($_SESSION['idsBrend']) == 0) {
                $_SESSION = array();

                // Закрываем соединение
                $mysqli->close();
                die("Скрипт закочил Работу!!!");
            }
            ?><meta http-equiv="refresh" content="5;URL=http://<?php echo $domenName ?>/parser/vir_start.php?id=<?php echo $_SESSION['idsBrend'][0] ?>"><?php
            die;
        } else {
            $report = new ReportVerify($_SESSION["cat_id"], 0, $step, "",
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

        InterfaceAdmin::init($_SESSION["id"], $countLinks)->setInterfaceVerify($step,
            $content, FALSE, FALSE);
        ?><meta http-equiv="refresh" content="5;URL=http://<?php echo $domenName ?>/parser/vir_main.php?step=1"><?php
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
        $requestUrl   = $domenName."/parser/vir_main.php?step={$nextStep}";
        $curLink      = filterLink($_SESSION["linkArray"][$step]);
        //$curLink      = 'http://vitality-opt.com.ua/detskaya-odezhda/1381-futbolka-dlja-devochek-koral.html';
//==============================================================================
//Проверяем не последняя ли ссылка на товар если нету то проверяем 
//есть ли еще бренд если нету бренда для проверки то заканчиваем скрипт
//==============================================================================    
        if ($step == $countLinks + 1) {
            $idsBrendComplite[]           = $idBrand;
            $_SESSION['idsBrendComplite'] = $idsBrendComplite;

            //Проверяем есть ли еще бренды для проверкм
            $_SESSION['idsBrend'] = array_values(array_diff($_SESSION['idsBrend'],
                    $_SESSION['idsBrendComplite']));
            //var_dump($_SESSION['idsBrend']);
            if (count($_SESSION['idsBrend']) == 0) {
                $_SESSION = array();
                // Закрываем соединение
                $mysqli->close();
                die("Скрипт закочил Работу!!!");
            }
            ?><meta http-equiv="refresh" content="5;URL=http://<?php echo $domenName ?>/parser/vir_start.php?id=<?php echo $_SESSION['idsBrend'][0] ?>"><?php
            die;
        }


//==============================================================================
//                  Подготовка обьекта для парсинга 
//==============================================================================
        //начало записи отчета
        $report = new ReportVerify($_SESSION["cat_id"], $remeindLinks, $step,
            $curLink, $countLinks);
        $report->reportStart();

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
            $provaderPage = ProvaderPageFactory::build($idBrand, $step, $curLink);
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
            if (($idBrand == 34 && ($statusCode == 301 || $statusCode == 404)) ||
                (($idBrand == 30 || $idBrand == 31) && $statusCode == 503) ||
                (($idBrand == 25 || $idBrand == 21 || $idBrand == 20 || $idBrand
                == 19 || $idBrand == 7 || $idBrand == 5 ||
                $idBrand == 30 || $idBrand == 31 || $idBrand == 13 || $idBrand == 39
                || $idBrand == 11 || $idBrand == 38 || $idBrand == 35) && $statusCode
                == 404) ||
                ($idBrand == 14 && $statusCode == 500) || ($idBrand == 35 && $statusCode
                == 410)) {
                
            } else {

                //записываем данные в файл отчета и в интерфейс
                $content = $report->reportEnd();
                InterfaceAdmin::init($idBrand, $countLinks)->setInterfaceVerify($step,
                    $content, FALSE, FALSE);
                ?><meta http-equiv="refresh" content="5;URL=http://<?php echo $requestUrl ?>"><?php
                die;
            }
        }

//==============================================================================
//       Подготовка данных проверяймого товара и запуск парсинга
//==============================================================================
        //Подготовка данных проверяймого товара
        $commodityID = $_SESSION['updateData']['commodity_ID'][$step];
        $price       = $_SESSION['updateData']['commodity_price'][$step];
        $price2      = $_SESSION['updateData']['commodity_price2'][$step];
        $comSizes    = $_SESSION['updateData']['com_sizes'][$step];
        $comOptions  = $_SESSION['updateData']['commodity_select'][$step];
        $code        = $_SESSION['updateData']['cod'][$step];
        $comName     = $_SESSION['updateData']['com_name'][$step];
        $comDesc     = $_SESSION['updateData']['com_fulldesc'][$step];
        $comCount    = $_SESSION['updateData']['size_count'][$step];

        //запускаем парсинг
        if ($_SESSION['changeCod'] == TRUE || $_SESSION['changeName'] == TRUE || $_SESSION['changeDesc']
            == TRUE) {
            $verify = "verifyImp";
        } else {
            $verify = "verify";
        }
        $resultParsArray = selectAndParserBrend($idBrand, $curLink, $saw,
            $verify, $statusCode, $pageBody, /* from cardo -> */ $mysqli,
            $commodityID);
        $verify          = "verify";

        //ПЕРЕЗАЛИВАЕМ ФОТО
        if ($_SESSION['changeIm'] == TRUE) {
            $resultImageArray = writeImage($idBrand, $curLink, $saw,
                $commodityID, $mysqli, $verify);

            //отчет если перезалилось фото
            $report->echoChangePphoto($resultImageArray['mainSrcImg'],
                $resultImageArray['dopSrcImg']);
        }

        //УДАЛЕНИЕ ТОВАРА
        $comDelete = $resultParsArray["delete"];

        //Удалить товар из интерфейса
        $comDeleteInterfice = ($_SESSION['deleteCom']) ? TRUE : FALSE;

        //$comDelete = TRUE;
        if ($comDelete == TRUE || $comDeleteInterfice == TRUE) {
            $delete        = deleteCommodityAll($idBrand, $commodityID,
                $curLink, $mysqli, $_SESSION['comVisibl']);
            //var_dump($delete);
            $visibleUpdate = TRUE;
            if ($delete == FALSE) {

                //Отчет если скрываеться или удаляеться фото
                $report->echoDeleteProd(false, $commodityID);
                updateVisiblOnly(0, $commodityID, $mysqli);
            } else {
                $report->echoDeleteProd(true, $commodityID);
            }
            $content = $report->reportEnd();
            InterfaceAdmin::init($idBrand, $countLinks)->setInterfaceVerify($step,
                $content, TRUE, $visibleUpdate);
            //die('asd');
            ?><meta http-equiv="refresh" content="2;URL=http://<?php echo $requestUrl ?>"><?php
            die;
        }


//==============================================================================
//          Подготовка,проверка,сравнение новых пропарсенных данных
//==============================================================================
        //записываем пропарсенные данные в переменные
        $comExist    = $resultParsArray["exist"];
        $priceNew    = (float) $resultParsArray["price"];
        $price2New   = (float) $resultParsArray["price2"];
        $comCountNew = $resultParsArray["count"];

        if ($idBrand == 27) {
            $comSizesNew = $comSizes;
        } else {
            $comSizesNew = $resultParsArray["sizes"];
        }
        if ($idBrand == 11) {
            $comOptionsNew = $comOptions;
        } else {
            $comOptionsNew = $resultParsArray["options"];
        }
        $codNew  = changeProperty($resultParsArray, $code,
            $_SESSION['changeCod'], "cod");
        $nameNew = changeProperty($resultParsArray, $comName,
            $_SESSION['changeName'], "name");
        $descNew = changeProperty($resultParsArray, $comDesc,
            $_SESSION['changeDesc'], "desc");
        //$duplicate = $resultParsArray["duplicate"];
        //Проверяем есть ли цена если нету то скрываем товар, цену оставляем старую
        if ($priceNew == 0 && $idBrand !== 25) {
            echo $report::STRING_NOPRICE;
            $priceNew  = $price;
            $price2New = $price2;
        }

        //проверяем изменились ли данные товара выводим что изменилось
        $update = FALSE;
        checkChanges($price, $priceNew, "Изминилась цена:");
        checkChanges($price2, $price2New, "Изминилась оптовая цена:");
        checkChanges($comSizes, $comSizesNew, "Изминились размеры:");
        checkChanges($comOptions, $comOptionsNew, "Изминились опции:");
        checkChanges($comCount, $comCountNew, "Изминилось количество:");
        checkChanges($code, $codNew, "Изминился код:");
        checkChanges($comName, $nameNew, "Изминились имя:");
        checkChanges($comDesc, $descNew, "Изминилось описание:");
        if ($price !== $priceNew || $comSizes !== $comSizesNew || $comOptions !== $comOptionsNew
            ||
            $price2 !== $price2New || $comCount !== $comCountNew || $code !== $codNew
            ||
            $comName !== $nameNew || $comDesc !== $descNew) {
            $update = TRUE;
        }

        //existProd for SwirlByswirl
        if ($idBrand == 4 && $comOptions !== $comOptionsNew) {
            $comExist = FALSE;
        }

        //если товара нет в наличии скрываем его
        if ($comExist == FALSE) {
            $comVisibl     = 0;
            $update        = TRUE;
            $visibleUpdate = TRUE;
            echo $report::STRING_NOEXIST;
        } else {
            $visibleUpdate = FALSE;
            if ($_SESSION['comVisibl'] == 1) {
                $comVisibl = 1;
            } else {

                //если товара c тегами и он появился в наличии то публикуем
                $result = checkTags($commodityID, $mysqli);
                if ($result !== 0) {
                    $visibleUpdate = TRUE;
                    $update        = TRUE;
                    $comVisibl     = 1;
                    echo $report::STRING_EXIST_PROD;
                } else {
                    $comVisibl = 0;
                }
            }
        }


//==============================================================================
//          Если какой-то значение товара поменялось то апдейтим в БД  
//==============================================================================
        if ($update == "TRUE") {
            if (!($stmt = $mysqli->prepare("UPDATE shop_commodity SET commodity_price=?,
                                                                commodity_price2=?,
                                                                com_sizes=?,
                                                                commodity_select=?,
                                                                commodity_visible=?,
                                                                size_count=?, cod=?, com_name=?, com_fulldesc=?
                                                    WHERE commodity_ID=?"))) { //duplicate=?
                die('Update shop_commodity Error ('.$mysqli->errno.') '.$mysqli->error);
            }
            $stmt->bind_param("ddssissssi", $priceNew, $price2New, $comSizesNew,
                $comOptionsNew, $comVisibl, $comCountNew, $codNew, $nameNew,
                $descNew, $commodityID);
            $stmt->execute();
            $stmt->close();
        }

        //отчет товар изменен
        $report->echoUpdateProd($update, $commodityID, $code, $comName,
            $priceNew, $price2New, $comSizesNew, $comOptionsNew, $comCountNew);

        //записываем данные в файл отчета и в интерфейс
        $content = $report->reportEnd();
        InterfaceAdmin::init($idBrand, $countLinks)->setInterfaceVerify($step,
            $content, $update, $visibleUpdate);
        //die("not");
        //Рендирим на новыю ссылку товара
        if ($idBrand == 13) {
            ?><meta http-equiv="refresh" content="2;URL=http://<?php echo $requestUrl ?>"><?php
        } else {
            ?><meta http-equiv="refresh" content="1;URL=http://<?php echo $requestUrl ?>"><?php
        }
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
 * Выбираем какой бренд парсим
 * @param type $idBrand
 * @param type $curLink
 * @param type $saw
 * @param type $verify
 * @param type $statusCode
 * @param type $pageBody
 * @param type $mysqli
 * @param type $commodityID
 * @return type
 */
function selectAndParserBrend($idBrand, $curLink, $saw, $verify, $statusCode,
                              $pageBody, $mysqli, $commodityID)
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
        case 23:
            require 'brands_parsers/B1/b1_artmilano_24(64,66).php';
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
    return $resultParsArray = array("exist" => $existProd, "price" => $price,
        "price2" => $price2, "sizes" => $sizesProd, "options" => $optionsProd,
        'count' => $comCount,
        "cod" => $codProd, "name" => $nameProd, "desc" => $descProd, /* "duplicate" => $duplicateProd, */
        "delete" => $deleteProd);
}
//==============================================================================
//                   Функции  интерфейса
//==============================================================================

/**
 * Проверяем изменения в товаре и выводим в отчет
 * @param type $propOld
 * @param type $propNew
 * @param type $str
 */
function checkChanges($propOld, $propNew, $str)
{
    if ($propOld !== $propNew) {
        $from    = array('<', '>');
        $to      = array('&lt;', '&gt;');
        $propOld = str_replace($from, $to, $propOld);
        $propNew = str_replace($from, $to, $propNew);
        echo "\n<span style='color:blue'>{$str}</span> Старое знач.({$propOld})\n Новое знач.({$propNew})\n";
    }
}

/**
 * Проверяем изменения в товаре и выводим в отчет
 * @param type $resultParsArray
 * @param type $prop
 * @param type $session
 * @param type $propName
 * @return type
 */
function changeProperty($resultParsArray, $prop, $session, $propName)
{
    if ($session == TRUE) {
        $propNew = $resultParsArray[$propName];
    } else {
        $propNew = $prop;
    }
    return $propNew;
}
//=========================Конец================================================

