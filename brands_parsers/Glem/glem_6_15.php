<?php
//==============================================================================
//			Glem	6-15        		
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
/* if ($verify !== "import" && ($statusCode == 404)) {
  $deleteProd = TRUE;
  return;
  } */
//Exist
foreach ($saw->shop->offers->offer as $key => $offer) {
    //var_dump($offer->picture);
    $url = (string) $offer->url;
    if ($url == $curLink) {
        $size    = (string) $offer->param[1];
        $color   = (string) $offer->param[0];
        //var_dump(count($offer->picture));
        $imgMain = (string) $offer->picture[0];
        foreach ($offer->picture as $key => $value) {
            $imgDop      = (string) $value;
            $imgDopArr[] = $imgDop;
        }

        $colSizeArray[$color][] = $size;
        $imgMainArr[]           = $imgMain;
        $arrayIdUrl             = array("cod" => (string) $offer['id'], "price" => (string) $offer->price,
            "price2" => (string) $offer->prices->price->value,
            "name" => (string) $offer->name, "colorSize" => $colSizeArray, "description" => (string) $offer->description,
            "mainSrcImg" => array_values(array_unique($imgMainArr)), "dopSrcImg" => array_values(array_unique($imgDopArr)));
    } else {
        continue;
    }
}

//Price price2
if (!isset($arrayIdUrl) || ($arrayIdUrl["mainSrcImg"][0] == "http://www.glem.com.ua/")) {
    $existProd = FALSE;
} else {
    // action discount 40%
    $price  = ceil($arrayIdUrl["price"] * $_SESSION['updatePrice']);
    $price2 = $arrayIdUrl["price2"];

    $optionsProdArr = $arrayIdUrl["colorSize"];
    if ($price == 0) {
        $existProd = FALSE;
        $price2    = 0;
    }
    foreach ($optionsProdArr as $key => $sizeArr) {
        $optionsProd .= ";".$key."=";
        foreach ($sizeArr as $value) {
            $optionsProd .= $value.",";
        }
    }
    $optionsProd         = substr(str_replace(",;", ";", $optionsProd), 1);
    $optionsProd         = substr($optionsProd, 0, -1);
    $_SESSION["imgMain"] = $arrayIdUrl["mainSrcImg"];
    $_SESSION["imgDop"]  = $arrayIdUrl["dopSrcImg"];
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
    if ($verify == "verify") {
        return;
    }
    $wovels   = array('R-10 ', '№1П', '№5ДН', '№3ДН', '№1ДН', "К д/р", "д/р", "№",
        "1Ф", "к/р", "б/р", "(зима)", "(весна)", "мод.",
        "Весна", "весна", "кор.", "К1", "2Н", "3П", "3Ш", "1Н",
        "-2В", "-3Б", "-2Б", "7Б", "1М", "п/", "1К", "С2", "К3", "Д", "1 Л", "1 П",
        "Ф2", "М2", "РП");
    $nameProd = trim(str_replace($wovels, "", $arrayIdUrl["name"]));
    //var_dump($nameProd);
    $regexp   = '/[0-9-.]/';
    $nameProd = trim(preg_replace($regexp, "", $nameProd));
    $codProd  = $arrayIdUrl["cod"];
    if (strpos($arrayIdUrl["description"], "Размерная сетка")) {
        $descProd = chengeDesc($arrayIdUrl["description"], $descProd);
    } else {
        $wovelsFrom                = array("Состав", "&nbsp;");
        $wovelsTo                  = array("Состав:", "");
        $arrayIdUrl["description"] = strip_tags(str_replace($wovelsFrom,
                $wovelsTo, $arrayIdUrl["description"]));

        $wovels      = array("состав", "длина изделия", "длина юбки", "ширина изделия (ремня)",
            "длина рукава");
        $searchArray = array("состав:", "длина изделия:", "длина юбки:", "ширина изделия (ремня):",
            "длина рукава:");
        $descProd    = getDesc($arrayIdUrl["description"], $descProd, $wovels,
            $searchArray);
    }
}

//var_dump($existProd);
//var_dump($price);
//var_dump($price2);
//var_dump($optionsProd);
//var_dump($nameProd);
//var_dump($codProd);
//var_dump($descProd);
//die;
//==============================================================================
//                           Функции				
//==============================================================================

/**
 * Функция обработки описания Glem
 * @param type $descStr
 * @param type $descProd
 * @return string
 */
function chengeDesc($descStr, $descProd)
{
    //var_dump($descStr);
    $descFirst   = trim(strstr($descStr, "<strong>Размерная сетка", true));
    $descMiddle  = trim(strstr($descStr, "<strong>Размерная сетка"));
    $descSecond  = trim(strstr_after($descMiddle, "</table>"));
    $descProdNew = $descFirst.$descSecond;
    $arrayDesc   = deleteEmptyArrDescValues(explode("<p>", $descProdNew));
    //var_dump($arrayDesc);
    $searchArray = array("состав:", "длина изделия:", "длина юбки:", "ширина изделия (ремня):",
        "длина рукава:");
    foreach ($arrayDesc as $key => $value) {
        $wovelsFrom = array("Состав", "&nbsp;");
        $wovelsTo   = array("Состав:", "");
        $value      = strip_tags(str_replace($wovelsFrom, $wovelsTo, $value));
        //var_dump($value);
        if ($value == 'Состав:') {
            $value .= trim(strip_tags($arrayDesc[$key + 1]));
        }
        $descProd = findStringDesc($value, $searchArray, $descProd);
        if (strpos($descStr, "One Size")) {
            $descProd .= "<p><span>Описание:</span>One Size - это универсальный размер, использующийся для изделий из элластичных, "
                ."тянущихся материалов, а также для изделий, конструктивно выполненных таким образом, что они подходят на целый диапазон размеров."
                ." Обычно этот диапазон - от размера XS/S до размера M/L.</p>";
        }
    }
    return $descProd;
}
