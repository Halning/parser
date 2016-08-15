<?php
//==============================================================================
//			Olis Style  	19-58         		
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
    foreach ($arrayExist as $value) {
        $exist  = mb_strtolower(trim($value), 'utf-8');
        $findme = 'есть';
        $pos    = mb_strpos($exist, $findme);
        $findme = 'дня';
        $pos1   = mb_strpos($exist, $findme);
        $findme = 'в';
        $pos2   = mb_strpos($exist, $findme);
        if ($pos !== false || $pos1 !== FALSE || $pos2 == 0) {
            $existProd = TRUE;
            break;
        } else {
            $existProd = FALSE;
        }
    }
}
//var_dump($existProd);
//Price price2
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9.]/';
    $price2 = filterPrice(trim($arrayPrice[0]), $regexp);
    $price  = ceil(($price2 + 100) * $_SESSION['updatePrice']);
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
    $existProd = ($sizesProd == '') ? FALSE : TRUE;
}
//var_dump($existProd);
//var_dump($sizesProd);
//die;
//Cod
$arrayCod = checkEmptyOrChangeSelector($_SESSION["cod"], $saw,
    'cod - код товара');
//var_dump($arrayCod);

if (isset($arrayCod)) {
    foreach ($arrayCod as $key => $value) {
        if (strlen(trim($value)) !== 0) {
            $arrayCodNew[] = $value;
        }
    }
    $codProd = trim($arrayCodNew[1]);
    if ($codProd == "ПРОДАНО") {
        $existProd = FALSE;
    }
}
//var_dump($codProd);
//var_dump($existProd);
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    return;
}

//Name
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $wovels   = array("оптовая цена", "Оптовая Цена", "Оптовая цена", "оптовая Цена",
        "ОПТОВАЯ ЦЕНА", "ОПТАВАЯ ЦЕНА");
    $name     = str_replace($wovels, "", $arrayName[0]);
    $nameProd = trim(str_replace("оптовая цена", "", $name));
}
//var_dump($nameProd);
//Description
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $arrayDesc         = deleteEmptyArrDescValues($arrayDesc);
    $beginSelectorP    = '<p>';
    $beginSelectorSpan = '<span>';
    $endSelectorSpan   = '</span>';
    $endSelectorP      = '</p>';
    foreach ($arrayDesc as $key => $value) {
        $searchArray = array('ткань:', "размеры:");
        $descProd    = findStringDesc($value, $searchArray, $descProd);

        $value = mb_strtolower(trim($value), 'utf-8');
        if (($value == "цвет" || $value == "размеры") && $key + 1 !== count($arrayDesc)) {
            $descNew = trim($arrayDesc[$key + 1]);
            if (!empty($descNew)) {
                $descProd .= $beginSelectorP.$beginSelectorSpan.trim($arrayDesc[$key]).":".$endSelectorSpan.trim($arrayDesc[$key
                        + 1]).$endSelectorP;
            }
        }
    }
}
//var_dump($arrayDesc);
//var_dump($descProd);
//die;
//==============================================================================
//                          Проверка на дубликаты
//==============================================================================
//Image
$arrayImage = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');
//var_dump($arrayImage);

$srcProd = "";
if (isset($arrayImage)) {
    $lowSrc  = str_replace("70x81", "500x579", $arrayImage[0]['src']);
    $srcProd = filterUrlImage($lowSrc, $curLink);
}
//var_dump($srcProd);
//Проверяем по главной картинке дубликаты
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


