<?php
//==============================================================================
//				Aliya	27-88				
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
/* if ($verify !== "import" && ($statusCode == 503)) {
  $deleteProd = TRUE;
  return;
  } */
//--------------------------------Exist-----------------------------------2----- 
$arrayExist    = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
//var_dump($arrayExist);
if (isset($arrayExist)) {
    foreach ($arrayExist as $value) {
        $value  = mb_strtolower(trim($value), 'utf-8');
        $findme = 'есть';
        $pos    = mb_strpos($value, $findme);
        $findme = 'пред';
        $pos1   = mb_strpos($value, $findme);
        if ($pos !== false || $pos1 !== FALSE) {
            $existProd = TRUE;
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
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9.]/';
    $price  = filterPrice(trim($arrayPrice[0]), $regexp);
    $price2 = $price;
}
if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}
//var_dump($price);
//------------------------------------------------------------------------------
//                          Colors Size                                 
//------------------------------------------------------------------------------ 
//var_dump($sizesProd);
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    return;
}

//--------------------------------Cod-------------------------------------4-----
$arrayCod = checkEmptyOrChangeSelector($_SESSION["cod"], $saw,
    'cod - код товара');
//var_dump($arrayCod);

if (isset($arrayCod)) {
    $codProd = trim($arrayCod[0]);
}
//var_dump($codProd);
//--------------------------------Name------------------------------------5-----
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $nameProd = trim($arrayName[0]);
}
//var_dump($nameProd);
//-------------------------------Description--------------------------------6---
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $arrayDesc = deleteEmptyArrDescValues($arrayDesc);
    foreach ($arrayDesc as $value) {
        $descProd = trim($descProd." ".trim($value));
    }
}
//var_dump($arrayDesc);
//var_dump($descProd);
//die;
//==============================================================================
//                          Проверка на дубликаты
//==============================================================================
//--------------------------------Image-------------------------------------7---
$arrayImage = checkEmptyOrChangeSelector('#image-additional-carousel img', $saw,
    'img - главная картинка');
//var_dump($arrayImage);

$srcProd = "";
if (isset($arrayImage)) {
    $lowSrc  = $arrayImage[0]['src'];
    $srcProd = filterUrlImage($lowSrc, $curLink);
}
//var_dump($srcProd);
//--------------------Проверяем по главной картинке дубликаты-------------8-----
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
//var_dump($duplicateProd);
//die;

