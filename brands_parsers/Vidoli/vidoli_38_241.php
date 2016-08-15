<?php
//==============================================================================
//			Vidoli   	38-241          		
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
//var_dump($existProd);
//---------------------------Price price2-----------------------------------3---
//$arrayPrice = checkEmptyOrChangeSelector(, $saw, 'price - цена');
$arrayPrice = $saw->get($_SESSION['price'])->toArray();
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9]/';
    $price2 = filterPrice(trim($arrayPrice[0]['content']), $regexp);
    $price  = ceil($price2 * 1.6);
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
$arraySize = checkEmptyOrChangeSelector($_SESSION["sizeCol"], $saw,
    'sizeCol - размер');
//var_dump($arraySize);

if (isset($arraySize)) {
    $sizesProd = trim(str_replace(",", ";", $arraySize[0]));
    $sizesProd = filterSizeColors($sizesProd);
}
//var_dump($sizesProd);
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    return;
}
//--------------------------------Cod------------------------------------5-----
$arrayCod = checkEmptyOrChangeSelector($_SESSION["cod"], $saw,
    'cod - код товара');
//var_dump($arrayCod);

if (isset($arrayCod)) {
    $codProd = trim($arrayCod[0]);
    $pos     = strpos($codProd, ",");
    if ($pos !== FALSE) {
        $codProd = trim(strstr_after($codProd, ","));
    }
}
//var_dump($codProd);
//--------------------------------Name------------------------------------6-----
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $wovels   = array(",", $codProd);
    $nameProd = trim(str_replace($wovels, "", $arrayName[0]));
}
//var_dump($nameProd);
//-------------------------------Description--------------------------------7---
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);
if (isset($arrayDesc)) {
    $arrayDesc   = deleteEmptyArrDescValues($arrayDesc);
    $searchArray = array('состав:', 'цвет:');
    foreach ($arrayDesc as $key => $value) {
        if ($value == 'Цвет') {
            $value = $value.":".trim($arrayDesc[$key + 1]);
        }
        $descProd = findStringDesc($value, $searchArray, $descProd);
    }
}
//var_dump($arrayDesc);
//var_dump($descProd);
//die;
//==============================================================================
//                          Проверка на дубликаты
//==============================================================================
//--------------------------------Image-------------------------------------8---
$arrayImage = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');

$srcProd = "";
if (isset($arrayImage)) {
    $lowSrc  = str_replace("-400x557.jpg", ".jpg", $arrayImage[0]['src']);
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