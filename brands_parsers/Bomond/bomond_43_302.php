<?php
//==============================================================================
//			Bomond  43-302
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
/* if ($verify !== "import" && ($statusCode == 404)) {
  $deleteProd = TRUE;
  return;
  } */
//Exist
$arrayExist    = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
//var_dump($arrayExist);

if (isset($arrayExist)) {
    foreach ($arrayExist as $value) {
        $value = mb_strtolower(trim($value), 'utf-8');
        $pos   = mb_strpos($value, 'есть в');
        if ($pos !== false) {
            $existProd = TRUE;
            break;
        } else {
            $existProd = FALSE;
        }
    }
}

//Price price2
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice) && count($arrayPrice) == 1) {
    $regexp = '/[^0-9]/';
    $price  = filterPrice(trim($arrayPrice[0]), $regexp);
    $price2 = intval($price + ($price - $price * 1.2));
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}
//var_dump($existProd);
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
    $codProd = trim($arrayCod[0]);
}
//var_dump($codProd);
//Name
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $nameProd = preg_replace('/[0-9]/', "", $arrayName[0]);
}
//var_dump($nameProd);
//Description
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    // delete all empty array data
    $arrayDesc = deleteEmptyArrDescValues($arrayDesc);

    // get final description with html tags
    $searchArray = array('размер:', "материал:", 'длина изделия:', 'длина рукава:',
        'длина кофты:', 'длина рукава кофты:', 'длина штанов:'
    );
    foreach ($arrayDesc as $key => $value) {
        $descProd = findStringDesc($value, $searchArray, $descProd);
    }
}
//var_dump($arrayDesc);
//var_dump($descProd);
//die;



