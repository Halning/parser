<?php
//==============================================================================
//			Lenida	7-16         		
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
if ($verify !== "import" && ($statusCode == 404)) {
    $deleteProd = TRUE;
    return;
}

//Exist
//echo $pageBody;
$arrayExist = $saw->get($_SESSION['no_nal'])->toArray();
//var_dump($arrayExist);

if (!empty($arrayExist)) {
    $existProd = FALSE;
}
//var_dump($existProd);
//Price price2
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9.]/';
    $price2 = filterPrice(trim($arrayPrice[0]), $regexp);
    $price  = (int) ceil($price2 * 1.5);
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}
//var_dump($price);
//var_dump($price2);
//die;
//Size
$arraySize = checkEmptyOrChangeSelector($_SESSION["sizeCol"], $saw,
    'sizeCol - размер');

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

//Image
$arrayImage = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');
//var_dump($arrayImage);
$srcProd    = "";
if (isset($arrayImage)) {
    $lowSrc  = str_replace("h595", "h1000", $arrayImage[0]['src']);
    $srcProd = filterUrlImage($lowSrc, $curLink);
    $size    = getimagesize($srcProd);
    if (!$size) {
        $existProd = FALSE;
    }
} else {
    $existProd = FALSE;
}
//var_dump($srcProd);
//var_dump($existProd);
//Cod
$arrayCod = checkEmptyOrChangeSelector($_SESSION["cod"], $saw,
    'cod - код товара');
//var_dump($arrayCod);

if (isset($arrayCod)) {
    $codProd = trim(str_replace("'", '"', $arrayCod[0]));
    $pos     = strpos($codProd, '"');
    $sub     = substr($codProd, 0, $pos + 1);
    $codProd = str_replace($sub, "", $codProd);

    $pos1    = strpos($codProd, '"');
    $sub2    = substr($codProd, $pos1, strlen($codProd));
    $codProd = str_replace($sub2, "", $codProd);
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
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $beginSelectorP    = '<p>';
    $beginSelectorSpan = '<span>';
    $endSelectorSpan   = '</span>';
    $endSelectorP      = '</p>';
    $arrayDesc         = deleteEmptyArrDescValues($arrayDesc);
    $descStr           = $saw->get('#tabs-2')->toText();
    $descStr           = mb_strtolower(trim($descStr), 'utf-8');
    $descProd          = $beginSelectorP.$beginSelectorSpan."Ткань:".$endSelectorSpan.$descStr.$endSelectorP;
    $descProdN         = "";
    foreach ($arrayDesc as $key => $value) {
        if (strlen(trim($value)) !== 0) {
            $descProdN .= trim($value);
        }
    }
    $desc = $beginSelectorP.$beginSelectorSpan."Описание: ".$endSelectorSpan.$descProdN.$endSelectorP;
    $descProd .= $desc;
}
//var_dump($arrayDesc);
//var_dump($descProd);
//echo $descProd;
//die;


