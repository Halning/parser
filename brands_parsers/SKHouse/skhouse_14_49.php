<?php
//==============================================================================
//			SKHouse   	14-49         		
//==============================================================================
//-------------------Переменные для записи в БД по умолчанию--------------------
$existProd     = FALSE;
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
if ($verify !== "import" && ($statusCode == 500)) {
    $deleteProd = TRUE;
    return;
}

//Price
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp = '/[^0-9.]/';
    $price  = filterPrice(trim(str_replace(",", ".", $arrayPrice[1])), $regexp);
}

//price2
$arrayPrice2 = checkEmptyOrChangeSelector($_SESSION['price2'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice2)) {
    $regexp = '/[^0-9.]/';
    $price2 = filterPrice(trim(str_replace(",", ".", $arrayPrice2[1])), $regexp);
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
//var_dump($arraySize);

if (isset($arraySize)) {
    $colorsProd = "";
    foreach ($arraySize as $value) {
        $sizesProd = $sizesProd.trim($value).";";
    }
} else {
    $existProd = FALSE;
}
//var_dump($sizesProd);
//Color
$arrayColor = checkEmptyOrChangeSelector('#ColorId option', $saw, 'Col - цвет');
//var_dump($arrayColor);

if (isset($arrayColor) && count($arrayColor) > 1) {
    foreach ($arrayColor as $value) {
        $colorsProd = $colorsProd.trim($value).";";
    }
    $vowels     = array("-- Цвет --;");
    $colorsProd = str_replace($vowels, "", $colorsProd);
    $colorsProd = (string) substr($colorsProd, 0, strlen($colorsProd) - 1);
}
//var_dump($colorsProd);
//ColorSize
//Exist
preg_match('/var leftovers =(.*)/', $pageBody, $matches);
//var_dump($matches);

if (isset($matches[1]) && isset($colorsProd) && isset($sizesProd)) {
    $json     = json_decode($matches[1], true);
    //var_dump($json);
    $colorArr = explode(";", $colorsProd);
    $sizesArr = explode(";", $sizesProd);
    //var_dump($colorArr);
    //var_dump($sizesArr);
    foreach ($colorArr as $key => $value) {
        $colorArr[$key] = transleteColorSize($value, "col", TRUE);
    }
    foreach ($sizesArr as $key => $value) {
        $sizesArr[$key] = transleteColorSize($value, "siz", TRUE);
    }
    //var_dump($colorArr);
    //var_dump($sizesArr);

    foreach ($json as $key => $value) {
        $colorName = transleteColorSize($value['ColorId'], "col", FALSE);
        $sizeName  = transleteColorSize($value['SizeId'], "siz", FALSE);

        if ($value['NotForSale'] == FALSE && $value['isAvailableForProduction'] == FALSE
            && in_array($value['ColorId'], $colorArr) && in_array($value['SizeId'],
                $sizesArr)) {
            if (!strpos($optionsProd, substr($colorName, 1))) {
                $optionsProd = substr($optionsProd, 0, -1);
                $optionsProd .= ";".$colorName."=".$sizeName.",";
            } else {
                $optionsProd .= $sizeName.",";
            }
        }
    }
}

if ($optionsProd !== "") {
    $existProd   = TRUE;
    $optionsProd = substr($optionsProd, 0, -1);
    $optionsProd = substr($optionsProd, 1);
}
$sizesProd = "";
//var_dump($optionsProd);
//var_dump($existProd);
//die;
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    return;
}

//Cod
if (isset($curLink)) {
    $codProd = strstr_after($curLink, "Products/Product/");
}
//var_dump($codProd);
//Name
$arrayName = checkEmptyOrChangeSelector($_SESSION['h1'], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $nameProd = trim(preg_replace("/\\d/", "", trim($arrayName[0])));
}
//var_dump($nameProd);
//Description
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');

if (isset($arrayDesc)) {
    $arrayDesc = deleteEmptyArrDescValues($arrayDesc);
    //var_dump($arrayDesc);

    $descProd = '<p>'.trim($arrayDesc[0]).'</p>';
    if (count($arrayDesc) > 1) {
        $descProd .= '<p><span>Состав:</span>';
        foreach ($arrayDesc as $key => $value) {
            if ($key !== 0) {
                $descProd .= ' '.$value;
            }
        }
        $descProd .= '</p>';
    }
}
//var_dump($descProd);
//==============================================================================
//                             Функции
//==============================================================================

/**
 * Транслитим кода цветов и размеров в названия
 * @param type $propId
 * @param type $colOrSize
 * @return string
 */
function transleteColorSize($propProp, $colOrSize, $select)
{
    if ($colOrSize == "col") {
        $array = array(3 => "Белый", 4 => "Бежевый", 5 => "Шоколадный", 6 => "Серый",
            8 => "Темно-серый",
            9 => "Розовый", 11 => "Леопардовый", 15 => "Темно-синий", 17 => "Коралловый",
            13 => "Синий",
            19 => "Красный", 20 => "Желтый", 21 => "Кремовый", 22 => "Зеленый", 24 => "Малиновый",
            32 => "Голубой",
            35 => "Оранжевый", 36 => "Коричневый", 38 => "Фиолетовый", 42 => "Бело-чёрный",
            48 => "Золотой", 54 => "Персик", 56 => "Лайм", 58 => "Бордовый",
            65 => "Бирюзовый", 74 => "Чёрный", 95 => "Салатовый", 115 => "Серебро",
            121 => "Пудра",
            124 => "Ментол", 130 => "Фукси", 146 => "Капучино", 158 => "Темно-фиолетовый",
            159 => "Чёрно-розовый", 160 => "Чёрно-бежевый", 161 => "Бежево-розовый",
            167 => "Молочный",
            180 => "Серый с черным", 181 => "Серый с белым", 182 => "Бежевый с черным",
            183 => "Бежевый с белым", 184 => "Розово-чёрный", 185 => "Желто-чёрный",
            189 => "Белый с розовым", 190 => "Белый с желтым", 191 => "Чёрный с розовым",
            192 => "Чёрный с желтым", 195 => "Синий с белым", 1195 => "Бежево-красный",
            1196 => "Бежево-черный",
            1197 => "Красно-бежевый", 1198 => 'Бронзовый');
    } else {
        $array = array(2 => "Универсальный S-L", 3 => "Универсальный S-M", 4 => "Универсальный M-L",
            6 => "S", 7 => "M", 10 => "Универсальный", 11 => "L", 12 => "XL", 13 => "XXL",
            14 => "3XL", 54 => "36", 55 => "35",
            17 => "37", 18 => "38", 19 => "39",
            20 => "40", 21 => "41", 22 => "42", 61 => "26", 62 => "27", 63 => "28",
            64 => "29", 65 => "30", 66 => "31",
            67 => "32", 68 => "33", 69 => "34", 71 => "Универсальный L-XL");
    }

    $propVal = "";
    if ($select == FALSE) {
        if (isset($array[$propProp])) {
            $propVal = $array[$propProp];
        }
    } else {
        $propVal = array_search($propProp, $array);
    }
    return $propVal;
}
