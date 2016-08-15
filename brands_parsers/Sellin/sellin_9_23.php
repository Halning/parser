<?php
//==============================================================================
//			Sellin   	9-23          		
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
//var_dump($existProd);
//Price price2
$arrayPrice    = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9.]/';
    $price  = filterPrice(trim($arrayPrice[0]), $regexp);
    $price2 = intval($price + ($price - $price * 1.5));
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}
//var_dump($price);
//var_dump($price2);
//Size
$arraySize = checkEmptyOrChangeSelector($_SESSION['sizeCol'], $saw,
    'sizeCol - размер');
//var_dump($arraySize);
if (isset($arraySize)) {
    foreach ($arraySize as $value) {
        $regexp = "/[0-9]/";
        if (preg_match($regexp, $value)) {
            $sizesProd = $sizesProd.trim($value).";";
        } else {
            $optionsProd = $optionsProd.trim($value).";";
        }
    }
    $sizesProd   = filterSizeColors($sizesProd);
    $optionsProd = filterSizeColors($optionsProd);
    $optionsProd = mb_strtolower(trim($optionsProd), 'utf-8');
}
//var_dump($optionsProd);
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
    $pos     = strpos($codProd, " ");
    $codProd = trim(substr($codProd, $pos, strlen($codProd)));
}
//var_dump($codProd);
//Name
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $nameProd = trim(str_replace($codProd, "", $arrayName[0]));
}
//var_dump($nameProd);
//Description
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $arrayDesc   = deleteEmptyArrDescValues($arrayDesc);
    $searchArray = array('материал:', "цвет:");
    foreach ($arrayDesc as $key => $value) {
        $value .= ":";
        if ($value == 'Материал:' || $value == "Цвет:") {
            $value .= trim($arrayDesc[$key + 1]);
        }
        $value    = str_replace("котон", "коттон", $value);
        $descProd = findStringDesc($value, $searchArray, $descProd);
    }
}
//var_dump($arrayDesc);
//var_dump($descProd);
//die;

