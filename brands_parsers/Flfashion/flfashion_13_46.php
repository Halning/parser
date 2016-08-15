<?php
//==============================================================================
//			flfashion  	13-46         		
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
//Price price2
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9]/';
    $price2 = filterPrice(trim($arrayPrice[0]), $regexp);

    // action discount 40%
    $price  = ceil(($price2 + 70) * $_SESSION['updatePrice']);
}

if ($price == 0 || $price == 70) {
    $existProd = FALSE;
    $price2    = $price     = 0;
}
//var_dump($price);
//var_dump($price2);
//var_dump($existProd);
//Size
$arraySize = checkEmptyOrChangeSelector($_SESSION["sizeCol"], $saw,
    'sizeCol - размер');
//var_dump($arraySize);

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

//Cod
$arrayCod = $saw->get($_SESSION['cod'])->toArray();
//var_dump($arrayCod);

if (!empty($arrayCod)) {
    $wovels    = array('Блуза', 'Юбка-шорты', "Юбка-Шорты");
    $codString = str_replace($wovels, "", $arrayCod[0]['value']);
    $codString = trim(str_replace("-", " ", $codString));
    if (substr_count($codString, " ") == 1) {
        $codProd    = $codString;
        $codInteger = "";
        $codStr     = $codProd;
    } else {
        $codInteger = strstr(utf8_strrev($codString), " ", true);
        $codInteger = utf8_strrev($codInteger);
        $codStr     = trim(strstr($codString, $codInteger, true));
        $codStr     = utf8_strrev($codStr);
        $pos        = strpos($codString, "-");
        $codStr     = utf8_strrev(strstr($codStr, " ", true));
        $codProd    = $codStr." ".$codInteger;
        $codProd    = trim(str_replace("'О'", "", $codProd));
    }
}
//var_dump($codProd);
//Name
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $nameCat = strstr($arrayName[0], "Модель", true);
    $nameCat = mb_strtolower($nameCat, 'utf-8');
    $wovels  = array(mb_strtolower($codStr, 'utf-8'), $codInteger, $nameCat, "-",
        "'", '"', "блуза футболка женская", "без рукава", "юбкашорты",
        "женская", "летнее");
    //var_dump(mb_strtolower($codProd, 'utf-8'));
    $nameStr = mb_strtolower($codString, 'utf-8');
    //var_dump($nameStr);
    $nameStr = trim(str_replace($wovels, "", $nameStr));
    $nameStr = mb_strtoupper(mb_substr($nameStr, 0, 1, 'utf-8'), 'utf-8')
        .mb_substr($nameStr, 1, null, 'utf-8');
    $nameCat = mb_strtoupper(mb_substr($nameCat, 0, 1, 'utf-8'), 'utf-8')
        .mb_substr($nameCat, 1, null, 'utf-8');
    if ($nameStr !== "") {
        $nameProd = $nameCat.' "'.$nameStr.'"';
    } else {
        $nameProd = $nameCat;
    }
}
//var_dump($nameProd);
//Description
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $arrayDesc = deleteEmptyArrDescValues($arrayDesc);

    $searchArray = array('ткань:', 'длина:', "аппликация:", "цвет:", "состав ткани:",
        "расцветки:", "принт:", "рукав:",
        "рисунок:", "длина изделия:", "цвет принта:", "цвет изделия:", "цвет полос:",
        "материал горловины:",
        "материал изделия:", "материал полос:", "фактура ткани:", "цвет верха:",
        "материал верха:", "материал ленты:",
        "цвет горловины:", "материал вставок:", "ткань нашивки:", "нашивка:", "цвет нашивки:",
        "оторочка низа:",
        "декор:", "пояс:", "длина переда:", "длина спинки:", "материал пояса:", "пряжки:",
        "украшение:", "материал платья:",
        "материал накидки:", "цвет платья:", "цвет рисунка:", "длина платья (по центру спинки):",
        "минимальная длина накидки (у шва):",
        "максимальная длина накидки:", "ткань юбки:", "ткань верха:", "длина по спинке:",
        "ткань изделия:", "ткань вставки:", "молния:", "материал:", "длина юбки:",
        "длина от горловины до конца рукава:", "материал горловины/манжетов/юбки:",
        "цвет кофты:", "цвет горловины/манжетов/юбки:", "материал отворотов и ворота:",
        "материал вставки:", 'полуобхват "подола":', "подклад:", "цвет костюма:",
        "цвет сетки:", "камни:", "длина верха:", "длина низа:");
    if (isset($arrayDesc)) {
        foreach ($arrayDesc as $key => $value) {
            $existWordArr  = array(" :", "Длинна", "&nbsp;");
            $changeWordArr = array(":", "Длина", "");
            $value         = trim(str_replace($existWordArr, $changeWordArr,
                    htmlentities($value)));
            if (($value == 'Ткань:' || $value == 'Длина:' || $value == 'Аппликация:'
                || $value == 'Цвет:' ||
                $value == 'Состав ткани:' || $value == 'Расцветки:' || $value == "Принт:"
                || $value == "Рукав:" ||
                $value == "Рисунок:" || $value == "Длина изделия:" || $value == "Ткань"
                || $value == "Цвет принта:" ||
                $value == "Цвет изделия:" || $value == "Цвет полос:" || $value == "Материал горловины:"
                || $value == "Фактура ткани:" ||
                $value == "Цвет верха:" || $value == "Материал верха:" || $value
                == "Материал ленты:" || $value == "Цвет горловины:" ||
                $value == "Материал вставок:" || $value == "Цвет нашивки:" || $value
                == "Нашивка:" || $value == "Ткань нашивки:" ||
                $value == "Оторочка низа:" || $value == "Декор:" || $value == "Пояс:"
                || $value == "Длина переда:" ||
                $value == "Длина спинки:" || $value == "Материал пояса:" || $value
                == "Пряжки:" || $value == "Украшение:" ||
                $value == "Материал платья:" || $value == "Материал накидки:" || $value
                == "Цвет платья:" ||
                $value == "Цвет рисунка:" || $value == "Длина платья (по центру спинки):"
                ||
                $value == "Минимальная длина накидки (у шва):" || $value == "Максимальная длина накидки:"
                ||
                $value == "Ткань юбки:" || $value == "Ткань верха:" || $value == "Длина по спинке:"
                || $value == "Ткань изделия:" || $value == "Ткань вставки:" || $value
                == "Молния:" || $value == "Материал:" || $value == "Длина юбки:"
                || $value == "Длина от горловины до конца рукава:" || $value == "Материал горловины/манжетов/юбки:"
                || $value == "Цвет кофты:" || $value == "Цвет горловины/манжетов/юбки:"
                || $value == "Материал отворотов и ворота:" || $value == "Материал вставки:"
                || $value == 'Полуобхват "подола":' || $value == "Подклад:" || $value
                == "Цвет костюма:" || $value == "Цвет сетки:" || $value == "Камни:"
                || $value == "Длина верха:" || $value == "Длина низа:") &&
                mb_strpos($arrayDesc[$key + 1], ":") == FALSE) {
                $value .= $arrayDesc[$key + 1];
            } else if ($value == 'Материал') {
                $value .= $arrayDesc[$key + 1].$arrayDesc[$key + 2];
            }
            $descProd = findStringDesc($value, $searchArray, $descProd);
        }
    }
}

//var_dump($arrayDesc);
//var_dump($descProd);
//echo $descProd;
//die;
//==============================================================================
//                            Функции
//==============================================================================

/**
 * strrev с нормальной кодировкой
 * @param type $str
 * @return type
 */
function utf8_strrev($str)
{
    preg_match_all('/./us', $str, $ar);
    return join('', array_reverse($ar[0]));
}
