<?php
//==============================================================================
//			Zdes   	36-239         		
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
$srcProd       = "";
$duplicateProd = "";
//var_dump($curLink);
//--------------------------------Exist URL-------------------------------1-----
if ($verify !== "import") {
    $arrayExist = $saw->get('#content > div.content')->toTextArray();
    //$arrayExist = array();
    if (!empty($arrayExist)) {
        $deleteProd = TRUE; //если только скрыть товар то коментируем
        return; //если только скрыть товар то коментируем
    }
}
//--------------------------------Exist------------------------------------2----
$arrayExist = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
//var_dump($arrayExist);

if (isset($arrayExist)) {
    foreach ($arrayExist as $value) {
        $value  = mb_strtolower(trim($value), 'utf-8');
        $findme = 'есть';
        $pos    = strpos($value, $findme);
        if ($pos !== false) {
            $existProd = TRUE;
            break;
        } else {
            $existProd = FALSE;
        }
    }
}
//var_dump($existProd);
//--------------------------Price and price2--------------------------------3---
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');

if (isset($arrayPrice)) {
    $regexp        = '/[^0-9.]/';
    $price         = filterPrice(trim($arrayPrice[0]), $regexp);
    $arrayOldPrice = $saw->get('.price-addcart span.price-new')->toTextArray();
    if (empty($arrayOldPrice)) {
        $price2 = intval($price + ($price - $price * 1.2));
    } else {
        $price2 = $price;
    }
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
$arraySelect = checkEmptyOrChangeSelector('div.option > b', $saw,
    'цвет или размер');

if (isset($arraySelect)) {
    $select = $arraySelect[0];
    $findme = 'вет';
    $pos    = strpos($select, $findme);
    if ($pos == FALSE) {
//-------------------------------Size---------------------------------------4---
        $arraySize = checkEmptyOrChangeSelector($_SESSION["sizeCol"], $saw,
            'sizeCol - размер');

        if (isset($arraySize)) {
            foreach ($arraySize as $value) {
                $sizesProd = trim($sizesProd.$value.";");
            }
            $sizesProd = filterSizeColors($sizesProd);
        }
    }
}/* else {
  //-------------------------------Color--------------------------------------5---
  $arrayColor = checkEmptyOrChangeSelector($_SESSION['colors'], $saw, 'colors - цвет');

  if (isset($arrayColor)) {
  foreach ($arrayColor as $value) {
  $colorsProd = trim($colorsProd . $value . ";");
  }
  $colorsProd = filterSizeColors($colorsProd);
  }

  if ($colorsProd !== "") {
  $colorArray = explode(";", $colorsProd);
  $beginSelect = "<select class=cl_choos2>";
  $selectEnd = "</select>";
  $colors = "";
  foreach ($colorArray as $value) {
  $colors .='<option value="' . $value . '" >' . $value . '</option>';
  }
  $colorsProd = $beginSelect . $colors . $selectEnd;
  }
  }
  }
  //var_dump($sizesProd);
  //var_dump($colorsProd); */

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
    $codProd = trim($arrayCod[2]);
}
//var_dump($codProd);
//--------------------------------Name------------------------------------7-----
$nameArray = explode("&", trim($curLink));
$intPath   = str_replace("path=", "", $nameArray[1]);
switch ($intPath) {
    case '97_85': $nameProd = "Футболка";
        break;
    case '97_80': $nameProd = "Худи";
        break;
    case '97_63': $nameProd = "Свитшот";
        break;
    case '97_98': $nameProd = "Майка";
        break;
    case '61_25': $nameProd = "Шапка";
        break;
    case '61_96': $nameProd = "Баф";
        break;
    case '61_86': $nameProd = "Рукавицы";
        break;
    case '61_95': $nameProd = "Очки";
        break;
    case '61_91': $nameProd = "Панама";
        break;
    case '87_88': $nameProd = "Backpack";
        break;
    case '87_94': $nameProd = "Big Hustle";
        break;
    case '87_76': $nameProd = "Hustlebag";
        break;
    default:
        break;
}
//var_dump($nameProd);
//-------------------------------Description--------------------------------8---
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);
if (isset($arrayDesc)) {
    $arrayDesc = deleteEmptyArrDescValues($arrayDesc);
    $strDesc   = "";
    foreach ($arrayDesc as $value) {
        if (strlen($value) !== 1) {
            $strDesc .= trim($value)." ";
        }
    }
    $wovels      = array("описание", "характеристики", "цена");
    $searchArray = array('описание:', 'характеристики:');
    $descProd    = getDesc($strDesc, $descProd, $wovels, $searchArray);
}
//var_dump($arrayDesc);
//var_dump($descProd);
//echo $descProd;
//die;
//==============================================================================
//                          Проверка на дубликаты
//==============================================================================
//--------------------------------Image-------------------------------------1---
$arrayImage = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');
//var_dump($arrayImage);

if (isset($arrayImage)) {
    $lowSrc  = $arrayImage[0]['src'];
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
        $duplicateProd                = $srcProd;
    }
}
//unset($_SESSION["duplicateArray"]);
//var_dump($_SESSION["duplicateArray"]);
//var_dump($duplicateProd);
//die;
    