<?php

use Parser\Provader\ProvaderPageCardo;

//==============================================================================
//			Cardo   	5-1          		
//==============================================================================
//Переменные для записи в БД по умолчанию
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
$arrayExist = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
//var_dump($arrayExist);

if (isset($arrayExist)) {
    $existProd = FALSE;
}
//var_dump($existProd);
//Price price2
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);

if (isset($arrayPrice)) {
    $regexp       = '/[^0-9]/';
    $price        = filterPrice(trim($arrayPrice[0]), $regexp);
    $price2       = $price;
    $provaderPage = new ProvaderPageCardo(str_replace("cardo.com.ua",
            "cardo-ua.com", $curLink));
    $provaderPage->getWebPage("brands_parsers/Cardo/cookie.txt");
    $sawOpt       = $provaderPage->createNokogiriObject();
    if ($sawOpt == FALSE) {
        echo "Нет страницы для оптовой цены!!! Урл: <a href={$curLink} target='_blank' >{$curLink}</a>\n";
        $existProd = FALSE;
    } else {
        $arrayPrice2 = checkEmptyOrChangeSelector($_SESSION['price'], $sawOpt,
            'price2 - опт цена');
        if (isset($arrayPrice)) {
            $regexp = '/[^0-9]/';
            $price2 = filterPrice(trim($arrayPrice2[0]), $regexp);
        }
    }
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
    for ($i = 1; $i <= 3; $i++) {
        preg_match("/addCombination.*\\);/", $pageBody, $matches);
        if (isset($matches[0])) {
            $pageBody                     = str_replace($matches[0], "",
                $pageBody);
            $sizeCod                      = strstr_after(strstr($matches[0],
                    "'),", true), "('");
            $sizeCount                    = (int) strstr(strstr_after($matches[0],
                        "'),"), ",", true);
            //var_dump($sizeCount);
            $sizeName                     = transleteCodToSize($sizeCod);
            $sizeFromHtmlArray[$sizeName] = $sizeCount;
            //var_dump($matches);
        }
    }
    //var_dump($sizeFromHtmlArray);

    foreach ($sizeFromHtmlArray as $key => $value) {
        if ($value !== 0) {
            $sizesProd .= $key;
        }
    }

    $sizesProd = filterSizeColors($sizesProd);

    //Count
    foreach ($sizeFromHtmlArray as $key => $value) {
        if ($value !== 0) {
            $key = str_replace(";", "", $key);
            $comCount .= $key."=".trim($value).";";
        }
    }
}
//var_dump($comCount);
//var_dump($sizesProd);
//die;
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    //From Cardo OLD
    //updateCardo($mysqli, $comCount, $commodityID);
    //end
    return;
}

//Cod
$arrayCod = checkEmptyOrChangeSelector($_SESSION["cod"], $saw,
    'cod - код товара');
//var_dump($arrayCod);

if (isset($arrayCod)) {
    $codProd = trim($arrayCod[0]);
}
//var_dump($codProd);
//Name
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $wovels   = array("ВЕСНА-", "ВЕСНА", "ЛЕТО-", "ЛЕТО", "ОСЕНЬ-", "ОСЕНЬ", "ЗИМА-",
        "ЗИМА", "2015", "2014", "2016", "|", "весна", ".");
    $nameProd = trim(str_replace($wovels, "", $arrayName[0]));
}
//var_dump($nameProd);
//Description
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $arrayDesc         = deleteEmptyArrDescValues($arrayDesc);
    $searchArray       = array('состав:', 'состав ткани:');
    foreach ($arrayDesc as $key => $value) {
        $existWordArr  = array(" :", "длинна");
        $changeWordArr = array(":", "длина");
        $value         = trim(str_replace($existWordArr, $changeWordArr, $value));
        $descProd      = findStringDesc($value, $searchArray, $descProd);
        if (strlen($value) > 250) {
            $descProd .= '<p><span>Описание:</span>'.trim($value).'</p>';
        }
    }
}

//var_dump($existProd);
//var_dump($arrayDesc);
//var_dump($descProd);
//die;
//==============================================================================
//                          Проверка на дубликаты
//==============================================================================
//--------------------Проверяем по главной картинке дубликаты-------------2-----
//var_dump($duplicate);
if (empty($duplicate)) {
    $duplicate[] = "";
}

foreach ($duplicate as $value) {
    if ($value == $codProd) {
        $existProd = FALSE;
        $existDub  = TRUE;
        break;
    } else {
        $_SESSION["duplicateArray"][] = $codProd;
        $_SESSION["duplicateArray"]   = array_unique($_SESSION["duplicateArray"]);
    }
}
$duplicateProd = $codProd;
//var_dump($_SESSION["duplicateArray"]);
//==============================================================================
//                             Функции
//==============================================================================

/**
 * Переводим кода размеров в названия
 * @param type $sizeCod
 * @return string
 */
function transleteCodToSize($sizeCod)
{
    $array = array(24 => "L(48);", 23 => "M(46);", 22 => "S(44);", 21 => "XS(42);",
        32 => "XXS(40);",
        27 => "M-L(46-48);", 26 => "XS-S(42-44);");

    $sizeName = "";
    if (isset($array[$sizeCod])) {
        $sizeName = $array[$sizeCod];
    }
    return $sizeName;
}
/**
 * From Cardo OLD
 * @param type $mysqli
 * @param type $comCount
 * @param type $commodityID
 */
/*function updateCardo($mysqli, $comCount, $commodityID)
{
    $comCountArr = explode(";", $comCount);
    array_pop($comCountArr);
    if (!($stmt        = $mysqli->prepare("SELECT size, quantity FROM shop_cardo_sizes WHERE commodity_id=?"))) {
        die('Error shop_cardo_sizes('.$mysqli->errno.') '.$mysqli->error);
    } else {
        $stmt->bind_param("i", $commodityID);
        $stmt->execute();
        $stmt->bind_result($sizeOld, $quantityOld);
        while ($stmt->fetch()) {
            $cardoSize[$sizeOld] = $quantityOld;
        }
        $stmt->close();
    }

    foreach ($comCountArr as $key => $value) {
        $size                  = strstr($value, "=", true);
        $quantity              = strstr_after($value, "=");
        $comCountArrNew[$size] = $quantity;
    }

    if (isset($cardoSize)) {
        foreach ($cardoSize as $key => $value) {
            if (isset($comCountArrNew[$key])) {
                $comCountArrLast[$key] = $comCountArrNew[$key];
            } else {
                $comCountArrLast[$key] = '0';
            }
        }

        foreach ($comCountArrLast as $key => $value) {
            if (!($stmt = $mysqli->prepare("UPDATE shop_cardo_sizes SET  quantity=?
          WHERE commodity_id=? AND size=?"))) {
                die('Update shop_cardo_sizes Error ('.$mysqli->errno.') '.$mysqli->error);
            }
            $stmt->bind_param("sis", $value, $commodityID, $key);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        insertCardo($mysqli, $comCount, $commodityID);
    }
}*/
