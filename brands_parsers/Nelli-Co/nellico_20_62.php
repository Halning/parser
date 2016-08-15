<?php
//==============================================================================
//			Nellico  	20-62         		
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
if ($verify !== "import" && ($statusCode == 404)) {
    $deleteProd = TRUE;
    return;
}
//Exist
$arrayExist = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
//var_dump($arrayExist);

if (isset($arrayExist)) {
    $exist = mb_strtolower(trim($arrayExist[0]), 'utf-8');
    $pos   = mb_strpos($exist, 'нет');
    $pos1  = mb_strpos($exist, "под зак");
    if ($pos !== false || $pos1 !== false) {
        $existProd = FALSE;
    }
}

//Price price2
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9]/';
    $price  = filterPrice(trim($arrayPrice[0]), $regexp);
    $price2 = intval($price + ($price - $price * 1.5));
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}
//var_dump($existProd);
//var_dump($price);
//var_dump($price2);
//die;
//Size
$arraySize = checkEmptyOrChangeSelector($_SESSION["sizeCol"], $saw,
    'sizeCol - размер');
//var_dump($arraySize);

if (isset($arraySize)) {
    $arraySize  = deleteEmptyArrDescValues($arraySize);
    $sizeString = "";
    foreach ($arraySize as $value) {
        $sizeString .= str_replace(array('&nbsp;', '  '), ' ',
            htmlentities($value));
    }
    $sizesStr = trim(strstr($sizeString, "азмер"));

    $sizesString = strstr($sizesStr, ":");
    if ($sizesString !== FALSE) {
        $sizesProd = str_replace(array(" ", ','), ";", trim($sizesString));
        $wovels    = array(',', '.', ':;', ':');
        $sizesProd = str_replace($wovels, "", str_replace(';;', ';', $sizesProd));
    } else if ($sizesStr !== FALSE) {
        $sizesProd = str_replace(" ", ";", trim($sizesStr));
        $wovels    = array(',', '.', ':;', ':', 'азмер;');
        $sizesProd = str_replace($wovels, "", str_replace(';;', ';', $sizesProd));
    }
}
//var_dump($sizesProd);
//die;
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
    $codProd = trim(str_replace("Код: ", "", $arrayCod[0]));
}
//var_dump($codProd);
//Name
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $nameProd = trim($arrayName[0]);
}
//var_dump($nameProd);
//Description
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $strDesc = "";
    foreach ($arrayDesc as $value) {
        if (strlen($value) <= 300) {
            $strDesc .= trim($value)." ";
        }
    }
    $wovels      = array("состав", "цвета ", "цвет", "размеры", "размер");
    $searchArray = array('состав:', 'цвета:', "цвет:", "размеры:", "размер:");
    $descProd    = getDesc($strDesc, $descProd, $wovels, $searchArray);
}
//var_dump($descProd);
//die;
//==============================================================================
//                          Проверка на дубликаты
//==============================================================================
//--------------------------------Image-------------------------------------1---
$arrayImage = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');

$srcProd = "";
if (isset($arrayImage)) {
    $lowSrc                     = str_replace("w200_h200", "w640_h640",
        $arrayImage[0]['src']);
    $srcProd                    = filterUrlImage($lowSrc, $curLink);
    $srcProdArray['mainSrcImg'] = $srcProd;
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



