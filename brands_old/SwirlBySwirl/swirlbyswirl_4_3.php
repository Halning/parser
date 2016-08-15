<?php
//==============================================================================
//			SwirlBySwirl  	4-3           		
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
//Exist
$arrayExist    = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
//var_dump($arrayExist);

if (isset($arrayExist)) {
    foreach ($arrayExist as $key => $value) {
        if (strlen(trim($value)) !== 0) {
            $value           = mb_strtolower(trim($value), 'utf-8');
            $arrayExistNew[] = trim($value);
        }
    }
    //var_dump($arrayExistNew);

    foreach ($arrayExistNew as $key => $value) {
        $pos = strpos($value, "количес");
        if ($pos !== FALSE) {
            if ($arrayExistNew[$key + 1] == "товара нет") {
                $existProd = FALSE;
                break;
            } else {
                break;
            }
        }
    }
} else {

//Exist URL
    $deleteProd = TRUE;
    $existProd  = FALSE;
    return;
}

//Price price2
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9]/';
    $price  = filterPrice(trim($arrayPrice[0]), $regexp);
    $price2 = (int) ceil($price / 2);
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}

//Color
$arrayColor = checkEmptyOrChangeSelector('div.bottom-tovar > span:nth-child(1)',
    $saw, 'Col - цвет');
//var_dump($arrayColor);

if (isset($arrayColor)) {
    $optionsProd = trim($arrayColor[1]);
}

//Size
$arraySize = checkEmptyOrChangeSelector($_SESSION['sizeCol'], $saw,
    'sizeCol - размер');
//var_dump($arraySize);
if (isset($arraySize)) {
    foreach ($arraySize as $key => $value) {
        if (strlen(trim($value)) !== 0) {
            $value          = mb_strtolower(trim($value), 'utf-8');
            $arraySizeNew[] = trim($value);
        }
    }
    //var_dump($arraySizeNew);
    $sizeStr   = implode(";", $arraySizeNew);
    $sizeStr   = strstr($sizeStr, ";количес", true);
    //var_dump($sizeStr);
    $sizesProd = strstr_after($sizeStr, "размер:;");
    $sizesProd = mb_strtoupper($sizesProd, 'utf-8');
}
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
    $codProd = trim(strstr(trim($arrayCod[0]), " ", true));
}

//Name
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $wovelsName = array($codProd, "SbS");
    $nameProd   = trim(str_replace($wovelsName, "", $arrayName[0]));
}

//Description
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $arrayDesc = deleteEmptyArrDescValues($arrayDesc);
    $arrayDesc = array_values(array_unique($arrayDesc));

    $descProd = "<p><span>Цвет:<span>{$optionsProd}</p>";

    $searchArray = array('состав:', 'ткань:', "длина изделия:");
    foreach ($arrayDesc as $key => $value) {
        if ($value == 'Ткань:' || $value == "Длина изделия:" || $value == "Состав:") {
            $value .= trim($arrayDesc[$key + 1]);
        }
        $descProd = findStringDesc($value, $searchArray, $descProd);
    }
}
$arrayDescDesc = checkEmptyOrChangeSelector('.description', $saw,
    'desc - описание');
//var_dump($arrayDescDesc);
if (isset($arrayDescDesc)) {
    $descProd .= "<p><span>".$arrayDescDesc[0]."</span>".$arrayDescDesc[1]."</p>";
}
//var_dump($arrayDesc);
//var_dump($existProd);
//var_dump($price);
//var_dump($price2);
//var_dump($existProd);
//var_dump($optionsProd);
//var_dump($sizesProd);
//var_dump($codProd);
//var_dump($nameProd);
//var_dump($descProd);
//die;
