<?php
ini_set('memory_limit', '-1');
//==============================================================================
//			Dembo Hause   	34-218        		
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

//--------------------------------Exist URL-------------------------------1-----
if ($verify !== "import" && ($statusCode  == 301 || $statusCode  == 404)) {
    $deleteProd = TRUE;
    return;
}
//--------------------------------Exist-----------------------------------2-----
//--------------------------------Name------------------------------------3-----
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');
//var_dump($arrayName);

if (isset($arrayName)) {
    $nameProd = trim($arrayName[0]);
    $nameProd = mb_strtoupper(mb_substr($nameProd, 0, 1, 'utf-8'), 'utf-8')
        .mb_substr($nameProd, 1, null, 'utf-8');
}
//var_dump($nameProd);
//---------------------Price  Size and color name cod-----------------------4---
if (file_exists('brands_parsers/DemboHouse/price.json')) {
    $exelJson = file_get_contents('brands_parsers/DemboHouse/price.json');
    if (isset($exelJson)) {
        $exelArray = json_decode($exelJson, true);
    }
    if (isset($exelArray)) {
        foreach ($exelArray as $value) {
            $nameToLower    = str_replace(" ", "",
                mb_strtolower($nameProd, 'utf-8'));
            $wovels         = array('"', " ");
            $nameExlToLower = trim(str_replace($wovels, "",
                    mb_strtolower($value[0], 'utf-8')));
            //var_dump($nameToLower);
            //var_dump($nameExlToLower);
            if ($nameToLower == $nameExlToLower) {
                $nameProd = trim($value[0]);
                $price    = ceil($value[6] * 1.5);
                $price2   = $price;
                if (!strpos($value[2], "on")) {
                    $sizesProd = trim($value[2]);
                }

                $optionsProd = $value[4];
                if (isset($value[1])) {
                    $codProd = $value[1];
                }
                break;
            } else {
                $price  = 0;
                $price2 = $price;
            }
        }
    }
} else {
    echo "<h4 style='color:red'>\nПрайс не записался в json!!!</h4>";
}

if ($price == 0) {
    $existProd = FALSE;
    echo "<h4 style='color:red'>\nНет цены!!!Нет имени товара-{$nameProd}, в прайсе demboHouse!!!</h4>";
}
//var_dump($nameProd);
//var_dump($price);
//var_dump($sizesProd);
//var_dump($optionsProd);
//var_dump($codProd);
//die;
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    return;
}
//-------------------------------Description--------------------------------5---
$strDesc = trim($saw->get($_SESSION['desc'])->toText());
//var_dump($arrayDesc);

if (!empty($strDesc) || $strDesc !== "") {
    $wovels      = array("тканин", "наповню", "опи");
    $searchArray = array("тканини :", "тканини:", "наповнювач :", "наповнювач:");
    $descProd    = getDesc($strDesc, $descProd, $wovels, $searchArray, $idBrand);
    $existWords  = array("Тканини :", "Тканини:", "Наповнювач :", "Наповнювач:");
    $changeWords = array("Ткань:", "Ткань:", "Наполнитель:", "Наполнитель:");
    $descProd    = str_replace($existWords, $changeWords, $descProd);
}
//var_dump($descProd);
//die;
