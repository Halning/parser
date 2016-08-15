<?php
//==============================================================================
//			Fstyle   	21-63         		
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
    $exist  = mb_strtolower(trim($arrayExist[0]), 'utf-8');
    $findme = 'есть';
    $pos    = mb_strpos($exist, $findme);
    if ($pos !== false) {
        $existProd = TRUE;
    } else {
        $existProd = FALSE;
    }
}
//var_dump($existProd);
//Price price2
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $arrayProperty = $saw->get('.red')->toTextArray();
    $regexp        = '/[^0-9.]/';
    if (!empty($arrayProperty)) {
        $price    = filterPrice(trim($arrayProperty[0]), $regexp);
        $priceOld = filterPrice(trim($arrayPrice[0]), $regexp);
        $price2   = intval($priceOld + ($priceOld - $priceOld * 1.4));
    } else {
        $price  = filterPrice(trim($arrayPrice[0]), $regexp);
        $price2 = intval($price + ($price - $price * 1.4));
    }
    if ($price < $price2) {
        $price2 = $price;
    }
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}
//var_dump($priceOld);
//var_dump($price);
//var_dump($price2);
//Size
$arraySize = checkEmptyOrChangeSelector($_SESSION["sizeCol"], $saw,
    'sizeCol - размер');

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
    $codProd = trim($arrayCod[0]);
    $codProd = trim(str_replace("арт", "", $codProd));
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
    $arrayDesc = deleteEmptyArrDescValues($arrayDesc);

    $beginSelectorP    = '<p>';
    $beginSelectorSpan = '<span>';
    $endSelectorSpan   = '</span>';
    $endSelectorP      = '</p>';
    $searchArray       = array('состав:', 'описание:');
    foreach ($arrayDesc as $key => $value) {
        $descProd = findStringDesc($value, $searchArray, $descProd);

        $value = mb_strtolower(trim($value), 'utf-8');
        if ($value == "описание" && $key + 1 !== count($arrayDesc)) {
            $descNew = trim($arrayDesc[$key + 1]);
            if (!empty($descNew) && $arrayDesc[$key + 1] !== "Детали") {
                $descProd .= $beginSelectorP.$beginSelectorSpan.trim($arrayDesc[$key]).":".$endSelectorSpan.trim($arrayDesc[$key
                        + 1]).$endSelectorP;
            }
        }
    }
}
//var_dump($arrayDesc);
//var_dump($descProd);
//die;

