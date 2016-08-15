<?php
//==============================================================================
//			Ghazel	43-311
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

    $url = (string) $offer->url;

    if ($url == $curLink && $offer['available'] == TRUE) {
        $exist = $size  = (string) $offer->param[1];
        $color = (string) $offer->param[0];

        $imgMain     = (string) $offer->picture[0];
        $imgDopArr[] = (string) $offer->picture[0];

        foreach ($offer->param as $key => $value) {
            $descArr[(string) $value['name'][0].':'] = (string) $value;
        }

        $colSizeArray[$color][] = $size;
        $imgMainArr[]           = $imgMain;
        $arrayIdUrl             = array("cod" => (string) $offer->article, "price" => (string) $offer->price,
            "price2" => (string) $offer->prices->price->value,
            "name" => (string) $offer->name, "colorSize" => $colSizeArray, "description" => $descArr,
            "mainSrcImg" => array_values(array_unique($imgMainArr)), "dopSrcImg" => array_values(array_unique($imgDopArr)));
    } else {
        continue;
    }
}
//var_dump($arrayIdUrl);
//die;
//Price price2
if (!isset($arrayIdUrl)) {
    $existProd = FALSE;
} else {
    $price  = (string) ceil($arrayIdUrl["price"]);
    $price2 = (string) ceil($arrayIdUrl["price2"]);

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

    $regexp   = '/[0-9-.]/';
    $nameProd = trim(preg_replace($regexp, "", $arrayIdUrl["name"]));
    $codProd  = $arrayIdUrl["cod"];

    //$descArr = $arrayIdUrl['description'];
    $searchArray = array("состав:", "состав ткани:", "отделка и украшения хар:",
        "силуэт:",
        "стиль:", 'тип ткани хар:', 'фасон выреза горловины:', 'фасон рукава:', 'длина изделия:',
        'тип ткани:', 'длина рукава:', 'длина рукава хар:', 'отделка и украшения:');
    foreach ($arrayIdUrl["description"] as $key => $value) {
        $descProd = findStringDesc($key.$value, $searchArray, $descProd);
    }
    $descProd = str_replace(' хар:', ':', $descProd);
}

//var_dump($existProd);
//var_dump($price);
////var_dump($price2);
//var_dump($optionsProd);
//var_dump($nameProd);
//var_dump($codProd);
//var_dump($descProd);
//die;
