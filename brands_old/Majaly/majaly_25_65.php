<?php
//==============================================================================
//			Majaly   	25-65
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
//--------------------------------Exist URL-------------------------------1-----
/* if ($verify !== "import" && ($statusCode == 404)) {
  $deleteProd = TRUE;
  return;
  } */
//--------------------------------Exist-----------------------------------1-----
$arrayExist    = checkEmptyOrChangeSelector($_SESSION['no_nal'], $saw,
    'no_nal - наличие');
//var_dump($arrayExist);
if (isset($arrayExist)) {
    foreach ($arrayExist as $value) {
        $value  = mb_strtolower(trim($value), 'utf-8');
        $findme = 'в нал';
        $pos    = strpos($value, $findme);
        if ($pos !== false) {
            $existProd = TRUE;
            break;
        }
    }
}

//==============================================================================
//                   Если это проверщик то выходим из скрипта
//==============================================================================
if ($verify == "verify") {
    return;
}
//--------------------------------Name------------------------------------4-----
$arrayName = checkEmptyOrChangeSelector($_SESSION["h1"], $saw,
    'name - имя товара');
//var_dump($arrayName);
if (isset($arrayName)) {
    $nameProd       = trim($arrayName[0]);
    /* $wovels         = array("Комбинезон", "Топ", "Блуза", "Летний костм", "Белый костюм",
      "Костюм", "Джинсовый спортивный костюм", "Брюки",
      "Спортивный костюм", "Платье", "Нарядное", "Платья", "-туника", "с карманами",
      "в пол", "Летний костюмчик", "Леопардовая футболка",
      "Футболки", "Майка", "Футболка", "- кофта серо-белая", "Модная", "Женская",
      "Трикотажная",
      "Стильная", "Белая", "Атласная",
      "Элегантная", "Яркая", "Привлекательная", "Летняя", "Нарядный", "Трикотажный",
      "Женский", "Трикотажное", "Чёрное", "Яркое", "Сексуальное",
      "Стильное", "Элегантное", "Модное", "Высококачественное", "Интересное", "Длинное",
      "Женское", "Привлекательное", "Весеннее");
      $nameProd       = str_replace($wovels, "", $nameProd); */
    $nameProdSearch = str_replace(" ", "", $nameProd);
    $nameProdSearch = mb_strtolower(trim(str_replace("э", "е", $nameProdSearch)),
        'utf-8');
}

//Cod
$arrayCod = checkEmptyOrChangeSelector($_SESSION['cod'], $saw,
    'code - код товара');

if (isset($arrayCod)) {
    $codProd = $arrayCod[0];
}

//-----------------------------Price size color desc name-------------------2---
if (file_exists('brands_parsers/Majaly/price.json')) {
    $exelJson = file_get_contents('brands_parsers/Majaly/price.json');
    if (isset($exelJson)) {
        $exelArray = json_decode($exelJson, true);
    }
    //var_dump($exelArray);
    if (isset($exelArray)) {
        foreach ($exelArray as $value) {
            if ($value[0] == "") {
                continue;
            }
            $codXml = explode(",", $value[0]);

            $codXml[0] = str_replace(" ", "", $codXml[0]);
            $codXml[0] = mb_strtolower(trim(str_replace("э", "е", $codXml[0])),
                'utf-8');

            $pos = strpos($nameProdSearch, $codXml[0]);
            if ($pos !== FALSE) {
                //var_dump($codXml[0]);
                //var_dump($nameProdSearch);

                $regexp    = '/[^0-9.]/';
                $price2    = filterPrice(trim($value[1]), $regexp);
                $price     = $price2 * 2;
                $sizesProd = str_replace(',', ";", $value[3]);
                if (trim($value[4]) !== "") {
                    $descProd = "<p><span>Цвет:</span>".trim($value[4])."</p>";
                }
                if (trim($value[2]) !== "") {
                    $descProd .= "<p><span>Состав:</span>".trim($value[2])."</p>";
                }
                $nameProd = trim($value[0]);
                break;
            }
        }
    }
} else {
    echo "<h4 style='color:red'>\nПрайс не записался в json!!!</h4>";
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
    echo "<h4 style='color:red'>\nНет оптовой цены!!!Нет кода товара-{$nameProdSearch}, в прайсе majaly!!!</h4>";
}

//var_dump($existProd);
//var_dump($price);
//var_dump($price2);
//var_dump($sizesProd);
//var_dump($descProd);
//var_dump($nameProd);
//var_dump($codProd);
//die;
