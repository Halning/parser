<?php
//==============================================================================
//			Reform  40-300
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
//Image
$arrayImage    = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');
//var_dump($arrayImage[0]['href']);

$srcProd = "";
if (!isset($arrayImage)) {
    $existProd = FALSE;
}
//var_dump($existProd);
//Price price2
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9]/';

    // action discount 40%
    $priceFor = filterPrice(trim($arrayPrice[0]), $regexp);
    $price  = ceil($priceFor * $_SESSION['updatePrice']);
    $price2 = intval($priceFor+ ($priceFor - $priceFor * 1.2));
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}
//var_dump($price);
//var_dump($price2);
//Size
preg_match('/"options":(.*),"preselect"/', $pageBody, $matches);
//var_dump($matches);

if (isset($matches[1])) {
    $json = json_decode($matches[1], true);
    //var_dump($json);
    foreach ($json as $size) {
        $sizesProd .= ";".$size['label'];
        //var_dump($size);
    }
    $sizesProd = substr($sizesProd, 1);
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
    $arrayCod = deleteEmptyArrDescValues($arrayCod);
    $codProd  = trim($arrayCod[1]);
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
$arrayDesc = checkEmptyOrChangeSelector('#product-attribute-specs-table'/* $_SESSION["desc"] */,
    $saw, 'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $arrayDesc = deleteEmptyArrDescValues($arrayDesc);
    $strDesc   = "";
    $wovelsIn  = array('Производитель', 'Страна производитель', 'Цвет', 'Состав ткани');
    $wovelsOut = array('производитель:', 'cтрана производитель:', 'цвет:', 'состав ткани:');
    foreach ($arrayDesc as $key => $value) {

        $value = trim(str_ireplace($wovelsIn, $wovelsOut, htmlentities($value)));
        if ($value == 'производитель:' || $value == 'cтрана производитель:' || $value
            == 'цвет:' || $value == 'состав ткани:') {
            $value .= $arrayDesc[$key + 1];
            $unFirstIn = array();
            $descProd  = str_replace(array('украина', 'reform'),
                array('Украина', 'Reform'),
                findStringDesc($value, $wovelsOut, $descProd));
        }
    }
}
//var_dump($arrayDesc);
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
