<?php
//==============================================================================
//			B1   	24 (64,66)         		
//==============================================================================
//Переменные для записи в БД по умолчанию
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
$catId         = 66;
//var_dump($curLink);
//Exist URL
/* if ($verify !== "import" && ($statusCode  == 404)) {
  $deleteProd = TRUE;
  return;
  } */
//Exist
preg_match('/kinds:(.*),/', $pageBody, $matches);
//var_dump($matches);
if (isset($matches[1])) {
    $optionsProdStr = "";
    $json           = json_decode(trim($matches[1]), true);
    if (isset($json)) {

        $dataArray = array_values($json);
        //var_dump($dataArray);
        foreach ($dataArray as $value) {

            if ($value['kindAvail'] == "yes") {
                $existProd = TRUE;
                $optionsProdStr .= $value['kindDescription']."=".$value['kindCsize'].",";

                //Colors Size
            }
        }
        $optionsProdArray = explode(",", $optionsProdStr);
        $curColor         = "";
        foreach ($optionsProdArray as $key => $value) {
            $prevColor = $curColor;
            if (!empty($value)) {
                $curColor = strstr($value, "=", true);
                $size     = strstr($value, "=");
                $size     = str_replace("=", "", $size);
            } else {
                continue;
            }

            if ($curColor == $prevColor || $key == 0) {
                $optionsArray[$curColor][] = $size;
            } else {
                $optionsArray[$curColor][] = $size;
            }
        }
        if (isset($optionsArray)) {
            foreach ($optionsArray as $key => $valueArr) {
                $optionsProd .= $key."=";
                for ($i = 0; $i < count($valueArr); $i++) {
                    if ($i !== count($valueArr) - 1) {
                        $word = ",";
                    } else {
                        $word = ";";
                    }
                    $optionsProd .= $valueArr[$i].$word;
                }
            }
            //var_dump($optionsArray);
        }
        foreach ($dataArray as $value) {
            if ($value['kindAvail'] == "yes") {
                $existProd = TRUE;
            }
        }
        if ($optionsProd !== "") {
            if ($optionsProd{strlen($optionsProd) - 1} == ';') {
                $optionsProd = substr($optionsProd, 0, -1);
            }
        }
    } else {
        echo "\n<h3 style='color:red'>Не нашел json на странице!!!</h3>\n";
    }
}

//var_dump($existProd);
//var_dump($optionsProd);
//die;
//Price
$arrayPrice = checkEmptyOrChangeSelector($_SESSION['price'], $saw,
    'price - цена');
//var_dump($arrayPrice);
if (isset($arrayPrice)) {
    $regexp = '/[^0-9.]/';
    $price  = filterPrice(trim($arrayPrice[0]), $regexp);
    $price2 = $price;
}

if ($price == 0) {
    $existProd = FALSE;
    $price2    = 0;
}
//var_dump($price);
//var_dump($price2);
//die;
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
    $codProd = trim($arrayCod[0]);
    $pos     = strpos($codProd, "B1");
    if ($pos !== false) {
        $codProd = strstr($codProd, "B1");
        $codProd = str_replace("B1", "", $codProd);
        $codProd = str_replace("B1 ", "", $codProd);
        $codProd = preg_replace("/[а-я\s]/ui", "", $codProd);
        //$codProd = strstr($codProd, " ", true);
    }
}
//var_dump($codProd);
//Name
$arrayNameOne = checkEmptyOrChangeSelector('div.bread-tools > ul > li > a',
    $saw, 'name - название товара');
//var_dump($arrayName);
if (isset($arrayNameOne)) {
    foreach ($arrayNameOne as $value) {
        $value    = mb_strtolower(trim($value), 'utf-8');
        $nameProd .= $value;
        $wovels   = array("трикотаж", "главная", "одежда", "свитера");
        $nameProd = trim(str_replace($wovels, "", $nameProd));
        $nameProd = mb_strtoupper(mb_substr($nameProd, 0, 1, 'utf-8'), 'utf-8')
            .mb_substr($nameProd, 1, null, 'utf-8');
    }
}
//var_dump($nameProd);
if ($nameProd == "Кэжуал" || "Классические") {
    $nameProd  = "";
    //var_dump($nameProd);
    $arrayName = checkEmptyOrChangeSelector('div.rside > ul > li', $saw,
        'name - название товара');
//var_dump($arrayName);

    if (isset($arrayName)) {
        foreach ($arrayName as $key => $value) {
            $value = trim($value);
            $value = mb_strtolower(trim($value), 'utf-8');
            if (!empty($value)) {
                $arrayNameA[] = $value;
            }
        }
        foreach ($arrayNameA as $key => $value) {
            if ($value == "стиль" || $value == "форма") {
                $nameProd .= $arrayNameA[$key + 1]." ";
            } else if ($value == "бренд") {
                $nameBrend = $arrayNameA[$key + 1];
                if ($nameBrend == "b1") {
                    $catId = 64;
                }
            }
        }
        $wovels   = array("трикотаж", "офисные", "планшет", "классические", "вечерние",
            "шляпы", "жакет", "шарфы", "платки", "перчатки", "стильная", "пуловер",
            "коктейльные", "спортивные", "классическая", "универсальная", "кэжуал");
        $nameProd = trim(str_replace($wovels, "", $nameProd));
        $nameProd = mb_strtoupper(mb_substr($nameProd, 0, 1, 'utf-8'), 'utf-8')
            .mb_substr($nameProd, 1, null, 'utf-8');
    }
}
//var_dump($nameProd );
if ($nameProd == "") {
    $nameProd = trim($arrayCod[0]);
    $nameProd = trim(mb_strstr($nameProd, " "));
    $nameProd = mb_strtoupper(mb_substr($nameProd, 0, 1, 'utf-8'), 'utf-8')
        .mb_substr($nameProd, 1, null, 'utf-8');
}
//var_dump($arrayNameOne[2]);
//var_dump($arrayNameOne[3]);
if ($arrayNameOne[2] == "Перчатки") {
    $nameProd = "Перчатки";
} else if ($arrayNameOne[2] == "Женские сумки") {
    $nameProd = "Женская сумка (".mb_strtolower(trim($arrayNameOne[3]), 'utf-8').")";
} else if ($arrayNameOne[2] == "Мужские сумки") {
    $nameProd = "Мужская сумка (".mb_strtolower(trim($arrayNameOne[3]), 'utf-8').")";
} else if ($arrayNameOne[2] == "Сумки унисекс") {
    $nameProd = "Сумка унисекс (".mb_strtolower(trim($arrayNameOne[3]), 'utf-8').")";
} else if ($arrayNameOne[2] == "Браслеты") {
    $nameProd = "Браслет";
} else if ($arrayNameOne[2] == "Колье") {
    $nameProd = $arrayNameOne[2];
} else if ($arrayNameOne[2] == "Серьги") {
    $nameProd = $arrayNameOne[2];
} else if ($arrayNameOne[2] == "Подвески") {
    $nameProd = "Подвеска";
} else if ($arrayNameOne[2] == "Воротники") {
    $nameProd = "Воротник";
} else if ($arrayNameOne[2] == "Натуральные камни") {
    $nameProd = "Натуральные камни";
} else if ($arrayNameOne[2] == "Свитера") {
    $nameProd = "Свитер";
} else if ($arrayNameOne[2] == "Броши") {
    $nameProd = "Брошь";
} else if ($arrayNameOne[2] == "Брелки") {
    $nameProd = "Брелок";
}

if (isset($arrayNameOne[3])) {

    if ($arrayNameOne[3] == "Визитница") {
        $nameProd = $arrayNameOne[3];
    } elseif ($arrayNameOne[3] == "Клатч") {
        $nameProd = $arrayNameOne[3];
    } elseif ($arrayNameOne[3] == "Кошелек") {
        $nameProd = $arrayNameOne[3];
    } elseif ($arrayNameOne[3] == "Рюкзак") {
        $nameProd = $arrayNameOne[3];
    } elseif ($arrayNameOne[3] == "Сумка мужская") {
        $nameProd = $arrayNameOne[3];
    } elseif ($arrayNameOne[3] == "Дорожная") {
        $nameProd = "Сумка дорожная";
    } elseif ($arrayNameOne[3] == "Шапки") {
        $nameProd = "Шапка";
    }
}
if ($nameProd == "Ветровки") {
    $nameProd = "Ветровка";
} elseif ($nameProd == "Зимние юбки") {
    $nameProd = "Зимняя юбка";
}
//var_dump($nameProd);
//var_dump($arrayNameA);
//var_dump($catId);
//var_dump($price);
//var_dump($price2);
//Description
$arrayDesc = checkEmptyOrChangeSelector($_SESSION["desc"], $saw,
    'desc - описание');
//var_dump($arrayDesc);

if (isset($arrayDesc)) {
    $arrayDesc   = deleteEmptyArrDescValues($arrayDesc);
    $searchArray = array('стиль:', 'материал:', "декор:", 'количество отделений и карманов:',
        'ручки:', 'цвет фурнитуры:', "ножки сумки:",
        "фактура:", "вес:", "внутренние отделения сумки:", "материал внутренний:",
        "внутренний размер сумки:", "внешний размер сумки:",
        "ткани:", "горловина:", "застежка:", "состав:", "пуговицы:", "утеплитель:",
        "материал подкладочный:", "материал верха:",
        "наличие пояса:");
    foreach ($arrayDesc as $key => $value) {
        if ($value == 'Стиль' || $value == "Материал" || $value == "Декор" || $value
            == "Количество отделений и карманов" || $value == "Ручки" ||
            $value == "Цвет фурнитуры" || $value == "Ножки сумки" || $value == "Фактура"
            || $value == "Вес" ||
            $value == "Внутренние отделения сумки" || $value == "Материал внутренний"
            || $value == "Внутренний размер сумки" ||
            $value == "Внешний размер сумки" || $value == "Ткани" || $value == "Горловина"
            || $value == "Застежка" || $value == "Состав" ||
            $value == "Пуговицы" || $value == "Утеплитель" || $value == "Материал подкладочный"
            || $value == "Материал верха" ||
            $value == "Наличие пояса") {
            if (trim($arrayDesc[$key + 1]) !== ',') {
                $value = $value.":".trim($arrayDesc[$key + 1]);
            }
        }
        $value    = str_replace("котон", "коттон", $value);
        $descProd = findStringDesc($value, $searchArray, $descProd);
    }
}
//var_dump($arrayDesc);
//var_dump($descProd);
//die;


