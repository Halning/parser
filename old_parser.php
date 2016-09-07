<?php

session_start();
header('Content-Type: text/html; charset=utf-8');

if (isset($_GET["step"])) {
    $request_url = $_SERVER['REQUEST_URI'];
}

ini_set("max_execution_time", "99999");
set_time_limit(99998);
error_reporting(E_ALL ^ E_NOTICE);
require_once("settings/conf.php");
require_once("settings/connect.php");
require_once ('includes/simplehtmldom/simple_html_dom.php');
require_once ("includes/phpexcel/Classes/PHPExcel.php");

if (isset($_POST["cat_id"]) && isset($_POST["cat2_id"])) {
    $_SESSION["cat_id"] = $_POST["cat_id"];
    $_SESSION["cat2_id"] = $_POST["cat2_id"];
}

//==============================================================================
//                      Вспомогательные функции                         1
//==============================================================================
//обрабатывает принимаеммое методом POST исображение и редактирует его-------1--

function getnewpngimg($type = 1, $x, $y, $prefix, $item_id, $new_file_name, $myfile, $wm = 0) {
    /**
     * getnewimg
     * обрабатывает принимаеммое методом POST исображение и редактирует его
     * @version 3.6.6
     * @param integer $type - тип обработки, 1 - вписывает в область, 2 - обрезает к области, 3 - фиксированный размер
     * @return boolean
     */
    global $parrent_dir, $gallery_domen, $glb;
    $dest = "uploads/temp_image.jpg";
    $src = "images/{$prefix}/{$item_id}/" . $new_file_name;
    $path = "/" . $src;
    sleep(2);
    if (isset($myfile)) {
        if ($glb["use_ftp"]) {
            $ftp_conn = ftp_connect($gallery_domen);
            $ftp_log = ftplogin($ftp_conn);
        }
        if (is_dir("images/{$prefix}/{$item_id}") == false) {
            if ($glb["use_ftp"]) {
                ftp_mkdir($ftp_conn, $parrent_dir . "/images/{$prefix}/{$item_id}");
            } else {
                mkdir("images/{$prefix}/{$item_id}");
            }
        }

        ini_set('display_errors', 1);
        error_reporting(E_ALL ^ E_NOTICE);

        if ($error_flag == 0) {
            if ($glb["use_ftp"]) {
                ftp_put($ftp_conn, $parrent_dir . "/" . $dest, $myfile, FTP_BINARY);
            } else {

                @copy($myfile, $dest);
                //move_uploaded_file($myfile,$dest);
            }
            $an_sp = $path;
            $size = getimagesize($dest);
            if ($size === false) {
                $ret = false;
            } else {
                $format = strtolower(substr($size['mime'], strpos($size['mime'], '/') + 1));
                $icfunc = "imagecreatefrom{$format}";
                if (function_exists($icfunc)) {
                    if ($type == 1) {
                        $new_img_x = min($x, $size[0]);
                        $new_img_y = min($y, $size[1]);
                        $x_ratio = $new_img_x / $size[0];
                        $y_ratio = $new_img_y / $size[1];
                        if ($y_ratio < $x_ratio) {
                            $x_ratio = $y_ratio;
                            $new_img_x = $size[0] * $x_ratio;
                        } else {
                            $y_ratio = $x_ratio;
                            $new_img_y = $size[1] * $y_ratio;
                        }
                        $ratio = min($x_ratio, $y_ratio);
                        echo "<br>" . $new_file_name;
                        echo $new_img_y / $new_img_x;
                        $s_title = ($new_img_y / $new_img_x > 1.55 && $new_file_name == "s_title.jpg") ? true : false;
                        if ($s_title) {
                            $new_img_y = 1.5 * $new_img_x;
                        }
                        var_dump($s_title);
                    } elseif ($type == 2) {
                        $new_img_x = min($x, $size[0]);
                        $new_img_y = min($y, $size[1]);
                        $x_ratio = $new_img_x / $size[0];
                        $y_ratio = $new_img_y / $size[1];
                        $ratio = max($x_ratio, $y_ratio);
                    } elseif ($type == 3) {
                        $new_img_x = $x;
                        $new_img_y = $y;
                        $x_ratio = $new_img_x / $size[0];
                        $y_ratio = $new_img_y / $size[1];
                        $ratio = min($x_ratio, $y_ratio);
                    }
                    $use_x_ratio = ($x_ratio == $ratio);
                    $new_width = $use_x_ratio ? $new_img_x : floor($size[0] * $ratio);
                    $new_height = !$use_x_ratio ? $new_img_y : floor($size[1] * $ratio);
                    $new_left = $use_x_ratio ? 0 : floor(($new_img_x - $new_width) / 2);
                    $new_top = !$use_x_ratio || $s_title ? 0 : floor(($new_img_y - $new_height) / 2);
                    $isrc = $icfunc($dest);
                    $idest = imagecreatetruecolor($new_img_x, $new_img_y);
                    if ($format == "png" && false) {
                        imageAlphaBlending($idest, false);
                        imageSaveAlpha($idest, true);
                    }
                    $rgb = imagecolorallocate($idest, 255, 255, 255);
                    imagefill($idest, 0, 0, $rgb);
                    imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0, $new_width, $new_height, $size[0], $size[1]);
                    $funcimg = "imagejpeg";
                    $wdwd = $format == "png" ? 0 : 90;
                    $funcimg($idest, $dest, $wdwd);
                    if ($glb["use_ftp"]) {
                        ftp_put($ftp_conn, $parrent_dir . "/" . $src, $dest, FTP_BINARY);
                    } else {
                        copy($dest, $src);
                    }
                    imagedestroy($isrc);
                    imagedestroy($idest);
                    if ($wm == 1 && false) {//$wm==1&&$glb["use_ftp"]
                        $watermark = new watermark3();

                        $img = imagecreatefrompng($src); //imagecreatefrompng($src);
                        $width = imagesx($img);
                        $width = $width >= 1200 ? 1200 : $width;
                        $wm_width = $width * 0.58;
                        /* if($width<600 and $width>350)
                          $watermark_name="watermark2.png";
                          elseif($width<=350)
                          $watermark_name="watermark1.png";
                          else
                          $watermark_name="watermark.png"; */
                        $watermark_name = "watermark.png";
                        $water_origin = imagecreatefrompng('images/' . $watermark_name);
                        $wm_width_origin = imagesx($water_origin);
                        $wm_height_origin = imagesy($water_origin);
                        $wm_ratio = $wm_width_origin / $wm_width;
                        $wm_height = $wm_height_origin / $wm_ratio;
                        //var_dump($wm_height,$wm_width_origin,$wm_width,$wm_ratio);
                        $water = imagecreatetruecolor($wm_width, $wm_height);
                        imagesavealpha($water, true);
                        $transPng = imagecolorallocatealpha($water, 0, 0, 0, 127);
                        imagefill($water, 0, 0, $transPng);
                        imagecopyresampled($water, $water_origin, 0, 0, 0, 0, $wm_width, $wm_height, $wm_width_origin, $wm_height_origin);
                        $im = $watermark->create_watermark($img, $water, 100);
                        imagejpeg($im, $dest, 100);
                        //ftp_put($ftp_conn,$parrent_dir."/".$src, $dest, FTP_BINARY);

                        if ($glb["use_ftp"]) {
                            ftp_put($ftp_conn, $parrent_dir . "/" . $src, $dest, FTP_BINARY);
                        } else {
                            copy($dest, $src);
                        }
                    }

                    $ret = true;
                } else {
                    $ret = false;
                }
            }
        } else {
            $ret = false;
        }
    } else {
        $ret = false;
    }
    return $ret;
}

//----------------------добовляет фильтры в базу данных----------------------2--
function addfiltr($com_id, $name, $text) {
    $sql = "SELECT * FROM `shop_filters-descriptions` 
	WHERE `filtr_name`='{$name}'";
    $row = mysql_fetch_assoc(mysql_query($sql));
    if ($row) {
        $id = $row["filtr_id"];
    } else {
        $sql = "
		INSERT INTO `shop_categories-filters`
		SET `fitr_catid`='90', `filtr_typeid`='4'
		";
        mysql_query($sql);
        $id = mysql_insert_id();
        $sql = "
		INSERT INTO `shop_filters-descriptions`
		SET `filtr_name`='{$name}', `filtr_id`='{$id}', lng_id='1'
		";
        mysql_query($sql);
    }
    $text = str_replace("/images/", "http://vents.ua/images/", $text);
    $sql = "
		INSERT INTO `shop_filters-values`
		SET `ticket_id`='{$com_id}', `ticket_filterid`='{$id}', ticket_value='{$text}', `visible`='1'
		";
    mysql_query($sql);
}

//------------------преаброзует символы в пустые строки----------------------3--
function translit($st) {
    $st = strtr($st, array(
        ":" => "",
        "«" => "",
        "»" => "",
        "\"" => "",
        "'" => "",
        "&" => "",
        "?" => "",
        " " => "-")
    );
    return strtolower($st);
}

//------------------?????????????????????????????----------------------------3--
function ad_new_img($url, $commodityID, $text) {

    $sql = "INSERT INTO `shop_images` SET `com_id`='{$commodityID}', `img_name`='{$text}' ;";
    mysql_query($sql) or die(mysql_error());
    $photo_id = mysql_insert_id();
    getnewpngimg(1, 1024, 786, "commodities", $commodityID, $photo_id . '.jpg', $url, 1);
    getnewpngimg(3, 200, 200, "commodities", $commodityID, "s_" . $photo_id . '.jpg', "http://makewear.com.ua/images/commodities/{$commodityID}/" . $photo_id . '.jpg');
}

function read_excel($filepath) {
    $ar = array();
    $inputFileType = PHPExcel_IOFactory::identify($filepath);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($filepath);
    $ar = $objPHPExcel->getActiveSheet()->toArray();

    return $ar;
}

//------------------------Convert Image Type--------------------------------4---
function convert_image_type($intype, $outimage, $filepath, $filename) {
    $txtimg = 'Convert ';

    if ($intype == 'png') {
        $srcImg = imagecreatefrompng($filepath);
        $txtimg.='png';
    }
    if ($intype == 'jpg') {
        $srcImg = imagecreatefromjpeg($filepath);
        $txtimg.='jpg';
    }
    if ($intype == 'jpeg') {
        $srcImg = imagecreatefromjpeg($filepath);
        $txtimg.='jpeg';
    }
    if ($intype == 'gif') {
        $srcImg = imagecreatefromgif($filepath);
        $txtimg.='png';
    }

    //--------------------------
    if ($outimage == 'jpg') {
        imagejpeg($srcImg, 'images/png_to_jpg/' . $filename . '.jpg');
        $getPath = 'images/png_to_jpg/' . $filename . '.jpg';
        $txtimg.=' to jpg';
    }
    if ($outimage == 'jpeg') {
        imagejpeg($srcImg, 'images/png_to_jpg/' . $filename . '.jpeg');
        $getPath = 'images/png_to_jpg/' . $filename . '.jpeg';
        $txtimg.=' to jpeg';
    }
    if ($outimage == 'png') {
        imagepng($srcImg, 'images/png_to_jpg/' . $filename . '.jpeg');
        $getPath = 'images/png_to_jpg/' . $filename . '.jpeg';
        $txtimg.=' to png';
    }
    if ($outimage == 'gif') {
        imagegif($srcImg, 'images/png_to_jpg/' . $filename . '.jpeg');
        $getPath = 'images/png_to_jpg/' . $filename . '.jpeg';
        $txtimg.=' to gif';
    }
    //echo $txtimg."<br>";
    return $getPath;
}

//------------------Возвращаем первую букву строки в верхнем регистре-------2---
function ucfirst_mb($str) {
    $u = 'utf-8';
    return mb_substr(mb_strtoupper($str, $u), 0, 1, $u) . mb_substr($str, 1, strlen($str), $u);
}

function get_http_error($theurl) {
    $head = get_headers($theurl);
    return substr($head[0], 9, 3);
}

function ggg($id) {
    $sql = "	
	SELECT * FROM `shop_filters-values`
	INNER JOIN `shop_filters-lists` ON `id`=`ticket_value`
	WHERE `ticket_id`='{$id}';";
    $res = mysql_query($sql);
    while ($row = mysql_fetch_assoc($res)) {
        $lines.="- " . $row["list_name"] . "<br>";
    }
    return $lines;
}

function gggColSize($id, $a) {
    $sql = "	
	SELECT * FROM `shop_filters-values`
	INNER JOIN `shop_filters-lists` ON `id`=`ticket_value`
	WHERE `ticket_id`='{$id}';";
    $res = mysql_query($sql);
    while ($row = mysql_fetch_assoc($res)) {
        if ($row["list_filterid"] == $a)
            $lines.=$row["list_name"] . "|";
    }
    return $lines;
}

//-----Baner url FlFashion--------------
function flfashion($urll) {
    $d = 0;
    $fff = 0;
    do {
        $a = get_http_error($urll);
        if ($a == "404") {
            $urll = str_replace("plate-letnee/pla-202/", "", $urll);
            $urll = str_replace("plate-letnee", "platya", $urll);
            if (isset($_GET["step"])) {
                echo "Baner url2";
            }
            $d++;
        }
        if ($a == "200") {
            $arru = explode("/", $urll);
            $sql = mysql_query("SELECT * FROM `shop_commodity`; ");
            while ($u = mysql_fetch_assoc($sql)) {
                $sql_pos = strpos($u['from_url'], $arru[count($arru) - 1]);
                if ($sql_pos !== false) {
                    mysql_query("UPDATE `shop_commodity` SET `from_url`={$urll} WHERE `categoryID`='{$u['categoryID']}'; ");
                    if (isset($_GET["step"])) {
                        echo " Update url";
                    }
                }
            }
            $fff = 1;
        }
        if ($d == 3) {
            if (isset($_GET["step"])) {
                echo "Not url!!!";
            }
            $fff = 1;
        }
    } while ($fff == 0);

    return $urll;
}

$order = 0;

function gettover($new_url) {
    //-----Glem with XML-------------------
    var_dump($new_url);
    if ($_SESSION["cat_id"] == 15) {
        $gxml = simplexml_load_file("http://www.glem.com.ua/eshop/ym4.php");
    }

    //$new_url="http://mygold.ge/index.php?lang=EN&ID=Pendant&item=f642&krazanaweb=1";
    if ($new_url == "")
        return "";
    $sql = "SELECT * FROM `shop_commodity` 
		WHERE `from_url`='{$new_url}';";
    $row = mysql_fetch_assoc(mysql_query($sql));
    if ($row) {
        //return "";
    }
//----Error HTTP-------------------------------------

    if ($_SESSION["cat_id"] == 46) {
        $new_url = flfashion($new_url);
    }

    $err = get_http_error($new_url);

    if ($err == "503" || $err == '303') {
        $e = get_headers($new_url);
        var_dump($e);
        if ($_SESSION["cat_id"] == 85) {
            if (isset($_GET["step"])) {
                echo $new_url;
            }
            $queryNo2 = "UPDATE `shop_commodity` SET `commodity_visible`='0' WHERE `from_url`='{$new_url}';";
            mysql_query($queryNo2) or die("error no_nal");
            if (isset($_GET["step"])) {
                echo "<p style='color:red'>Товар не найдено!</p>";
            }
        }
        return;
    }
    if ($err == "404") {
        $e = get_headers($new_url);
        //var_dump($e);
        if (isset($_GET["step"])) {
            echo "<br/><h2>Error 404</h2><br/><a href={$new_url} target='_blank' >{$new_url}</a>";
        }
        $updateNonal = "UPDATE `shop_commodity` SET `commodity_visible`='0' WHERE `from_url`='{$new_url}';";
        mysql_query($updateNonal);
        return;
    } else {
        if ($_SESSION["cat_id"] == 1) {
            //$url = 'http://sk-house.ua/Products/SetCurrency?cur=%D0%93%D0%A0%D0%9D';
            $url = 'http://cardo-ua.com/changecurrency.php?id_currency=1';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); // отправляем на
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:23.0) Gecko/20100101 Firefox/23.0");
            curl_setopt($ch, CURLOPT_HEADER, 0); // пустые заголовки
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // возвратить то что вернул сервер
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // следовать за редиректами
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // таймаут4
            curl_setopt($ch, CURLOPT_REFERER, $new_url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // просто отключаем проверку сертификата
            curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/my_cookies.txt'); // сохранять куки в файл
            curl_setopt($ch, CURLOPT_COOKIEFILE, '/my_cookies.txt');
            $total = curl_exec($ch);
            $url2 = $new_url;
            curl_setopt($ch, CURLOPT_URL, $url2);
            curl_setopt($ch, CURLOPT_REFERER, $url);
            curl_setopt($ch, CURLOPT_COOKIEFILE, '/my_cookies.txt');
            $total = curl_exec($ch);
            if (curl_errno($ch)) {
                print curl_error($ch);
                exit;
            }
            curl_close($ch);
        }
        if ($_SESSION["cat_id"] == 49) {
            $url = 'http://sk-house.ua/Products/SetCurrency?cur=%D0%93%D0%A0%D0%9D';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); // отправляем на
            curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:23.0) Gecko/20100101 Firefox/23.0");
            curl_setopt($ch, CURLOPT_HEADER, 0); // пустые заголовки
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // возвратить то что вернул сервер
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // следовать за редиректами
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // таймаут4
            curl_setopt($ch, CURLOPT_REFERER, $new_url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // просто отключаем проверку сертификата
            curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/my_cookies.txt'); // сохранять куки в файл
            curl_setopt($ch, CURLOPT_COOKIEFILE, '/my_cookies.txt');
            $total = curl_exec($ch);
            if (curl_errno($ch)) {
                print curl_error($ch);
                exit;
            }
            //echo $total; 
            curl_close($ch);
            //die();
        }
        if ($_SESSION["cat_id"] != 49 && $_SESSION["cat_id"] != 1) {

            $total = file_get_contents($new_url);
        }
        // echo $total;
    }
//-----------------------------------------
    //echo $total;
    //	$total=file_get_contents($new_url);
    $total = str_replace("	", "", $total);
    $total = str_replace("  ", "", $total);
    $total = str_replace("<div style='margin:3px;'>", '<div style="margin:3px;">', $total);
    $total = str_replace("&nbsp;", " ", $total);
    $total = str_replace("itemprop", "class", $total);
    $total = str_replace("data-src", "src", $total);
    $total = str_replace("name='option_3'", 'name="option_3"', $total);
    $total = str_replace("<arel", ' <a rel', $total);
    $total = str_replace("<ahref", '<a href', $total);
    $total = str_replace("<imgsrc=", '<img src=', $total);
    $total = str_replace('class=""', 'class="imageSellin"', $total);
    if ($total == "")
        return "";
    if (isset($_GET["step"])) {
        echo "<br />Импортирован:{$new_url}<br />";
    }
    //	echo "url. ".$new_url;
    //echo $total;


    $total = str_replace('"></li><li class="size-enable"', '" class="cl_001"></li><li class="size-enable"', $total);
    $total = str_replace("'src", "' src", $total);
    $txt = '<a class="upspa" id="inline" href="#" onclick="document.getElementById(\'windrazmer\').style.display=\'block\'; return false;" title="Таблица размером">Таблица размеров</a>      <div id="windrazmer">  <div class="loginf">   <table id="sizesr-t">  <tbody>  <tr>   <td style="text-align: center;border: 1px solid;padding: 5px 10px;">Международный</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">Российский</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">Объем груди</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">Объем талии</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">Объем бедер</td></tr>  <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">S</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">42</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">84</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">68</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">92</td></tr>   <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">M</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">44</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">88</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">72</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">96</td></tr>   <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">L</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">46</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">92</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">76</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">100</td></tr>   <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">XL</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">48</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">96</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">80</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">104</td></tr>  <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">XXL</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">50</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">100</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">84</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">108</td></tr> <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">3XL</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">52</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">104</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">88</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">112</td></tr>   </tbody>  </table>  *В зависимости от ткани, параметры могут расходиться на +\- 2см!<br> *Все вещи стандартные и соответствуют этой таблице.<br> *S-L - универсальный размер, с тянущейся тканью, подходит на размеры S, M, L.    <div title="Закрыть" class="fancybox-klose" onclick="document.getElementById(\'windrazmer\').style.display=\'none\'; return false;"></div>  </div></div> ';
    $txt2 = ' <a class="upspa" id="inline" href="#" onclick="document.getElementById("windrazmer").style.display="block"; return false;" title="Таблица размером">Таблица размеров</a>        <div id="windrazmer">  <div class="loginf">  <b>Таблица больших размеров:</b><br><br> <table id="sizesr-t">  <tbody>  <tr>     <td style="text-align: center;border: 1px solid;padding: 5px 10px;">Международный</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">Российский</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">Объем груди</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">Объем талии</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">Объем бедер</td></tr>   <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">XL</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">48</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">96</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">82</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">106</td></tr>   <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">XXL</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">50</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">100</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">86</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">110</td></tr>   <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">3XL</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">52</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">106</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">92</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">116</td></tr>   <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">4XL</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">54</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">112</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">98</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">122</td></tr>  <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">5XL</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">56</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">118</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">104</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">128</td></tr>  <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">6XL</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">58</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">124</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">110</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">134</td></tr>  <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">7XL</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">60</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">130</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">116</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">140</td></tr>  <tr> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">8XL</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">62</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">136</td> <td style="text-align: center;border: 1px solid;padding: 5px 10px;">122</td><td style="text-align: center;border: 1px solid;padding: 5px 10px;">146</td></tr>   </tbody>  </table>  *В зависимости от ткани, параметры могут расходиться на +- 2см!<br> *Все вещи стандартные и соответствуют этой таблице.<br>  *L-XXL (и другие подобные) - универсальный размер, с 
тянущейся тканью,<br>подходит на размеры от L до XXL, т.е.: L, XL, XXL.    <div title="Закрыть" class="fancybox-klose" onclick="document.getElementById("windrazmer").style.display="none"; return false;"></div>  </div></div> ';

    $total = str_replace($txt, "", $total);

    $html = str_get_html($total);


    //==========Order===================================
    if (isset($_GET["step"])) {
        $order = $_GET["step"] - 1;
    } else {
        $order = $_SESSION['orderr'];
    }
    //	$qu="UPDATE `shop_commodity` SET `commodity_order`='{$order}' WHERE `from_url`='{$new_url}';";
    //	mysql_query($qu) or die("Error commodity_order");
    if (isset($_GET["step"])) {
        echo "Order: " . ($_GET["step"] - 1) . "<br/>";
    }


//------------------------------------------------------------------------------
//                          Parser Cod
//------------------------------------------------------------------------------    
    $cod = $html->find($_SESSION["cod"], 0)->plaintext;

//-----------------------------Olis Style---------------------------------------        
    if ($_SESSION["cat_id"] == 58) {
        $title = $html->find('.col-lg-6 h1');
        foreach ($title as $h1) {
            if (trim($h1->plaintext) == 'ПРОДАНО') {
                $queryNo2 = "UPDATE `shop_commodity` SET `commodity_visible`='0' WHERE `from_url`='{$new_url}';";
                mysql_query($queryNo2) or die("error no_nal");
                return;
            }
        }
    }
//-----------------------------Alva---------------------------------------------    
    if ($_SESSION["cat_id"] == 43) {
        $title = $html->find('.description');
        $escape = true;
        foreach ($title as $span) {
            $absent = trim($span->plaintext);
            $aa = preg_match('/В наличии/', $absent);
            if (preg_match('/В наличии/', $absent)) {
                $escape = false;
            }
        }
        if ($escape) {
            $queryNo2 = "UPDATE `shop_commodity` SET `commodity_visible`='0' WHERE `from_url`='{$new_url}';";
            mysql_query($queryNo2) or die("error no_nal");
            if (isset($_GET["step"])) {
                echo "<p style='color:red'>В наличии  - не найдено!</p>";
            }
            return;
        }
    }

    //------Majaly--------------------					
    if ($_SESSION["cat_id"] == 65) {
        //	foreach($html->find($_SESSION["cod"]) as $code){
        //			echo "<br>Code:".$code->plaintext;				
        //		}
        //	$p1=strpos($code,"\"");
        //	$p2=stripos($code,'"');
        //	echo "<br>Pos: ".$p1.", ".$p2;
        $title = $html->find('.b-product__data');
        $escape = true;
        foreach ($title as $span) {
            $absent = trim($span->plaintext);
            $aa = preg_match('/В наличии/', $absent);
            if (preg_match('/В наличии/', $absent)) {
                $escape = false;
            }
        }
        if ($escape) {
            $queryNo2 = "UPDATE `shop_commodity` SET `commodity_visible`='0' WHERE `from_url`='{$new_url}';";
            mysql_query($queryNo2) or die("error no_nal");
            if (isset($_GET["step"])) {
                echo "<p style='color:red'>В наличии  - не найдено!</p>";
            }
            return;
        }
    }
    //------Glem--------------------					
    if ($_SESSION["cat_id"] == 15) {
        $f = 0;
        for ($id = 0; $id <= count($gxml->shop->offers->offer); $id++) {
            if ($new_url == $gxml->shop->offers->offer[$id]->url && $f == 0) {
                $cod = $gxml->shop->offers->offer[$id][id];
                $f = 1;
            }
        }
    }
    //------FashionUp--------------------					
    if ($_SESSION["cat_id"] == 2) {
        $cod = str_replace("артикул: ", "", $cod);
        $cod = str_replace($ag, "", $cod);
    }
    //------Swirl by Swirl-MamaMia-Tutsi--------------------					
    if (($_SESSION["cat_id"] == 3) || ($_SESSION["cat_id"] == 5) || ($_SESSION["cat_id"] == 14)) {
        //$cod=substr($cod,0,5);
        //$cod=str_replace(" ","",$cod);
        $cod = preg_replace("/[^0-9]/", "", $cod);
    }
    //------Sellin--------------------					
    if ($_SESSION["cat_id"] == 23) {
        $pos = strpos($cod, " ");
        $cod = substr($cod, $pos, strlen($cod));
        $cod = str_replace(" ", "", $cod);
    }
    //------Seventeen--------------------					
    if ($_SESSION["cat_id"] == 47) {
        //$pos=strpos($cod," ");
        //$cod=substr($cod,$pos,strlen($cod));
        //$cod=str_replace(" ","",$cod);
        $cod = preg_replace("/\D/", "", $cod);
    }
    //------FlFalshion--------------------					
    if ($_SESSION["cat_id"] == 46) {
        $pos = strpos($cod, "-");
        $cod = substr($cod, $pos, strlen($cod));
        $cod = str_replace("- ", "", $cod);
        $cod = str_replace("-", "", $cod);

        $pos2 = strpos($cod, ":");
        if ($pos2 !== false) {
            $cod = strstr($cod, ":");
            $cod = str_replace(":", "", $cod);
            $cod = str_replace(": ", "", $cod);
        }
        $pos22 = strrpos($cod, '"');
        if ($pos22 !== false) {
            $cod = substr($cod, $pos22 + 1, strlen($cod));
            $cod = str_replace(" ", "", $cod);
        }
    }
    //------Meggi--------------------					
    if ($_SESSION["cat_id"] == 42) {
        //$cod = preg_replace('/\D/', '', $cod);
        $cod = strstr($cod, "- ");
        $cod = str_replace("Новинка", "", $cod);
        $cod = str_replace("- ", "", $cod);
    }
    if ($_SESSION["cat_id"] == 43) {
        $search = array("Модель: ", "Наличие: ", "В наличии", "артикул: ");
        $cod = str_replace($search, "", $cod);
    }
    //------Agio-Z-------------------					
    if ($_SESSION["cat_id"] == 45) {
        $ag = array("Платье ", '"', "Комбидресс ", "летнее", "нарядное", "Платье-костюм ", "Футболка ", "-блузка", "-рубашка", "Кофта ", "Блуза ", "Брюки ", "Болеро ", "Свитшот ", "Свитер ", "Кофточка ", "Туника", "летний", "Сарафан", "Лосины ", "Костюм ", "Блуза-двойка ", "Комбинезон ", "Жилет ", "Нарядное платье ", "Юбка ", "Юбка-шорты", "Платье-туника");
        $cod = str_replace($ag, "", $cod);
        $cod = str_replace('"', '', $cod);
        $cod = str_replace(" ", "", $cod);
    }
    //------S&L-------------------					
    if ($_SESSION["cat_id"] == 48) {
        $elementSL = $html->find("div[class='col-md-8 about-wedo-right'] table", 0);
        $cod = $elementSL->children(0)->children(1)->children(0)->children(1)->plaintext;
        $cod = preg_replace('/[^0-9]/', '', $cod);
    }

    //------Lenida--------------------					
    if ($_SESSION["cat_id"] == 16) {
        $posl = strpos($cod, '"');
        $sub = substr($cod, 0, $posl + 1);
        $cod = str_replace($sub, "", $cod);

        $posl2 = strpos($cod, '"');
        $sub2 = substr($cod, $posl2, strlen($cod));
        $cod = str_replace($sub2, "", $cod);
    }
    //------Majaly--------------------					
    if ($_SESSION["cat_id"] == 65) {
        //	$cod = htmlentities($cod, null, 'utf-8');
        $maj = strpos($cod, '&#34;');
        if ($maj !== false) {
            $cod = str_replace('&#34;', "|", $cod);
            $cod = strstr($cod, "|");
            $mpos = strpos($cod, "|");
            $mpos2 = strrpos($cod, "|");

            if ($mpos != $mpos2) {
                $cod = substr($cod, 0, $mpos2);
            }

            $m = strpos($cod, '-');
            if ($m !== false) {
                $cod = strstr($cod, '-', true);
                $cod = str_replace(" ", "", $cod);
            }
            $m2 = strpos($cod, ',');
            if ($m2 !== false) {
                $cod = strstr($cod, ',', true);
            }
            $cod = str_replace("|", "", $cod);
            $cod = str_replace(" ", "", $cod);
            $cod = str_replace("тыде", "ты де", $cod);
        } else {
            $maa = strstr($cod, '(');
            $ma22 = strpos($maa, ')');
            $codd = substr($maa, 0, $ma22 + 2);
            $cod = str_replace($codd, "", $cod);

            $cod = str_replace(",", "", $cod);
            $cod = str_replace(", ", "", $cod);
            if ((strpos($cod, "атье")) != false) {
                $cod = strstr($cod, "атье");
                $cod = str_replace("атье ", "", $cod);
            }
            if ((strpos($cod, "остюм")) != false) {
                $cod = strstr($cod, "остюм");
                $cod = str_replace("остюм ", "", $cod);
            }
            $mp = strpos($cod, " ");
            $cod = substr($cod, 0, $mp);
            $cod = str_replace(" ", "", $cod);
        }
        $cod = mb_strtolower($cod, 'utf-8');
        $cod = mb_substr(mb_strtoupper($cod, 'utf-8'), 0, 1, 'utf-8') . mb_substr($cod, 1, strlen($cod), 'utf-8');
    }

    //----SKHouse-------------------
    if ($_SESSION["cat_id"] == 49) {
        $strsk = strstr($cod, "Артикул:");
        $cod = str_replace("Артикул:", "", $strsk);

        $strsk2 = strstr($cod, "Описание");
        $cod = str_replace($strsk2, "", $cod);
    }

    //----OlisStyle-----------------
    if ($_SESSION["cat_id"] == 58) {
        $cod = preg_replace("/\D/", "", $cod);
    }
    //----Nelli_co-----------------
    if ($_SESSION["cat_id"] == 62) {
        $cod = str_replace("Код: ", "", $cod);
    }
    //----FStyle-----------------
    if ($_SESSION["cat_id"] == 63) {
        $cod = str_replace("арт", "", $cod);
        $cod = str_replace(" ", "", $cod);
    }
    //----Sergio Torri-----------------
    if ($_SESSION["cat_id"] == 85) {
        $cod = preg_replace("/\D/", "", $cod);
    }

    //----B1-----------------
    if ($_SESSION["cat_id"] == 64) {
        $b = strpos($cod, "B1");
        if ($b !== false) {
            $cod = strstr($cod, "B1");
            $cod = str_replace("B1", "", $cod);
            $cod = str_replace("B1 ", "", $cod);
            $cod = preg_replace("/[^0-9a-zA-Z]/", "", $cod);
        }
    }
    //------Crisma-------------------					
    if ($_SESSION["cat_id"] == 87) {
        $codd = strstr($cod, "Артикул");
        $cod = strstr($codd, "Наличие", true);
        $cod = preg_replace("/[^0-9]/", "", $cod);
    }
    //---Vitality-----------
    if ($_SESSION["cat_id"] == 88 || $_SESSION["cat_id"] == 205) {
        $cod = preg_replace('/[^0-9]/', '', $cod);
    }
    //---Dajs-----------
    if ($_SESSION["cat_id"] == 215) {
        $cod = preg_replace('/[^0-9]/', '', $cod);
    }
    //---Dajs-----------
    if ($_SESSION["cat_id"] == 217) {
        $cod = preg_replace('/[^0-9a-zA-Z- ]/', '', $cod);
    }
    //---Dembo House-----------
    if ($_SESSION["cat_id"] == 218) {
        //	$cod=mb_strtolower($cod);
        //	$cod=ucfirst_mb($cod);			
    }
    //---Jhiva-----------
    if ($_SESSION["cat_id"] == 219) {
        $cod = preg_replace('/[^0-9a-zA-Z-]/', '', $cod);
    }

//------------------------------------------------------------------------------
//                        Parser Name
//------------------------------------------------------------------------------
    $name = trim(str_replace("новый товар", "", $html->find($_SESSION["h1"], 0)->plaintext));
    $name = strip_tags($name);
    $name = trim($name);
    $name = str_replace("&quot;", '"', $name);
    //------Cardo--------------------					
    if ($_SESSION["cat_id"] == 1) {
        $name = str_replace("ВЕСНА-", "", $name);
        $name = str_replace("ВЕСНА", "", $name);
        $name = str_replace("ЛЕТО", "", $name);
        $name = str_replace("ОСЕНЬ", "", $name);
        $name = str_replace("ОСЕНЬ-", "", $name);
        $name = str_replace("ЗИМА", "", $name);
        $name = str_replace("2015", "", $name);
        $name = str_replace("2014", "", $name);
        $name = str_replace("|", "", $name);
    }
    //------FashionUp--------------------					
    if ($_SESSION["cat_id"] == 2) {
        //$name=str_replace('"',"",$name);
    }
    //------Sellin--------------------					
    if ($_SESSION["cat_id"] == 23) {
        $name = preg_replace("/[0-9a-zA_Z]/", "", $name);
        $name = str_replace("P--", "", $name);
    }
    //------S&L-------------------					
    if ($_SESSION["cat_id"] == 48) {
        $name = strstr($name, "№", true);
    }
    //------Alva--------------------					
    if ($_SESSION["cat_id"] == 43) {
        $str = substr($name, 0, strlen($name) - 4);
        $name = $str;
    }
    //------Lenida--------------------					
    if ($_SESSION["cat_id"] == 16) {
        //	$name=$name;
        /* 	$query="
          UPDATE `shop_commodity`
          SET
          `com_name`='{$name}'
          WHERE `from_url`='{$new_url}'
          ;";

          mysql_query($query); */
    }
    //------Meggi--------------------					
    if ($_SESSION["cat_id"] == 42) {
        $name = str_replace("Новинка", "", $name);
        $name = strstr($name, "-", true);
        $name = str_replace(" ", "", $name);
    }
    //------Swirl by Swirl-MamaMia-Tutsi--------------------					
    if (($_SESSION["cat_id"] == 3) || ($_SESSION["cat_id"] == 5) || ($_SESSION["cat_id"] == 14)) {
        //$name2=substr($name,6,strlen($name));
        //	$nsea=array("пл","ко","бл","юб");
        //$nsea2=array("Пл","Ко","Бл","Юб");			
        //$name=str_replace($nsea,$nsea2,$name2);
        $name = preg_replace("/[0-9 ]/", "", $name);
        $name = str_replace("В", "", $name);
        $name = ucfirst_mb($name);
    }
    //------Seventeen--------------------					
    if ($_SESSION["cat_id"] == 47) {
        $pos = strpos($name, " ");
        $name = substr($name, 0, $pos);
        $name = str_replace(" ", "", $name);
    }
    //------FlFashion--------------------					
    if ($_SESSION["cat_id"] == 46) {
        $pos1 = strpos($name, ":");
        $pos2 = strrpos($name, '-');
        if ($pos2 === false) {
            $name = strstr($name, ":", true);
        } else {
            $name = substr($name, $pos1, $pos2);
        }
        //	$name=str_replace(" ","",$name);
        $name = str_replace(" Модель", "", $name);
        $name = str_replace(": ", "", $name);
        $name = str_replace(":", "", $name);
        $name = str_replace(" -", "", $name);
        $name = str_replace("-", "", $name);
        $name = str_replace($cod, "", $name);
    }
    //----OlisStyle-----------------
    if ($_SESSION["cat_id"] == 58) {
        $name = str_replace("оптовая цена", "", $name);
        $name = str_replace("Оптовая цена", "", $name);
        $name = str_replace("ОПТОВАЯ ЦЕНА", "", $name);
        $name = str_replace("Оптовая Цена", "", $name);
        $name = ucfirst_mb($name);
    }
    //----Glem-----------------
    if ($_SESSION["cat_id"] == 15) {
        $name = ucfirst_mb($name);
    }
    //----FStyle-----------------
    if ($_SESSION["cat_id"] == 63) {
        $name = str_replace("арт ", "", $name);
        $name = str_replace("арт", "", $name);
        $name = str_replace($cod, "", $name);
    }
    //----Aliya-----------------
    if ($_SESSION["cat_id"] == 86) {
        $name = str_replace("Noch'", "Noch", $name);
    }
    //----B1-----------------
    if ($_SESSION["cat_id"] == 64) {
        $b = strpos($name, "B1");
        if ($b !== false) {
            $name = strstr($name, "B1", true);
            $name = str_replace("B1", "", $name);
            $name = str_replace("B1 ", "", $name);
            $name = str_replace(" B1", "", $name);
        }
        $name = preg_replace("/[0-9a-zA-Z+]/", "", $name);
    }
    //----Sergio Torri-----------------
    if ($_SESSION["cat_id"] == 85) {
        $name = preg_replace("/\d/", "", $name);
    }
    //---Crisme-----------
    if ($_SESSION["cat_id"] == 87) {
        $name22 = preg_replace('/[^0-9]/', '', $name);
        $name = preg_replace('/[0-9]/', '', $name);
        $name = str_replace("/", "", $name);
    }
    //---Vitality-----------
    if ($_SESSION["cat_id"] == 88 || $_SESSION["cat_id"] == 205) {
        $name = preg_replace('/[0-9]/', '', $name);
        $name = str_replace("Розница ", "", $name);
    }
    //---Dajs-----------
    if ($_SESSION["cat_id"] == 215) {
        $name = preg_replace('/[0-9]/', '', $name);
    }
    //---Jhiva-----------
    if ($_SESSION["cat_id"] == 219) {
        $name = preg_replace('/[0-9№]/', '', $name);
    }

//------------------------------------------------------------------------------
//                          Parser Image
//------------------------------------------------------------------------------			
    $lowsrc_dix = $html->find($_SESSION["img"], 0)->alt;
    if (substr_count($lowsrc_dix, '.jpg') > 0) {
        $lowsrc = $html->find($_SESSION["img"], 0)->alt;
    } else {
        $lowsrc2 = $html->find($_SESSION["img"], 0)->src;
        $lowsrc = str_replace("/77x117/", "/", $lowsrc2);
        if ($lowsrc == "")
            $lowsrc = $html->find($_SESSION["img"], 0)->href;
    }
    //------Glem--------------------					
    if ($_SESSION["cat_id"] == 15) {
        /* 	$f=0;
          for($id=0; $id<=count($gxml->shop->offers->offer); $id++){
          if($new_url==$gxml->shop->offers->offer[$id]->url && $f==0){
          $lowsrc=$gxml->shop->offers->offer[$id]->picture[0];
          if($lowsrc!="http://www.glem.com.ua/")
          $f=1;
          }
          } */
    }
    //------Seventeen--------------------					
    if ($_SESSION["cat_id"] == 47) {
        $lowsrc = str_replace("s_", "_", $lowsrc);
        $lowsrc = str_replace("s", "", $lowsrc);
        $lowsrc = str_replace("m_", "_", $lowsrc);
        $lowsrc = str_replace("m", "", $lowsrc);
        $lowsrc = str_replace("_h", "_sh", $lowsrc);
    }
    //------Swirl by Swirl-MamaMia-Tutsi--------------------					
    if (($_SESSION["cat_id"] == 3) || ($_SESSION["cat_id"] == 5) || ($_SESSION["cat_id"] == 14)) {
        $lowsrc = str_replace("135___195", "___", $lowsrc);
        $lowsrc = str_replace("330___515", "___", $lowsrc);
    }
    //------Lenida--------------------					
    if ($_SESSION["cat_id"] == 16) {
        $lowsrc = str_replace("h595", "h1000", $lowsrc);
    }
    //------Cardo--------------------					
    if ($_SESSION["cat_id"] == 1) {
        $lowsrc = str_replace("-large/", "/", $lowsrc);
    }
    //------Glem--------------------					
    if ($_SESSION["cat_id"] == 43) {
        $lowsrc = str_replace("200x300.jpg", "550x825.jpg", $lowsrc);
    }
    //------Agio-Z-------------------					
    if ($_SESSION["cat_id"] == 45) {
        $lowsrc = str_replace(".JPG", "_enl.JPG", $lowsrc);
    }
    //------S&L-------------------					
    if ($_SESSION["cat_id"] == 48) {
        $lowsrc = str_replace("smal/", "/", $lowsrc);
        $lowsrc = str_replace("/middle/", "/", $lowsrc);
    }
    //------SKHouse--------------------					
    if ($_SESSION["cat_id"] == 49) {
        //	$lowsrc=str_replace("200x300.jpg","550x825.jpg",$lowsrc);
        $lowsrc = str_replace(".jpg.product.jpg", ".jpg", $lowsrc);
    }
    //----OlisStyle-----------------
    if ($_SESSION["cat_id"] == 58) {
        $lowsrc = str_replace("-70x81.jpg", "-500x579.jpg", $lowsrc);
    }
    //------Majaly-Nelli_co-------------------					
    if ($_SESSION["cat_id"] == 65 || $_SESSION["cat_id"] == 62) {
        $lowsrc = str_replace("w200_h200", "w640_h640", $lowsrc);
    }
    //------Crisma-------------------					
    if ($_SESSION["cat_id"] == 87) {
        $lowsrc = str_replace("m.jpg", ".jpg", $lowsrc);
    }
    //----B1-----------------
    if ($_SESSION["cat_id"] == 64) {
        $barr = array();
        $k = 0;
        foreach ($html->find('.color-selector li') as $a) {
            $bpos = strpos($a->plaintext, "В наличии ");
            if ($bpos !== false) {
                //	echo $a->plaintext."<br/>";
                $barr[$k] = 1;
            } else {
                $barr[$k] = 0;
            }
            $k++;
        }

        $k = 0;
        $bff = 0;
        foreach ($html->find('.color-selector li a') as $a) {
            if ($barr[$k] == 1) {
                $barr[$k] = $a->color;
                $bff = 1;
            }
            $k++;
        }
        for ($i = 0; $i < count($barr); $i++) {
            if ($barr[$i] == true) {
                $class = ".class_" . $barr[$i] . " img";
                foreach ($html->find($class) as $a) {
                    //	echo $a->src."<br/>";
                    $bsrc = explode("/", $a->src);
                    $gf = $bsrc[count($bsrc) - 1];
                    $p = strpos($gf, "-");
                    $bsub = substr($gf, 0, $p);
                    $bsubb.=$bsub . "|";
                }
            }
        }
        $bsubb = substr($bsubb, 0, strlen($bsubb) - 1);

        if ($bsubb == false)
            $bff = 0;

        foreach ($html->find('.prod-gallery a') as $a) {
            $sa = explode("|", $bsubb);
            $aaa = strpos($a->href, $sa[0]);
            if ($aaa !== false) {
                $lowsrc = $a->href;
            }
        }
        if ($bff == 0) {
            $queryNo2 = "UPDATE `shop_commodity` SET `commodity_visible`='0' WHERE `from_url`='{$new_url}';";
            mysql_query($queryNo2) or die("error no_nal");
            if (isset($_GET["step"])) {
                echo "<br/><b style='color:red;' >Немає фото!!! Не опубликовать!</b>";
            }
            return;
        }
    }
    //----FStyle-----------------
    if ($_SESSION["cat_id"] == 63) {
        $lowsrc = str_replace("_2.png", "_1.png", $lowsrc);
        $lowsrc = str_replace("_3.png", "_1.png", $lowsrc);
        $lowsrc = str_replace("_4.png", "_1.png", $lowsrc);
        $lowsrc = str_replace("_5.png", "_1.png", $lowsrc);
    }
    //----HelenLaven-----------------
    if ($_SESSION["cat_id"] == 217) {
        $lowsrc = str_replace("_m.jpg", "_b.jpg", $lowsrc);
    }


//------------------------------------------------------------------------------
//                  Парсер Андрей с помощью нокогири
//------------------------------------------------------------------------------
//----------------------Подключение нокогири и получение данных-----------------
    require_once ('includes/verify/lib_nokogiri/nokogiri.php');
    //$new_url = 'http://zdesbrand.com/index.php?route=product/product&path=87&product_id=232';
    $html1 = file_get_contents($new_url);
    $saw = (new nokogiri())->fromHtmlNoCharset($html1);
    $arrayCod = $saw->get($_SESSION["cod"])->toArray();
    $arrayName = $saw->get($_SESSION["h1"])->toArray();
    $arrayExist = $saw->get($_SESSION['no_nal'])->toArray();
    $arrayImage = $saw->get($_SESSION['img'])->toArray();
    $arrayPrice = $saw->get($_SESSION['price'])->toArray();


//----------------------Zdes------------36-239----------------------------------    

    if ($_SESSION["cat_id"] == 239) {
        require 'includes/verify/brands_parsers/zdes_36_239.php';
    }
//------------------------------------------------------------------------------
//                  Валидация картинки
//------------------------------------------------------------------------------   
    $eff = str_replace("http://", "", $new_url);
    //$eff=str_replace("http://","",$eff);
    $adasda = explode("/", $eff);

    $domain = array_shift($adasda);
    $lowsrc = str_replace("http://", "", $lowsrc);
    $lowsrc = str_replace($domain, "", $lowsrc);

    $src = $domain . "/" . $lowsrc;
    $src = str_replace("//", "/", $src);
    $src = "http://" . $src;

    $src = str_replace("majaly.com.ua/", "", $src);
    $src = str_replace("nelli-co.com/", "", $src);
    if (strpos($src, "%") === false) {
        $src = rawurlencode($src);
    }

    $src = str_replace("%3A", ":", $src);
    $src = str_replace("%2F", "/", $src);

    $typeImg = explode(".", $src);
    $type = $typeImg[count($typeImg) - 1];

    if ($type == 'png') {
        $typeName = explode("/", $src);
        $typeName2 = $typeName[count($typeName) - 1];
        $typeName2 = strstr($typeName2, '.', type);
        $src = convert_image_type($type, 'jpg', $src, $typeName2);
        echo "Type Image: " . $typeName2 . ": " . $srcc . "<br/>";
    }
    if ($src == "http://www.glem.com.ua/") {
        $n = 1;
    }
    //echo  $src;
    //die();
//=========================Конец================================================
//------------------------------------------------------------------------------
//обновляем код в бд и выводим на экран код и название и картинка
//------------------------------------------------------------------------------    

    $query = "UPDATE `shop_commodity` SET `cod`='{$cod}' WHERE `from_url`='{$new_url}';";
    mysql_query($query);

    if (isset($_GET["step"])) {
        echo "Category: " . $_SESSION["cat_id"] . "<br/>";
        echo "Cod: " . $cod . "<br/>";
    }
    $uptext = "Category: " . $_SESSION["cat_id"] . "<br/>";
    $uptext.="Cod: " . $cod . "<br/>";
    $uptext.="Title: " . $name . "<br/>";
    echo "Name: " . $name . "<br/>";


    if (isset($_GET["step"])) {
        echo "Url: <a href={$new_url} target='_blank'>{$new_url}</a><br/>";
        echo "img: <a href={$src} target='_blank'>{$src}</a><br/>";
        //	echo "<img src={$src} width='100px' />";
        // die();
    }
    $uptext.="Url: <a href={$new_url} target='_blank'>{$new_url}</a><br/>";
    if ($name == "")
        return "";
    $commodity_add_date = date("Y-m-d h:i:s");
    $alias = translit($name);
    $ii = 0;
    //$old_price=$html->find(".cl_old_price",0)->plaintext;
//------------------------------------------------------------------------------
//                        Parser Price цена товара                  
//------------------------------------------------------------------------------			
    $price = $html->find($_SESSION["price"], 0)->plaintext; //

    if ($price == "")
        $price = $html->find($_SESSION["price"], 0)->value; //
    $price = strip_tags($price);

    //------S&L--------------------
    if ($_SESSION["cat_id"] == 48) {
        $price22 = strstr($price, "Розница", true); //Для опт.ціна
        $price = strstr($price, "Розница");
        //echo "Pr: ".$price22;
    }
    //------Andrea Crocetta --------------------					
    if ($_SESSION["cat_id"] == 63) {
        $price = str_replace(" грн.", "", $price);
        $price = str_replace("грн.", "", $price);
        $pricee = strstr($price, " ");
        if (intval($pricee) != 0) {
            $price = strstr($price, " ");
        }
    }
    //---Cardo-----------
    if ($_SESSION["cat_id"] == 1) {
        //	require_once 'includes/simple_html_dom.php';
        //	$ht=file_get_html($new_url);
        //foreach($ht->find('#our_price_display') as $e){
        //		echo "<br/> Cardo Price22: ".$e->plaintext;
        //	}
        //	echo $html;
    }
    //---Dajs-----
    if ($_SESSION["cat_id"] == 215) {
        $price_d = $html->find(".price-new", 0)->plaintext;
        if ($price_d) {
            $price = $price_d;
        }
    }

    $price = htmlentities($price, null, 'utf-8');
    $price = str_replace("&nbsp;", "", $price);
    $price = str_replace(" ", "", $price);
    $price = str_replace(",00", "", $price);
    $price = str_replace(".00", "", $price);
    $price = str_replace("грн.", "", $price);
    $price = str_replace("Цена", "", $price);
    $price = str_replace("цена", "", $price);
    $price = str_replace(":", "", $price);
    $price = str_replace("грн", "", $price);
    $price = str_replace(",", "", $price);
    $price = str_replace("Розница", "", $price);
    $price = str_replace("Стоимость:", "", $price);
    $price = (int) $price;

    //------SKHouse--------------------					
    if ($_SESSION["cat_id"] == 49) {
        
    }
    //------Alav--------------------					
    if ($_SESSION["cat_id"] == 43) {
        $p = strlen($price);
        if ($p >= 4) {
            $price = substr($price, ($p / 2), $p);
        }
    }
    //----Majaly-----------------
    if ($_SESSION["cat_id"] == 65) {
        $excPrice = read_excel('excel/majaly.xls');
        $cod2 = $cod;
        $num = strpos($cod, "№");
        if ($num !== false)
            $cod2 = str_replace("№", "", $cod);
        foreach ($excPrice as $val) {
            $n = explode(",", $val[0]);
            if ($n[0] == $cod2) {
                $price = $val[1];
                $price = str_replace(".00", "", $price);
            }
        }
        $num2 = strpos($n[0], "№");
    }
    //------Crisma-------------------					
    if ($_SESSION["cat_id"] == 87) {
        //echo "aart ".$name22;
        $v2 = str_replace(" ", "", $name22);
        $coddd = strlen($v2);
        //echo "siz ".$coddd;
        if ($coddd == 5) {
            $art = substr($v2, 2, $coddd);
        } elseif ($coddd == 6) {
            $art = substr($v2, 2, 3);
        } elseif ($coddd = 4) {
            $art = $v2;
        }
        echo "<br/>art:" . $art;
        $excPrice = read_excel('excel/crisma.xls');
        foreach ($excPrice as $val) {
            $v = str_replace(" ", "", $val[0]);

            if ($v == $art) {
                $price = $val[1];
                $price = strstr($price, "+");
                $price = str_replace(".", "", $price);
                $price = str_replace(" ", "", $price);
                $price = str_replace("+", "", $price);
                $price = str_replace("грн", "", $price);
                $price = intval($price);
                echo $val[0] . "<br/><span style='color:red'>Yes</span>";
                //die('Stop');
            }
        }
    }
    //----Dembo House----------------				
    if ($_SESSION["cat_id"] == 218) {
        $excPrice = read_excel('excel/dembohouse.xlsx');
        $stro_cod = mb_strtolower($cod, 'utf-8');
        foreach ($excPrice as $val) {
            if (strpos(mb_strtolower($val[4], 'utf-8'), $stro_cod) !== false) {
                //echo mb_strtolower($val[4],'utf-8')."-".$val[9]."<br>";
                $price = intval($val[9]);
            }
        }
    }
    if (isset($_GET["step"])) {
        echo "<br/>Price: " . $price;
    }
    //die();
    //echo $price;die();
    //echo $src;	die();
    //echo $src;die();
//=Price_Opt========================================						
    $price2 = $html->find($_SESSION["price2"], 0)->plaintext; //
    if ($price2 == "")
        $price2 = $html->find($_SESSION["price2"], 0)->value; //
    $price2 = strip_tags($price2);

    //------S&L--------------------
    if ($_SESSION["cat_id"] == 48) {
        $price2 = $price22;
    }

    $price2 = htmlentities($price2, null, 'utf-8');
    $price2 = str_replace("грн", "", $price2);
    $price2 = str_replace("&nbsp;", "", $price2);
    $price2 = str_replace("грн.", "", $price2);
    $price2 = str_replace(",", "", $price2);
    $price2 = str_replace("Розница", "", $price2);
    $price2 = str_replace("Опт ", "", $price2);
    $price2 = str_replace("Опт:", "", $price2);
    $price2 = str_replace(" ", "", $price2);
    $price2 = (int) $price2;

    //------Glem--------------------					
    if ($_SESSION["cat_id"] == 15) {
        /* 		$f=0;
          for($id=0; $id<=count($gxml->shop->offers->offer); $id++){
          if($new_url==$gxml->shop->offers->offer[$id]->url && $f==0){
          $price2=$gxml->shop->offers->offer[$id]->prices->price->value;
          $f=1;
          }
          } */
    }
    //------SKHouse--------------------					
    if ($_SESSION["cat_id"] == 49) {
        $pri2 = $html->find($_SESSION["price2"], 1)->plaintext;
        if ($pri2 == true) {
            $price2 = $html->find($_SESSION["price2"], 1)->plaintext;
            if ($price2 == "")
                $price2 = $html->find($_SESSION["price2"], 1)->value;
            $price2 = strip_tags($price2);
        }
        $price2 = substr($price2, 0, strlen($price2) - 2);
        $price2 = str_replace(",", "", $price2);
    }

    //------Crisma-------------------					
    if ($_SESSION["cat_id"] == 87) {
        $excPrice = read_excel('excel/Crisma_new.xls');
        foreach ($excPrice as $val) {
            if ($cod == $val[0]) {
                $price = $val[2];
                $price2 = $val[4];
            }
        }
    }

    //------Jhiva------------------					
    if ($_SESSION["cat_id"] == 219) {
        $excPrice = read_excel('excel/jhiva2.xls');
        foreach ($excPrice as $val) {
            if ($cod == $val[1]) {
                $price2 = $val[14];
            }
        }
    }

    if (isset($_GET["step"])) {
        echo "<br/>Price2: " . $price2;
    }
    //die();
    //echo $src;die();
    //echo $_SESSION["price"];die();
    // if($_SESSION["per"]!=0)
    // {
    // 	$price2+=($price2/100)*$_SESSION["per"];
    // }
//------Glem--------------------					
    if ($_SESSION["cat_id"] == 15) {
        $f = 0;
        for ($id = 0; $id <= count($gxml->shop->offers->offer); $id++) {
            if ($new_url == $gxml->shop->offers->offer[$id]->url && $f == 0) {
                $price = $gxml->shop->offers->offer[$id]->price;
                $price2 = $gxml->shop->offers->offer[$id]->prices->price->value;
                $f = 1;
                if (isset($_GET["step"])) {
                    echo "<br/>XMLPrice: " . $price;
                    echo "<br/>XMLOpt: " . $price2;
                }
            }
        }
    }
    /* 	if($price==0){
      $query="
      UPDATE `shop_commodity`
      SET
      `commodity_visible`='0'
      WHERE  `from_url`='{$new_url}'
      ;";
      echo "<br/>VISIBLE=0";
      } */
//=Desc================================================
    $fs = 0;
    $sid = 0;
    $desc2 = $html->find($_SESSION["desc"]);
    if (is_array($desc2)) {
        foreach ($desc2 as $value) {
            $sea = array("{$price}", "цена:", "цена: ", "цена :", "Цена : ", "Цена: ", "Цена:", "Наличие", "Наличие:", "грн.", "грн", ":  есть", ":есть", ": есть", "<p>  </p>", "<p>  &nbsp;</p>", "<p>&nbsp;</p>");
            $desc3 = str_replace($txt, "", $value);
            if (($_SESSION["cat_id"] != 3) && ($_SESSION["cat_id"] != 5) && ($_SESSION["cat_id"] != 14))
                $desc.=$desc3;
            if ((($_SESSION["cat_id"] == 3) || ($_SESSION["cat_id"] == 5) || ($_SESSION["cat_id"] == 14)) && $sid == 0) {
                $desc.=$desc3;
                $sid = 1;
            }
        }
    } else {
        $desc = $desc2;
    }
    //---Fashioup-----------------------
    if ($_SESSION["cat_id"] == 2) {
        $cod2 = "Модель: " . $cod;
        $desc = str_replace($cod, "", $desc);
        $desc = str_replace("t'ame", 't\'ame', $desc);
        $desc = str_replace("<span>Модель:</span>", "", $desc);
        $desc = str_replace('<div class="sttt"> </div>', '', $desc);
        $desc = str_replace('<div class="sttt">', '<div class=sttt >', $desc);
    }
    //------Cardo-------------------					
    if ($_SESSION["cat_id"] == 1) {
        $pos = strrpos($desc, "<p>");
        $desc = str_replace(($desc[$pos] . $desc[$pos + 1] . $desc[$pos + 2] . $desc[$pos + 3] . $desc[$pos + 4] . $desc[$pos + 5] . $desc[$pos + 6]), ($desc[$pos] . $desc[$pos + 1] . $desc[$pos + 2] . "<span class=sttt2 >Описание: </span>" . $desc[$pos + 3] . $desc[$pos + 4] . $desc[$pos + 5] . $desc[$pos + 6]), $desc);
        $desc = str_replace(' data-sheets-value="[null,2,&quot;u0421u043eu0441u0442u0430u0432: u0432u0438u0441u043au043eu0437u0430 60%, u0445u043bu043eu043fu043eu043a 40% (u0448u0442u0430u043fu0435u043bu044c)&quot;]" data-sheets-userformat="[null,null,897,[null,0],null,null,null,null,null,null,1,1,0]"', '', $desc);
        $desc = str_replace(' data-sheets-value="[null,2,&quot;u0421u043eu0441u0442u0430u0432: u0445u043bu043eu043fu043eu043a 50%, u0432u0438u0441u043au043eu0437u0430 20%, u043fu043eu043bu0438u044du0441u0442u0435u0440 22%, u044du043bu0430u0441u0442u0430u043d 8%(u043au043eu0442u0442u043eu043d-u0434u0436u0438u043du0441 u0442u043eu043du043au0438u0439)&quot;]" data-sheets-userformat="[null,null,897,[null,0],null,null,null,null,null,null,1,1,0]"', '', $desc);
        $desc = str_replace(' data-sheets-value="[null,2,&quot;u0421u043eu0441u0442u0430u0432: u043fu043eu043bu0438u044du0441u0442u0435u0440 50%, u0432u0438u0441u043au043eu0437u0430 50%(u0442u0440u0438u043au043eu0442u0430u0436&quot;u0436u0430u043au043au0430u0440u0434&quot;)&quot;]" data-sheets-userformat="[null,null,897,[null,0],null,null,null,null,null,null,1,1,0]"', '', $desc);
        $cstr = strstr($desc, ' data-sheets');
        $cpos = strpos($cstr, ']">');
        $sub = substr($cstr, 0, $cpos + 2);

        $desc = str_replace($sub, '', $desc);

        $desc = str_replace('<span class=sttt2 >Описание: </span><span>Состав:', 'Состав:', $desc);
    }
    //------Swirl by Swirl-MamaMia-Tutsi--------------------					
    if (($_SESSION["cat_id"] == 3) || ($_SESSION["cat_id"] == 5) || ($_SESSION["cat_id"] == 14)) {
        $desea = array("<p>", "</p>", "<span>", "</span>", "</div>", '<div class="right-tovar">', '<span class="size">', "<ul>", "</ul>");
        $desc = str_replace($desea, "", $desc);
        $desc = str_replace("Состав:" . $cod, "", $desc);
        //	$desc=str_replace(,"",$desc);	
        $desc = str_replace("<p></p>", "", $desc);
        $desc = str_replace("<li>", "<p>", $desc);
        $desc = str_replace("</li>", "</p>", $desc);
        $col = "";
        $color = $html->find('.cell', 0)->plaintext;
        $pos = strpos($color, "Цвет:");
        if ($pos !== false) {
            $col = "<p>" . $color . "</p>";
        }
        //echo "<br/>{$col}, {$color}<br/>";
        $desc.=$col;
    }
    //------Seventeen--------------------					
    if ($_SESSION["cat_id"] == 47) {

        $dtxt = array("<br />", "<br/>", "<br>", "<div>", "</div>", $txt2);
        $desc = str_replace($dtxt, "", $desc);
        //	$desc.="<p>";
        $dtxt = array('<div style="padding:30px 0px 0px 0px">', "Материал:", "Длина", "Цвет:", "Внима", "Празднич", "Рукава", "Воротник", "ВНИМАН", "Коктей", "Плать", "Исполь");
        $dtxt2 = array('<div><p>', "</p><p>Материал:", "</p><p>Длина", "</p><p>Цвет:", "</p><p>Внима", "</p><p>Празднич", "</p><p>Рукава", "</p><p>Воротник", "</p><p>ВНИМАН", "</p><p>Коктей", "</p><p>Плать", "</p><p>Исполь");
        $desc = str_replace($dtxt, $dtxt2, $desc);
        //	$desc.="</p>";

        $dtxt2 = array("<p></p>", "<p><p>");
        $desc = str_replace($dtxt2, "", $desc);

        // $spos=strpos($desc,"<a");
        // $spos2=strpos($desc,"</a>");
        // $subs=substr($desc, $spos, $spos2);
        // $desc=str_replace($subs,"",$desc);
        $spos = strstr($desc, '<a class="upspa"');
        $desc = str_replace($spos, "", $desc);
        $desc = str_replace('ь" class="fancybox-klose" onclick="document.getElementById(\'windrazmer\').style.display=\'none\'; return false;">', "", $desc);


        $rus2 = strstr($desc, "Россий");
        $desc = str_replace($rus2, "", $desc);
        $desc = str_replace(" %26%2365533%3Bдный", "", $desc);
        //echo "Pos: ".$rus2;
    }
    //------Glem--------------------					
    if ($_SESSION["cat_id"] == 15) {

        $desc = str_replace("104", "", $desc);
        $desc = str_replace(":", "", $desc);
        $desc = str_replace("<p> </p>", "", $desc);
        $desc = str_replace("<strong>", "", $desc);
        $desc = str_replace("</strong>", "", $desc);
        $desc = str_replace("<br />", "", $desc);
        $desc = str_replace("<p>&nbsp;</p>", "", $desc);
        $desc = str_replace('<img title="как мерять" src="../img/12157/796.png" alt="как мерять" height="127" width="200">', '', $desc);

        $as = array("Состав", "Длина");
        $as2 = array("<span class=sttt2 >Состав </span>", "<span class=sttt2 >Размеры: </span>длина");
        $desc = str_replace($as, $as2, $desc);

        $strr = strstr($desc, "<img");
        $ss = strpos($strr, '" />');
        $sub = substr($strr, 0, $ss + 4);
        $desc = str_replace($sub, "", $desc);
        $desc = str_replace("</p><p></p><p>", "</p><p>", $desc);
        $desc = str_replace("<p>Размерная сетка</p>", "", $desc);
        $desc = str_replace("</p><p>Дайвинг.", "Дайвинг.</p><p>", $desc);
        $desc = str_replace('<span style="color: #ff006a;"><a href="../articles-odegda/vorotnichki-v-podarok.html"><span style="color: #ff006a;">Акция!</span></a></span>', "", $desc);

        $colset = "";
        $sizset = "";


        $m = "";
        for ($id = 0; $id <= count($gxml->shop->offers->offer); $id++) {
            if ($new_url == $gxml->shop->offers->offer[$id]->url) {

                $col = $gxml->shop->offers->offer[$id]->param[0];
                $colset.="{$col}|";

                $si = $gxml->shop->offers->offer[$id]->param[1];
                $sizset.=$si . ";";
            }
        }
        $colorGlem = "";
        $sizeGlem = "";
        $colset2 = explode("|", $colset);
        for ($i = 0; $i < count($colset2); $i++) {
            if ($colset2[$i] != false)
                if ($colset2[$i] != $colset2[$i + 1])
                    $colorGlem .= ", " . $colset2[$i];
        }
        $sizeGlem = $sizset;


        $desc.="<p>Цвет: {$colorGlem}</p>";
        $desc = str_replace("Цвет: ,", "Цвет: ", $desc);
    }

    //-----Lenida--------------------------
    if ($_SESSION["cat_id"] == 16) {
        $dtxt = array("<em style=\"line-height: 20.7999992370605px;\">", "</em>", "<em>", "<em style=\"color: rgb(50, 25, 0); font-family: open_sanslight, sans-serif; font-size: 14px; line-height: 16px; background-color: rgb(253, 246, 234);\">");
        $descc = str_replace($dtxt, "", $desc);
        //	$desc.="<p>";
        $t = array('<div id="tabs-1" class="descr tab">', "Длин", "Рост", "Рекомендуемый", "<br>", "<br/>", "Жакет", "</div>", 'Рост модели');
        $t2 = array('<p><span class="sttt2">Описание: </span>', "</p><p>Длин", "</p><p>Рост", "</p><p>Рекомендуемый", "", "", "</><p>Жакет", "</p>", '<span class="sttt2">Рост модели</span>');
        $desc2 = str_replace($t, $t2, $descc);
        $desc2.="2</p>";

        $dtxt2 = array("<h3>", "<p></p>", "<p><p>", "<p> </p>", "<p>&nbsp;</p>", "<p><i>&nbsp;</i></p>", "<br/>", "<br />", "<br>", "<h3>&nbsp;</h3>", "<i>&nbsp;</i></p>");
        $desc = str_replace($dtxt2, "", $desc2);
        $desc = str_replace("</span> <p>", "</span>", $desc);
        $desc = str_replace("<i>&nbsp;</i></p>", "", $desc);
        $desc = str_replace("<h3>&nbsp;</h3>", "", $desc);
        $desc = str_replace("</p>2</p>", "", $desc);
        $desc = str_replace("Размер", "</p><p>Размер", $desc);
        $desc = str_replace("Пояс", "</p><p>Пояс", $desc);
        $desc = str_replace("2</p>", "", $desc);

        //	$desc=str_replace('Приятных Вам покупок! С уважением, компания производитель модной женской одежды "LENIDA"',"",$desc);
        $str = strstr($desc, "<p>Приятных Вам покупок!");
        if ($str == false)
            $str = strstr($desc, "Приятных Вам покупок!");
        $desc = str_replace($str, "", $desc);

        $sos = $html->find("#tabs-2", 0)->plaintext;
        $sos1 = $html->find('#tabs-1', 0)->plaintext;
        $sos1 = strip_tags($sos1);
        if ($sos1 == "") {
            $desc = str_replace('<p><span class="sttt2">Описание: </span>', "", $desc);
            $sos = $html->find("#tabs-2", 0)->plaintext;
        } else {
            //	echo "True";			
        }

        $desc.="<p>Состав: " . $sos . "</p>";
        $desc = str_replace("Состав:  Ткань", "Ткань", $desc);
    }
    //------Sellin--------------------					
    if ($_SESSION["cat_id"] == 23) {
        $desc = str_replace('<div class="cpt_product_description"><div>', '', $desc);
        $desc = str_replace("</div>", "", $desc);
        $desc = str_replace("<p>Пальто женское</p>", "", $desc);

        /* 	$sel=$html->find(".product_option",0)->plaintext;
          $sel="Цвет:".$sel;
          $sel=str_replace("Не определено","",$sel);
          $sel=str_replace("Не определено","",$sel);
          $sel2=str_replace("  ",", ",$sel);
          $sel2=str_replace("   ",", ",$sel);
          $sel=str_replace("Цвет:, ","Цвет: ",$sel2); */
        $r = 0;
        $rr = 0;
        while ($rr == 0) {
            $sell = $html->find(".cpt_product_params_fixed table tr", $r)->plaintext;
            $fsell = strpos($sell, "Цвет");
            if ($fsell !== false) {
                $rr = 1;
            }
            $r++;
        }
        $sell = str_replace("Цвет ", "", $sell);
        //	echo "col: ".$sell;
        //	echo "qq".$sell;
        /* 	$fsell=strpos($sell,"Цвет");
          if($fsell!==false){
          $sell=$html->find(".second select option");
          foreach($sell as $a){
          $sell.=$a->plaintext.";";
          }
          $sel="Цвет: ".$sell;
          } */
        $colSellin = $sell;
        $sel = "Цвет: " . $sell;
        $desc.="<p>" . $sel . "</p>";
    }
    //------Meggi--------------------					
    if ($_SESSION["cat_id"] == 42) {
        $mp = strpos($desc, "<p>");
        $mp1 = strpos($desc, "</p>");
        $d1 = substr($desc, $mp, $mp1);
        $desc = str_replace($d1, "", $desc);
        $desc = str_replace("<ul>", "", $desc);
        $desc = str_replace("</ul>", "", $desc);
        $dtxt2 = array("<span>", "<p>", "</p>");
        $dtxt3 = array("<span class='sttt2'>", "", "");
        $desc = str_replace("<li>", "", $desc);
        $col = $html->find('#colorselect', 0)->plaintext;
        $desc.="<p>Цвет: {$col}</p>";
    }
    //------Agio-Z-------------------					
    if ($_SESSION["cat_id"] == 45) {
        $d2 = array("Ткань:", "Длина:");
        $d22 = array("<span class=sttt2 >Ткань:</span>", "<span class=sttt2 >Длина:</span>");
        $desc = str_replace($d2, $d22, $desc);
    }

    //------S&L-------------------					
    if ($_SESSION["cat_id"] == 48) {
        $excDesc = read_excel('excel/sl.xls');
        foreach ($excDesc as $val) {
            if ($cod == $val[0]) {
                $desc = $val[4];
            }
        }
        $d2 = array("Длина :", "Длина рукава:", "Застёжка:", "Ткань:", "Длина пиджака:", "Длина:", "Потайная", "Длина платья:", "Пояс", "Длина рубашки:", "Длина юбки:", "Застёжка:", "Длина");
        $d22 = array("</p><p><span class=sttt2 >Длина: </span>", "</p><p><span class=sttt2 >Длина рукава:</span>", "</p><p><span class=sttt2 >Застёжка:</span>", "</p><p><span class=sttt2 >Ткань:</span>", "</p><p><span class=sttt2 >Длина пиджака:</span>", "</p><p>Длина:", "</p><p>Потайная", "</p><p>Длина платья:", "</p><p>Пояс", "</p><p>Длина рубашки:", "</p><p>Длина юбки:", "</p><p>Застёжка:", "</p><p>Длина");
        $desc = str_replace($d2, $d22, $desc);
        $desc = str_replace("<p>  </p>", "", $desc);
        $desc = str_replace("Состав:" . $cod, "", $desc);
        $desc = str_replace("</p><p></p><p>", "</p><p>", $desc);
        $desc = str_replace("</p><p><span class=sttt2 ></p><p>", "</p><p>", $desc);
        $desc = str_replace("Застежка:", "</p><p>Застежка:", $desc);
        $desc = str_replace("Застёжка", "</p><p>Застёжка", $desc);
        $desc = str_replace("Блуза прямого", "</p><p>Блуза прямого", $desc);
        $desc = str_replace("см. Юбка", "см.</p><p>Юбка", $desc);
        $desc = str_replace("<strong>", "<span class=sttt2 >", $desc);
        $desc = str_replace("</strong>", "</span>", $desc);
        //$slpos=strpos($desc,"Пояс идёт");
        //if($slpos!==false){
        $desc = str_replace("<p>Пояс идёт в комплекте.</p>", "", $desc);
        $desc = str_replace("Белый пояс идёт в комплекте.", "", $desc);
        $desc = str_replace("Горловина и талия в платье стянуты на резинку.", "", $desc);
        $desc = str_replace("В платье есть карманы.", "", $desc);
        $desc = str_replace("Пояс идёт в комплекте.", "", $desc);
        $desc = str_replace("<p>Пояс из атлас-сатина входит в комплект.</p>", "", $desc);
        $desc = str_replace("карманы.", "карманы. Пояс идёт в комплекте.", $desc);
        $desc = str_replace("гипюра.", "гипюра. Белый пояс идёт в комплекте.", $desc);
        $desc = str_replace("образе.", "гобразе. Горловина и талия в платье стянуты на резинку. В платье есть карманы. Пояс идёт в комплекте.", $desc);
        $desc = str_replace("жаккарда. ", "жаккарда. Пояс из атлас-сатина входит в комплект.", $desc);
        //}
    }
    //------Alva-------------------					
    if ($_SESSION["cat_id"] == 43) {
        $d2 = array($price, '<span style="color: rgb(72, 62, 59); font-family: \'Trebuchet MS\', Helvetica, Jamrul, sans-serif; font-size: 13px; line-height: 19px;">', "</span>");
        $ppa = strpos($desc, "Цена");
        if ($ppa !== false) {
            $stra = strstr($desc, "<p>  Цена");
            $aa = strpos($stra, "</p>");
            $stra = substr($stra, 0, $aa);
            $desc = str_replace($stra . "</p>", "", $desc);
            //	echo $stra;		
        }
        $ppa2 = strpos($desc, "Наличие");
        if ($ppa2 !== false) {
            $stra = strstr($desc, "<p>  Наличие");
            $aa = strpos($stra, "</p>");
            $stra = substr($stra, 0, $aa + 3);
            $desc = str_replace($stra . "</p>", "", $desc);
            //	echo $stra;		
        }
        $desc = str_replace($d2, "", $desc);
        $desc = str_replace("<p>    </p>", "", $desc);
        $desc = str_replace("<p>   </p>", "", $desc);
        $desc = str_replace("<p>  </p>", "", $desc);
        $desc = str_replace("Есть в наличии.", "", $desc);
        $desc = str_replace("РАСПРОДАЖА", "", $desc);
        $desc = str_replace("<p>  &nbsp;</p>", "", $desc);
        $desc = str_replace('<span style="background-color:#ffd700;">', "", $desc);
        $desc = str_replace('</span>', "", $desc);
        $desc = str_replace('<strong>', "", $desc);
        $desc = str_replace('</strong>', "", $desc);
        $desc = str_replace('style="border: 0px; font-family: Arial, Helvetica, sans-serif; margin: 0px 0px 20px; padding: 0px; vertical-align: baseline; color: rgb(51, 51, 51); font-size: 14px;"', "", $desc);
        $desc = str_replace('<span style="border: 0px; font-family: \'Trebuchet MS\', Helvetica, Jamrul, sans-serif; margin: 0px; padding: 0px; vertical-align: baseline; color: rgb(72, 62, 59); font-size: 13px; line-height: 19px;">', "", $desc);
    }
    //-----FlFashion------------------					
    if ($_SESSION["cat_id"] == 46) {
        $desc = str_replace("<p>&nbsp;</p>", "", $desc);
        $name2 = str_replace('"', "", $name);
        $nam1 = strstr($name2, " ");
        $nam2 = strstr($name2, " ", true);
        $nam1 = str_replace(" ", "", $nam1);
        $nam2 = str_replace(" ", "", $nam2);
        if ($nam1) {
            $nampos = strpos($desc, $nam1);
        } else {
            $nampos = strpos($desc, $nam2);
        }

        if ($nampos !== false) {
            $desc = str_replace("«", "", $desc);
            $desc = str_replace("»", "", $desc);
            $desc = str_replace('"', "", $desc);

            $desc = str_replace($nam1, "", $desc);
            $desc = str_replace($nam2, "", $desc);
        }

        $desc = str_replace('<p>Комбинезон </p>', "", $desc);
        $desc = str_replace('Комбинезон "Амур"', "", $desc);
        $desc = str_replace('<p>Комбинезон-шорты «Бутон»</p>', "", $desc);
        $desc = str_replace('</h2>', "", $desc);
        $desc = str_replace('<h2>', "", $desc);
        $desc = str_replace('<span style="line-height: 1.5em;">', "", $desc);
        $desc = str_replace('<p> Санта - НОВИНКА ЭТОЙ ОСЕНИ!</p>', "", $desc);
        $desc = str_replace('Платье летнее ""', "", $desc);
        $desc = str_replace("Розничная цена: +70грн к цене на сайте", "", $desc);

        $desc = str_replace("Розничная цена: +70 грн к цене на сайте", "", $desc);
        $desc = str_replace("<h3><strong>Розничная цена +70грн к цене</strong></h3>", "", $desc);
        $desc = str_replace("<h4><strong>СПЕЦИАЛЬНЫЕ УСЛОВИЯ ДЛЯ СП&nbsp; (подробности по запросу)</strong></h4>", "", $desc);
        $desc = str_replace("<h4><strong>СПЕЦИАЛЬНЫЕ УСЛОВИЯ ДЛЯ СП  (подробности по запросу)</strong></h4>", "", $desc);
        $desc = str_replace("<h4><strong>СПЕЦИАЛЬНЫЕ УСЛОВИЯ ДЛЯ СП&nbsp; (подробности по запросу)</strong></h4> ", "", $desc);
        $desc = str_replace("<p>&nbsp;</p>", "", $desc);
        $desc = str_replace("<p></p>", "", $desc);
        $desc = str_replace("<br>", "</p><p>", $desc);
        $desc = str_replace("<br />", "", $desc);
        $desc = str_replace("<span>Розничная цена: +70 грн к цене на сайте</span>", "", $desc);

        $fl = strpos($desc, "Розн");
        if ($fl !== false) {
            $sub1 = strstr($desc, "Розн");
            $sub = strstr($sub1, "</p>", true);
            $desc = str_replace($sub, "", $desc);
        }
        $desc = str_replace("- это", $name . " - это", $desc);
        $desc = str_replace("<p>.</p>", "", $desc);
        $desc = str_replace("<p>. </p>", "", $desc);
        $desc = str_replace("<p> .</p>", "", $desc);
        $desc = str_replace("<p> </p>", "", $desc);
        $desc = str_replace("<p>;</p>", "", $desc);
        $desc = str_replace("<ul>  <li><em><span></p>", "", $desc);
        $desc = str_replace("<ul>  <li><em><span style=line-height: 1.5em;></p>", "", $desc);
        $desc = str_replace("<li><em><span style=line-height: 1.5em;>.</span></em></li>", "", $desc);
        $desc = str_replace("<li>", "<p>", $desc);
        $desc = str_replace("</li>", "</p>", $desc);
        $desc = str_replace("<span style=line-height: 1.5em;>", "", $desc);
        $desc = str_replace("<span>", "", $desc);
        $desc = str_replace("</span>", "</p>", $desc);
        $desc = str_replace("<em>", "", $desc);
        $desc = str_replace("</em>", "", $desc);
        $desc = str_replace("<ul>", "", $desc);
        $desc = str_replace("</ul>", "", $desc);
        $desc = str_replace("<h4><strong>СПЕЦИАЛЬНЫЕ УСЛОВИЯ ДЛЯ СП&nbsp; (подробности по запросу)</strong></h4>", "", $desc);
    }
    //die();
    //------SKHouse--------------------					
    if ($_SESSION["cat_id"] == 49) {
        $desc = str_replace($desc[0] . $desc[1] . $desc[2] . $desc[3] . $desc[4], "<p>" . $desc[0] . $desc[1] . $desc[2] . $desc[3] . $desc[4], $desc);

        $desc = str_replace('alt=""', '', $desc);

        for ($i = 0; $i < 60; $i++) {
            if ($i < 10) {
                $desc = str_replace('src="http://sk-house.ua/Images/data/blogs/0' . $i . '.png"', '', $desc);
                $desc = str_replace('src="http://sk-house.ua/Images/data/blogs/Sings%2F0' . $i . '.png"', '', $desc);
                $desc = str_replace('src="/Images/data/blogs/Sings%2F0' . $i . '.png"', '', $desc);
                $desc = str_replace('src="/Images/data/blogs/0' . $i . '.png"', '', $desc);
                $desc = str_replace('src="http://sk-house.ua/Images/data/blogs/image00' . $i . '.gif"', '', $desc);
            } else {
                $desc = str_replace('src="/Images/data/blogs/Sings%2F' . $i . '.png"', '', $desc);
                $desc = str_replace('src="http://sk-house.ua/Images/data/blogs/Sings%2F' . $i . '.png"', '', $desc);
                $desc = str_replace('src="/Images/data/blogs/' . $i . '.png"', '', $desc);
                $desc = str_replace('src="http://sk-house.ua/Images/data/blogs/' . $i . '.png"', '', $desc);
                $desc = str_replace('src="http://sk-house.ua/Images/data/blogs/image0' . $i . '.gif"', '', $desc);
            }
            $desc = str_replace('height="' . $i . '"', '', $desc);
            $desc = str_replace('width="' . $i . '"', '', $desc);
        }
        $im = strpos($desc, "<img");
        $im2 = strpos($desc, '" />');
        $imga = substr($desc, $im, $im2);
        //	$desc=str_replace($imga,"",$desc);			

        $desc = str_replace('title="чистка с использованием углеводорода, хлорного этилена, монофтортрихлорметана"', '', $desc);
        $desc = str_replace('title="только ручная стирка, температура – 30 градусов"', '', $desc);
        $desc = str_replace('title="нельзя отбеливать"', '', $desc);
        $desc = str_replace('title="строго придерживаться указанной температуры, не подвергать сильной механической обработке, полоскать, переходя постепенно к холодной воде, при отжиме в стиральной машине, ставить медленный режим вращения центрифуги"', '', $desc);
        $desc = str_replace('title="сушить при низкой температуре"', '', $desc);
        $desc = str_replace('title="гладить при средней температуре (до 130 градусов)"', '', $desc);
        $desc = str_replace('title="не выжимать,не сушить в стиральной машине"', '', $desc);
        $desc = str_replace('title="температура воды 30 градусов"', '', $desc);
        $desc = str_replace('title="гладить при низкой температуре (до 120 градусов)"', '', $desc);
        $desc = str_replace('style="font-family:Calibri, sans-serif;font-size:14.6666669845581px;line-height:16.8666667938232px;"', '', $desc);
        $desc = str_replace(' style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:16.15pt;background:white;"', '', $desc);
        $desc = str_replace('<img     />', '', $desc);
        $desc = str_replace('<span style="font-family:tahoma, arial, verdana, sans-serif,', '', $desc);
        $desc = str_replace(';font-size:11px;line-height:15.0699996948242px;">', '', $desc);
        $desc = str_replace("'Lucida Sans'", '', $desc);
        $desc = str_replace('<span style="font-size:13.5pt;font-family:', '', $desc);
        $desc = str_replace('<span style="font-size:11.5pt;font-family:', '', $desc);
        $desc = str_replace(';color:black;">', '', $desc);
        $desc = str_replace("'Times','serif'", '', $desc);
        $desc = str_replace("<strong>&nbsp;</strong>", '', $desc);
        $desc = str_replace('style="margin:0cm 0cm 12pt;"', '', $desc);
        $desc = str_replace('<span style="font-family:Arial, sans-serif;"', '', $desc);
        $desc = str_replace('style="margin-top:0cm;margin-right:0cm;margin-bottom:12.0pt;margin-left:0cm;"', '', $desc);
        $desc = str_replace('<span style="font-size:11pt;font-family:', '', $desc);
        $desc = str_replace(', sans-serif;">', '', $desc);
        $desc = str_replace("'Calibri Light'", '', $desc);
        $desc = str_replace("<p >></p>", '', $desc);
        //	$desc=str_replace("<strong>",'',$desc);		
        $desc = str_replace('src="/Images/data/blogs/Sings%2F04.png"', '', $desc);

        //$desc=str_replace("'Lucida Sans'",'',$desc);		
        $desc = str_replace("'Lucida Sans'", '', $desc);
        $desc = str_replace('<img     />', '', $desc);
        $desc = str_replace('<img    />', '', $desc);
        $ad = array("<span>", "</span>", '<span lang="EN-US" style="font-size:11.0pt;line-height:115%;font-family:\'Calibri\',\'sans-serif\';">',
            '<img alt="" src="/Images/data/blogs/Sings%2F18.png" title="температура воды 30 градусов" />',
            '<img alt="" src="/Images/data/blogs/09.png" title="нельзя отбеливать" />',
            '<img alt="" src="/Images/data/blogs/Sings%2F06.png" title="сушить при низкой температуре" />',
            '<img alt="" src="/Images/data/blogs/Sings%2F23.png" title="гладить при средней температуре (до 130 градусов)" />',
            '<img alt="" src="/Images/data/blogs/Sings%2F04.png" title="не выжимать,не сушить в стиральной машине" />',
            "<br />",
            '<span style="font-size:$str=preg_replace("/[^0-9a-zA-Z]/","",$str);11.0pt;line-height:115%;font-family:\'Calibri\',\'sans-serif\';">',
            '<img alt="" src="/Images/data/blogs/Sings%2F03.png" title="чистка с использованием углеводорода, хлорного этилена, монофтортрихлорметана" />',
            '<img alt="" src="/Images/data/blogs/Sings%2F22.png" title="гладить при низкой температуре (до 120 градусов)" />',
            '<span style="color:#444347;font-family:frizquadratacregular, Arial, Helvetica, sans-serif;font-size:14px;line-height:18px;">');
        $desc2 = str_replace($ad, "", $desc);
        $ad2 = array("S длина", "M длина", "L длинСостав:71166а", "XL длина", "</p></p>");
        $ad22 = array("</p><p>S длина", "</p><p>M длина", "</p><p>L длина", "</p><p>XL длина", "</p>");
        $desc33 = str_replace($ad2, $ad22, $desc2);
        $desc333 = str_replace("X</p><p>L", "</p><p>XL", $desc33);
        $desc = str_replace("</p></p>", "</p>", $desc333);
        $desc = str_replace("<p></p>", "", $desc);
        $desc = str_replace("<p><p>", "<p>", $desc);
        //$desc=str_replace("35","Соста́в 35",$desc);

        $w = strpos($desc, "%");
        if ($w !== false) {
            $wa = strstr($desc, "%", true);
            $w2 = strrpos($wa, "<p>");
            if (isset($_GET["step"])) {
                echo "<br>" . strrpos($wa, "<p>") . "-" . $w;
            }
            for ($i = $w2; $i < $w; $i++) {
                $pp.=$desc[$i];
            }
            $k = 0;
            for ($i = $w2; $i < $w; $i++) {
                if ($k == 3) {
                    $pp2.="<span class=sttt2 >Состав:</span> ";
                }
                $pp2.=$desc[$i];

                $k++;
            }
            $desc = str_replace($pp, $pp2, $desc);
        }
    }
    //----OlisStyle-----------------
    if ($_SESSION["cat_id"] == 58) {
        $desc = strip_tags($desc);
        $desc = str_replace("Характеристика", "", $desc);
        $desc = str_replace("Цвет", "<p>Цвет:", $desc);
        $desc = str_replace("Размеры", "</p><p>Размеры:", $desc);
        $desc.="</p>";
    }
    //----Majaly-----------------
    if ($_SESSION["cat_id"] == 65) {

        $desc = str_replace('<p>  Цвет:     <i class="b-product-info__value-icon" style="background-image: url(http://images.ua.prom.st/18119382_w16_h16_images_1.jpg)"></i>   Разные цвета   </p>', "", $desc);
        $desc = str_replace(' <i class="b-product-info__value-icon" style="background-image: url(https://images.ua.prom.st/18119412_w16_h16_0000ff_sinij.png)"></i>', "", $desc);
        $desc = str_replace(' <i class="b-product-info__value-icon" style="background-image: url(https://images.ua.prom.st/38264938_w16_h16_12belyj.png)"></i>', "", $desc);
        $desc = str_replace('<tr> <th class="b-product-info__header" colspan="2">Основные</th> </tr>', "", $desc);
        $desc = str_replace('<span class="icon-help" id="product-attribute-0-9"> </span>', "", $desc);
        $desc = str_replace('</td> <td class="b-product-info__cell">', ":", $desc);
        $desc = str_replace('<tr> <td class="b-product-info__cell">', "<p>", $desc);
        $desc = str_replace('</td> </tr>', "</p>", $desc);
        $desc = str_replace('<table class="b-product-info"> ', "", $desc);
        $desc = str_replace('</table>', "", $desc);
        $desc = str_replace('<span class="icon-help" id="product-attribute-0-13"> </span>', "", $desc);
        $desc = str_replace('  :', ":", $desc);
        $desc = str_replace(' :', ":", $desc);
        $desc = str_replace('<span class="b-product-info__value">', "", $desc);
        $desc = str_replace('<span class="icon-help" id="product-attribute-0-13">', "", $desc);
        $desc = str_replace('</span>', "", $desc);
        $desc = str_replace('<span class="icon-help" id="product-attribute-0-0">', "", $desc);
        $desc = str_replace(' <span class="icon-help" id="product-attribute-0-0"> </span>', "", $desc);
        $desc = str_replace('<tr> <td class="b-product-info__cell">  Производитель <span class="icon-help" id="product-attribute-0-0"> </span>  </td> <td class="b-product-info__cell">       Majaly   </td> </tr>', "", $desc);

        $desc = str_replace('<p> Минимальный заказ: Украина (5шт), Россия (10шт) </p>', "", $desc);
        $desc = str_replace('<p> Минимальный заказ: Украина (5шт), Россия (10шт) любых моделей и размеров </p>', "", $desc);
        $desc = str_replace('Основные атрибуты', "", $desc);
        $desc = str_replace('Дополнительные характеристики', "", $desc);
    }
    //-------Nelli_co----------------
    if ($_SESSION['cat_id'] == 62) {
        $desc = str_replace("<p><strong>Состав:</strong></p><p>", "<p><span class=sttt2 >Состав: </span>", $desc);
        $ne = strpos($desc, "<p><strong>Цвет:");
        $ne2 = strrpos($desc, "<strong>Размеры:</strong>");
        $txtne = substr($desc, $ne, $ne2);
        $desc = str_replace($txtne, "", $desc);

        $txtne = str_replace("<strong>", "", $txtne);
        $txtne = str_replace("</strong>", "", $txtne);
        $txtne = str_replace("</p><p>", ", ", $txtne);
        $txtne = str_replace("Цвет:,", "Цвет:", $txtne);
        $txtne = str_replace(", Размеры:,", "</p><p> Размеры:", $txtne);

        $nes = strpos($desc, "%");
        $nes2 = strrpos($desc, "%");
        $dnes = substr($desc, $nes, $nes2);
        $dnes2 = str_replace("</p><p>", " ", $dnes);
        $desc = str_replace($dnes, $dnes2, $desc);
        $desc.=$txtne;
    }
    //-------FStyle----------------
    if ($_SESSION['cat_id'] == 63) {
        $desc = "<p>" . $desc;

        $desc = str_replace("<h4>Наличие : Есть на складе</h4>", "", $desc);
        $desc = str_replace("<h3>Описание</h3>", "", $desc);
        $desc = str_replace("<h3>", "<p>", $desc);
        $desc = str_replace("</h3>", "</p>", $desc);
        $desc = str_replace(' style="text-align: justify;"', "", $desc);
        $desc = str_replace("<p>Детали</p>  <p>", "<p>Детали: ", $desc);
        $desc = str_replace("<p>Детали</p> <p>", "<p>Детали: ", $desc);
        $desc = str_replace("<p> </p>", "", $desc);
        $desc = str_replace("</div>", "", $desc);
        //	$desc=str_replace("Платье Andrea Crocetta","<p>Платье Andrea Crocetta",$desc);
        //	$desc=str_replace("Рубашка Andrea Crocetta","<p>Рубашка Andrea Crocetta",$desc);

        $fpos = strpos($desc, "<p>Детали:");
        if ($fpos !== false) {
            $ss = strstr($desc, "<p>Детали:");
            $ss = strstr($ss, "</p>", true);
            $desc = str_replace($ss, "", $desc);
        }

        $pf = strpos($desc, '<div class="ctext">');
        $pf2 = strpos($desc, '<p>');
        $tf = substr($desc, $pf, $pf2);
        $desc = str_replace($tf, "", $desc);

        $af = strpos($desc, "<a");
        $af2 = strpos($desc, '</a>');
        $ta = substr($desc, $af, $af2);
        $ta2 = strip_tags($ta);
        $desc = str_replace($ta, $ta2, $desc);
        $desc = str_replace("Состав:", "</p><p>Состав:", $desc);

        $npos = strpos($desc, 'Скрыть');
        if ($npos !== false) {
            $n = 1;
        }

        $desc = str_replace("<p></p><p>", "<p>", $desc);
        $desc = str_replace("<p></p>", "", $desc);
        $desc.="</p>";
    }
    //----B1-----------------
    if ($_SESSION["cat_id"] == 64) {
        $art = strpos($desc, "Art Millano");
        if ($art !== false) {
            $artCatID = 66;
            //$_SESSION["cat_id"] = 66;
            if (isset($_GET["step"])) {
                echo "<br>Yes Art Millano<br>";
            }
        }
        $artArr = array("CROMIA", "CHIARUGI", "EMFACI", "Giorgio Armani", "Montblanc", "TAVECCHI", "GIRONACCI", "CERRUTI");
        for ($i = 0; $i < count($artArr); $i++) {
            $art = strpos($desc, $artArr[$i]);
            if ($art !== false) {
                if (isset($_GET["step"])) {
                    echo "<br/>RETURN!";
                }
                return;
            }
        }
        $desc = str_replace('<ul class="params nobullet">', "", $desc);
        $desc = str_replace("</ul>", "", $desc);
        $desc = str_replace("</li>", "", $desc);
        $desc = str_replace("<li>", "", $desc);
        $desc = str_replace("<span>Основные характеристики</span>", "", $desc);
        $desc = str_replace("<span>", "<span class=sttt2 >", $desc);
        $desc = str_replace("</span>", ": </span>", $desc);
        $desc = str_replace("</span> <p>", "</span>", $desc);
        $desc = str_replace("<span", "<p><span", $desc);
        $desc = str_replace('<p><span class=sttt2 >Бренд: </span> B1 </p>', "", $desc);
        $desc = str_replace('<p><span class=sttt2 >Бренд: </span> Art Millano </p>', "", $desc);
        $desc = str_replace('<p><span class=sttt2 >Бренд: </span> CROMIA </p>', "", $desc);
        $desc = str_replace('<p><span class=sttt2 >Бренд: </span> CHIARUGI </p>', "", $desc);
        $desc = str_replace('<p><span class=sttt2 >Бренд: </span> Giorgio Armani </p>', "", $desc);
        $desc = str_replace('<p><span class=sttt2 >Бренд: </span> Montblanc </p>', "", $desc);
        $desc = str_replace('<p><span class=sttt2 >Бренд: </span> TAVECCHI </p>', "", $desc);
        $desc = str_replace('<p><span class=sttt2 >Бренд: </span> GIRONACCI </p>', "", $desc);
        $desc = str_replace('<p><span class=sttt2 >Бренд: </span> CERRUTI </p>', "", $desc);
        $desc = str_replace('<p><span class=sttt2 >Пол: </span> Женский </p>', "", $desc);
        $desc = str_replace('<p><span class=sttt2 >оперативная доставка: </span> Экспресс - отправка </p>', "", $desc);
        if ($artCatID == 66) {
            $descForFilter = explode("<p>", $desc);
            $descNew = '';
            $needle = [
                'Цвет', 'Ткани', 'фото', 'Состав', 'Тема', 'Материал', 'Форма'
            ];
            foreach ($descForFilter as $forFilter) {
                foreach ($needle as $singleNeedle) {
                    if (strstr($forFilter, $singleNeedle)) {
                        $descNew .= "<p>" . $forFilter;
                    }
                }
            }
            $desc = $descNew;
        }
        //-------Color and Size, Наличии-----------------------
        $nonal2 = $html->find(".color-selector", 0)->plaintext;

        //	echo $nonal."<br/>";
        $nonal2 = str_replace("наличии", "наличии.", $nonal2);
        $pushCol = "<p><span class=sttt2 >Цвет:</span>";
        $col = explode(".", $nonal2);
        for ($i = 0; $i < count($col); $i++) {
            $posCol = strpos($col[$i], "В наличии");
            if ($posCol !== false)
                $pushCol.=", " . $col[$i];
        }
        $pushCol = str_replace("Цвет:</span>,", "Цвет:</span>", $pushCol);
        $pushCol = str_replace(" В наличии", "", $pushCol);
        if ($pushCol == "") {
            $n = 1;
            if (isset($_GET["step"])) {
                echo "<br/>нет наличии";
            }
        } else {
            //	echo $pushCol;
        }
        //------------------------------

        $tbeg = strstr($desc, "<p><span class=sttt2 >Цвет: </span>");
        if ($tbeg) {
            $beg = strpos($tbeg, "<p>");
            $beg1 = strpos($tbeg, "<p>", 1);
            $sub = substr($tbeg, $beg, $beg1);

            $desc = str_replace($sub, $pushCol . "</p>", $desc);
        } else {
            $desc.=$pushCol . "</p>";
        }
        //echo "col: ".$pushCol;
        if ($name == $cod) {
            $pp = strpos($desc, "Стиль");
            if ($pp !== false) {
                $namee = strstr($desc, '<p><span class=sttt2 >Стиль:');
                $namee = strstr($namee, "</p>", true);

                $desc = str_replace($namee . "</p>", "", $desc);
                $namee = str_replace("<p><span class=sttt2 >Стиль: </span> ", "", $namee);
                $namee = str_replace("</p>", "", $namee);
                $namee = ucfirst_mb($namee);
                $name = $namee;
                if (isset($_GET["step"])) {
                    echo "Name: " . $name;
                }
            }
        } else {
            $namee = strstr($desc, '<p><span class=sttt2 >Стиль:');
            $namee = strstr($namee, "</p>", true);
            $desc = str_replace($namee . "</p>", "", $desc);
        }

        $fpos = strpos($desc, "Форма");
        if ($fpos !== false) {
            $ff = strstr($desc, '<p><span class=sttt2 >Форма:');
            $ff = strstr($ff, "</p>", true);
            $desc = str_replace($ff, "", $desc);
        }
    }
    //----Sergio Torri-----------------
    if ($_SESSION["cat_id"] == 85) {
        $desc = str_replace('<div class="product-short-description">', "<p>", $desc);
        $desc = str_replace("<br />", "</p><p>", $desc);
        $desc = str_replace("</div>", "</p>", $desc);
    }
    //------Vitality-------------------					
    if ($_SESSION["cat_id"] == 88 || $_SESSION["cat_id"] == 205) {
        do {
            $fff = 0;
            $sstyle = strstr($desc, ' style="');
            $sstyle = strstr($sstyle, ';"', true);
            $sstyle.=';"';
            $desc = str_replace($sstyle, "", $desc);

            if (strpos($desc, 'style') !== false) {
                $fff = 1;
                //echo "Style";				
            }
        } while ($fff == 1);


        $desc = str_replace('<p><span><span>Скачать все фото товара можно <span><span><a href="http://vitality-opt.com.ua/news/detalle/index/id/6/.html/" target="_blank">ЗДЕСЬ</a></span></span> </span></span></p>  <p><span><span>Условия сотрудничества для стран СНГ можно найти <span><span><a href="http://vitality-opt.com.ua/news/detalle/index/id/8/.html/" target="_blank">ЗДЕСЬ</a></span></span> </span></span></p>  <p><span><span>Условия сотрудничества для Украины опубликованы <span><span><a href="http://vitality-opt.com.ua/news/detalle/index/id/7/.html/" target="_blank">ЗДЕСЬ</a></span></span></span></span></p>', "", $desc);
        $desc = str_replace('<p><span>Скачать все фото товара можно </span><span><span><a href="http://vitality-opt.com.ua/news/detalle/index/id/6/.html/" target="_blank">ЗДЕСЬ</a></span></span></p>  <p><span><span>Условия сотрудничества для стран СНГ можно найти <span><span><a href="http://vitality-opt.com.ua/news/detalle/index/id/8/.html/" target="_blank">ЗДЕСЬ</a></span></span> </span></span></p>  <p><span><span>Условия сотрудничества для Украины опубликованы <span><span><a href="http://vitality-opt.com.ua/news/detalle/index/id/7/.html/" target="_blank">ЗДЕСЬ</a></span></span></span></span></p>', "", $desc);
        $desc = str_replace('<p><span lang="RU">Скачать все фото товара можно <a href="http://vitality-opt.com.ua/news/detalle/index/id/6/.html/" target="_blank">ЗДЕСЬ</a> </span></p>  <p><span lang="RU">Условия сотрудничества для стран СНГ можно найти <a href="http://vitality-opt.com.ua/news/detalle/index/id/8/.html/" target="_blank">ЗДЕСЬ</a> </span></p>  <p><span lang="RU">Условия сотрудничества для Украины опубликованы <a href="http://vitality-opt.com.ua/news/detalle/index/id/7/.html/" target="_blank">ЗДЕСЬ</a></span></p>', "", $desc);
        $desc = str_replace('<div class="std">', "", $desc);
        $desc = str_replace('<div>', "<p>", $desc);
        $desc = str_replace('</div>', "</p>", $desc);
        $desc = str_replace('<b>', "<span class=sttt2 >", $desc);
        $desc = str_replace('</b>', "</span>", $desc);
        $desc = str_replace('</br>', "", $desc);
        $desc = str_replace('<br>', "", $desc);
        $desc = str_replace('<br/>', "", $desc);

        // Divide category Brenda
        if (strpos($desc, "Бренд") !== false) {
            $bra = strstr($desc, '<p>');
            $bra = strstr($bra, "</p>", true);
            $desc = str_replace($bra, "", $desc);
            $bra = str_replace("</span>", "", $bra);
            $bra = str_replace("</p>", "", $bra);
            $bra = str_replace("<p>", "", $bra);
            $bra = str_replace("<span class=sttt2 >", "", $bra);
            $bra = str_replace("Бренд: ", "", $bra);
            if ($bra[0] == " ") {
                $bra = substr($bra, 1, strlen($bra));
            }
            //$bra=str_replace(" ","",$bra);
            //echo "<br>Brenda: ".$bra;
        }

        if (strpos($desc, "Страна производитель") !== false) {
            $bra2 = strstr($desc, '<p>');
            $bra2 = strstr($bra2, "</p>", true);
            $desc = str_replace($bra2, "", $desc);
            $bra2 = str_replace("</span>", "", $bra2);
            $bra2 = str_replace("</p>", "", $bra2);
            $bra2 = str_replace("<p>", "", $bra2);
            $bra2 = str_replace("<span class=sttt2 >", "", $bra2);
            $bra2 = str_replace("Страна производитель: ", "", $bra2);
            if ($bra2[0] == " ") {
                $bra2 = substr($bra2, 1, strlen($bra2));
            }
            //$bra=str_replace(" ","",$bra);
            //echo "<br>Brenda: ".$bra;
        }
    }
    //------Aliya-------------------					
    if ($_SESSION["cat_id"] == 86) {
        if ($desc == "") {
            $descc = $html->find("#tab-description");
            foreach ($descc as $value) {
                $desc = $value->innertext;
                $desc = str_replace("Под заказ до 3 недель", "<p>Под заказ до 3 недель</p>", $desc);
            }
        }

        do {
            $fff = 0;
            $sstyle = strstr($desc, ' style="');
            $sstyle = strstr($sstyle, ';"', true);
            $sstyle.=';"';
            $desc = str_replace($sstyle, "", $desc);

            if (strpos($desc, 'style') !== false) {
                $fff = 1;
                //echo "Style";				
            }
        } while ($fff == 1);

        //	echo $desc;
        $iimg = strstr($desc, "<img");
        $iimg = strstr($iimg, "/>", true);
        $desc = str_replace($iimg, "", $desc);
        $iimg = strstr($desc, "<img");
        $iimg = strstr($iimg, "/>", true);
        $desc = str_replace($iimg, "", $desc);


        $desc = str_replace(' style="margin: 15px 0px 0px; padding: 0px; border: 0px; outline: 0px; clear: both; overflow: hidden; color: rgb(47, 47, 47); font-family: Arial, Helvetica, sans-serif; line-height: 14.7719993591309px; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;"', "", $desc);
        $desc = str_replace(' style="margin: 0px 0px 4px; padding: 0px; border: 0px; outline: 0px; background: transparent;"', "", $desc);
        $desc = str_replace(' style="margin: 0px; padding: 0px; border: 0px; outline: 0px; background: transparent;"', "", $desc);
        $desc = str_replace('<div>  <img alt="" src="http://aliya.com.ua/image/data/Razmerka/yana, eva,djuliet, polli, natulya, yasya, tyf Classica, tyf Sofi, bot Yulia, veronica, shikers luks and premium,iren, elya.jpg" style="width: 388px; height: 237px;" /><img alt="" src="http://aliya.com.ua/image/data/Razmerka2.jpg" style="width: 327px; height: 400px;" /></div>', "", $desc);
        $desc = str_replace('<img alt="" src="http://aliya.com.ua/image/data/Razmerka/riana, djina, snejana.jpg" style="width: 415px; height: 219px;" />  <img alt="" src="http://aliya.com.ua/image/data/Razmerka2.jpg" style="width: 327px; height: 400px;" />', "", $desc);
        $desc = str_replace('<span class="apple-converted-space">', "", $desc);
        $desc = str_replace(' style="margin: 0px 0px 4px; padding: 0px; border: 0px; outline: 0px; color: rgb(47, 47, 47); font-family: Arial, Helvetica, sans-serif; line-height: 14.7719993591309px; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;"', "", $desc);
        $desc = str_replace('<p>  Модель может быть произведена по Вашему желанию из кожи и замши любых цветов.<o:p></o:p></p>', "", $desc);

        $desc = str_replace('<a alt="Елка 41 см N03206" href="http://cityadspix.com/tsclick-EBQRILTK-VRMIQUYF?url=http%3A%2F%2Fwww.enter.ru%2Fproduct%2Fhousehold%2Fmishura-winter-wings-3-h-1000-sm-2040701009467&amp;sa=6&amp;sa1=&amp;sa2=&amp;sa3=&amp;sa4=&amp;bt=20&amp;pt=9&amp;lt=2&amp;tl=3&amp;im=Mjc3NS0wLTE0MTc1MjA3NzYtMTE5OTI4NzQ%3D&amp;fid=NDQ2MjkxMjc1&amp;prdct=380e3a0f3a00360c38&amp;kw=41%20%D1%81%D0%BC" style="margin: 0px; padding: 0px; border: 0px; outline: 0px; color: rgb(0, 0, 0); text-decoration: none; transition: color 1s; -webkit-transition: color 1s; background: transparent;" target="_blank" title="Елка 41 см N03206">', "", $desc);
        $desc = str_replace(' style="margin: 0px; padding: 0px; border: 0px; outline: 0px; color: rgb(47, 47, 47); font-family: Arial, Helvetica, sans-serif; line-height: 14.7719993591309px; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;"', "", $desc);
        $desc = str_replace(' style="color: rgb(47, 47, 47); font-family: Arial, Helvetica, sans-serif; line-height: 14.7719993591309px;"', "", $desc);
        $desc = str_replace(' style="margin: 0px; padding: 0px; border: 0px; outline: 0px; font-size: 12px; background: transparent;"', "", $desc);
        $desc = str_replace(' style="margin: 0px 0px 4px; padding: 0px; border: 0px; outline: 0px; font-size: 12px; color: rgb(47, 47, 47); font-family: Arial, Helvetica, sans-serif; line-height: 14.7719993591309px; background-image: initial; background-attachment: initial; background-size: initial; background-origin: initial; background-clip: initial; background-position: initial; background-repeat: initial;"', "", $desc);
        $desc = str_replace(' style="margin: 0px; padding: 0px; border: 0px; outline: 0px; font-size: 12px; line-height: 1.231; background: transparent;"', "", $desc);

        $desc = str_replace('<a alt="Елка 41 см N03206" href="http://cityadspix.com/tsclick-EBQRILTK-VRMIQUYF?url=http%3A%2F%2Fwww.enter.ru%2Fproduct%2Fhousehold%2Fmishura-winter-wings-3-h-1000-sm-2040701009467&amp;sa=6&amp;sa1=&amp;sa2=&amp;sa3=&amp;sa4=&amp;bt=20&amp;pt=9&amp;lt=2&amp;tl=3&amp;im=Mjc3NS0wLTE0MTc1Mzg3MjQtMTAxNzU4NjM%3D&amp;fid=NDQ2MjkxMjc1&amp;prdct=380e3a0f3a00360c38&amp;kw=41%20%D1%81%D0%BC" target="_blank" title="Елка 41 см N03206">', "", $desc);
        //$desc=str_replace("'Lucida Grande'",'"Lucida Grande"',$desc);				
        //	$desc=str_replace(' style="color: rgb(117, 113, 108); font-family: 'Open Sans', sans-serif; line-height: 17.142858505249px;"',"",$desc);	 	
        //	$desc=str_replace(' style="box-sizing: border-box; color: rgb(0, 0, 0); font-family: 'Lucida Grande', Arial, tahoma, verdana, sans-serif; line-height: 16.7999992370605px;"',"",$desc);	 	
        $desc = str_replace("<o:p></o:p>", "", $desc);
        //			$desc=str_replace("Модель может быть произведена по Вашему желанию из кожи и замши любых цветов.","",$desc);		
        $desc = str_replace("<em>", "", $desc);
        $desc = str_replace("</em>", "", $desc);
        $desc = str_replace("</a>", "", $desc);
        $desc = str_replace("<p>  </p>", "", $desc);
        $desc = str_replace(" </p>", "", $desc);
        $desc = str_replace(" <p>", "", $desc);
        $desc = str_replace(" </span>", "", $desc);
        $desc = str_replace("</span>", "", $desc);
        $desc = str_replace("<span>", "", $desc);
        $desc = str_replace("<strong>", "</p><p><span class=sttt2 >", $desc);
        $desc = str_replace("<b>", "</p><p><span class=sttt2 >", $desc);
        $desc = str_replace("</strong>", "</span>", $desc);
        $desc = str_replace("</b>", "</span>", $desc);
        $desc = str_replace("<br />", "</p>", $desc);
        $desc = str_replace("</p>  <p>", "<p>", $desc);
        $desc = str_replace('<div id="category_description">', "", $desc);
        $desc = str_replace("</div>", "", $desc);
        $desc = str_replace("</p>  </p>", "</p>", $desc);
        $desc = str_replace("</p>   </p>", "</p>", $desc);
        $desc = str_replace("<p>  </p><p>", "<p>", $desc);
        $desc = str_replace("</p>  В", "</p>", $desc);
        $desc = str_replace("ысота от подошвы", "Bысота от подошвы", $desc);
        $desc = str_replace("Под заказ от 2 до 3 недель.", "<p>Под заказ от 2 до 3 недель.</p>", $desc);


        $desc = str_replace("  />  />", "", $desc);
        $desc = str_replace("/>/>", "", $desc);
        $desc = str_replace("/>", "", $desc);
        $desc = str_replace("6-37", "36-37", $desc);
        $desc = str_replace("336-37", "36-37", $desc);

        if (strpos($desc, '<a') !== false) {
            $aaaa = strstr($desc, "<a");
            $aaaa = strstr($aaaa, ">", true);
            $aaaa.=">";
            $desc = str_replace($aaaa, "", $desc);
        }
    }

    //----HelenLaven-----------------
    if ($_SESSION["cat_id"] == 217) {
        $desc = str_replace('<div class="c_arts_text">', "", $desc);
        $desc = str_replace('</div>', "", $desc);
        $desc = str_replace('style="text-align: left;"', "", $desc);
        $desc = str_replace('Состав:', "<span class=sttt2 >Состав:</span>", $desc);

        $nn = strstr($desc, "<p");
        $nn = strstr($nn, "</p>", true);
        $nn.="</p>";
        $nnn = $nn;


        //--Set name---
        $nn2 = str_replace(" ", "", $name);
        $nn2 = ucfirst_mb($nn2);
        if (strpos($desc, $nn2 . ":") === false)
            if (strpos($desc, $nn2) !== false) {
                $nn = str_replace("<p", "", $nn);
                $nn = str_replace("</p>", "", $nn);
                $nn = str_replace(".", "", $nn);
                $nn = str_replace(">", "", $nn);
                $name = $nn;
            }

        $desc = str_replace($nnn, "", $desc);
    }
    //----Dembo House----------------
    if ($_SESSION["cat_id"] == 218) {
        $dh = strstr($desc, "</p>", true);
        $dh.="</p>";
        $desc = str_replace($dh, "", $desc);

        $desc = str_replace("<b>", "<span class=sttt2 >", $desc);
        $desc = str_replace("</b>", "</span>", $desc);
    }

    //----Jhiva----------------
    if ($_SESSION["cat_id"] == 219) {
        $desc = str_replace("<p>   </p>", "", $desc);
        $desc = str_replace("'", "\'", $desc);
    }
    if (isset($_GET["step"])) {
        echo $desc;
    }
    $query2 = "UPDATE `shop_commodity` SET `com_fulldesc`='{$desc}' WHERE `from_url`='{$new_url}';";
    mysql_query($query2);
    //		mysql_query($query2) or die("Error desc text");
    //	echo $query2;
    //die();	
    //echo $_SESSION["price"];die();
//===================Нет в наличии=================================
    $nonal = $html->find($_SESSION["no_nal"], 0)->plaintext;
    //$n=0;	
    //------Fashioup------------
    if ($_SESSION["cat_id"] == 2) {
        switch ($nonal) {
            case 'Нет в наличии':
                $n = 1;
                break;
            case 'Есть в наличии':
                $n = 0;
                break;
            case 'Ограниченное количество':
                $n = 0;
                break;
        }
    }
    if ($_SESSION["cat_id"] == 47) {
        $sn = strpos($nonal, "Нет в наличии!");
        if ($sn !== false) {
            $n = 1;
        }
    }
    if ($_SESSION["cat_id"] == 49) {
        $sn = strpos($nonal, "Нет в наличии");
        if ($sn !== false) {
            $n = 1;
        }
        $orn = $html->find('.color', 0)->plaintext;
        $orn2 = strpos($orn, "Цвет");
        if ($orn2 === false) {
            $n = 1;
        } else {
            if (isset($_GET["step"])) {
                echo "yes";
            }
        }
    }
    if ($_SESSION["cat_id"] == 48) {
        $sn = strpos($nonal, "нет на складе");
        if ($sn !== false) {
            $n = 1;
        }
    }
    //------Crisma-------------------					
    if ($_SESSION["cat_id"] == 87) {
        if ($cod == "") {
            $n = 1;
        }
    }
    //echo "<br>{$nonal}";
//	die();
//=====================================================
    if ($_SESSION["cat_id"] != 15)
        if (!is_numeric($price) || $price == 0) {
            $query = "
				UPDATE `shop_commodity` 
				SET  
				`commodity_visible`='0',
				`commodity_price`='{$price}'
				WHERE  `from_url`='{$new_url}'
				;";

            mysql_query($query);
            return "";
        }
//=Наценка===============================
    if ($_SESSION["per"] != 0) {
        $plus = strpos($_SESSION["per"], '+');
        $minus = strpos($_SESSION["per"], '-');
        $p = strpos($_SESSION["per"], '%');
        $dil = strpos($_SESSION["per"], '/');
    }
    if ($_SESSION["cat_id"] == 48) {
        $priceSL = $elementSL->children(0)->children(1)->children(0)->children(6)->plaintext;
        $prices = explode('грн', $priceSL);
        foreach ($prices as $cena) {
            if (stristr($cena, 'Розница')) {
                $price = preg_replace('/[^0-9]/', '', $cena);
            }
            if (stristr($cena, 'Опт')) {
                $price2 = preg_replace('/[^0-9]/', '', $cena);
            }
        }
    }
    //------Nelli_co------------------					
    if ($_SESSION["cat_id"] == 62) {
        if ($price2 == 0) {
            $price2 = $price / $_SESSION["per"];
        }
    }

    if (($_SESSION["cat_id"] == 2) || ($_SESSION["cat_id"] == 3) || ($_SESSION["cat_id"] == 5) || ($_SESSION["cat_id"] == 14)) {
        $price2 = $price / $_SESSION["per"];
    }
    if (isset($_GET["step"])) {
        echo "<br/>Opt2: {$price2}<br/>";
    }
    //------Meggi--------------------					
    //	if($_SESSION["cat_id"]==42){
    //		$price2=$price;
    /* $query="
      UPDATE `shop_commodity`
      SET
      `commodity_price`='{$price}',
      `commodity_price2`='{$price2}'
      WHERE  `from_url`='{$new_url}'
      ;";

      mysql_query($query); */
    //	}
    //------Sellin--Cardo------------------					
    /* if($_SESSION["cat_id"]==23 || $_SESSION["cat_id"]==1 || $_SESSION["cat_id"]==63){
      $per=$_SESSION["per"];
      $sea=array("-","%");
      $per2=str_replace($sea,"",$per);

      $price2=(($price/100)*$per)+$price;
      }
     */
    if ($_SESSION["cat_id"] == 63 || $_SESSION["cat_id"] == 23) {
        if ($_SESSION["cat_id"] == 63) {
            $price2 = $html->find('span.price span.crossed', 0)->plaintext; //            
            $price2 = strip_tags($price2);
            $price2 = str_replace(' ', '', $price2);
            $price2 = (int) preg_replace('/[^0-9]/', '', $price2);
            if ($price2) {
                $price2 = ceil($price2 * 0.6);
                if ($price2 > $price) {
                    $price2 = $price;
                }
            } else {
                $price2 = ceil($price * 0.6);
            }
        } else {
            $price2 = ceil($price * 0.6);
        }
    }
    //------OlisStyle------------------					
    if ($_SESSION["cat_id"] == 58) {
        $per = $_SESSION["per"];
        $per = str_replace("*", "", $per);
        $price2 = $price;
        $price = $price2 * $per + 5;
    }
    //------Agio-Z--Seventeen--FlFashion----------------					
    if (($_SESSION["cat_id"] == 45) || $_SESSION["cat_id"] == 47 || $_SESSION["cat_id"] == 46) {
        $per = $_SESSION["per"];
        $price2 = $price;
        $price+=$per;
    }
    //------Lenida--Meggi--Majaly-Vitality-------------					
    if ($_SESSION["cat_id"] == 49 || $_SESSION["cat_id"] == 218 || ($_SESSION["cat_id"] == 88 || $_SESSION["cat_id"] == 205) || ($_SESSION["cat_id"] == 16) || ($_SESSION["cat_id"] == 42) || ($_SESSION["cat_id"] == 43) || ($_SESSION["cat_id"] == 65)) {
        $price2 = $price;
        $per = $_SESSION["per"];
        $sea = array("+", "%");
        $per2 = str_replace($sea, "", $per);

        $price = ($price / 100) * $per + $price2;
    }
    if ($_SESSION["cat_id"] == 1) {
        $price2 = $price;
        $per = $_SESSION["per"];
        $sea = array("%");
        $per2 = str_replace($sea, "", $per);
        $per2 = intval($per2);

        $price = ($price / 100) * $per + $price2;
    }
    //-----B1-------------					
    if ($_SESSION["cat_id"] == 64 || $_SESSION["cat_id"] == 66) {
        $price2 = $price;
    }
    // //------Crisma-------------------					
    // if($_SESSION["cat_id"]==87){
    // 	$price2=$price;
    // 	$price=0;
    // }
    //------Dajs-------------------					
    if ($_SESSION["cat_id"] == 215) {
        $price2 = $price;
    }
//------------------------------------------------------------------------------
//                           Ended price into bd
//------------------------------------------------------------------------------
    $query = "UPDATE `shop_commodity` SET `commodity_price`='{$price}', `commodity_price2`='{$price2}' WHERE  `from_url`='{$new_url}';";
    mysql_query($query) or die("Error price");

    //	die();
    if (isset($_GET["step"])) {
        echo "Per: " . $_SESSION["per"] . "<br/>";
        echo "Price: {$price}";
        echo "<br/>Opt: {$price2}<br/>";
    }
    //	die();
    $uptext.="Per: " . $_SESSION["per"] . "<br/>";
    $uptext.="Price: {$price}";
    $uptext.="<br/>Opt: {$price2}<br/>";
//------------------------------------------------------		
// ArtMIlano
    if ($artCatID == 66) {
        $nameFind = $html->find('ul[class="params nobullet"] li');
        $name = '';
        foreach ($nameFind as $nameCheck) {
            if (strpos($nameCheck->plaintext, "Форма")) {
                $name = trim(str_replace("Форма ", "", $nameCheck->plaintext));
            }
        }
        if ($name == "Прямоугольная" || $name == "Саквояж") {
            $name = $cod;
        }
        if ($name == "" || $name == "Трикотаж") {
            $menuFind = $html->find('ul[class="breadcrumbs nobullet"] li');
            $menuName = $menuFind[count($menuFind) - 1];
            $name = trim($menuName->plaintext);
            $name = str_replace("Броши", "Брошь", $name);
            $name = str_replace("Воротники", "Воротник", $name);
            $name = str_replace("Браслеты", "Браслет", $name);
            $name = str_replace("Подвески", "Подвеска", $name);
        }
    }
    $queryn = "UPDATE `shop_commodity` SET `com_name`='{$name}' WHERE `from_url`='{$new_url}';";
    mysql_query($queryn);
    if (isset($_GET["step"])) {
        echo "Title: " . $name . "<br/>";
    }
    //die();	
//------------------------------------------------------	
    $q = mysql_query("SELECT * FROM `shop_commodity`") or die("Error select");

    //===========Search========================

    for ($i = 0; $i < mysql_num_rows($q); $i++) {
        $f = mysql_fetch_array($q);
        if ($new_url == $f['from_url']) {
            //	echo "ComId: ".$f['commodity_ID']."<br/>";
            $comid = $f['commodity_ID'];
        }
    }
//------------------------------------------------------------------------------
//                      S&L select color and size
//------------------------------------------------------------------------------
    $selColSize = "";
    $selSize2 = array();

    $selSize = $html->find($_SESSION["sizeCol"]);
    //--------Fashioup--Meggi-FStyle-Seventeen-flfashion-----------------
    if ($_SESSION["cat_id"] == 1 || $_SESSION["cat_id"] == 2 || $_SESSION["cat_id"] == 42 || $_SESSION["cat_id"] == 46 || $_SESSION["cat_id"] == 47 || $_SESSION["cat_id"] == 63 || $_SESSION["cat_id"] == 65) {
        foreach ($selSize as $key => $a) {
            $selSize2[$key].=$a->plaintext;
        }
        $selSize = implode(";", $selSize2);
        $selSize = str_replace("Без пояса", "", $selSize);
        $selSize = str_replace("С поясом (+25грн.)", "", $selSize);
        $selSize = str_replace(";;", "", $selSize);
    }

    //--------B1-------------------
    if ($_SESSION["cat_id"] == 64) {
        echo "selSize";
        $selSize = "";
        //$query33="UPDATE `shop_commodity` SET `com_sizes`='', `select_color`=''  WHERE  `from_url`='{$new_url}';";
        //mysql_query($query33) or die("Error select2"); 
        //		foreach($selSize as $key=>$a){
        //			$selSize2[$key].=$a->plaintext;
        //		}	
        //		$selSize=implode(";", $selSize2);
        //		echo "S: ".$selSize->plaintext;
        $pp = strpos($desc, "Цвет:");
        if ($pp != false) {
            $selB = strstr($desc, "Цвет:");
            $posB = strpos($selB, "</p>");
            $subb = substr($selB, 0, $posB);
            $subb = str_replace("Цвет: ", "", $subb);
            $subb = str_replace("Цвет:", "", $subb);
        } else {
            $selB = strstr($desc, "Цвет");
            $posB = strpos($selB, "</p>");
            $subb = substr($selB, 0, $posB);
            $subb = str_replace("Цвет фурнитуры: ", "", $subb);
            $subb = str_replace("Цвет фурнитуры:", "", $subb);
        }
        $subb = str_replace(", ", "|", $subb);
        $subb = str_replace(",", "|", $subb);

        $subb2 = explode("|", $subb);
        $selColSize.="<select id=id_choos2_{$comid} class=cl_choos2 rel={$comid}>";
        if (is_array($subb2)) {
            //echo 'max2'.count($subb2);
            for ($i = 0; $i < count($subb2); $i++) {
                $selColSize.='<option value="' . $subb2[$i] . '" >' . ucfirst_mb($subb2[$i]) . '</option>';
            }
        }
        $selColSize.="<select>";
        //$selColSize=$subb;
        //echo $subb;
    }

    //--------ArtMilano-------------------
    if ($artCatID == 66) {
        $selSize = "";
        $sizeFind = $html->find('ul[class="params nobullet"] li');
        foreach ($sizeFind as $sizeCheck) {
            if (strpos($sizeCheck->plaintext, "Цвет")) {
                $sizeArr = trim(str_replace("Цвет ", "", $sizeCheck->plaintext));
                break;
            }
        }
        $colorsMilano = explode(",", $sizeArr);
        if (count($colorsMilano) > 1 && is_array($colorsMilano)) {
            $selColSize .= "<select id=id_choos2_{$comid} class=cl_choos2 rel={$comid}>";
            foreach ($colorsMilano as $colorMilano) {
                $colorMilano = ucfirst_mb(trim($colorMilano));
                $selColSize .= '<option value="' . $colorMilano . '" >' . $colorMilano . '</option>';
            }
            $selColSize .= "<select>";
        }
        //$selColSize=$subb;
        //echo $subb;
    }
    //-------SK-House-------------------
    if ($_SESSION["cat_id"] == 49) {

        $selColSize.="<select id=id_choos2_{$comid} class=cl_choos2 rel={$comid}>";
        foreach ($selSize as $key => $a) {
            if ($a->plaintext != "-- Цвет --")
                $selColSize.='<option value="' . $a->plaintext . '" >' . ucfirst_mb($a->plaintext) . '</option>';
        }
        //$selSize=implode(";", $selSize2);
        $selColSize.="<select>";
        $selSize = "";
        $selSize2 = $html->find('#SizeId option');
        $selColSize.="<select id=id_choos_{$comid} class=cl_choos rel={$comid}>";
        foreach ($selSize2 as $key => $a) {
            if ($a->plaintext != "-- Размер --") {
                $selColSize.='<option value="' . $comid . ';' . $a->plaintext . '" >' . $a->plaintext . '</option>';
                $selSizeSK.=$a->plaintext . ";";
            }
        }
        $selColSize.="<select>";
        $selSize = substr($selSizeSK, 0, strlen($selSizeSK) - 1);
    }
    //--------olis-style------------------
    if ($_SESSION["cat_id"] == 58) {
        foreach ($selSize as $key => $a) {
            if ($a->plaintext != " --- Выберите --- ") {
                $selSize2[] = $a->plaintext;
            }
        }
        $selSize = implode(";", $selSize2);
        //	$selSize=substr($selSizeSK,0,strlen($selSizeSK)-1);
    }
    //--------Alva------------------
    if ($_SESSION["cat_id"] == 48) {
        $selSize = "";
    }
    //--------Alva------------------
    if ($_SESSION["cat_id"] == 43) {
        foreach ($selSize as $a) {
            if ($a->plaintext > 20)
                $selll.=$a->plaintext . ";";
            //	echo "s: ".$a->plaintext;
        }

        $acol = gggColSize($comid, 32);
        //$aa=gggColSize($comid,27);
        if ($acol == true) {
            //---Color----
            $ass22 = explode("|", $acol);
            $selColSize.="<select id=id_choos2_{$comid} class=cl_choos2 rel={$comid}>";
            for ($i = 0; $i < count($ass22); $i++) {
                if ($ass22[$i] != false)
                    $selColSize.='<option value="' . $ass22[$i] . '" >' . $ass22[$i] . '</option>';
            }
            $selColSize.="<select>";
            //---Size---
            $acol2 = explode(";", $selll);
            $selColSize.="<br><select id=id_choos_{$comid} class=cl_choos rel={$comid}>";
            for ($i = 0; $i < count($acol2); $i++) {
                if ($acol2[$i] != false)
                    $selColSize.='<option value="' . $comid . ';' . $acol2[$i] . '" >' . $acol2[$i] . '</option>';
            }
            $selColSize.="<select>";
        }
        //$ass=explode("|", $aa);
        //$ass2=implode(";",$ass);
        //$ass2=substr($ass2,0,strlen($ass2)-1);
        //echo $ass2;
        $selll = str_replace(" ", "", $selll);
        $selll = str_replace("-;", "", $selll);
        $selll = str_replace("-", "", $selll);
        $selll = substr($selll, 0, strlen($selll) - 1);
        $selSize = $selll;
    }
    //--------agio-z------------------
    if ($_SESSION["cat_id"] == 45) {
        $selSize = "";
        $selSize2 = "";
        $selSizee = $html->find("select[name='option_3'] option");
        $txt22.= "<br><select id=id_choos_{$comid} class=cl_choos rel={$comid}>";
        foreach ($selSizee as $a) {
            $selSize2.=$a->plaintext . ";";
            if ($a->plaintext != "Не определено")
                $txt22.='<option value="' . $comid . ";" . $a->plaintext . '" >' . $a->plaintext . '</option>';
        }
        $txt22.="</select>";
        $selSize2 = str_replace("Не определено;", "", $selSize2);
        $selSize2 = str_replace(" ", "", $selSize2);
        $selSize2 = substr($selSize2, 0, strlen($selSize2) - 1);
        //echo $selSize2;
        //-----Select Size and Color------
        $selSize3 = "";
        $selSizee = $html->find("select[name='option_2'] option");
        $select = "<select id=id_choos2_{$comid} class=cl_choos2 rel={$comid}>";
        foreach ($selSizee as $a) {
            $selSize3.=$a->plaintext . ";";
            if ($a->plaintext != "Не определено")
                $select.='<option value="' . $a->plaintext . '" >' . $a->plaintext . '</option>';
        }
        $select.="</select>";
        $checksel = strip_tags($select);
        if ($checksel == "") {
            $select = "";
        }
        $checktxt = strip_tags($txt22);
        if ($checktxt == "") {
            $ppo = strpos($desc, "Размер");
            if ($ppo != false) {
                $st = strstr($desc, "Размер");
                $ppo2 = strpos($st, "</p>");
                $selSize2 = substr($st, 0, $ppo2);
                $selSize2 = str_replace("Размер:", "", $selSize2);
            }
        }
        $selColSize = $select . $txt22;
        $checksell = strip_tags($selColSize);
        if ($checksell == "") {
            $selColSize = "";
        }
        $selSize = $selSize2;
    }
    //-------- Andrea Crocetta --------------------
    //if($_SESSION["cat_id"]==63){
    //	echo "S:".$selSize->innertext;
    //}
    //--------Lenida---------------------
    if ($_SESSION["cat_id"] == 16) {
        foreach ($selSize as $e) {
            if ($e->plaintext != false)
                $selSizeL.=$e->plaintext . ";";
        }
        $selSize = substr($selSizeL, 0, strlen($selSizeL) - 1);
    }

    //-------SwirlBySwirly-MamaMia-Tutsi---------------------------
    if (($_SESSION["cat_id"] == 3) || ($_SESSION["cat_id"] == 5) || ($_SESSION["cat_id"] == 14)) {
        $sel = strstr($desc, "<p>Размеры:");
        $spos = strpos($sel, "<p>");
        $spos2 = strpos($sel, "</p>");
        $subs = substr($sel, $spos, $spos2);
        $subs = str_replace("<p>", "", $subs);
        $subs = str_replace("</p>", "", $subs);
        $subs = str_replace("Размеры: ", "", $subs);
        $arr = explode(" ", $subs);
        //$txt22.= "<select id=id_choos_{$comid} class=cl_choos rel={$comid}><option value={$comid} >Размер</option>";
        for ($i = 0; $i < count($arr); $i++) {
            if ($arr[$i] != false)
            //	$txt22.='<option value="'.$comid.";".$arr[$i].'" >'.$arr[$i].'</option>';
                $txt22.=$arr[$i] . ";";
        }
        //$txt22.="</select>"; 
        //echo $txt22;
        $selColSize = "";
        $selSize = $selSize = substr($txt22, 0, strlen($txt22) - 1);
        ;
    }
    //--------Sellin----------------------
    if ($_SESSION["cat_id"] == 23) {
        $colarr = array();
        $txt22 = "";
        foreach ($selSize as $colSellinarr) {
            if (preg_match("/^[\D]+$/", $colSellinarr->plaintext) && $colSellinarr->plaintext != trim("Не определено")) {
                $colarr[] = $colSellinarr->plaintext;
            }
        }
        if (count($colarr) > 1) {
            $txt22.= "<br/><select id=id_choos2_{$comid} class=cl_choos2 rel={$comid}>";
            for ($i = 0; $i < count($colarr); $i++) {
                $colarr[$i] = str_replace("Цвет: ", "", $colarr[$i]);
                $txt22.='<option value="' . $colarr[$i] . '" >' . $colarr[$i] . '</option>';
            }
            $txt22.="</select>";
        }
        $txt22.= "<br/><select id=id_choos_{$comid} class=cl_choos rel={$comid}>";
        foreach ($selSize as $key => $a) {
            $sel = intval($a->plaintext);
            if ($sel != 0) {
                $selSize2[$key].=$sel;
                $selSize2[$key] = str_replace(" ", "", $selSize2[$key]);
                $txt22.='<option value="' . $comid . ";" . $selSize2[$key] . '" >' . $selSize2[$key] . '</option>';
                $s[$key] = $selSize2[$key];
            }
        }
        $txt22.="</select>";
        //	$selSize=implode(";", $selSize2);
        echo "Size" . $colSellin;
        $selColSize = $txt22;
        $selSize = implode(";", $s);
    }
    //--------Meggi----------------------
    /* 	if($_SESSION["cat_id"]==42){
      foreach($selSize as $key=>$a){
      $selSize2[$key].=$a->plaintext;
      }
      $selSize=implode(";", $selSize2);
      } */

    //--------nelli-co----------------------
    if ($_SESSION["cat_id"] == 62) {
        $sen = strstr($desc, "Размер");
        $pn = strpos($sen, "</p>");
        $selSize = substr($sen, 0, $pn);
        $selSize = str_replace(" ", ";", $selSize);
        $selSize = str_replace(",", ";", $selSize);
        $selSize = str_replace(";;", ";", $selSize);
        $selSize = str_replace("Размер;", "", $selSize);
        $selSize = str_replace("Размеры:;", "", $selSize);
    }
    //--------Majaly----------------------
    if ($_SESSION["cat_id"] == 65) {
        $posm = strpos($desc, "Размер:");
        if ($posm !== false) {
            $strm = strstr($desc, "Размер");
            $posm2 = strpos($strm, "</p>");
            $subm = substr($strm, 0, $posm2 - 1);
            $subm = str_replace(" ", "", $subm);
            $subm = str_replace("Размер:", "", $subm);
            $subm = str_replace(",", ";", $subm);
            //	echo $subm;
        }

        $posm2 = strpos($desc, "Цвета:");
        if ($posm2 === false) {
            $strm2 = strstr($desc, "Цвет:");
            $posm22 = strpos($strm2, "</p>");
            $subm2 = substr($strm2, 0, $posm22 - 1);
            //$subm2=str_replace(" ","",$subm2);
            $subm2 = str_replace("Цвета:", "", $subm2);
            $subm2 = str_replace("Цвет:", "", $subm2);
            $subm2 = str_replace("Как на фото", "", $subm2);
            $subm2 = str_replace("Разные цвета", "", $subm2);
            $subm2 = str_replace("Вставка: ", "", $subm2);
            $subm2 = str_replace("Вискоза: ", "", $subm2);
            $subm2 = str_replace(",", ";", $subm2);
        } else {
            $strm2 = strstr($desc, "Цвета:");
            $posm22 = strpos($strm2, "</p>");
            $subm2 = substr($strm2, 0, $posm22 - 1);
            //$subm2=str_replace(" ","",$subm2);
            $subm2 = str_replace("Цвета:", "", $subm2);
            $subm2 = str_replace("Цвет:", "", $subm2);
            $subm2 = str_replace("Как на фото", "", $subm2);
            $subm2 = str_replace("Вставка: ", "", $subm2);
            $subm2 = str_replace("Вискоза: ", "", $subm2);
            $subm2 = str_replace(",", ";", $subm2);
            //	echo $subm2;
        }
        if ($subm2 != false) {
            $arrcol = explode(";", $subm2);
            if (count($arrcol) > 1) {
                $txt22.= "<select id=id_choos2_{$comid} class=cl_choos2 rel={$comid}>";
                for ($i = 0; $i < count($arrcol); $i++) {
                    $arrcol[$i] = strip_tags($arrcol[$i]);
                    $txt22.='<option value=' . $arrcol[$i] . ' >' . $arrcol[$i] . '</option>';
                    //echo "i:".$arrcol[$i];
                }
                $txt22.="</select>";
            }
        }
        if ($subm != false) {
            $arrsize = explode(";", $subm);
            $txt22.= "<br/><select id=id_choos_{$comid} class=cl_choos rel={$comid}>";
            for ($i = 0; $i < count($arrsize); $i++) {
                $arrsize[$i] = strip_tags($arrsize[$i]);
                $txt22.='<option value="' . $comid . ";" . $arrsize[$i] . '" >' . $arrsize[$i] . '</option>';
            }
            $txt22.="</select>";
        }
        $mm = strip_tags($txt22);
        $mmm = strip_tags($subm2);
        $mmm = str_replace(" ", "", $mmm);
        if ($mm = "" || $mmm == "") {
            $selColSize = "";
        } else {
            $selColSize = $txt22;
        }
        $selSize = $subm;
    }
    //-Aliya
    if ($_SESSION["cat_id"] == 86) {

        $selSize = "";
        if (strpos($desc, "Размер") !== false) {

            $al = strstr($desc, "Разме");
            $al = strstr($al, "</p>", true);
            if (strpos($al, "см") === false) {
                $al = str_replace("Размеры", "", $al);
                $al = str_replace("Размер", "", $al);
                $al = str_replace("</span>", "", $al);
                $al = str_replace(":", "", $al);
                $al = str_replace(" ", "", $al);

                if (strpos($al, "по") !== false) {
                    $al = str_replace("-", "", $al);
                    $al = str_replace("с", "", $al);
                    $al = str_replace("по", "-", $al);
                }

                //	echo $al;
                $al2 = explode("-", $al);
                for ($i = $al2[0]; $i <= $al2[1]; $i+=2) {
                    if ($al2[1] == $i)
                        $als.=$i . ";";
                    else
                        $als.=$i . "-" . ($i + 1) . ";";
                }
                $als = substr($als, 0, strlen($als) - 1);
                $selSize = $als;
            }
        }
    }

    //--------Sergio Torri---------------------
    if ($_SESSION["cat_id"] == 85) {
        //	echo "wwwww";
        $selSize = "";
        $selColSize = "";
        $query = "UPDATE `shop_commodity` SET `com_sizes`='', `select_color`=''  WHERE  `from_url`='{$new_url}';";
        mysql_query($query) or die("Error select2");
    }
    //--------Vitality-------------------
    if ($_SESSION["cat_id"] == 88 || $_SESSION["cat_id"] == 205) {
        
    }
    //--------Dajs-------------------
    if ($_SESSION["cat_id"] == 215) {
        foreach ($selSize as $key => $value) {
            $da.=";" . $value->plaintext;
        }
        $da = substr($da, 1, strlen($da));
        $selSize = $da;
    }
    //----HelenLaven-----------------
    if ($_SESSION["cat_id"] == 217) {
        $selSize = "";
    }
    //----Dembo House----------------
    if ($_SESSION["cat_id"] == 218) {
        $dhs = strstr($desc, "Розмір");
        $dhs = strstr($dhs, "</p>", true);
        $dhs = str_replace("Розмір:", "", $dhs);
        $dhs = str_replace(" ", "", $dhs);
        $dhs = str_replace(",", ";", $dhs);
        $selSize = $dhs;
    }
    //----Jhiva----------------
    if ($_SESSION["cat_id"] == 219) {
        //$selSize="";
        foreach ($selSize as $key => $value) {
            $jh.=";" . $value->plaintext;
        }
        $jh = str_replace(";--- Оберіть ---;", "", $jh);
        $selSize = $jh;
    }

//------------------------------------------------------------------------------
//                      Ended size and col size in bd
//------------------------------------------------------------------------------
    if ($selSize != "") {
        if (isset($_GET["step"])) {
            echo "Size: " . $selSize;
        }
        $query = "UPDATE `shop_commodity` SET `com_sizes`='{$selSize}',`select_color`=''  WHERE  `from_url`='{$new_url}';";
        mysql_query($query) or die("Error select2");
    }
    if ($selColSize != "") {
        if (isset($_GET["step"])) {
            echo "SizeColor: <br>" . $selColSize;
        }
        $query = "UPDATE `shop_commodity` SET `select_color`='{$selColSize}'  WHERE  `from_url`='{$new_url}';";
        mysql_query($query) or die("Error select2");
    }
    //die();
//--------Glem----------------------
    if ($_SESSION["cat_id"] == 15) {

        $k = 0;
        $cglem = explode(',', $colorGlem);
        for ($i = 0; $i < count($cglem); $i++) {
            if ($cglem[$i] != "") {
                $cglem2[$k] = $cglem[$i];
                $k++;
            }
        }


        $j = 0;
        $k = 0;

        $setGlem = explode(";", $sizeGlem);
        for ($i = 0; $i < count($setGlem); $i++) {
            if ($setGlem[$i] != false) {
                if ($setGlem[$i] < $setGlem[$i + 1]) {
                    $mulCol[$j][$k] = $setGlem[$i];
                    $k++;
                } else {
                    $mulCol[$j][$k] = $setGlem[$i];
                    $k = 0;
                    $j++;
                }
            }
        }
        //	var_dump($cglem2);
        //	var_dump($mulCol);
        $comsel = "";

        for ($i = 0; $i < count($cglem2); $i++) {
            $comsel.=$cglem2[$i] . "=";
            for ($j = 0; $j < count($mulCol[$i]); $j++) {
                $comsel.=$mulCol[$i][$j] . ",";
            }
            $comsel.=";";
        }
        $comsel = str_replace(",;", ";", $comsel);
        $comsel = substr($comsel, 0, strlen($comsel) - 1);
        if (isset($_GET["step"])) {
            echo "<br/>Com_select: " . $comsel;
        }
        $query = "UPDATE `shop_commodity` SET `commodity_select`='{$comsel}'  WHERE  `from_url`='{$new_url}';";
        mysql_query($query) or die(mysql_error());
    }
    //die();
//---S&L---------------				
    if ($_SESSION["cat_id"] == 48) {
        // $selColSize="";
        // $selectsSL = $elementSL->innertext;
        // $selectsSL = explode('</table>', $selectsSL)[0];
        // $selectsSL = explode('<tr>', $selectsSL);
        // foreach($selectsSL as $e){
        //     $colorSL = '';
        //     $sizeSL = [];
        //     $e2 = explode('</td>', $e);
        //     if (count($e2) >= 2) {
        //         $e3 = explode('<div',$e2[1]);
        //         foreach ($e3 as $key =>$e4) {
        //             preg_match('/<b>(.*)<\/b>/', $e4, $e5);
        //             if (count($e5) == 2 && $e5[1] != "") {
        //                 if ($key == 0) {
        //                     $colorSL = ucfirst_mb($e5[1]) . '=';
        //                 } else {
        //                     $sizeSL[] = $e5[1];
        //                 }
        //             }
        //         }
        //         if (strlen($colorSL) > 1 && count($sizeSL) > 0) {
        //             foreach ($sizeSL as $size) {
        //                 $colorSL .= $size.',';
        //             }
        //         } else {
        //             $colorSL = '';
        //         }
        //     }
        //     if (strlen($colorSL) > 1) {
        //         $ssel .= $colorSL.";";
        //     }
        // }

        $excDesc = read_excel('excel/sl.xls');
        foreach ($excDesc as $val) {
            if ($cod == $val[0]) {
                $getSl = $val[5];
            }
        }
        $getSl = str_replace("(", "=", $getSl);
        $getSl = str_replace("+", ",", $getSl);
        $getSl = str_replace(")|", ";", $getSl);
        $getSl = str_replace(")", "", $getSl);
        //$getSl=str_replace(" ", "", $getSl);
        $ssel = $getSl;


        if ($ssel == "") {
            $querysel = "UPDATE `shop_commodity` SET `commodity_visible`='0' WHERE `commodity_ID`='{$comid}';";
//            mysql_query($querysel) or die("error no_nal");
            $updated = true;
            echo "Нет наличии 0";
        }
        //$ssel=str_replace(",;",";",$ssel);
        //$ssel=substr($ssel,0,strlen($ssel)-1);
        if (isset($_GET["step"])) {
            echo $ssel;
        }
        $query = "UPDATE `shop_commodity` SET `commodity_select`='{$ssel}' WHERE  `from_url`='{$new_url}';";
        mysql_query($query) or die("Error select2");
    }
    //--------Vitality-------------------
    if ($_SESSION["cat_id"] == 88 || $_SESSION["cat_id"] == 205) {
        
    }
//	die();	
    //--------------------------------------------------------------
    // B1 with brenda
    //--------------------------------------------------------------
    if ($_SESSION["cat_id"] == 64) {
        $query = "INSERT INTO `shop_commodities-categories` SET `commodityID`='{$comid}', `categoryID`='{$_SESSION["cat_id"]}';";
        mysql_query($query);

        if ($artCatID == 66) {
            $query = "INSERT INTO `shop_commodities-categories` SET `commodityID`='{$comid}', `categoryID`='{$artCatID}';";
            mysql_query($query);
            if (isset($_GET["step"])) {
                echo "<br/>CatId: " . $artCatID;
            }
            $delCat = "DELETE FROM `shop_commodities-categories` WHERE `commodityID`='{$comid}' AND `categoryID`='{$_SESSION["cat_id"]}'; ";
            mysql_query($delCat);
        }
    }
//------------------------------------------------------------------------------
//Коментируем если хотим проверить раскоментируем если хотим загрузить фото
//------------------------------------------------------------------------------
//die()/*
    /* if(isset($_GET["step"])){
      echo "<br>CommodityID: ".$comid." CatId: ".$_SESSION["cat_id"]."<br/>";
      }
      //	die();
      $k=0;
      //	die();

      if($src!="" || $src!="http://www.glem.com.ua/")
      {
      //------------Delete dop.image-------------------------------------
      $gallery_domen="makewear.com.ua";
      $tit="/www/{$gallery_domen}/images/commodities/{$comid}/";
      $stit="/www/{$gallery_domen}/images/commodities/{$comid}/s_title.jpg";

      $ftp=ftp_connect("makewear.com.ua") or die("Not connected");
      if(ftp_login($ftp, "zoond", "makewear1234567890")){
      if(isset($_GET["step"])){
      echo "Work FTP)))";
      }
      $f=ftp_nlist($ftp,$tit.".");
      foreach($f as $value){
      //	if($value!=$tit."s_title.jpg" && $value!=$tit."title.jpg" && $value!=$tit."." && $value!=$tit.".."){
      if($value==$tit."s_title.jpg" && $value==$tit."title.jpg"){
      //	if($value!=$tit."." && $value!=$tit.".."){
      ftp_delete($ftp,$value);
      //	echo $value."<br/>";
      }
      }
      }
      $sql="DELETE FROM `shop_images` WHERE `com_id`='{$comid}'";
      mysql_query($sql) or die(mysql_error());
      //----------------------------------------------------------
      //	echo ftp_rawlist($ftp, "/");
      /*if(ftp_delete($ftp, $stit)) {
      echo "Delete file s_title.jpg";
      }else {echo "Not deleted s_title.jpg";}
      if(ftp_delete($ftp, $tit)) {
      echo "Delete file title.jpg";
      }else {echo "Not deleted title.jpg";}
      /*					}else{
      echo "Error login or password";
      } */
    /* 					getnewpngimg(1,1024,786,"commodities",$comid,"title.jpg",$src,1);
      $type = 1;
      $sWidth = 300;
      $sHeight = 300;
      $size = getimagesize("uploads/temp_image.jpg");
      if ($size[1]/$size[0] < 1.46 && $size[1]/$size[0] > 1.15) {
      $type = 2;
      $sWidth = 200;
      }
      getnewpngimg($type,$sWidth,$sHeight,"commodities",$comid,"s_title.jpg","http://{$gallery_domen}/images/commodities/{$comid}/title.jpg");
      echo "<br/>New image";
      }
      if(isset($_GET["step"])){
      echo "Set DopImg<br/>";
      }
      $idimg=0;
      foreach($html->find($_SESSION["dopimg"]) as $a)
      {
      // echo $a->src;
      // die();
      //echo "Set DopImg2".$a->src;
      $iiis=str_replace("/th-","/",$a->src);
      $iiis=str_replace(" ","%20",$iiis);
      $iiis=str_replace("tov/204_","tov/",$iiis);
      $iiis=str_replace("/77x117","",$iiis);
      //	$iiis=str_replace("s_","_",$iiis);
      //	$iiis=str_replace("m_","_",$iiis);
      //	$iiis=str_replace("s.jpg",".jpg",$iiis);
      //	$iiis=str_replace("m.jpg",".jpg",$iiis);
      $iiis=str_replace("/smal/","/",$iiis);
      //------Swirl by Swirl-MamaMia-Tutsi------------Розница --------
      if(($_SESSION["cat_id"]==3) || ($_SESSION["cat_id"]==5) || ($_SESSION["cat_id"]==14)){
      $iiis=str_replace("135___195","___",$iiis);
      $iiis=str_replace("330___515","___",$iiis);
      $fii++;
      }
      //------Lenida--------------------
      if($_SESSION["cat_id"]==16){
      $i=array("_imagem_","h595");
      $i2=array("_images_","h1000");
      $iiis=str_replace($i,$i2,$iiis);
      $iiis=str_replace("_image_","_images_",$iiis);
      }
      //------Cardo--------------------
      if($_SESSION["cat_id"]==1){
      $iiis=str_replace("-medium/","/",$iiis);
      }
      //------Alva--------------------
      if($_SESSION["cat_id"]==43){
      $iiis=str_replace("200x300.jpg","550x825.jpg",$iiis);
      }
      //------SKHouse-------------------
      if($_SESSION["cat_id"]==49){
      $iiis=str_replace(".thumb.jpg",".jpg",$iiis);
      $iiis=str_replace(".jpg.product.jpg",".jpg",$iiis);
      $iiis=str_replace(".jpg.product.thumb.jpg",".jpg",$iiis);

      }
      //------S&L-------------------
      if($_SESSION["cat_id"]==48){
      $iiis=str_replace("smal/","/",$iiis);
      }
      //------Seventeen-------------------
      if($_SESSION["cat_id"]==47){
      $iiis=str_replace("s_","_",$iiis);
      $iiis=str_replace("s.jpg",".jpg",$iiis);
      $iiis2=str_replace("m.jpg",".jpg",$iiis);
      if($iiis2!=$src)
      $iiis=str_replace("m.jpg",".jpg",$iiis);
      }
      //------Agio-Z-------------------
      if($_SESSION["cat_id"]==45){
      $iiis=str_replace("productm_pictures","products_pictures",$iiis);
      $iiis=str_replace("_thm.JPG","_enl.JPG",$iiis);
      $iiis=str_replace("_thm.jpg","_enl.jpg",$iiis);
      $iiis=str_replace("_thm.png","_enl.png",$iiis);
      }
      //----OlisStyle-----------------
      if($_SESSION["cat_id"]==58){
      $iiis=str_replace("-70x81.jpg","-500x579.jpg",$iiis);
      }
      //----Sellin----------------
      if($_SESSION["cat_id"]==23){
      $iiis=str_replace("product_pictures","products_pictures",$iiis);
      $iiis=str_replace("_enl.jpg",".jpg",$iiis);
      $iiis=str_replace("_enl.JPG",".JPG",$iiis);
      $iiis=str_replace("_thm.JPG",".JPG",$iiis);
      $iiis=str_replace("_thm.jpg",".jpg",$iiis);
      $iiis=str_replace("_th.jpg",".jpg",$iiis);
      $iiis=str_replace("_th.JPG",".JPG",$iiis);
      }
      //------Majaly-Nelli_co-------------------
      if($_SESSION["cat_id"]==65|| $_SESSION["cat_id"]==62){

      $iiis=str_replace("w200_h200","w640_h640",$iiis);
      $iiis=str_replace("w40_h40","w640_h640",$iiis);

      }
      //------Crisma-------------------
      if($_SESSION["cat_id"]==87){
      $iiis=str_replace("m.jpg",".jpg",$iiis);
      $iiis=str_replace("s.jpg",".jpg",$iiis);
      $iiis=str_replace("s_","_",$iiis);
      }
      //------Vitality-------------------
      if($_SESSION["cat_id"]==88 || $_SESSION["cat_id"]==205){
      $iiis=str_replace("__1.jpg","_1.jpg",$iiis);
      $iiis=str_replace("/thumbnail/70x/","/image/",$iiis);
      }
      //------Helen Laven-------------------
      if($_SESSION["cat_id"]==217){
      $iiis=str_replace("_m.jpg","_b.jpg",$iiis);
      }
      //------Jhiva-------------------
      if($_SESSION["cat_id"]==219){
      $iiis=str_replace("74x110.JPG","680x970.JPG",$iiis);
      }
      $iiis=str_replace("http://","",$iiis);
      $iiis=str_replace($domain,"",$iiis);
      $src33=$domain."/".$iiis;
      $src33=str_replace("//","/",$src33);
      $src33="http://".$src33;

      $src33=str_replace("majaly.com.ua/","",$src33);
      $src33=str_replace("nelli-co.com/","",$src33);
      if(strpos($src33,"%")===false){
      $src33=rawurlencode($src33);
      }
      $src33=str_replace("%3A",":",$src33);
      $src33=str_replace("%2F","/",$src33);
      $src33=str_replace("%2520","%20",$src33);
      $hp=strpos($src33,"http://images.ua.prom.st");
      //------SKHouse-------------------
      if($_SESSION["cat_id"]==49){
      $src33=str_replace("%25","%",$src33);
      }

      if($fii<=1){
      if($src33!="http://b-1.ua/")
      if($src33!=""){
      //ad_new_img($src33,$comid,"");
      //echo "DopImgZmi: <a href={$src33} target='_blank'>{$src33}</a><br/>";
      $setArr.=$src33."|";
      }
      }
      }
      //----B1----------------
      if($_SESSION["cat_id"]==64){

      $barr=array();
      $k=0;
      foreach($html->find('.color-selector li') as $a){
      $bpos=strpos($a->plaintext,"В наличии ");
      if($bpos!==false){
      if(isset($_GET["step"])){
      echo $a->plaintext."<br/>";
      }
      $barr[$k]=1;
      }else {
      $barr[$k]=0;
      }
      $k++;
      }

      $k=0;
      foreach($html->find('.color-selector li a') as $a){
      if($barr[$k]==1)
      $barr[$k]=$a->color;
      $k++;
      }

      for($i=0; $i<count($barr); $i++){
      if($barr[$i]==true){
      $class=".class_".$barr[$i]." img";
      foreach($html->find($class) as $a){
      if(isset($_GET["step"])){
      echo $a->src."<br/>";
      }
      $bsrc=explode("/",$a->src);
      $gf=$bsrc[count($bsrc)-1];
      $p=strpos($gf,"-");
      $bsub=substr($gf,0,$p);
      $bsubb.=$bsub."|";
      }
      }
      }
      $bsubb=substr($bsubb,0,strlen($bsubb)-1);
      if(isset($_GET["step"])){
      echo $bsubb."<br>";
      }

      foreach($html->find('.prod-gallery a') as $a){
      $sa=explode("|",$bsubb);
      for($i=0; $i<count($sa); $i++){
      $aaa=strpos($a->href,$sa[$i]);
      if($aaa!==false){
      //	echo $a->href."<br/>";
      //	$iiis=$a->href;
      $setArr.=$a->href."|";
      }
      }
      //echo $a->href."<br/>";
      }

      $iiis2=$html->find($_SESSION["dopimg"],$idimg)->href;
      $po=strpos($iiis2,".jpg");
      if($po!==false){
      $iiis=$iiis2;
      if(isset($_GET["step"])){
      echo "yes";
      }
      }else{
      if(isset($_GET["step"])){
      echo "no";
      }
      }

      $idimg++;

      }
      //	echo "<br/>SetArr: ".$setArr;
      $getArr=explode("|",$setArr);
      for($i=0; $i<count($getArr); $i++){
      for($j=$i; $j<count($getArr); $j++ ){
      if($i!=$j){
      if($getArr[$i]!="e")
      if($getArr[$i]==$getArr[$j]){
      $getArr[$j]="e";
      }
      }
      }
      }
      for($i=0; $i<count($getArr); $i++){
      if($getArr[$i]!="")
      if($getArr[$i]!="e"){
      if(strpos($src33,"%")===false){
      $src33=rawurlencode($src33);
      }
      $src33=str_replace("%3A",":",$src33);
      $src33=str_replace("%2F","/",$src33);

      $typeImg2=explode(".",$getArr[$i]);
      $type2=$typeImg2[count($typeImg2)-1];

      if($type=='png'){
      $typeName2=explode("/",$getArr[$i]);
      $typeName22=$typeName2[count($typeName2)-1];
      $typeName22=strstr($typeName22,'.',type);
      $getArr[$i]=convert_image_type($type2,'jpg',$getArr[$i],$typeName22);
      }

      ad_new_img($getArr[$i],$comid,"");
      if(isset($_GET["step"])){
      echo "DopImgZmi: <a href={$getArr[$i]} target='_blank'>{$getArr[$i]}</a><br/>";
      }

      }
      }
      //------Glem--------------------
      $imgArr=array();
      $k=0;
      if($_SESSION["cat_id"]==15){
      $imGet="";
      for($id=0; $id<=count($gxml->shop->offers->offer); $id++){
      if($new_url==$gxml->shop->offers->offer[$id]->url ){
      for($j=0; $j<count($gxml->shop->offers->offer[$id]->picture); $j++){
      $iiig=$gxml->shop->offers->offer[$id]->picture[$j];
      $imGet.=$iiig."|";
      }
      }
      }
      }


      //echo "<br/>".$imGet;
      $imArr=explode("|",$imGet);
      //	echo "<br/>";
      for($i=0; $i<count($imArr); $i++){
      for($j=$i; $j<count($imArr); $j++){
      if($i!=$j){
      if($imArr[$i]!="e")
      if($imArr[$i]==$imArr[$j])
      $imArr[$j]="e";
      }
      }
      }
      for($i=0; $i<count($imArr); $i++){
      if($imArr[$i]!="http://www.glem.com.ua/")
      if($imArr[$i]!=false)
      if($imArr[$i]!="e"){
      ad_new_img($imArr[$i],$comid,"");
      if(isset($_GET["step"])){
      echo "XML DopImg: ".$imArr[$i]."<br/>";
      }
      }
      }
      //	echo "<br/>End";
      //	die(); */
//------------------------------------------------------------------------------
//Коментируем если хотим проверить раскоментируем если хотим загрузить фото
//------------------------------------------------------------------------------		
//============UpText=================================================
    $par_idd = $_SESSION["id"];
    $uptext = str_replace("'_blank'", "", $uptext);
    //echo $uptext;
    mysql_query("UPDATE `parser_interface` SET `text`='{$uptext}' WHERE `par_id`='{$par_idd}';") or die(mysql_error());

//=============================================================

    $gallery_domen = "makewear.com.ua";
    $catid = $_SESSION["cat_id"];

    // Divide category Vitality
    if ($catid == 88 || $catid == 205) {

        echo "<br><span style='color:blue'>Category: </span> " . $bra . "";
        $ret = array("Lida Golden Day", "Cold Day", "Lida Sorrento", "Jolinesse", "Lida Classic", "MARILIN", "PANTHER", "no name", "lemila", "MILANO", "Nima Zaree", "Bsn & Club", "ORIGINAL", "Acousma", "ORI", "AO JIA SHI", "Prestige", "Skarpetki damskie", "POMPEQ", "Etienne", "SiSi", "Climber", "Glo Story", "Denis Simachёv", "PUMA", "Рата");

        for ($i = 0; $i < count($ret); $i++) {
            if ($ret[$i] == $bra) {
                echo "RETURN";
                mysql_query("DELETE FROM `shop_commodity` WHERE `from_url`='{$new_url}'");
                $de = mysql_query("SELECT * FROM `shop_commodity` WHERE  `from_url`='{$new_url}';");
                $dee = mysql_fetch_assoc($de);
                $dd = $dee['commodity_ID'];
                mysql_query("DELETE FROM `shop_commodities-categories` WHERE `commodityID`={$dd}");
                echo "<br>RETURN";
                echo " <span style='color:blue'>DDelete:</span> " . $dd;
                return;
            }

            //	mysql_query("INSERT INTO `shop_commodities-categories`(`categoryID`, `commodityID`) VALUES ('','')") or die(mysql_error());			
        }

        /* 	if($bra=="Yes!Miss" || $bra=="Yes!miss" || $bra=="Miss" ){
          $catid=96;
          echo " Set category {$catid}=".$bra."<br>";
          }elseif($bra=="DDStyle" || $bra=="D.D.Style") {
          $catid=97;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="TNT8") {
          $catid=98;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="M.B.21" || $bra=="MB.21" || $bra=="MB21") {
          $catid=99;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="Shaton") {
          $catid=100;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="ToonToy") {
          $catid=101;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="Remi") {
          $catid=102;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="Milano") {
          $catid=103;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="NimaZaree") {
          $catid=104;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="Joulie") {
          $catid=105;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="Piertu") {
          $catid=106;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="Ambitionfly") {
          $catid=107;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="Como") {
          $catid=108;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="Alnwgck" || $bra=="ALNWJCK") {
          $catid=109;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="ORI") {
          $catid=110;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="Lyvof") {
          $catid=111;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="CRO") {
          $catid=112;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="CalvinKlein") {
          $catid=114;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="Collection") {
          $catid=115;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="RAW") {
          $catid=116;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="A.M.N.") {
          $catid=117;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="DLF") {
          $catid=118;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra=="M C Y") {
          $catid=185;
          echo " Set category {$catid}=".$bra."<br>";
          }
          elseif($bra==" no name" || $bra==" Lida Golden Day" || $bra==" Cold Day" || $bra==" Lida Sorrento" || $bra==" Jolinesse" || $bra==" Lida Classic" || $bra==" MARILIN" || $bra==" PANTHER" ) {
          echo "Return";
          return;
          }
          else{
          $ct=mysql_query("SELECT * FROM `shop_categories` WHERE `cat_name`='{$bra}'");
          $ctt=mysql_fetch_assoc($ct);
          if(!$ctt){
          $bra=str_replace("'","\'",$bra);
          $ma=mysql_query("SELECT * FROM `shop_categories` ORDER BY `categories_of_commodities_ID` DESC LIMIT 1");
          $max=mysql_fetch_assoc($ma);
          $maxx=$max['categories_of_commodities_ID'];
          $maxx=mysql_insert_id();
          $alisa2=str_replace(" ","-",$bra);
          $alisa2=strtolower($alisa2);
          mysql_query("INSERT INTO `shop_categories` (`categories_of_commodities_ID`,`cat_name`,`categories_of_commodities_parrent`,`categories_of_commodities_order`,`alias`) VALUES ('{$maxx}','{$bra}','10','{$maxx}','{$alisa2}') ") or die(mysql_error());
          $catid=$maxx;
          echo "<br/><span style='color:green'>Create category: {$bra}</span>";
          }else{
          echo " ".$ctt['categories_of_commodities_ID']."=".$ctt['cat_name'];
          $catid=$ctt['categories_of_commodities_ID'];
          }
          //echo "<br><span style='color:red'>No Category.</span> New brenda: ".$bra;
          //die();
          } */
    }


    if ($artCatID != 0) {
        $catid = $artCatID;
    }


    $sql = "SELECT * FROM `shop_commodity` 
		WHERE  `from_url`='{$new_url}';";
    $row = mysql_fetch_assoc(mysql_query($sql));
    if ($row) {
        if ($catid == 14 || $catid == 3 || $catid == 5) {
            $pattern = '/Цвет:([\S]*)[\s]?<\/p>/';
            preg_match($pattern, $desc, $coincidence);
            preg_match($pattern, $row['com_fulldesc'], $coincidence1);
            if ($coincidence[1] != $coincidence1[1]) {
                $queryNo2 = "UPDATE `shop_commodity` SET `commodity_visible`='0' WHERE `from_url`='{$new_url}';";
                mysql_query($queryNo2) or die("error no_nal");
                if (isset($_GET["step"])) {
                    echo "<br/><b style='color:red;' >Не опубликовать! (MamaMia, Tutsi, SwirlBySwirl - разний цвет!)</b>";
                }
                return;
            }
        }

        //-------Aliya------------------
        if ($catid == 86) {

            $sql_name = mysql_query("SELECT count(`com_name`) name,`commodity_ID`,`cod`,`commodity_visible` 
            						FROM `shop_commodity` 
            						WHERE `com_name` LIKE '{$name}'");
            $rn = mysql_fetch_assoc($sql_name);
            $cou = $rn['name'];
            if ($cou >= 2) {
                echo "<br>Name2:" . $name . " = " . $cou;
                $sql_name2 = mysql_query("SELECT `com_name`,`commodity_ID`,`cod`,`commodity_visible` 
            						FROM `shop_commodity` 
            						WHERE `com_name` LIKE '{$name}'");
                while ($rn2 = mysql_fetch_assoc($sql_name2)) {
                    $comm_id = $rn2['commodity_ID'];
                    $vis = $rn2['commodity_visible'];
                    $comm[$comm_id] = $vis;
                    /* if($rn2['commodity_visible']==0){
                      mysql_query("DELETE FROM `shop_commodity` WHERE `commodity_ID`='{$comm_id}'");
                      echo "<span style='color:red;'>Delete: </span>".$comm_id;
                      } */
                }
                $flaggg = 0;
                foreach ($comm as $k => $v) {
                    echo "<br>" . $k . "=" . $v;
                    $upp = $k;
                    if ($v == 1) {
                        $flaggg = 1;
                    }
                }
                if ($flaggg == 0) {
                    $comm[$upp] = 1;
                }
                foreach ($comm as $k => $v) {
                    if ($v == 0) {
                        mysql_query("DELETE FROM `shop_commodity` WHERE `commodity_ID`='{$k}'");
                        echo "<span style='color:red;'>Delete: </span>" . $k;
                    }
                }
            }
        }
        if ($catid == 88 || $catid == 205) {
            $sel = mysql_query("SELECT * FROM `shop_commodities-categories` WHERE `commodityID`='{$row["commodity_ID"]}'");
            $sell = mysql_fetch_assoc($sel);
            if (!$sell) {
                //	mysql_query("INSERT INTO `shop_commodities-categories` SET `commodityID`='{$row["commodity_ID"]}', `categoryID`='{$catid}';");
                //	echo "<br>Have category";
            } else {
                //	mysql_query("UPDATE `shop_commodities-categories` SET `categoryID`='{$catid}' WHERE `commodityID`='{$row["commodity_ID"]}' ");
                //	echo "<br>Update category".$row["commodity_ID"];
            }
        }


        //------Acces price---------------------
        // if($_SESSION["cat_id"]!=87){
        $sql_price = "`commodity_price`='{$price}', `commodity_price2`='{$price2}'";
        // }
        //mysql_query($queryu);
        @mysql_query($query);
        $commodityID = $row["commodity_ID"];

        $query = "
					UPDATE `shop_commodity` 
					SET 
					`commodity_order`='{$order}',
					{$sql_price},
					`commodity_old_price`='{$old_price}',
					`com_name`='{$name}',
					`commodity_visible`='1'
					WHERE  `from_url`='{$new_url}'
					;";
        if ($price <= 10) {
            $n = 1;
            if (isset($_GET["step"])) {
                echo "<br/><b style='color:red;' >Немає ціна!</b>";
            }
        }

        if ($src == 'http://www.glem.com.ua/' || $src == "http://www.lenida.com.ua/") {
            $n = 1;
            if (isset($_GET["step"])) {
                echo "<br/><b style='color:red;' >Немає фото!!!</b>";
            }
        }
        mysql_query($query);
        $f = ggg($comid);
        if ($f == false) {
            $n = 1;
            $query2 = "UPDATE `shop_commodity` SET `commodity_order`='0' WHERE `from_url`='{$new_url}';";
            mysql_query($query2) or die("no tegi");
            if (isset($_GET["step"])) {
                echo "<br>Немає теги";
            }
        } else {
            //	echo $comid."Tegi: ".$f;
            $query2 = "UPDATE `shop_commodity` SET `commodity_visible`='1' WHERE `from_url`='{$new_url}';";
            mysql_query($query2) or die("error");
        }
        if ($n == 1) {
            $queryNo2 = "UPDATE `shop_commodity` SET `commodity_visible`='0' WHERE `from_url`='{$new_url}';";
            mysql_query($queryNo2) or die("error no_nal");
            if (isset($_GET["step"])) {
                echo "<br/><b style='color:red;' >Не опубликовать!</b>";
            }
        }
    } else {
        if ($catid == 85 || $catid == 42 || $catid == 15 || $catid == 1 || $catid == 3 || $catid == 5 || $catid == 14 || $catid == 58 || $catid == 63) {
            $query = "SELECT `cod`, `com_name`
									FROM  `shop_commodity` AS c
									INNER JOIN  `shop_commodities-categories` AS cc ON c.`commodity_ID` = cc.commodityID
									WHERE cc.categoryID =$catid";
            $data = mysql_query($query);
            $items = array();
            while ($row = mysql_fetch_assoc($data)) {
                $items[] = $row;
            }
            foreach ($items as $item) {
                if ($cod == $item['cod']) {
                    if ($catid == 14 || $catid == 3 || $catid == 5) {
                        $pattern = '/Цвет:([\S]*)[\s]?<\/p>/';
                        preg_match($pattern, $desc, $coincidence);
                        preg_match($pattern, $item['com_fulldesc'], $coincidence1);
                        if ($coincidence[1] == $coincidence1[1]) {
                            if (isset($_GET["step"])) {
                                echo '<span style="color:red">Товар уже есть в базе данных! (MamaMia, Tutsi, SwirlBySwirl - одинаковий цвет)</span>';
                            }
                            return;
                        }
                    } else {
                        if (isset($_GET["step"])) {
                            echo '<span style="color:red">Товар уже есть в базе данных!!!!</span>';
                        }
                        return;
                    }
                }
                if ($catid == 15) {
                    if ($item['name'] == $name) {
                        if (isset($_GET["step"])) {
                            echo '<span style="color:red">Одинаковое название товара!!!!</span>';
                        }
                        return;
                    }
                }
            }
            if ($cod == '') {
                if (isset($_GET["step"])) {
                    echo '<span style="color:red">Пустой код!!!!</span>';
                }
                return;
            }
        }
        if ($catid == 86) {

            $sql_name = mysql_query("SELECT `com_name`,`commodity_ID`,`cod`,`commodity_visible` 
            						FROM `shop_commodity` 
            						WHERE `com_name` LIKE '{$name}'");
            $mm = mysql_fetch_assoc($sql_name);
            if ($mm) {
                echo '<br><span style="color:red">1 Товар уже есть в базе данных!!!!</span>';
                return;
            }
        }
//------------------------------------------------------------------------------
//                   Записываем новый товар в бд
//------------------------------------------------------------------------------        

        $desc = str_replace("'", "\"", $desc);
        $query = "
					INSERT INTO `shop_commodity` 
					SET  
					`cod`='{$cod}', 
					`commodity_visible`='0', 
					`from_url`='{$new_url}',
					`commodity_bigphoto`='1',
					`commodity_price`='{$price}',
					`commodity_price2`='{$price2}',
					`commodity_old_price`='{$old_price}',
					`commodity_add_date`='{$commodity_add_date}',
					`alias`='{$alias}', 
					`com_name`='{$name}',
					`commodity_order`='{$order}',  
					`com_fulldesc`='{$desc}', 
					`lng_id`='1'
					;";

        mysql_query($query);
        $commodityID = mysql_insert_id();
        $query = "
					INSERT INTO `shop_commodities-categories` 
					SET `commodityID`='{$commodityID}', `categoryID`='{$catid}';";
        @mysql_query($query);

        //addfiltr2($commodityID,$html->find("#id_for_cookie",0)->value);
        if (isset($_GET["step"])) {
            echo "<br/>CommodityID: " . $commodityID . " CatId: " . $catid . "<br/>";
        }
        if ($src != "" || $src != "http://www.glem.com.ua/") {

            getnewpngimg(1, 1024, 1024, "commodities", $commodityID, "title.jpg", $src, 1);
            $type = 1;
            $sWidth = 300;
            $sHeight = 300;
            $size = getimagesize("uploads/temp_image.jpg");
            if ($size[1] / $size[0] < 1.46 && $size[1] / $size[0] > 1.1) {
                echo $size[1] / $size[0] . "new!";
                $type = 2;
                $sWidth = 200;
            }
            getnewpngimg($type, $sWidth, $sHeight, "commodities", $commodityID, "s_title.jpg", "http://{$gallery_domen}/images/commodities/{$commodityID}/title.jpg");
        }
        $tt = 0;
//=DopImage============================================	
        //	echo "Set DopImg<br/>";	
        foreach ($html->find($_SESSION["dopimg"]) as $a) {
            echo "DopSrc: " . $a->src;
            //die();
            //	echo "Set DopImg2<br/>";
            $iiis = str_replace("/th-", "/", $a->src);
            $iiis = str_replace(" ", "%20", $iiis);
            $iiis = str_replace("tov/204_", "tov/", $iiis);
            $iiis = str_replace("/77x117", "", $iiis);
            //	$iiis=str_replace("s_","_",$iiis);
            //	$iiis=str_replace("s.jpg",".jpg",$iiis);
            $iiis = str_replace("/smal/", "/", $iiis);
            //------Swirl by Swirl-MamaMia-Tutsi--------------------					
            if (($_SESSION["cat_id"] == 3) || ($_SESSION["cat_id"] == 5) || ($_SESSION["cat_id"] == 14)) {
                $iiis = str_replace("135___195", "___", $iiis);
                $iiis = str_replace("330___515", "___", $iiis);
                $fii++;
            }
            //------Lenida--------------------					
            if ($_SESSION["cat_id"] == 16) {
                $i = array("_imagem_", "h595");
                $i2 = array("_images_", "h1000");
                $iiis = str_replace($i, $i2, $iiis);
                $iiis = str_replace("_image_", "_images_", $iiis);
            }
            //------Cardo--------------------					
            if ($_SESSION["cat_id"] == 1) {
                $iiis = str_replace("-medium/", "/", $iiis);
            }
            //------Alva--------------------					
            if ($_SESSION["cat_id"] == 43) {
                $iiis = str_replace("200x300.jpg", "550x825.jpg", $iiis);
            }
            //------SKHouse-------------------					
            if ($_SESSION["cat_id"] == 49) {
                $iiis = str_replace(".thumb.jpg", ".jpg", $iiis);
                $iiis = str_replace(".jpg.product.jpg", ".jpg", $iiis);
                $iiis = str_replace(".jpg.product.thumb.jpg", ".jpg", $iiis);
            }
            //------S&L-------------------					
            if ($_SESSION["cat_id"] == 48) {
                $iiis = str_replace("smal/", "/", $iiis);
            }
            //------Seventeen-------------------					
            if ($_SESSION["cat_id"] == 47) {
                $iiis2 = str_replace("s_", "_", $iiis);
                $iiis2 = str_replace("s.jpg", ".jpg", $iiis);
                $iiis2 = str_replace("m.jpg", ".jpg", $iiis);
                if ($iiis2 != $src)
                    $iiis = str_replace("m.jpg", ".jpg", $iiis);
            }
            //------Agio-Z-------------------					
            if ($_SESSION["cat_id"] == 45) {
                $iiis = str_replace("productm_pictures", "products_pictures", $iiis);
                $iiis = str_replace("_thm.JPG", "_enl.JPG", $iiis);
                $iiis = str_replace("_thm.jpg", "_enl.jpg", $iiis);
                $iiis = str_replace("_thm.png", "_enl.png", $iiis);
            }
            //----OlisStyle-----------------
            if ($_SESSION["cat_id"] == 58) {
                $iiis = str_replace("-70x81.jpg", "-500x579.jpg", $iiis);
            }
            //----Sellin----------------
            if ($_SESSION["cat_id"] == 23) {
                $iiis = str_replace("product_pictures", "products_pictures", $iiis);
                $iiis = str_replace("_enl.jpg", ".jpg", $iiis);
                $iiis = str_replace("_enl.JPG", ".JPG", $iiis);
                $iiis = str_replace("_thm.JPG", ".JPG", $iiis);
                $iiis = str_replace("_thm.jpg", ".jpg", $iiis);
                $iiis = str_replace("_th.jpg", ".jpg", $iiis);
                $iiis = str_replace("_th.JPG", ".JPG", $iiis);
            }
            //------Majaly-Nelli_co-------------------					
            if ($_SESSION["cat_id"] == 65 || $_SESSION["cat_id"] == 62) {

                $iiis = str_replace("w200_h200", "w640_h640", $iiis);
                $iiis = str_replace("w40_h40", "w640_h640", $iiis);
            }
            //----B1----------------
            if ($_SESSION["cat_id"] == 64) {
                $iiis2 = $html->find($_SESSION["dopimg"], $idimg)->href;
                $po = strpos($iiis2, ".jpg");
                if ($po !== false) {
                    $iiis = $iiis2;
                    if (isset($_GET["step"])) {
                        echo "yes";
                    }
                } else {
                    if (isset($_GET["step"])) {
                        echo "no";
                    }
                }

                $idimg++;
            }
            //------Crisma-------------------					
            if ($_SESSION["cat_id"] == 87) {
                $iiis = str_replace("m.jpg", ".jpg", $iiis);
            }
            //------Vitality-------------------					
            if ($_SESSION["cat_id"] == 88 || $_SESSION["cat_id"] == 205) {
                $iiis = str_replace("__1.jpg", "_1.jpg", $iiis);
                $iiis = str_replace("/thumbnail/70x/", "/thumbnail/", $iiis);
            }
            //------Helen Laven-------------------					
            if ($_SESSION["cat_id"] == 217) {
                $iiis = str_replace("_m.jpg", "_b.jpg", $iiis);
            }
            //------Jhiva-------------------					
            if ($_SESSION["cat_id"] == 219) {
                $iiis = str_replace("74x110.JPG", "680x970.JPG", $iiis);
            }

            $iiis = str_replace("http://", "", $iiis);
            $iiis = str_replace($domain, "", $iiis);
            $src33 = $domain . "/" . $iiis;
            $src33 = str_replace("//", "/", $src33);
            $src33 = "http://" . $src33;

            $src33 = str_replace("majaly.com.ua/", "", $src33);
            $src33 = str_replace("nelli-co.com/", "", $src33);
            if (strpos($src33, "%") === false) {
                $src33 = rawurlencode($src33);
            }
            $src33 = str_replace("%3A", ":", $src33);
            $src33 = str_replace("%2F", "/", $src33);
            $src33 = str_replace("%2520", "%20", $src33);
            $hp = strpos($src33, "http://images.ua.prom.st");
            //------SKHouse-------------------					
            if ($_SESSION["cat_id"] == 49) {
                $src33 = str_replace("%25", "%", $src33);
            }

            if ($fii <= 1) {
                if ($src33 != "http://b-1.ua/")
                    if ($src33 != "") {
                        //ad_new_img($src33,$comid,"");												
                        //echo "DopImgZmi: <a href={$src33} target='_blank'>{$src33}</a><br/>";
                        $setArr.=$src33 . "|";
                    }
            }
        }
        //	echo "<br/>SetArr: ".$setArr;
        $getArr = explode("|", $setArr);
        for ($i = 0; $i < count($getArr); $i++) {
            for ($j = $i; $j < count($getArr); $j++) {
                if ($i != $j) {
                    if ($getArr[$i] != "e")
                        if ($getArr[$i] == $getArr[$j]) {
                            $getArr[$j] = "e";
                        }
                }
            }
        }


        for ($i = 0; $i < count($getArr); $i++) {
            if ($getArr[$i] != "")
                if ($getArr[$i] != "e") {
                    if (strpos($src33, "%") === false) {
                        $src33 = rawurlencode($src33);
                    }
                    $src33 = str_replace("%3A", ":", $src33);
                    $src33 = str_replace("%2F", "/", $src33);

                    $typeImg2 = explode(".", $getArr[$i]);
                    $type2 = $typeImg2[count($typeImg2) - 1];

                    if ($type == 'png') {
                        $typeName2 = explode("/", $getArr[$i]);
                        $typeName22 = $typeName2[count($typeName2) - 1];
                        $typeName22 = strstr($typeName22, '.', type);
                        $getArr[$i] = convert_image_type($type2, 'jpg', $getArr[$i], $typeName22);
                    }
                    ad_new_img($getArr[$i], $commodityID, "");
                    if (isset($_GET["step"])) {
                        echo "DopImgNew: <a href={$getArr[$i]} target='_blank'>{$getArr[$i]}</a><br/>";
                    }
                }
        }
        //	echo "<br/>End";
        //	die();
//===============XML DopImg======================================================
        //------Glem--------------------					
        if ($_SESSION["cat_id"] == 15) {
            $imGet = "";
            for ($id = 0; $id <= count($gxml->shop->offers->offer); $id++) {
                if ($new_url == $gxml->shop->offers->offer[$id]->url) {
                    for ($j = 0; $j < count($gxml->shop->offers->offer[$id]->picture); $j++) {
                        $iiig = $gxml->shop->offers->offer[$id]->picture[$j];
                        $imGet.=$iiig . "|";
                    }
                }
            }
        }
        //echo "<br/>".$imGet;
        $imArr = explode("|", $imGet);
        //	echo "<br/>";
        for ($i = 0; $i < count($imArr); $i++) {
            for ($j = $i; $j < count($imArr); $j++) {
                if ($i != $j) {
                    if ($imArr[$i] != "e")
                        if ($imArr[$i] == $imArr[$j])
                            $imArr[$j] = "e";
                }
            }
        }
        for ($i = 0; $i < count($imArr); $i++) {
            if ($imArr[$i] != "http://www.glem.com.ua/")
                if ($imArr[$i] != "e" && $imArr[$i] != "") {
                    ad_new_img($imArr[$i], $commodityID, "");
                    if (isset($_GET["step"])) {
                        echo "XMLnew DopImg: " . $imArr[$i] . "<br/>";
                    }
                }
        }

//==================================================
        $count = count($html->find("#tabs1 td"));
        $i = 0;
    }
    sleep(1);
}

if (isset($_GET["step"])) {

    if (true) {
        if ($_POST["price"] != "") {
            $_SESSION["id"] = $_POST["id"];
            $_SESSION["cat_id"] = $_POST["cat_id"];
            $_SESSION["h1"] = $_POST["h1"];
            $_SESSION["img"] = $_POST["img"];
            $_SESSION["price"] = $_POST["price"];
            $_SESSION["price2"] = $_POST["price2"];
            $_SESSION["desc"] = $_POST["desc"];
            $_SESSION["sizeCol"] = $_POST["sizeCol"];
            $_SESSION["cod"] = $_POST["cod"];
            $_SESSION["dopimg"] = $_POST["dopimg"];
            $_SESSION["links11"] = $_POST["links11"];
            $_SESSION["per"] = $_POST["per"];
            $_SESSION["no_nal"] = $_POST["no_nal"];
        }
        $count = count($_SESSION["links"]);
        if ($count > 0) {


            $links = $_SESSION["links"];

            $step = $_GET["step"];
            $step2 = $step + 1;
            $count2 = $count - $step;
            echo "Отсалось: {$count2}";


            gettover($links[$step]);

            $request_url = "/parser.php?step={$step2}";

            $a = $count / 100;
            $a2 = $step / $a;

            $iddd = $_SESSION["id"];
            $today = date("d-m-Y H:i:s");
            $isql = mysql_query("SELECT * FROM `parser_interface` WHERE `par_id`='{$iddd}'; ");
            $par = mysql_fetch_assoc($isql);
            if ($par) {
                $step2-=2;
                mysql_query("UPDATE `parser_interface` SET `update_prog`='{$a2}', `update_add`='{$step2}' WHERE `par_id`='{$iddd}' ");
                if ($step2 == 2) {
                    //	mysql_query("UPDATE `parser_interface` SET `update_add`=`update_add`+1, `update_date`='{$today}' WHERE `par_id`='{$iddd}' ");
                    mysql_query("UPDATE `parser_interface` SET `update_date`='{$today}' WHERE `par_id`='{$iddd}' ");
                }
            } else {
                mysql_query("INSERT INTO `parser_interface` (`par_id`) VALUES ('{$iddd}') ");
            }

            if ($step == $count) {
                unset($_SESSION["links"]);
            } else {

                echo "<script>setTimeout('ddddd();', 1500);
							
							function ddddd()
							{
								location.href='{$request_url}';
							}
							</script>";
            }
        } else {
            if (isset($_POST["rrr"])) {
                if ($_POST["rrr"] == 1) {
                    for ($i = $_POST["from"], $ii = 1; $i <= $_POST["to"]; $i++, $ii++) {
                        $links[$ii] = str_replace("iii", $i, $_POST["url"]);
                    }
                } else {
                    $_SESSION["links11"] = str_replace(" ", "", $_SESSION["links11"]);
                    $_SESSION["links11"] = str_replace("http", "\nhttp", $_SESSION["links11"]);
                    $links22 = explode("\n", str_replace("\r", '', $_SESSION["links11"]));
                    $ii = 1;
                    foreach ($links22 as $key => $value) {
                        $links[$ii] = $value;
                        $ii++;
                    }
                }
                $_SESSION["links"] = $links;


                $new_url = "http://ukrmobile.com.ua/index.php?categoryID={$_SESSION["cat_id"]}&show_all=yes";

                $request_url = "/parser.php?step=1";

                echo "<script>location.href='{$request_url}';</script>";
            } elseif ($_GET["step"]) {
                
            } else {

                if ($request_url == "/parser.php") {
                    unset($catttts);
                    unset($_SESSION["links"]);
                    unset($_SESSION["cat_id"]);
                    unset($_SESSION["cat2_id"]);
                    $_SESSION["start"] = 0;
                    echo "Начните импорт
									
									<form action='/parser.php?step=1' method='POST'><br />
									Импортировать: <br>
									<input type='radio' name='rrr' value=1 id='yy1' checked> правилом<br>
									<input type='radio' name='rrr' value=2 id='yy2'> списком<br>
									<div id='g1'>						
										Ссылка на товар (всесто ID - iii, пример http://ukrmobile.com.ua/index.php?productID=iii )
										<br /><input type='text' name='url' value=''><br />
										Диапазон от:
										<br /><input type='text' name='from' value='1'><br>
										Диапазон до:
										<br /><input type='text' name='to' value='10'>
									</div>
									<div style='display:none;' id='g2'>
										<textarea name='links11' style='width:100%;height:200px;'></textarea>
									</div>
									
									
									<br>
									
									ID категории для импорта:
									<br /><input type='text' name='cat_id' value=''>
									<br>
									
									Заголовок:
									<br /><input type='text' name='h1' value='h1'>
									<br>
									
									Картинка:
									<br /><input type='text' name='img' value=''>
									<br>
									
									Цена:
									<br /><input type='text' name='price' value=''>
									<br>
									Оптовая Цена:
									<br /><input type='text' name='price2' value=''>
									<br>
									Описание:
									<br /><input type='text' name='desc' value=''>
									<br>
									Код:
									<br /><input type='text' name='cod' value=''>
									<br>
									<br>
									Дополнительные фото:
									<br /><input type='text' name='dopimg' value=''>
									<br>
									<br>
																Наценка (%):
									<br /><input type='text' name='per' value='0'>
									<br>
									<br>
									<input type='submit' value='Start'>
									</form>
									
									
									<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js' type='text/javascript'></script>
									
									 <script type='text/javascript'>
		        $(document).ready(function() {
		          
		            	jQuery('#yy2').click(function()
				{
					jQuery('#g1').hide();
					jQuery('#g2').show();
				}); 
				
		            	jQuery('#yy1').click(function()
				{
					jQuery('#g2').hide();
					jQuery('#g1').show();
				}); 
		   
		        });       
		    </script>
		    <style>
			input{width:300px;}
		    </style>
									
									";
                } else {
                    echo "импорт полностью завершен!!!!!!!!!!";
                }
            }
        }
    } else {
        echo count($_GET);
    }
} else {
    $time_start = microtime(true);
    echo "Developer auto\n";

    //------Info log---------------
    $today = date("d-m-Y H:i:s");
    $log = $today . " Start parser \n";
    echo $log;
    $w = mysql_query("SELECT * FROM `parser_log` WHERE `id`='1';");
    $wr = mysql_fetch_assoc($w);
    $write = $wr['log'] . $log;
//	echo $write."\n";
    mysql_query("UPDATE `parser_log` SET `log`='{$write}' WHERE `id`=1; ");
    //----------------------------------

    /* if($_POST["price"]!="")
      {

      $_SESSION["cat_id"]=$_POST["cat_id"];
      $_SESSION["h1"]=$_POST["h1"];
      $_SESSION["img"]=$_POST["img"];
      $_SESSION["price"]=$_POST["price"];
      $_SESSION["price2"]=$_POST["price2"];
      $_SESSION["desc"]=$_POST["desc"];
      $_SESSION["sizeCol"]=$_POST["sizeCol"];
      $_SESSION["cod"]=$_POST["cod"];
      $_SESSION["dopimg"]=$_POST["dopimg"];
      $_SESSION["links11"]=$_POST["links11"];
      $_SESSION["per"]=$_POST["per"];
      $_SESSION["no_nal"]=$_POST["no_nal"];
      }
      $count=count($_SESSION["links"]);
      gettover($links[$step]); */

    $today_h = date("H");

    switch ($today_h) {
        case 1:
            $set = "WHERE `id`='1'";
            break;
        case 2:
            $set = "WHERE `id`='1'";
            break;
        case 3:
            $set = "WHERE `id`='1'";
            break;
        case 4:
            $set = "WHERE `id`='1'";
            break;
        case 5:
            $set = "WHERE `id`='1'";
            break;
        case 6:
            $set = "WHERE `id`='1'";
            break;
        case 7:
            $set = "WHERE `id`='1'";
            break;
        case 8:
            $set = "WHERE `id`='1'";
            break;
        case 9:
            $set = "WHERE `id`='1'";
            break;
        case 10:
            $set = "WHERE `id`='1'";
            break;
        case 11:
            $set = "WHERE `id`='1'";
            break;
        case 12:
            $set = "WHERE `id`='1'";
            break;
        case 13:
            $set = "WHERE `id`='1'";
            break;
        case 14:
            $set = "WHERE `id`='1'";
            break;
        case 15:
            $set = "WHERE `id`='1'";
            break;
    }



    $res = mysql_query("SELECT * FROM  `parser` {$set}");
    while ($p = mysql_fetch_assoc($res)) {
        $link = $p['links11'];
        $cat_name = $p['name'];
        $_SESSION["cat_id"] = $p["cat_id"];
        $_SESSION["h1"] = $p["h1"];
        $_SESSION["img"] = $p["img"];
        $_SESSION["price"] = $p["price"];
        $_SESSION["price2"] = $p["price2"];
        $_SESSION["desc"] = $p["desc"];
        $_SESSION["sizeCol"] = $p["sizeCol"];
        $_SESSION["cod"] = $p["cod"];
        $_SESSION["dopimg"] = $p["dopimg"];
        $_SESSION["links11"] = $p["links11"];
        $_SESSION["per"] = $p["per"];
        $_SESSION["no_nal"] = $p["no_nal"];

        echo $p['cat_id'] . ". ";
        $link2 = explode(" ", $link);
        echo $cat_name . ": " . count($link2) . ";\n";

        //------Info log---------------
        $today = date("d-m-Y H:i:s");
        $log = $today . " Upload brenda: {$cat_name} \n";
        $w = mysql_query("SELECT * FROM `parser_log` WHERE `id`='1';");
        $wr = mysql_fetch_assoc($w);
        $write = $wr['log'] . $log;
        echo $log . "\n";
        mysql_query("UPDATE `parser_log` SET `log`='{$write}' WHERE `id`=1; ");


        for ($i = 0; $i < count($link2); $i++) {
            echo "Commodity: " . $i . "\n";
            $_SESSION['orderr'] = $i;
            gettover($link2[$i]);
            //sleep(1);
        }
    }

    echo "\n";

    for ($i = 0; $i < count($link2); $i++) {
        //	gettover($link2[$i]);
        //sleep(1);
    }

    $time_end = microtime(true);
    $time = $time_end - $time_start;
    echo "Time: " . ($time);

    //------Info log---------------
    $today = date("d-m-Y H:i:s");
    $log = $today . " End parser. Time: " . $time . " \n";
    $w = mysql_query("SELECT * FROM `parser_log` WHERE `id`='1';");
    $wr = mysql_fetch_assoc($w);
    $write = $wr['log'] . $log;
    echo $log . "\n";
    mysql_query("UPDATE `parser_log` SET `log`='{$write}' WHERE `id`=1; ");
    //------------------------------	

    echo "\n";
}
?>
