<?php
//==============================================================================
//				Sergio Torii	26-85				
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
var_dump($curLink);
if ($verify !== "import") {
//--------------------------------Exist URL-------------------------------1-----
    $arrayExist = $saw->get($_SESSION["no_nal"])->toTextArray();
    //var_dump($arrayExist);
    //$arrayExist= array();
    if (empty($arrayExist)) {
        $deleteProd = TRUE; //если только скрыть товар то коментируем
        return; //если только скрыть товар то коментируем
    }
}
//--------------------------------Exist-----------------------------------2-----
//Exist
$arrayExist = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
var_dump($arrayExist);

if (isset($arrayExist)) {
    foreach ($arrayExist as $value) {
        $exist  = mb_strtolower(trim($value), 'utf-8');
        $findme = 'нет в нал';
        $pos    = mb_strpos($exist, $findme);
        if ($pos !== false) {
            $existProd = FALSE;
        }
    }
}
var_dump($existProd);
die;
//--------------------------------Price price2------------------------------3---
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9,]/';
    $price  = filterPrice(trim($arrayPrice[0]), $regexp);
    $price2 = $price;
}
if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}

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
    $regexp  = "/[^0-9\\/]/";
    $codProd = trim(preg_replace($regexp, "", $arrayCod[0]));
}

//--------------------------------Name------------------------------------5-----
$arrayName = checkEmptyOrChangeSelector($_SESSION['h1'], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $findme = "(";
    $pos    = strpos($arrayName[0], $findme);
    if ($pos !== FALSE) {
        $name = strstr($arrayName[0], "(", true);
    } else {
        $name = $arrayName[0];
    }

    $name = strstr_after($name, "й", true);
    if ($name !== FALSE) {
        $nameProd = str_replace($name, "", $arrayName[0]);
    } else {
        $arrayProperty = $saw->get('title')->toTextArray();
        $name          = trim(strstr($arrayProperty[0], ":", true));
        $nameProd      = getMultiplyToOnly($name);
    }
    $nameProd = mb_strtolower(trim($nameProd), 'utf-8');
    $nameProd = mb_strtoupper(mb_substr($nameProd, 0, 1, 'utf-8'), 'utf-8')
        .mb_substr($nameProd, 1, null, 'utf-8');
}

//==============================================================================
//                          Проверка на дубликаты
//==============================================================================
//--------------------------------Image-------------------------------------1---
$arrayImage = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');
//var_dump($arrayImage);

$srcProd = "";
if (isset($arrayImage)) {
    $lowSrc  = $arrayImage[0]['href'];
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
//-------------------------------Description--------------------------------3---
$arrayDesc     = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $arrayDesc   = deleteEmptyArrDescValues($arrayDesc);
    $searchArray = array("ширина:", "длина:", "цвет:", "состав:", "цвет первой стороны:",
        "цвет второй стороны:");
    foreach ($arrayDesc as $value) {
        $descProd = findStringDesc($value, $searchArray, $descProd);
    }
}
//var_dump($price);
//var_dump($price2);
//var_dump($codProd);
//var_dump($nameProd);
//var_dump($arrayDesc);
//var_dump($descProd);
//die;
//==============================================================================
//                          Функции
//==============================================================================

/**
 * Названия переводим в единственное число
 * @param type $name
 * @return string
 */
function getMultiplyToOnly($name)
{
    $name = trim($name);
    //  массив возможных вариантов
    $ends = array(
        "Корсеты" => "Корсет",
        'Тонкие "Sergio Torri"' => 'Ремень тонкий'
    );

    foreach ($ends as $key => $value) {
        if ($key == $name) {
            $name = $value;
        }
    }
    return $name;
}
