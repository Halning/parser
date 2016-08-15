<?php
error_reporting(-1);

use Modules\BlobStorage;

//==============================================================================
//                              Функции
//==============================================================================

/**
 * 
 * @global type $startTime
 */
function myShutdown()
{
    global $startTime;
    echo "execution took: ".
    (microtime(true) - $startTime).
    " seconds.";
}

//==============================================================================
//                   Функции обрезки и записи картинок
//==============================================================================
//------------------------------------------------------------------------------
//        Подготовка к обработке картинок запись временной картинки          1
//------------------------------------------------------------------------------
function cropAndWriteImageBegin($srcProdArray, $commodityID, $nameImArray,
                                $brendName, $idBrand)
{
    $blobStorage = new BlobStorage();

    if (is_dir('../images/commodities/'.$commodityID.'/') == FALSE) {
        mkdir('../images/commodities/'.$commodityID);
    }

    if ($blobStorage->isContainer((string)$commodityID)) {
        $blobStorage->deleteAllBlobsInContainer((string)$commodityID);

    }

    if (!$blobStorage->isContainer((string) $commodityID)) {
        $blobStorage->createContainer((string) $commodityID);
    }

    if ($srcProdArray['mainSrcImg'] !== "" && isset($srcProdArray['mainSrcImg'])) {

        $path   = 'uploads/temp_image.jpg';
        $handle = file_get_contents($srcProdArray['mainSrcImg']);
        if ($handle !== FALSE) {
            file_put_contents($path, $handle);
        }

        //обрезка белих полей
        if ($idBrand == 19) {
            cropInboxImg($path, 140, 10);
        } else if ($idBrand == 40) {
            cropInboxImg($path, 335, 0);
        }

        setMirrorImage($path);
        if ($handle !== FALSE) {
            $nameImg  = $nameImArray[0];
            $sNameImg = $nameImArray[1];
            cropAndWriteImage($path, $commodityID, $nameImg, $sNameImg,
                $brendName, $idBrand);
        }
    }

    if (!empty($srcProdArray['dopSrcImg'])) {
        $i = 0;
        foreach ($srcProdArray['dopSrcImg'] as $srcIm) {
            if ($srcIm !== "" && isset($srcIm)) {
                $path   = 'uploads/temp_image.jpg';
                $handle = file_get_contents($srcIm);
                if ($handle === FALSE) {
                    continue;
                } else {
                    file_put_contents($path, $handle);
                }

                if ($idBrand == 19) {
                    cropInboxImg($path, 140, 10);
                } else if ($idBrand == 40) {
                    cropInboxImg($path, 335, 0);
                }
                setMirrorImage($path);

                $nameImg  = $nameImArray[2][$i];
                $sNameImg = "s_".$nameImg;
                $i++;
                cropAndWriteImage($path, $commodityID, $nameImg, $sNameImg,
                    $brendName, $idBrand);
            }
        }
    }

    if (is_dir('../images/commodities/'.$commodityID.'/') == TRUE) {
        removeDirectory('../images/commodities/'.$commodityID);
    }
}

//------------------------------------------------------------------------------
//                  Обработка и запись картинок                             2
//------------------------------------------------------------------------------
function cropAndWriteImage($path, $commodityID, $nameImg, $sNameImg, $brendName,
                           $idBrand)
{
    $blobStorage = new BlobStorage();

    $image = new Imagick();
    $image->readImage($path);
    $image->setImageCompression(Imagick::COMPRESSION_JPEG);
    $image->setImageCompressionQuality(75);
    $image->stripImage();
    $image->setImageFormat('jpg');
    $image->writeImage('../images/commodities/'.$commodityID.'/'.$nameImg.'.jpg');

    //upload image to blob storage
    $blobStorage->uploadBlob(
        '../images/commodities/'.$commodityID.'/'.$nameImg.'.jpg',
        $nameImg.'.jpg', (string) $commodityID
    );

    list($width, $height) = getimagesize($path);
    $sImage   = $image;
    $imWidth  = 200;
    $imHeight = 300;
    $cofMin   = 1.4;
    $cofMax   = 1.6;
    $cofImage = $height / $width;
    //var_dump($cofImage);
    if ($width >= 150) {
        if ($idBrand == 36) {
            $imHeight = 0;
            //echo "коф: < 1  Широкое фото ,ширана 200 высота пропорционально!\n";
        } else if ($cofImage < 1) {
            $imHeight = 0;
            //echo "коф: < 1  Широкое фото ,ширана 200 высота пропорционально!\n";
        } else if ($cofImage > $cofMax) {
            $crop       = $height;
            $cropHeight = $width * 1.5;
            $yCrop      = 0;
            $xCrop      = 0;
            $sImage->cropImage($crop, $cropHeight, $xCrop, $yCrop);
            //echo "коф: > 1.6  Высокое фото , обрезаеться снизу!\n";
        } else if ($cofImage < $cofMin && $cofImage >= 1) {
            $crop      = $height;
            $cropWidth = $height / 1.5;
            $yCrop     = 0;
            $xCrop     = ($width - $cropWidth) / 2;
            $sImage->cropImage($cropWidth, $crop, $xCrop, $yCrop);
            //echo "коф: < 1.4 >= 1  Фото квадрат или высота не на много длиннее, обрезаеться по бокам\n";
        }

        $sImage->setImageCompression(Imagick::COMPRESSION_JPEG);
        $sImage->setImageCompressionQuality(90);
        $sImage->stripImage();
        $sImage->thumbnailImage($imWidth, $imHeight);
        $sImage->writeImage('../images/commodities/'.$commodityID.'/'.$sNameImg.'.jpg');

        //upload image to blob storage
        $blobStorage->uploadBlob(
            '../images/commodities/'.$commodityID.'/'.$sNameImg.'.jpg',
            $sNameImg.'.jpg', (string) $commodityID
        );
    }
}

//------------------------------------------------------------------------------
//                запись в базу данных если есть доп картинки              3
//------------------------------------------------------------------------------
function insertInShopImBd($commodityID, $mysqli)
{
    $mysqli->query("INSERT INTO shop_images SET com_id='{$commodityID}'");
    if ($mysqli->errno) {
        die('Select Error ('.$mysqli->errno.') '.$mysqli->error);
    }
    $photoId = $mysqli->insert_id;
    return $photoId;
}

//------------------------------------------------------------------------------
//              выбираем id доп фото для перезаливки              4
//------------------------------------------------------------------------------
function deleteDopImgFromDB($commodityID, $mysqli)
{
    $mysqli->query("DELETE FROM `shop_images` WHERE `com_id`='{$commodityID}'");
    if ($mysqli->errno) {
        die('DELETE shop_images Error ('.$mysqli->errno.') '.$mysqli->error);
    }
}

//------------------------------------------------------------------------------
//                         режим входящие картинки                       5
//------------------------------------------------------------------------------

function cropInboxImg($path, $widthMinus, $heightMinus)
{
    $image      = new Imagick();
    $image->readImage($path);
    $image->setImageFormat('jpg');
    list($width, $height) = getimagesize($path);
    $cropWidth  = $width - $widthMinus;
    $cropHeight = $height - $heightMinus;
    $xCrop      = $widthMinus / 2;
    $yCrop      = $heightMinus / 2;
    $image->cropImage($cropWidth, $cropHeight, $xCrop, $yCrop);
    $image->writeImage($path);
}

//------------------------------------------------------------------------------
//создаем зеркальное изображение для квадратных и широких фото            6
//------------------------------------------------------------------------------
function setMirrorImage($path)
{
    /* Чтение изображения */
    $im = new Imagick();
    $im->readImage($path);

    $width    = $im->getImageWidth();
    $height   = $im->getImageHeight();
    $cofMin   = 1.4;
    $cofImage = $height / $width;
    if ($cofImage < 1 || ($cofImage < $cofMin && $cofImage >= 1)) {
        /* Клонируем изображение и зеркально поворачиваем его */
        $reflection = clone $im;
        $reflection->flipImage();

        /* Создаём градиент. Это будет наложением для отражения */
        $gradient = new Imagick();

        /* Градиент должен быть достаточно большой для изображения и его рамки */
        $gradient->newPseudoImage($reflection->getImageWidth() + 10,
            $reflection->getImageHeight() + 10, "gradient:transparent-white");

        /* Наложение градиента на отражение */
        $reflection->compositeImage($gradient, imagick::COMPOSITE_OVER, 0, 0);

        /* Добавляем прозрачность. Требуется ImageMagick 6.2.9 или выше */
        $reflection->setImageOpacity(0.9);

        /* Создаём пустой холст */
        $canvas = new Imagick();

        /* Холст должен быть достаточно большой, чтобы вместить оба изображения */
        $widthNew  = $im->getImageWidth() + 40;
        $heightNew = ($im->getImageHeight() * 2) + 30;
        if ($heightNew / $widthNew > 1.7) {
            $heightNew = $heightNew - ($heightNew - ($width * 1.7 / 1));
        }
        $canvas->newImage($widthNew, $heightNew, new ImagickPixel("white"));
        $canvas->setImageFormat("jpg");

        /* Наложение оригинального изображения и отражения на холст */
        $canvas->compositeImage($im, imagick::COMPOSITE_OVER, 20, 10);
        $canvas->compositeImage($reflection, imagick::COMPOSITE_OVER, 20,
            $im->getImageHeight() + 10);
        $canvas->writeImage($path);
    }
}

//==============================================================================
//                   Функции обработки описания товара
//==============================================================================
//------------------------------------------------------------------------------
//                   Находим ключевые слова в описании (desc)               1
//------------------------------------------------------------------------------
function findStringDesc($str, $searchArray, $descProd)
{
    $str = mb_strtolower(trim($str), 'utf-8');

    foreach ($searchArray as $searchString) {
        $pos      = mb_strpos($str, $searchString);
        $findWord = mb_strstr($str, $searchString);
        //var_dump($searchString);
        //var_dump($findWord);
        //var_dump($str);
        if ($findWord !== FALSE && $pos == 0) {
            $lastWord = mb_substr($searchString, -1);
            $string   = mb_strstr($str, $lastWord, true);
            $strSpan  = mb_strtoupper(mb_substr($string, 0, 1, 'utf-8'), 'utf-8')
                .mb_substr($string, 1, null, 'utf-8').$lastWord;
            $strP     = strstr_after($str, $searchString);
            $descProd .= '<p><span>'.$strSpan.'</span>'.$strP.'</p>';
        } elseif ($findWord !== FALSE) {
            //$descProd .= $beginSelectorP . $str . $endSelectorP;
        }
    }
//----------------Проверяем нет ли повторяющегося описания----------------------
    if (!empty($descProd)) {
        $descProdArray = array_unique(explode('<p>', $descProd));
        $descProd      = implode('<p>', $descProdArray);
    }
    return $descProd;
}

//------------------------------------------------------------------------------
//             Вытаскиваем описание из строки по ключевым словам            2
//------------------------------------------------------------------------------
function getDesc($strDesc, $descProd, $wovels, $searchArray)
{
    $strDesc = mb_strtolower($strDesc, 'utf-8');
    //var_dump($strDesc);
    foreach ($wovels as $value) {
        $pos    = strpos($strDesc, $value);
        $string = trim(strstr($strDesc, $value));
        if ($string) {
            $arrayDesc2[$pos] = $string;
        }
    }
    if (isset($arrayDesc2)) {
        ksort($arrayDesc2);
        $arrayDesc2 = array_values($arrayDesc2);
        //var_dump($arrayDesc2);
        $arrayDesc2 = array_reverse($arrayDesc2);
        $countDesc2 = count($arrayDesc2);
        foreach ($arrayDesc2 as $key => $value) {
            if ($key == 0) {
                $arrayDesc3[] = $arrayDesc2[$key];
            }
            if ($key !== $countDesc2 - 1) {
                $arrayDesc3[] = str_replace($arrayDesc2[$key], "",
                    $arrayDesc2[$key + 1]);
            }
        }
    }
    if (isset($arrayDesc3)) {
        $arrayDesc3 = array_reverse($arrayDesc3);
        //var_dump($arrayDesc3);
        foreach ($arrayDesc3 as $key => $value) {
            if (!substr_count($value, ":")) {
                $value = str_replace($wovels, $searchArray, $value);
            }
            $descProd = findStringDesc($value, $searchArray, $descProd);
        }
    }
    return $descProd;
}

//------------------------------------------------------------------------------
//           удаляем пустые строки с массива описания                   3
//------------------------------------------------------------------------------
function deleteEmptyArrDescValues($arrayDesc)
{
    foreach ($arrayDesc as $value) {
        if (strlen(trim($value)) !== 0) {
            $arrayDescNew[] = trim($value);
        }
    }
    if (!isset($arrayDescNew)) {
        return;
    }
    return $arrayDescNew;
}

//------------------------------------------------------------------------------
//   улчшенная функция strstr(обрезает только после указанного символа)      4
//------------------------------------------------------------------------------
function strstr_after($haystack, $needle, $case_insensitive = false)
{
    $strpos = ($case_insensitive) ? 'stripos' : 'strpos';
    $pos    = $strpos($haystack, $needle);
    if (is_int($pos)) {
        return mb_substr($haystack, $pos + mb_strlen($needle));
    }
    return $pos;
}

//==============================================================================
//                      Функции фильтрации данных
//==============================================================================
//------------------------------------------------------------------------------
//                       фильтр цены товара                                 1
//------------------------------------------------------------------------------

function filterPrice($price, $regexp)
{
    $price = htmlentities($price, null, 'utf-8');
    $price = (int) ceil(preg_replace($regexp, "", $price));
    if ($price < 10) {
        $price = 0;
    }
    return $price;
}

//------------------------------------------------------------------------------
//                       фильтр размеров и цветов товара                     2
//------------------------------------------------------------------------------

function filterSizeColors($propertyProd)
{
    $vowelsSizeColor = array("--- Выберите --- ;", " ", "---Оберіть---;", "всеразмеры;",
        "Выбратьразмер;", "Всірозміри;", "---Выберите---;",
        "Безпояса;", "--Размер--;", "--Цвет--;", "--------------------;", "--------------------",
        "подзаказ;", "Неопределено;", "ростовка", 'Безброшки;', 'Сброшкой(+30грн.);',
        'Выбратьопцию;');
    $propertyProd    = str_replace($vowelsSizeColor, "", $propertyProd);
    $propertyProd    = substr($propertyProd, 0, strlen($propertyProd) - 1);
    $propertyProd    = (string) $propertyProd;

    return $propertyProd;
}

//------------------------------------------------------------------------------
//                       фильтр картинок товара                             3
//------------------------------------------------------------------------------
function filterUrlImage($srcImage, $curLink)
{
    $eff    = str_replace("http://", "", $curLink);
    $adasda = explode("/", $eff);

    $domain   = array_shift($adasda);
    $srcImage = str_replace("http://", "", $srcImage);
    $srcImage = str_replace($domain, "", $srcImage);

    $src = $domain."/".$srcImage;
    $src = str_replace("//", "/", $src);
    $src = "http://".$src;

    $src = str_replace("majaly.com.ua/", "", $src);
    $src = str_replace("nelli-co.com/", "", $src);
    if (strpos($src, "%") === false) {
        $src = rawurlencode($src);
    }

    $src = str_replace("%3A", ":", $src);
    $src = str_replace("%2F", "/", $src);

    return $src;
}

//------------------------------------------------------------------------------
//                       фильтр ссылки товара                             4
//------------------------------------------------------------------------------
function filterLink($curLink)
{
    $curLink = str_replace("amp;", "", $curLink);
    return $curLink;
}

/**
 * Transliterate alias string for shop_com table
 * @param string $input - input alias string
 * @return string - output alias string
 */
function transliterate($input)
{
    $chars = array(
        "А" => "a", "Б" => "b", "В" => "v", "Г" => "g", "Д" => "d",
        "Е" => "e", "Ё" => "yo", "Ж" => "j", "З" => "z", "И" => "i",
        "Й" => "y", "К" => "k", "Л" => "l", "М" => "m", "Н" => "n",
        "О" => "o", "П" => "p", "Р" => "r", "С" => "s", "Т" => "t",
        "У" => "u", "Ф" => "f", "Х" => "h", "Ц" => "c", "Ч" => "ch",
        "Ш" => "sh", "Щ" => "sch", "Ъ" => "", "Ы" => "yi", "Ь" => "",
        "Э" => "e", "Ю" => "yu", "Я" => "ya", "а" => "a", "б" => "b",
        "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ё" => "yo", "ж" => "j",
        "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
        "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
        "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
        "ц" => "c", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
        "ы" => "y", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya",
        " " => "_", "." => "", "/" => "_", "#" => "", "$" => "",
        "%" => "", "^" => "", "&" => "", "*" => "", "(" => "", ")" => "",
        "+" => "", "=" => "", ";" => "", ":" => "", "'" => "", '"' => '_'
    );

    $replacement = array(
        '____',
        '___',
        '__',
        "\r\n",
        ' '
    );

    $string = strtolower(str_replace($replacement, '_', strtr($input, $chars)));
    $string = '_' === substr($string, -1) ? substr($string, 0, -1) : $string;

    return $string;
}

//==============================================================================
// Проверяем есть ли селектор в таблице parser если есть то
//  есть ли селектор на страничке товара который парсится
//==============================================================================

function checkEmptyOrChangeSelector($selector, $saw, $columnNameSelector)
{
    if ($selector == "") {
        echo "<span style='color:green'>Сторока с селектором колонки {$columnNameSelector}, пуста!</span>\n";
        return;
    }
    $findme = "img";
    $pos    = mb_strpos($columnNameSelector, $findme);
    $pos1   = mb_strpos($columnNameSelector, "a_href");
    if ($pos === FALSE && $pos1 === FALSE) {
        $arrayProperty = $saw ? $saw->get($selector)->toTextArray() : null;
    } else {
        $arrayProperty = $saw ? $saw->get($selector)->toArray() : null;
    }

    if (empty($arrayProperty)) {
        echo "<span style='color:blue'>Массив пуст, на странице товара нет {$selector} - селектора, {$columnNameSelector}!</span>\n";
        return;
    }
    return $arrayProperty;
}

//==============================================================================
//                         Функции удаления товара
//==============================================================================
//------------------------------------------------------------------------------
//                       Полностью удаляем товар                            1
//------------------------------------------------------------------------------
function deleteCommodityAll($idBrand, $comId, $delUrl, $mysqli, $visible)
{
    if ($visible == 1) {
        return $delete = FALSE;
    }
    if (!($stmt = $mysqli->prepare("SELECT id FROM shop_orders_coms WHERE com_id=?"))) {
        die('Select shop_commodity Error ('.$mysqli->errno.') '.$mysqli->error);
    } else {
        $stmt->bind_param("i", $comId);
        $stmt->execute();
        $stmt->bind_result($comOrdersId);
        $stmt->fetch();
        $stmt->close();
    }
    //var_dump($comOrdersId);
    if ($comOrdersId == 0) {
//------------------------------------------------------------------------------
        //delete container from blob storage
        $blobStorage = new BlobStorage();
        $blobStorage->deleteContainer((string) $comId);
//------------------------------------------------------------------------------
        $mysqli->query("DELETE FROM `shop_commodities-categories` WHERE `commodityID`='{$comId}'");
        if ($mysqli->errno) {
            die('DELETE shop_commodities-categories Error ('.$mysqli->errno.') '.$mysqli->error);
        }
//------------------------------------------------------------------------------
        $mysqli->query("DELETE FROM `shop_filters-values` WHERE `ticket_id`='{$comId}'");
        if ($mysqli->errno) {
            die('DELETE shop_filters-values Error ('.$mysqli->errno.') '.$mysqli->error);
        }
//------------------------------------------------------------------------------
        $mysqli->query("DELETE FROM `shop_images` WHERE `com_id`='{$comId}'");
        if ($mysqli->errno) {
            die('DELETE shop_images Error ('.$mysqli->errno.') '.$mysqli->error);
        }
//------------------------------------------------------------------------------
        $mysqli->query("DELETE FROM `shop_commodity` WHERE `commodity_ID`='{$comId}'");
        if ($mysqli->errno) {
            die('DELETE shop_commodity Error ('.$mysqli->errno.') '.$mysqli->error);
        }
        $delete = TRUE;
    } else {
        $delete = FALSE;
    }
    return $delete;
}

//------------------------------------------------------------------------------
//                рекурсивное удаление папок с файлами                      2
//------------------------------------------------------------------------------
function removeDirectory($dir)
{
    if ($objs = glob($dir."/*")) {
        foreach ($objs as $obj) {
            is_dir($obj) ? removeDirectory($obj) : unlink($obj);
        }
    }
    rmdir($dir);
}

//------------------------------------------------------------------------------
//                      просто скрываем товар                                3
//------------------------------------------------------------------------------

function updateVisiblOnly($comVisibl, $commodityID, $mysqli)
{
    if (!($stmt = $mysqli->prepare("UPDATE shop_commodity SET  commodity_visible=?
                                                            WHERE commodity_ID=?"))) {
        die('Update shop_commodity visible Error ('.$mysqli->errno.') '.$mysqli->error);
    }
    $stmt->bind_param("ii", $comVisibl, $commodityID);
    $stmt->execute();
    $stmt->close();
}

//------------------------------------------------------------------------------
//                          проверяем если у товра теги                      4
//------------------------------------------------------------------------------
function checkTags($id, $mysqli)
{
    if (!($stmt = $mysqli->prepare("SELECT ticket_filterid FROM `shop_filters-values` WHERE `ticket_id`=?"))) {
        die('Select shop_commodity Error ('.$mysqli->errno.') '.$mysqli->error);
    } else {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();
        $stmt->close();
    }
    return $result;
}

//==============================================================================
//                  Запускаем обрезку и запись картинки
//==============================================================================
function writeImage($idBrand, $curLink, $saw, $commodityID, $mysqli, $verify)
{
    if ($idBrand == 36) {
        require 'brands_parsers/Zdes/zdes_image.php';
    } else if ($idBrand == 35) {
        require 'brands_parsers/Jhiva/jhiva_image.php';
    } else if ($idBrand == 34) {
        require 'brands_parsers/DemboHouse/dembo_house_image.php';
    } else if ($idBrand == 33) {
        require 'brands_parsers/Dajs/dajs_image.php';
    } else if ($idBrand == 32) {
        require 'brands_parsers/HelenLaven/helen_laven_image.php';
    } else if ($idBrand == 37) {
        require 'brands_parsers/FrancoCasel/francocassel_image.php';
    } else if ($idBrand == 31 || $idBrand == 30) {
        require 'brands_parsers/VitalityAll/vitality_image.php';
    } else if ($idBrand == 29) {
        require 'brands_parsers/Crisma/crisma_image.php';
    } else if ($idBrand == 27) {
        require 'brands_parsers/Aliya/aliya_image.php';
    } else if ($idBrand == 26) {
        require 'brands_parsers/SergioTorri/sergio_torri_image.php';
    } else if ($idBrand == 25) {
        require 'brands_parsers/Majaly/majaly_image.php';
    } else if ($idBrand == 24 || $idBrand == 23) {
        require 'brands_parsers/B1/b1_image.php';
    } else if ($idBrand == 21) {
        require 'brands_parsers/FStyle/fstyle_image.php';
    } else if ($idBrand == 20) {
        require 'brands_parsers/Nelli-Co/nellico_image.php';
    } else if ($idBrand == 19) {
        require 'brands_parsers/OlisStyle/olisstyle_image.php';
    } else if ($idBrand == 17) {
        require 'brands_parsers/SandL/sandl_image.php';
    } else if ($idBrand == 16) {
        require 'brands_parsers/Seventeen/seventeen_image.php';
    } else if ($idBrand == 14) {
        require 'brands_parsers/SKHouse/skhouse_image.php';
    } else if ($idBrand == 13) {
        require 'brands_parsers/Flfashion/flfashion_image.php';
    } else if ($idBrand == 11) {
        require 'brands_parsers/Alva/alva_image.php';
    } else if ($idBrand == 10) {
        require 'brands_parsers/Meggi/meggi_image.php';
    } else if ($idBrand == 9) {
        require 'brands_parsers/Sellin/sellin_image.php';
    } else if ($idBrand == 7) {
        require 'brands_parsers/Lenida/lenida_image.php';
    } else if ($idBrand == 6) {
        require 'brands_parsers/Glem/glem_image.php';
    } else if ($idBrand == 5) {
        require 'brands_parsers/Cardo/cardo_image.php';
    } else if ($idBrand == 4) {
        require 'brands_parsers/SwirlBySwirl/swirlbyswirl_image.php';
    } else if ($idBrand == 1) {
        require 'brands_parsers/Fashionup/fashionup_image.php';
    } else if ($idBrand == 38) {
        require 'brands_parsers/Vidoli/vidoli_image.php';
    } else if ($idBrand == 39) {
        require 'brands_parsers/LavanaFashion/lavanaFashion_image.php';
    } else if ($idBrand == 40) {
        require 'brands_parsers/Reform/reform_image.php';
    } else if ($idBrand == 41) {
        require 'brands_parsers/TaliTtet/tali_ttet_image.php';
    } else if ($idBrand == 43) {
        require 'brands_parsers/Ghazel/ghazel_image.php';
    } else if ($idBrand == 44) {
        require 'fashion_look_image.php';
    }
    return $srcProdArray;
}
//==============================================================================
//                   Функции  записи кардо
//==============================================================================
//Cardo ONLY
/*function insertCardo($mysqli, $comCount, $commodityID)
{
    $comCountArr = explode(";", $comCount);
    array_pop($comCountArr);
    foreach ($comCountArr as $key => $value) {
        $size                  = strstr($value, "=", true);
        $quantity              = strstr_after($value, "=");
        $comCountArrNew[$size] = $quantity;
    }
    //var_dump($comCountArrNew);
    //var_dump($commodityID);
    foreach ($comCountArrNew as $key => $value) {
        $mysqli->query("INSERT INTO `shop_cardo_sizes` SET
                      commodity_id={$commodityID},
                      size='{$key}',
                      quantity='{$value}'");
        if ($mysqli->errno) {
            die('Insert shop_cardo_sizes Error ('.$mysqli->errno.') '.$mysqli->error);
        }
    }
}
//==============================================================================
//                  Функции для запуска парсинга
//==============================================================================

/**
 * получаем страницу по ссылке товара
 * @param type $url
 * @param type $getCookie
 * @return type
 */
/* function getWebPage($url, $getCookie)
  {
  $options = array(
  CURLOPT_URL => $url,
  CURLOPT_RETURNTRANSFER => true, // return web page
  CURLOPT_HEADER => false, // don't return headers
  CURLOPT_FOLLOWLOCATION => true, // follow redirects
  CURLOPT_ENCODING => "", // handle all encodings
  CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17", // who am i
  CURLOPT_AUTOREFERER => true, // set referer on redirect
  CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
  CURLOPT_TIMEOUT => 120, // timeout on response
  CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
  );
  if ($getCookie == "/brands_parsers/SKHouse/cookie.txt") {
  $options[CURLOPT_URL]            = "http://sk-house.ua/Products/SetCurrency?cur=%D0%93%D0%A0%D0%9D";
  $options[CURLOPT_REFERER]        = $url;
  $options[CURLOPT_SSL_VERIFYPEER] = false;
  } else if ($getCookie == "/brands_parsers/Cardo/cookie_cur.txt") {
  getCurrensyCardo("http://cardo.com.ua/changecurrency.php?rand=1457527032864");
  }
  if ($getCookie !== "") {
  $options[CURLOPT_COOKIEFILE] = (__DIR__).$getCookie;
  }
  $ch                = curl_init();
  curl_setopt_array($ch, $options);
  $content           = curl_exec($ch);
  $err               = curl_errno($ch);
  $errmsg            = curl_error($ch);
  $header            = curl_getinfo($ch);
  curl_close($ch);
  $header['errno']   = $err;
  $header['errmsg']  = $errmsg;
  $header['content'] = $content;
  return $header;
  }
  /**
 * регестрируемся Cardo
 * @param type $url
 */
/* function loginCardo($url)
  {
  getCurrensyCardo("http://cardo-ua.com/changecurrency.php?rand=1457530350103");
  $ch = curl_init();
  if (strtolower((substr($url, 0, 5)) == 'https')) { // если соединяемся с https
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  }
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_REFERER, $url);     // откуда пришли на эту страницу
  curl_setopt($ch, CURLOPT_VERBOSE, 1);     // cURL будет выводить подробные сообщения о всех производимых действиях
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS,
  array(
  'email' => 'v.chupovsky@makewear.com.ua', //'email' => 'a.homenko@makewear.com.ua',
  'passwd' => 'qqqqqqqqq', //'passwd' => '80983522900a',
  'back' => 'my-account.php',
  'SubmitLogin' => 'Вход'
  ));
  curl_setopt($ch, CURLOPT_USERAGENT,
  "Mozilla/4.0 (Windows; U; Windows NT 5.0; En; rv:1.8.0.2) Gecko/20070306 Firefox/1.0.0.4");
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_COOKIEFILE,
  (__DIR__).'/brands_parsers/Cardo/cookie_cur.txt');
  //сохранять полученные COOKIE в файл
  curl_setopt($ch, CURLOPT_COOKIEJAR,
  (__DIR__).'/brands_parsers/Cardo/cookie.txt');
  $result = curl_exec($ch);
  curl_close($ch);
  } */

/**
 * Выбираем курс грн для кардо
 * @param type $url
 */
/* function getCurrensyCardo($url)
  {
  $ch = curl_init();
  if (strtolower((substr($url, 0, 5)) == 'https')) { // если соединяемся с https
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  }
  curl_setopt($ch, CURLOPT_URL, $url);
  // откуда пришли на эту страницу
  curl_setopt($ch, CURLOPT_REFERER, $url);
  // cURL будет выводить подробные сообщения о всех производимых действиях
  curl_setopt($ch, CURLOPT_VERBOSE, 1);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS,
  array(
  'id_currency' => "1"
  ));
  curl_setopt($ch, CURLOPT_USERAGENT,
  "Mozilla/4.0 (Windows; U; Windows NT 5.0; En; rv:1.8.0.2) Gecko/20070306 Firefox/1.0.0.4");
  curl_setopt($ch, CURLOPT_HEADER, 1);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  //сохранять полученные COOKIE в файл
  curl_setopt($ch, CURLOPT_COOKIEJAR,
  (__DIR__).'/brands_parsers/Cardo/cookie_cur.txt');
  $result = curl_exec($ch);
  curl_close($ch);
  } */

//------------------------------------------------------------------------------
// Проверяем читаеться ли ссылка если да создаем обьект нокогири            3
//------------------------------------------------------------------------------
/* function createNokogiriObject($curLink, $getCookie)
  {
  $resultArray = getWebPage($curLink, $getCookie);
  if (($resultArray['errno'] != 0 ) || ($resultArray['http_code'] != 200)) {
  echo "<h4 style='color:red'>{$resultArray['errmsg']}</span>\n";
  echo "<span style='color:red'>{$resultArray['http_code']}</span> - код ошибки!!!\n";
  echo "\n<h3 style='color:red'>getWebPage не смог прочитать страницу товара!!!</h3>\n";
  echo "URL: <a href={$curLink} target='_blank' >{$curLink}</a>\n";
  echo "-------------------------------------------------------------\n\n\n";
  $saw    = FALSE;
  $result = array($saw, $resultArray);
  } else {
  $html   = str_replace("&nbsp;", "", $resultArray['content']);
  $saw    = (new nokogiri())->fromHtmlNoCharset($html);
  $result = array($saw, $resultArray);
  }
  return $result;
  } */

//==============================================================================
//                   Функции для отчета и интерфейса
//==============================================================================
//------------------------------------------------------------------------------
//                          создаем файл отчета                          1
//------------------------------------------------------------------------------
/*function createFileReport($nameReport)
{
    $date        = new \DateTime('now');
    $day         = $date->format('d_M');
    $dir_to_save = 'reporsts/'.$day;
    if (!is_dir($dir_to_save)) {
        mkdir($dir_to_save);
    }
    $_SESSION['filename'] = $dir_to_save.DIRECTORY_SEPARATOR.'catid_'.$_SESSION['cat_id'].$nameReport;
    $fp                   = fopen($_SESSION['filename'], "w");
    fwrite($fp,
        "<meta charset='utf-8'><pre><?php<h4 style='color:green'>Файл создан</h4>");
    fclose($fp);
}*/

//------------------------------------------------------------------------------
//                      Записать в файл отчета                          2
//------------------------------------------------------------------------------
/*function writeContentInReport()
{
    $content = ob_get_contents();
    file_put_contents($_SESSION['filename'], $content, FILE_APPEND);
    ob_flush();
    ob_end_clean();
    return $content;
}*/
//==============================================================================
//                   Функции для обработки XML
//==============================================================================
//------------------------------------------------------------------------------
//                      Создаем файл XML                          1
//------------------------------------------------------------------------------
/*function createXmlFileAll($sawXml, $xmlPathAll)
{
    $xmlstr = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<contentMain>
    <title>Обьединение xml</title>
XML;
    foreach ($sawXml->link as $value) {
        //var_dump((string) $value);
        $area = xmlnewObjload((string) $value);
        $xmlstr .= xmlpattern($area->asXML());
    }
    $xmlstr .= <<<XML
</contentMain>
XML;
    $wovels = array("<data>", "</data>");
    $xmlstr = str_replace($wovels, "", $xmlstr);

    if (file_exists($xmlPathAll)) {
        file_put_contents($xmlPathAll, $xmlstr);
    } else {
        $fp = fopen($xmlPathAll, "w");
        fwrite($fp, $xmlstr);
        fclose($fp);
    }
    $saw = simplexml_load_file($xmlPathAll);
    return $saw;
}

//------------------------------------------------------------------------------
//                      Создаем обькт XML                               2
//------------------------------------------------------------------------------
function xmlnewObjload($xml)
{
    $xml = simplexml_load_file($xml);
    return $xml;
}

//------------------------------------------------------------------------------
//                       Паттерн XML                                  3
//------------------------------------------------------------------------------
function xmlpattern($xml)
{
    $pattern = "'<\?xml.*?>'si";
    $content = preg_replace($pattern, '', $xml);
    return $content;
}*/
