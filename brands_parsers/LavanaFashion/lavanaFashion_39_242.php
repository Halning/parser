<?php
//==============================================================================
//                       Lavana Fashion	39-242	         		
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
if ($verify !== "import" && ($statusCode == 404)) {
    $deleteProd = TRUE;
    return;
}
//--------------------------------Exist-----------------------------------2-----
$arrayExist = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
//var_dump($arrayExist);
if (isset($arrayExist)) {
    foreach ($arrayExist as $value) {
        $value  = mb_strtolower(trim($value), 'utf-8');
        $findme = 'нет в нал';
        $pos    = mb_strpos($value, $findme);
        if ($pos !== false) {
            $existProd = FALSE;
            break;
        }
    }
}

//Exist depends foto
$arrayImage = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');
if (!isset($arrayImage)) {
    $existProd = FALSE;
}
//var_dump($existProd);
//die;
//---------------------------Price price2-----------------------------------3---
//Price
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9.]/';

    $price = ceil(filterPrice(trim($arrayPrice[1]), $regexp) * $_SESSION['updatePrice']);
}
//var_dump($price);
//price2
$arrayPrice2 = checkEmptyOrChangeSelector($_SESSION['price2'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice2)) {
    $arrayPrice2 = deleteEmptyArrDescValues($arrayPrice2);
    //var_dump($arrayPrice);
    $regexp      = '/[^0-9.]/';
    $price2      = filterPrice(trim($arrayPrice2[1]), $regexp);
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}
//var_dump($price);
//var_dump($price2);
//------------------------------------------------------------------------------
//                          Colors Size                                 
//------------------------------------------------------------------------------
//-------------------------------Size---------------------------------------4---
preg_match("/new Product.Config\((.*)\);/", $pageBody, $matches);
//var_dump($matches);

if (isset($matches[1])) {
    $json       = json_decode($matches[1], true);
    $arraySize  = $json['attributes'][180]['options'];
    //var_dump($json['attributes'][180]['options']);
    $arrayColor = $json['attributes'][92]['options'];
}

if (isset($arraySize)) {
    foreach ($arraySize as $value) {
        $sizesProd = $sizesProd.trim($value['label']).";";
    }
    $sizesProd = filterSizeColors($sizesProd);
}

if (isset($arrayColor)) {
    $descProdColor = "<p><span>Цвет:</span>";
    foreach ($arrayColor as $value) {
        $descProdColor .= trim(mb_strtolower($value['label'], "utf-8"))."</p>";
    }
    //$descProdColor = filterSizeColors($descProd);
}
//var_dump($descProdColor);
//var_dump($sizesProd);
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    return;
}
//--------------------------------Cod------------------------------------5-----
$arrayCod = checkEmptyOrChangeSelector($_SESSION['cod'], $saw,
    'cod - код товара');
//var_dump($arrayCod);

if (isset($arrayCod)) {
    $arrayCod = deleteEmptyArrDescValues($arrayCod);
    $codProd  = trim($arrayCod[1]);
}
//var_dump($codProd);
//--------------------------------Name------------------------------------6-----
$arrayName = checkEmptyOrChangeSelector($_SESSION['h1'], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $arrayName = deleteEmptyArrDescValues($arrayName);
    $nameProd  = trim($arrayName[0]);
}
//var_dump($nameProd);
//-------------------------------Description--------------------------------7---
$arrayDesc = checkEmptyOrChangeSelector($_SESSION['desc'], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $arrayDesc   = deleteEmptyArrDescValues($arrayDesc);
    //var_dump($arrayDesc);
    $searchArray = array('состав:', 'размеры:', "длина гольфа:", "длина рукава:",
        "длина брюк:", "длина изделия:", "длина кофты:",
        "длина юбки:");
    foreach ($arrayDesc as $key => $value) {
        $descProd = findStringDesc($value, $searchArray, $descProd);
        if (count($arrayDesc) == $key + 1) {
            $descProd .= $descProdColor."<p><span>Описание:</span>".$arrayDesc[$key]."</p>";
        }
    }
}
//var_dump($descProd);
//die;
//==============================================================================
//                          Проверка на дубликаты
//==============================================================================
//--------------------Проверяем по главной картинке дубликаты-------------2-----
//var_dump($duplicate);
if (empty($duplicate)) {
    $duplicate[] = "";
}

foreach ($duplicate as $value) {
    if ($value == $codProd) {
        $existProd = FALSE;
        $existDub  = TRUE;
        break;
    } else {
        $_SESSION["duplicateArray"][] = $codProd;
        $_SESSION["duplicateArray"]   = array_unique($_SESSION["duplicateArray"]);
    }
}
$duplicateProd = $codProd;
//var_dump($_SESSION["duplicateArray"]);


