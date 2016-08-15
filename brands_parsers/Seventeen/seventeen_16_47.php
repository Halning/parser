<?php
//==============================================================================
//			seventeen 	16-47         		
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
$arrayExist    = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
//var_dump($arrayExist);

if (isset($arrayExist)) {
    $pos  = strpos($arrayExist[0], "Нет в наличии!");
    $pos1 = strpos($arrayExist[0], "Замена цвет");
    $pos2 = strpos($arrayExist[0], "Ожидается");

    if ($pos !== false || $pos1 !== FALSE || $pos2 !== FALSE) {
        $existProd = FALSE;
    }
    $arrayProperty = $saw->get('span.newprice')->toArray();
    if (!empty($arrayProperty)) {
        $existProd = FALSE;
    }
}
//var_dump($existProd);
//Price price2
$codPrice   = str_replace("http://seventeen.com.ua/shop/", "", $curLink);
$codPrice   = strstr($codPrice, "/", true);
$arrayPrice = checkEmptyOrChangeSelector('.id-good-'.$codPrice.'-price', $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9]/';
    $price2 = filterPrice(trim($arrayPrice[0]), $regexp);

    // action discount 40%
    $price  = ceil(($price2 + 70) * $_SESSION['updatePrice']);
}
if ($price2 == 0) {
    $existProd = FALSE;
    $price     = 0;
}
//var_dump($price);
//var_dump($price2);
//Size
$arraySize = checkEmptyOrChangeSelector($_SESSION["sizeCol"], $saw,
    'sizeCol - размер');
//var_dump($arraySize);

if (isset($arraySize)) {
    foreach ($arraySize as $value) {
        if (strstr($value, "пояс") == FALSE) {
            $sizesProd = $sizesProd.trim($value).";";
        }
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
    $codProd = preg_replace("/\D/", "", trim($arrayCod[0]));
}
//var_dump($codProd);
//Name
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $nameProd = trim(str_replace($codProd, "", trim($arrayName[0])));
}
//var_dump($nameProd);
//Description
$arrayDesc = checkEmptyOrChangeSelector($_SESSION['desc'], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $beginSelectorP    = '<p>';
    $endSelectorP      = '</p>';
    $beginSelectorSpan = '<span>';
    $endSelectorSpan   = '</span>';
    $arrayDesc         = deleteEmptyArrDescValues($arrayDesc);
    $pos               = strpos($arrayDesc[0], ":");
    if ($pos == FALSE) {
        $descProd = $beginSelectorP.$beginSelectorSpan."Описание: ".$endSelectorSpan.trim($arrayDesc[0]).$endSelectorP;
    }
    $searchArray = array('материал:', 'длина изделия:', "цвет:", "длина рукава:",
        "воротник:", "длина изделия по боковой части:", "длина:",
        "декор:", "длина кофты:", "длина штанов по шагу:", "длина рукава от горловины:",
        "длина по боковому шву:", "по шагу:", "по боковому шву:",
        "боковому шву:", "длина изделия на фото:", "длина брюк по шагу:", "длина брюк в поясе по резинке:");
    foreach ($arrayDesc as $key => $value) {
        $descProd = trim(findStringDesc($value, $searchArray, $descProd));
    }
}
//var_dump($arrayDesc);
//var_dump($descProd);
//echo $descProd;
//die;


