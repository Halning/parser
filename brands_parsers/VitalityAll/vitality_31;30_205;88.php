<?php
//==============================================================================
//				Vitality    31;30-205;88				
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
if ($verify !== "import" && ($statusCode == 503 || $statusCode == 404)) {
    $deleteProd = TRUE;
    return;
}
//--------------------------------Exist-----------------------------------2----- 
$arrayExist = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
//var_dump($arrayExist);

if (isset($arrayExist)) {
    foreach ($arrayExist as $value) {
        $value  = findEncode($value);
        $value  = mb_strtolower(trim($value), 'utf-8');
        $findme = 'есть';
        $pos    = mb_strpos($value, $findme);
        if ($pos !== false) {
            $existProd     = TRUE;
            $arrayNewPrice = $saw->get('p.special-price > span.price')->toTextArray();
            //var_dump($arrayNewPrice);
            if (!empty($arrayNewPrice)) {
                $existProd = FALSE;
            }
            break;
        } else {
            $existProd = FALSE;
        }
    }
}
//var_dump($existProd);
//--------------------------------Price price2------------------------------3---
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');

if (isset($arrayPrice)) {
    $regexp = '/[^0-9,]/';
    $price  = filterPrice(trim($arrayPrice[0]), $regexp);
    $price2 = $price;
    $price = (int) ceil($price2 * 1.3 * $_SESSION['updatePrice']);

} else {
    $price = 0;
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
preg_match('/Product.Config\((.*)\)/', $pageBody, $matches);
//var_dump($matches);
//die;
if (isset($matches[1])) {
    $json      = json_decode($matches[1], true);
    $keyId     = array_keys($json['attributes'])[0];
    //var_dump($keyId);
    $jsonSizes = $json['attributes'][$keyId]['options'];
    foreach ($jsonSizes as $size) {
        $sizesProd .= ";".$size['label'];
        //var_dump($size);
    }
    $sizesProd = substr($sizesProd, 1);
}
//var_dump($sizesProd);
//--------------------------------Name------------------------------------5-----
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');

if (isset($arrayName)) {
    $strName  = findEncode(trim($arrayName[0]));
    $regexp   = '/[0-9]/u';
    $nameProd = trim(preg_replace($regexp, "", $strName));
    $vowels   = array("Уценка ", "Розница ", " арт ");
    $nameProd = trim(str_replace($vowels, "", $nameProd));
    $findme   = "%";
    $pos      = strpos($nameProd, $findme);
    if ($pos !== FALSE) {
        $existProd = FALSE;
    }
    $findme = "-";
    $pos    = strpos($nameProd, $findme);
    if ($pos <= 1) {
        $nameProd = trim(str_replace("-", "", $nameProd));
    }
}
//var_dump($nameProd);
//var_dump($existProd);
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    return;
}
//--------------------------------Cod-------------------------------------6-----
$arrayCod = checkEmptyOrChangeSelector($_SESSION["cod"], $saw,
    'cod - код товара');

if (isset($arrayCod)) {
    $regexp  = "/[^0-9]/";
    $codProd = trim(preg_replace($regexp, "", $arrayCod[0]));
}
//var_dump($codProd);
//-------------------------------Description--------------------------------7---
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);
if (isset($arrayDesc)) {
    $strDesc   = "";
    $arrayDesc = deleteEmptyArrDescValues($arrayDesc);
    foreach ($arrayDesc as $value) {
        $value = findEncode($value);
        $strDesc .= trim($value);
    }
    $wovels      = array("состав", "описание");
    $searchArray = array("состав:", "описание:");
    $descProd    = getDesc($strDesc, $descProd, $wovels, $searchArray);
}
//var_dump($arrayDesc);
//var_dump($descProd);
//die;
//==============================================================================
//                          Проверка на дубликаты
//==============================================================================
//--------------------------------Image-------------------------------------1---
$arrayImage = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');
//var_dump($arrayImage);

$srcProd = "";
if (isset($arrayImage)) {
    $lowSrc  = str_replace("/image/", "/thumbnail/", $arrayImage[0]['src']);
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
//==============================================================================
//                           Функции				
//==============================================================================
//------------------------------------------------------------------------------
//                           Декодирования строк
//------------------------------------------------------------------------------
function findEncode($str)
{
    $findmeDecod = 'Ð';
    $posDecod    = mb_strpos($str, $findmeDecod);
    if ($posDecod !== false) {
        return utf8_decode($str);
    } else {
        return $str;
    }
}
