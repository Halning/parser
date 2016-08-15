<?php

use Parser\Provader\ProvaderPageFactory;

//==============================================================================
//				Dajs	33-215				
//==============================================================================
//-------------------Переменные для записи в БД по умолчанию--------------------
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
//--------------------------------Exist URL-------------------------------1-----
if ($verify !== "import") {
    $arrayExist = $saw->get('#content > h1')->toTextArray();
    //$arrayExist = array();
    //var_dump($arrayExist);
    if (!empty($arrayExist)) {
        $deleteProd = TRUE; //если только скрыть товар то коментируем
        return; //если только скрыть товар то коментируем
    }
}
//--------------------------------Exist-----------------------------------2----- 
$arrayExist = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');

if (isset($arrayExist)) {
    foreach ($arrayExist as $value) {
        $value  = mb_strtolower(trim($value), 'utf-8');
        $findme = 'есть';
        $pos    = mb_strpos($value, $findme);
        if ($pos !== false) {
            $existProd = TRUE;
            break;
        } else {
            $existProd = FALSE;
        }
    }
}
//var_dump($existProd);
//--------------------------------Price2 -----------------------------------3---
$arrayPrice2 = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price2 - цена оптовая');
//var_dump($arrayPrice);

if (isset($arrayPrice2)) {
    $regexp = '/[^0-9.]/';
    if (count($arrayPrice2) == 1) {
        $price2 = filterPrice(trim($arrayPrice2[0]), $regexp);
    } else {
        $priceNew = 0;
        $priceArr = array();
        foreach ($arrayPrice2 as $value) {
            $priceNew = filterPrice(trim($value), $regexp);
            if ($priceNew == 0) {
                continue;
            }
            $priceArr[] = $priceNew;
        }
        if ($priceArr[1] < $priceArr[0]) {
            $price2 = $priceArr[1];
        } else {
            $price2 = $priceArr[0];
        }
    }
}
if ($price2 == 0) {
    $existProd = FALSE;
    $price     = 0;
}
//var_dump($price2);
//--------------------------------Cod-------------------------------------4-----
$arrayCod = checkEmptyOrChangeSelector($_SESSION["cod"], $saw,
    'cod - код товара');

if (isset($arrayCod)) {
    $regexp  = "/[^0-9\\/]/";
    $codProd = trim(preg_replace($regexp, "", $arrayCod[0]));
    $codProd = str_replace("/1", "", $codProd);
}
//var_dump($codProd);
//--------------------------------Price ------------------------------------5---
//$codProd = "12222460";
if ($price2 !== 0) {
    try {
        $provaderPage = ProvaderPageFactory::build(0, 0,
                "http://dajs.in.ua/index.php?route=product/search&filter_name={$codProd}");
        $sawNew       = $provaderPage->nokogiriObject;
    } catch (Exception $ex) {
        var_dump($ex->getMessage());
    }
    if ($sawNew !== FALSE) {
        $arrayPrice = checkEmptyOrChangeSelector('div.price', $sawNew,
            'price - цена розница');
        //var_dump($arrayPrice);

        if (isset($arrayPrice)) {
            $regexp = '/[^0-9.]/';
            if (count($arrayPrice) == 3) {
                $price = filterPrice(trim($arrayPrice[0]), $regexp);
            } else {
                $priceNew = 0;
                $priceArr = array();
                foreach ($arrayPrice as $value) {
                    $priceNew = filterPrice(trim($value), $regexp);
                    if ($priceNew == 0) {
                        continue;
                    }
                    $priceArr[] = $priceNew;
                }
                if (!empty($priceArr)) {
                    if ($priceArr[1] < $priceArr[0]) {
                        $price = $priceArr[1];
                    } else {
                        $price = $priceArr[0];
                    }
                }
            }
        }
        if ($price == 0) {
            $existProd = FALSE;
            $price2    = 0;
        }
    }
}
//var_dump($price);
//------------------------------------------------------------------------------
//                          Colors Size                                 
//------------------------------------------------------------------------------ 
//--------------------------------Size------------------------------------6-----
$arraySize = checkEmptyOrChangeSelector($_SESSION["sizeCol"], $saw,
    'sizeCol - размер');

if (isset($arraySize)) {
    foreach ($arraySize as $value) {
        $value = preg_replace("/\\s/u ", "", $value);
        if (!empty($value)) {
            $sizesProd = $sizesProd.trim($value).";";
        }
    }
    $sizesProd = filterSizeColors($sizesProd);
}
//var_dump($sizesProd);
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    return;
}
//--------------------------------Name------------------------------------7-----
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');

if (isset($arrayName)) {
    //$regexp = '/[0-9\\/-]/u';
    //$nameProd = trim(preg_replace($regexp, "", $arrayName[0]));
    $wovels   = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
    $nameProd = str_replace($wovels, ";", trim($arrayName[0]));
    $nameProd = trim(strstr($nameProd, ";", true));
}
//var_dump($nameProd);
//-------------------------------Description--------------------------------8---
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $strDesc   = "";
    $arrayDesc = deleteEmptyArrDescValues($arrayDesc);
    foreach ($arrayDesc as $value) {
        $strDesc .= trim($value);
    }
    $wovels      = array("состав", "наполнитель", "подкладка", "размер");
    $searchArray = array("состав:", "наполнитель:", "подкладка:", "размер:");
    $descProd    = getDesc($strDesc, $descProd, $wovels, $searchArray);
}
//var_dump($arrayDesc);
//var_dump($descProd);
//die;
//==============================================================================
//                          Проверка на дубликаты
//==============================================================================
//--------------------------------Image-------------------------------------1---
$arrayImage = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');
//var_dump($arrayImage);

$srcProd = "";
if (isset($arrayImage)) {
    $lowSrc  = str_replace("-320x480.jpg", ".jpg", $arrayImage[0]['src']);
    $lowSrc  = str_replace("cache/", "", $lowSrc);
    $srcProd = filterUrlImage($lowSrc, $curLink);
}
//var_dump($srcProd);
//--------------------Проверяем по главной картинке дубликаты-------------2-----
//var_dump($duplicate);
if (empty($duplicate)) {
    $duplicate[] = "";
}

foreach ($duplicate as $value) {
    if ($value == $srcProd) {
        $existProd = FALSE;
        $existDub  = TRUE;
        break;
    } else {
        $_SESSION["duplicateArray"][] = $srcProd;
        $_SESSION["duplicateArray"]   = array_unique($_SESSION["duplicateArray"]);
    }
}
$duplicateProd = $srcProd;
//var_dump($_SESSION["duplicateArray"]);



