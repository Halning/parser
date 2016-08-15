<?php

use Modules\MySQLi;

error_reporting(-1);
header('Content-type: text/html; charset=utf-8');

require_once("../vendor/autoload.php");

$mysqli = MySQLi::getInstance()->getConnect();

session_start();

//eсли есть гет ид то фильтруем его и начинаем работу
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (isset($id) && !empty($id) && $id != "") {
//==============================================================================
//       id бренда и по нему выбираем данные с талицы parser
//==============================================================================
    //бренд Art Milano
    if ($id == 23 || $id == 24) {
        $catIdArtM = 66;
        $id        = 24;
        $sqlShCom  = "SELECT commodity_ID ,from_url FROM shop_commodity WHERE brand_id IN (?,?)";
    } else {
        $sqlShCom = "SELECT commodity_ID ,from_url FROM shop_commodity WHERE brand_id=?";
    }

    //выбираем данные
    if (!($stmt = $mysqli->prepare("SELECT cat_id, im_url ,a_href
                                    FROM parser WHERE id=?"))) {
        die('Se- Error нет бренда в parser('.$mysqli->errno.') '.$mysqli->error);
    } else {
        $stmt->bind_param("i", $id);
        $stmt->execute();

        //Записываем данные с этого скрипта в сессии(селекторы из таблицы parser)
        $stmt->bind_result($_SESSION["cat_id"], $_SESSION["im_url"],
            $_SESSION["a_href"]);
        $stmt->fetch();
        $stmt->close();
    }
    $_SESSION["id"] = $id;
//==============================================================================
//   Вытягиваем все данные товара из таблицы товара а ссылки записываем в массив
//==============================================================================
    if (!($stmt           = $mysqli->prepare($sqlShCom))) {
        die('Select Error нет бренда в parser('.$mysqli->errno.') '.$mysqli->error);
    } else {
        if ($id == 24) {
            $stmt->bind_param("ii", $_SESSION["cat_id"], $catIdArtM);
        } else {
            $stmt->bind_param("i", $_SESSION["cat_id"]);
        }
        $stmt->execute();
        $stmt->bind_result($comId, $comUrl);

        //Выбрать значения
        while ($stmt->fetch()) {
            $updateData['commodity_ID'][] = $comId;
            $updateData['from_url'][]     = $comUrl;
        }
        $stmt->close();
    }
//==============================================================================
//              проверяем есть ли товары для проверки
//==============================================================================
    if (isset($updateData)) {

        //Записываем данные с этого скрипта в сессии(селекторы из таблицы parser)
        foreach ($updateData as $key => $value) {
            $value            = array_combine(array_merge(array_slice(array_keys($value),
                        1), array(count($value))), array_values($value));
            $updateData[$key] = $value;
        }
        $_SESSION['updateData'] = $updateData;
    } else {
        $_SESSION['updateData'] = FALSE;
    }
//var_dump($updateData);
//die;
    header("Location: http://".filter_input(INPUT_SERVER, 'HTTP_HOST',
            FILTER_SANITIZE_STRING)."/parser/spider_main.php?step=0");
} else {
    die("Id not found!!!");
}


    