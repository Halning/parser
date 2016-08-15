<?php
//==============================================================================
//			Helen Laven	32-217				
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
/* if ($verify !== "import") {
  $arrayExist = $saw->get('#content > h1')->toTextArray();
  //$arrayExist = array();
  //var_dump($arrayExist);
  if (!empty($arrayExist)) {
  $deleteProd = TRUE; //если только скрыть товар то коментируем
  return; //если только скрыть товар то коментируем
  }
  } */
//-------------------------Price and price2---------------------------------2---
$arrayPrice    = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');

if (isset($arrayPrice)) {
    $regexp = '/[^0-9.]/';
    $price  = filterPrice(trim($arrayPrice[0]), $regexp);
    $price2 = intval($price + ($price - $price * 1.2));
}
if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}
//var_dump($price);
//------------------------------------------------------------------------------
//                          Colors Size                                 
//------------------------------------------------------------------------------
//-------------------------------Size---------------------------------------3---
$arraySize = checkEmptyOrChangeSelector($_SESSION["sizeCol"], $saw,
    'sizeCol - размер');
//var_dump($arraySize);

if (isset($arraySize)) {
    foreach ($arraySize as $value) {
        $sizesProd = $sizesProd.trim($value).";";
    }
    $sizesProd = filterSizeColors($sizesProd);
    //var_dump($sizesProd);
//--------------------------------Exist-----------------------------------4-----     
    if ($sizesProd == "") {
        $existProd = FALSE;
    }
} else if (strpos($curLink, "sumki/") == FALSE) {
    $existProd = FALSE;
}
//var_dump($sizesProd);
//var_dump($existProd);
//die;
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    return;
}
//--------------------------------Cod------------------------------------5-----
$arrayCod = checkEmptyOrChangeSelector($_SESSION["cod"], $saw,
    'cod - код товара');

if (isset($arrayCod)) {
    $codProd = strstr_after(trim($arrayCod[0]), " ");
}
//var_dump($codProd);
//--------------------------------Name------------------------------------6-----
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');

if (isset($arrayName)) {
    $nameProd = trim($arrayName[0]);
    $nameProd = mb_strtoupper(mb_substr($nameProd, 0, 1, 'utf-8'), 'utf-8')
        .mb_substr($nameProd, 1, null, 'utf-8');
}
//var_dump($nameProd);
//-------------------------------Description--------------------------------7---
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);
if (isset($arrayDesc)) {
    $arrayDesc = deleteEmptyArrDescValues($arrayDesc);
    foreach ($arrayDesc as $value) {
        $descProd = $descProd." ".trim($value);
    }
}
//var_dump($arrayDesc);
//var_dump($descProd);
//die;






