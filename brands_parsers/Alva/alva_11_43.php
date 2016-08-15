<?php
//==============================================================================
//			Alva   	11-43         		
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
if ($verify !== "import" && $statusCode == 404) {
    $deleteProd = TRUE;
    return;
}
if ($verify !== "import") {
    $arrayExist = $saw->get('#content > h1')->toTextArray();

    if (!empty($arrayExist)) {
        if (trim($arrayExist[0]) == 'Товар не найден!') {
            $deleteProd = TRUE; //если только скрыть товар то коментируем
            return; //если только скрыть товар то коментируем
        }
    }
}

//Exist
$arrayExist = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
//var_dump($arrayExist);

if (isset($arrayExist)) {
    $exist  = " ".mb_strtolower($arrayExist[4], 'utf-8');
    $findme = 'в наличии';
    $pos    = mb_strpos($exist, $findme);
    if ($pos == false) {
        $existProd = FALSE;
    }
}
//var_dump($existProd);
//Price price2
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9]/';
    if (count($arrayPrice) == 1) {
        $price2 = filterPrice(trim($arrayPrice[0]), $regexp);
    } else {
        $price2 = filterPrice(trim($arrayPrice[3]), $regexp);
    }
    $price = intval($price2 * 1.8);
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}
//var_dump($price);
//var_dump($price2);
//die;
//Size color
$arraySize = checkEmptyOrChangeSelector($_SESSION["sizeCol"], $saw,
    'sizeCol - размер');
//var_dump($arraySize);

if (isset($arraySize)) {
    $sizeString = strstr_after(implode(",", $arraySize), "Размер:,");
    if (strpos($sizeString, "Цвет")) {
        $sizeString = strstr($sizeString, "*,", true);
    }

    $arraySize = deleteEmptyArrDescValues(explode(",", $sizeString));
    if (isset($arraySize)) {
        foreach ($arraySize as $key => $value) {
            $sizesProd = $sizesProd.trim($value).";";
        }
        $sizesProd = str_replace(",", "", filterSizeColors($sizesProd));
    }
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
    foreach ($arrayCod as $key => $value) {
        if (trim($value) == "Модель:") {
            $codProd = trim($arrayCod[$key + 1]);
        }
    }
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
    $wovels      = array('описание:', "размер:", "размеры:", "состав:", "ткань:");
    $searchArray = array('описание:', "размер:", "размеры:", "состав:", "ткань:");
    foreach ($arrayDesc as $key => $value) {
        $value = str_replace(" :", ":", $value);

        if ($value == 'Описание:' || $value == 'Размер:' || $value == 'Размеры:'
            || $value == 'Ткань:') {
            $value .= $arrayDesc[$key + 1];
        }
        $descProd = getDesc($value, $descProd, $wovels, $searchArray);
    }
}
//var_dump($arrayDesc);
//var_dump($descProd);
//echo $descProd;
//die;
//==============================================================================
//                          Проверка на дубликаты
//==============================================================================
//Image
$arrayImage = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');

$srcProd = "";
if (isset($arrayImage)) {
    $lowSrc  = str_replace("200x300.jpg", "550x825.jpg", $arrayImage[0]['href']);
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


