<?php
//==============================================================================
//                          Crisma 29-87				
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
//--------------------------------Exist URL-------------------------------1-----
/* if ($verify !== "import" && ($statusCode  == 503)) {
  $deleteProd = TRUE;
  return;
  } */
//--------------------------------Exist-----------------------------------2----- 
$arrayExist    = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
//var_dump($arrayExist);
if (isset($arrayExist)) {
    foreach ($arrayExist as $value) {
        $value  = mb_strtolower(trim($value), 'utf-8');
        $findme = 'нет';
        $pos    = mb_strpos($value, $findme);
        if ($pos !== false) {
            $existProd = FALSE;
            break;
        } else {
            $existProd = TRUE;
        }
    }
}
//var_dump($existProd);
//--------------------------------Price price2------------------------------2---
if (file_exists('brands_parsers/Crisma/price.json')) {
    $exelJson = file_get_contents('brands_parsers/Crisma/price.json');
    if (isset($exelJson)) {
        $exelArray = json_decode($exelJson, true);
    }
    //var_dump($exelArray);
    if (isset($exelArray)) {
        foreach ($exelArray as $value) {
            if (trim($curLink) === trim($value[5])) {
                $price  = ceil($value[2]);
                $price2 = ceil($value[4]);
                break;
            } else {
                $price = 0;
            }
        }
    }
} else {
    echo "<h4 style='color:red'>\nПрайс не записался в json!!!</h4>";
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
    echo "<h4 style='color:red'>\nНет URL товара-{$curLink}, в прайсе crisma!!!</h4>";
}
//var_dump($price);
//var_dump($price2);
//die;
//==============================================================================
//                      Colors Size another options                      
//==============================================================================
//------------------------парсим все опции crisma--------------------------4----
$arrayOptions = checkEmptyOrChangeSelector('span.opt', $saw,
    'options - опции товара');

if (isset($arrayOptions)) {
    $strOptionsName = "";
    foreach ($arrayOptions as $value) {
        $strOptionsName .= "=;".$value;
    }

    $strOptionsName   = strstr($strOptionsName, "36")."=";
    $optionsNameArray = explode(";", $strOptionsName);
    $urlCod           = strstr(strstr_after($curLink, 'shop/'), "/", true);
    foreach ($optionsNameArray as $key => $valueName) {
        if ($key <= 4) {
            $int = $key + 2;
        } else {
            $int = $key + 3;
        }

        $selector      = "#id-{$urlCod}-oval-{$int}";
        $arrayProperty = $saw->get($selector)->toTextArray();

        $values = "";
        foreach ($arrayProperty as $value) {
            $values .= $value.",";
        }
        $values = substr($values, 0, strlen($values) - 1);
        $optionsProd .= $valueName.$values.";";
    }
}
//var_dump($optionsProd);
//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    return;
}

//--------------------------------Cod-------------------------------------5-----
$arrayCod = checkEmptyOrChangeSelector($_SESSION['cod'], $saw,
    'cod - код товара');

if (isset($arrayCod)) {
    $codProd = trim(mb_strstr($arrayCod[0], " "));
}
//var_dump($codProd);
//--------------------------------Name------------------------------------6-----
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - название товара');

if (isset($arrayName)) {
    $nameProd = mb_strstr($arrayName[0], " ", true); //trim(preg_replace($regexp, "", $arrayName[0]));
}

//var_dump($nameProd);
//-------------------------------Description--------------------------------7---
//var_dump($descProd);
//die;
