<?php

use Parser\Provader\ProvaderPageFactory;

//==============================================================================
//			Fashion up   	1-2          		
//==============================================================================
//Переменные для записи в БД по умолчанию
$existProd     = TRUE;
$deleteProd    = FALSE;
$codProd       = "";
$price         = 0;
$price2        = 0;
$sizesProd     = "";
$optionsProd   = "";
$comCount      = "";
$nameProd      = "";
$descProd      = "";
$existDub      = FALSE;
$duplicateProd = "";
//var_dump($curLink);
//Exist URL
/* if ($verify !== "import" && ($statusCode  == 404)) {
  $deleteProd = TRUE;
  return;
  } */
//Exist
try {
    $provaderPage = ProvaderPageFactory::build(0, 0, $curLink);
    $sawNok       = $provaderPage->nokogiriObject;
} catch (Exception $ex) {
    var_dump($ex->getMessage());
}

$arrayExist = checkEmptyOrChangeSelector($_SESSION['no_nal'], $sawNok,
    'no_nal - наличие');
//var_dump($arrayExist);
//die;
if (isset($arrayExist)) {
    $exist  = mb_strtolower(trim($arrayExist[0]), 'utf-8');
    $findme = 'нет в';
    $pos    = mb_strpos($exist, $findme);
    if ($pos !== FALSE) {
        $existProd = FALSE;
    }
}

if ($existProd == TRUE) {
    foreach ($saw->content as $key => $content) {
        $url = (string) $content->url;
        if ($url == $curLink) {
            //var_dump($content);
            $price                  = (int) (string) $content->cost;
            $price2                 = (int) (string) $content->{'cost-opt'};
            $colSize                = str_replace(",", ";",
                (string) $content->sizes);
            $descArray["Состав:"]   = (string) $content->cloth;
            $descArray["Цвет:"]     = (string) $content->color;
            $descArray["Описание:"] = (string) $content->descr;
            $arrayIdUrl             = array("cod" => (string) $content->articul,
                "price" => (int) ceil($price), "price2" => (int) ceil($price2),
                "name" => (string) $content->title, "colorSize" => $colSize, "desc" => $descArray,
                "imgMain" => (string) $content->poster);
            break;
        } else {
            continue;
        }
    }

//Price price2
    if (!isset($arrayIdUrl)) {
        $existProd = FALSE;
    } else {
        $price               = $arrayIdUrl["price"];
        $price2              = $arrayIdUrl["price2"];
        $sizesProd           = $arrayIdUrl["colorSize"];
        $_SESSION["imgMain"] = $arrayIdUrl["imgMain"];
        if ($price == 0) {
            $existProd = FALSE;
            $price2    = 0;
        }
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
        if ($verify == "verify") {
            return;
        }
        $nameProd = $arrayIdUrl["name"];
        $codProd  = $arrayIdUrl["cod"];
        $descProd = changeDesc($arrayIdUrl["desc"]);
    }
}

//var_dump($existProd);
//var_dump($price);
//var_dump($price2);
//var_dump($sizesProd);
//var_dump($nameProd);
//var_dump($codProd);
//var_dump($descProd);
//var_dump($_SESSION["imgMain"]);
//die;

/**
 * Функция обработки описания Glem
 * @param type $descArray
 * @return string
 */
function changeDesc($descArray)
{
    $beginSelectorP    = '<p>';
    $beginSelectorSpan = '<span>';
    $endSelectorSpan   = '</span>';
    $endSelectorP      = '</p>';
    $descProd          = "";
    foreach ($descArray as $key => $value) {
        if ($value !== "") {
            $descProd .= $beginSelectorP.$beginSelectorSpan.$key.$endSelectorSpan.$value.$endSelectorP;
        }
    }
    return $descProd;
}
