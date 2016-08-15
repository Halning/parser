<?php

//==============================================================================
//			francocasel   	37-240         		
//==============================================================================
//-------------------Переменные для записи в БД по умолчанию--------------------
$existProd = TRUE;
$codProd = "";
$price = 0;
$sizesProd = "";
$colorsProd = "";
$optionsProd = "";
$nameProd = "";
$descProd = "";
//--------------------------------Exist-----------------------------------1-----
//var_dump($existProd );
//--------------------------------Price price2------------------------------2---
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw, 'price - цена');

if (isset($arrayPrice)) {
    $price = filterPrice(trim($arrayPrice[0]));
    $price2 = $price;
}

if ($price == 0) {
    $existProd = FALSE;
    $price2 = 0;
}
var_dump($price);

//------------------------------------------------------------------------------
//                          Colors Size                                 3
//------------------------------------------------------------------------------
//-------------------------------Size---------------------------------------4---
$arraySize = checkEmptyOrChangeSelector($_SESSION["sizeCol"], $saw, 'sizeCol - размер');
//var_dump($arraySize);

if (isset($arraySize)) {
    foreach ($arraySize as $value) {
        $sizesProd = $sizesProd . trim($value) . ";";
    }
    $sizesProd = filterSizeColors($sizesProd);
}
var_dump($sizesProd);
//var_dump($colorsProd);
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    return;
}
//--------------------------------Cod------------------------------------4-----
$arrayCod = checkEmptyOrChangeSelector($_SESSION["cod"], $saw, 'cod - код товара');
//var_dump($arrayCod);

if (isset($arrayCod)) {
    $regexp = "/[^0-9\\/]/";
    $codProd = filterCod(trim($arrayCod[0]), $regexp);
}
var_dump($codProd);

//--------------------------------Name------------------------------------5-----
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw, 'name - название товара');
var_dump($arrayName);

if (isset($arrayName)) {
    $regexp = '/[0-9№\\/]/u';
    $nameProd = filterName(trim($arrayName[0]), $regexp);
    $nameProd = str_replace("Арт.", '', $nameProd);
}
var_dump($nameProd);

//-------------------------------Description--------------------------------6---
$arrayDesc = checkEmptyOrChangeSelector('div.prod-info-efields', $saw, 'desc - описание');

if (isset($arrayDesc)) {
    foreach ($arrayDesc as $value) {
        $descProd = $descProd . " " . trim($value);
    }
}
var_dump($descProd);
die;