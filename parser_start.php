<?php

use Modules\MySQLi;

header('Content-type: text/html; charset=utf-8');
error_reporting(-1);

require_once("../vendor/autoload.php");

$mysqli = MySQLi::getInstance()->getConnect();

//eсли есть гет ид то фильтруем его и начинаем работу
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (isset($id) && !empty($id) && $id != "") {
//==============================================================================
//       id бренда и по нему выбираем данные с талицы parser
//==============================================================================
    if ($id == 23 || $id == 24) {

        //бренд Art Milano
        $catIdArtM = 66;
        $id        = 24;
        $sqlShCom  = "SELECT duplicate,from_url,commodity_ID FROM shop_commodity WHERE brand_id IN (?,?)";
    } else {
        $sqlShCom = "SELECT duplicate,from_url,commodity_ID FROM shop_commodity WHERE brand_id=?";
    }

    //выбираем данные
    session_start();
    $_SESSION = array();
    if (!($stmt     = $mysqli->prepare("SELECT id, name, cat_id, h1, img ,price,
                                    price2, no_nal, sizeColor, `desc`, 
                                    cod, dopimg, new_links, per FROM parser WHERE id=?"))) {
        die('Select Error parser('.$mysqli->errno.') '.$mysqli->error);
    } else {
        $stmt->bind_param("i", $id);
        $stmt->execute();

        //Записываем данные с скрипта parse_start в сессии(селекторы из таблицы parser)
        $stmt->bind_result($_SESSION["id"], $_SESSION["name"],
            $_SESSION["cat_id"], $_SESSION["h1"], $_SESSION["img"],
            $_SESSION["price"], $_SESSION["price2"], $_SESSION["no_nal"],
            $_SESSION["sizeCol"], $_SESSION["desc"], $_SESSION["cod"],
            $_SESSION["dopimg"], $_SESSION["links"], $_SESSION["per"]);
        $stmt->fetch();
        $stmt->close();
    }

    //записываем новую дату год в таблицу парсера
    $curDate = date("Y");
    if (!$mysqli->query("UPDATE parser SET date='{$curDate}' WHERE id='{$id}'")) {
        die("Error update data:".$mysqli->error);
    }

    //var_dump($_SESSION["cat_id"]);
    //die;
//==============================================================================
//              вытягиваем данные для проверки на дубликаты
//==============================================================================    
    //вытягиваем данные для проверки на дубликаты
    $_SESSION['linkArrayCom'][] = '';
    if (!($stmt                       = $mysqli->prepare($sqlShCom))) {
        die('Select Error shop_commodity('.$mysqli->errno.') '.$mysqli->error);
    } else {
        $duplicateArray = array();
        if ($id == 24) {
            $stmt->bind_param("ii", $_SESSION["cat_id"], $catIdArtM);
        } else {
            $stmt->bind_param("i", $_SESSION["cat_id"]);
        }
        $stmt->execute();
        $stmt->bind_result($duplicate, $urlCom, $comExistId);
        while ($stmt->fetch()) {
            $duplicateArray[]                      = $duplicate;
            $_SESSION['linkArrayCom'][$comExistId] = $urlCom;
        }
        $stmt->close();
    }

    //Записываем массив дубликатов и их id в сессии
    $_SESSION['linkArrayCom']   = array_flip(array_unique($_SESSION['linkArrayCom']));
    $_SESSION["duplicateArray"] = $duplicateArray;
    //var_dump($_SESSION["duplicateArray"]);
    //var_dump($_SESSION['linkArrayCom']);
    //die;
    //Закрываем соединение
    $mysqli->close();

    //Запускаем скрипт парсинга
    header("Location: http://".filter_input(INPUT_SERVER, 'HTTP_HOST',
            FILTER_SANITIZE_STRING)."/parser/parser_main.php?step=0");
} else {
    die("Id not found!!!");
}
