<?php
//==============================================================================
//			Meggi	10-42	        		
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
//Exist URL
/* if ($verify !== "import" && ($statusCode == 404)) {
  $deleteProd = TRUE;
  return;
  } */
//Exist
$arrayExist    = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
$arrayNewPrice = $saw->get('span.productPrice-ofd')->toTextArray();
//var_dump($arrayExist);
//var_dump($arrayNewPrice);

if (isset($arrayExist) || !empty($arrayNewPrice)) {
    $existProd = FALSE;
} else {
    $existProd = TRUE;
}
//var_dump($existProd);
//Price price2
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9]/';
    $price2 = filterPrice(trim($arrayPrice[0]), $regexp);
    $price  = (int) ceil($price2 * 1.6 * $_SESSION['updatePrice']);
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}
//var_dump($price);
//var_dump($price2);
//Size
$arraySize = checkEmptyOrChangeSelector($_SESSION["sizeCol"], $saw,
    'sizeCol - размер');
//var_dump($arraySize);

if (isset($arraySize)) {
    foreach ($arraySize as $value) {
        $sizesProd = $sizesProd.trim($value).";";
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

//Cod
$arrayCod = checkEmptyOrChangeSelector($_SESSION["cod"], $saw,
    'cod - код товара');
//var_dump($arrayCod);

if (isset($arrayCod)) {
    $codProd = trim(strstr_after($arrayCod[0], "-"));
}
//var_dump($codProd);
//Name
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $wovels   = array($codProd, "-");
    $nameProd = trim(str_replace($wovels, "", $arrayName[0]));
}
//var_dump($nameProd);
//Description
$arrayDesc      = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
$arrayDescColor = checkEmptyOrChangeSelector('.color option', $saw,
    'color - описание цвета');
//var_dump($arrayDesc);
//var_dump($arrayDescColor);
if (isset($arrayDescColor)) {
    $beginSelectorP    = '<p>';
    $beginSelectorSpan = '<span>';
    $endSelectorSpan   = '</span>';
    $endSelectorP      = '</p>';
    $descProd          = $beginSelectorP.$beginSelectorSpan."Цвет:".$endSelectorSpan.trim($arrayDescColor[0]).$endSelectorP;
}
if (isset($arrayDesc)) {
    $arrayDesc   = deleteEmptyArrDescValues($arrayDesc);
    $searchArray = array('состав изделия:');
    foreach ($arrayDesc as $key => $value) {
        $value .= ":";
        if ($value == 'Состав изделия:') {
            $value .= trim($arrayDesc[$key + 1]);
        }
        $descProd = findStringDesc($value, $searchArray, $descProd);
    }
}
//var_dump($arrayDesc);
//var_dump($descProd);
//die;



