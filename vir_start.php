<?php

use Modules\MySQLi;

error_reporting(-1);
header('Content-type: text/html; charset=utf-8');

require_once("../vendor/autoload.php");

$mysqli = MySQLi::getInstance()->getConnect();

session_start();
//==============================================================================
//                           входные данные 
//==============================================================================
//не из админки
//$_SESSION['comVisibl']  = 1; ////!!!!!!!! выбираем проверять опуликованные или неопубликованные товары
//$_SESSION['changeIm']   = FALSE;
//$_SESSION['changeCod']  = FALSE;
//$_SESSION['changeName'] = FALSE;
//$_SESSION['changeDesc'] = FALSE;
//$idsBrend = [36, 35, 34, 33, 32, 31, 30, 29, 27, 26, 25, 24, 23, 21, 20, 19, 17, 16, 14, 13, 11, 10, 9, 7, 6, 5, 4, 1]; ////!!!!!!!! ид брендов которые будут проверяться
//$idsBrend               = [36];
//из админки
$idsBrend = $_SESSION['idsBrend'];
//var_dump($idsBrend);
//var_dump($_SESSION['comVisibl']);
//var_dump($_SESSION['changeIm']);
//var_dump($_SESSION['changeCod']);
//var_dump($_SESSION['changeName']);
//var_dump($_SESSION['changeDesc']);
//die;
//==============================================================================
//гет появляеться если проверяем несколько брендов иначе берем первый id бренда 
//==============================================================================
$id       = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
if (isset($id) && !empty($id) && $id !== "") {
    foreach ($idsBrend as $key => $idBrand) {
        if ($idBrand == $id) {
            $_SESSION['id'] = $idBrand;
            $id             = $_SESSION['id'];
        }
    }
} else {
    $_SESSION['id']       = $idsBrend[0];
    $id                   = $_SESSION['id'];
    $_SESSION['idsBrend'] = $idsBrend;
}

//==============================================================================
//       id бренда и по нему выбираем данные с талицы parser
//==============================================================================
//бренд Art Milano
if ($id == 23) {
    $catIdArtM = 66;
    $id        = 24;
}

//выбираем данные
if (!($stmt = $mysqli->prepare("SELECT cat_id ,price,
                                    price2, no_nal, sizeColor,
                                    cod, h1, `desc`, img, dopimg
                                    FROM parser WHERE id=?"))) {
    die('Error нет бренда в parser('.$mysqli->errno.') '.$mysqli->error);
} else {
    $stmt->bind_param("i", $id);
    $stmt->execute();

    //Записываем данные с этого скрипта в сессии(селекторы из таблицы parser)
    $stmt->bind_result($_SESSION["cat_id"], $_SESSION["price"],
        $_SESSION["price2"], $_SESSION["no_nal"], $_SESSION["sizeCol"],
        $_SESSION["cod"], $_SESSION["h1"], $_SESSION["desc"], $_SESSION["img"],
        $_SESSION["dopimg"]);
    $stmt->fetch();
    $stmt->close();
}

//бренд Art Milano
if (isset($catIdArtM)) {
    $_SESSION["cat_id"] = 66;
}
//var_dump($_SESSION["price"]);
//var_dump($_SESSION["cat_id"]);
//die;
//==============================================================================
//   Вытягиваем все данные товара из таблицы товара а ссылки записываем в массив
//==============================================================================
if (!($stmt = $mysqli->prepare("SELECT commodity_ID ,from_url,
                                    commodity_price, commodity_price2,
                                    com_sizes, commodity_select, cod, com_name, 
                                    com_fulldesc, size_count
                                    FROM shop_commodity WHERE brand_id=? AND commodity_visible=?"))) {
    die('Select Error нет бренда в parser('.$mysqli->errno.') '.$mysqli->error);
} else {
    $stmt->bind_param("ii", $_SESSION["cat_id"], $_SESSION['comVisibl']);
    $stmt->execute();
    $stmt->bind_result($comId, $comUrl, $comPrice, $comPrice2, $comSize,
        $comSelect, $code, $comName, $comDesc, $comCount);

    //Выбрать значения
    while ($stmt->fetch()) {
        $updateData['commodity_ID'][]     = $comId;
        $updateData['from_url'][]         = $comUrl;
        $updateData['commodity_price'][]  = $comPrice;
        $updateData['commodity_price2'][] = $comPrice2;
        $updateData['com_sizes'][]        = $comSize;
        $updateData['commodity_select'][] = $comSelect;
        $updateData['cod'][]              = $code;
        $updateData['com_name'][]         = $comName;
        $updateData['com_fulldesc'][]     = $comDesc;
        $updateData['size_count'][]       = $comCount;
    }
    $stmt->close();
}
//var_dump($updateData['com_name']);
//проверяем есть ли товары для проверки
if (isset($updateData)) {

    //Записываем данные с этого скрипта в сессии(селекторы из таблицы parser)
    foreach ($updateData as $key => $value) {
        $value            = array_combine(array_merge(array_slice(array_keys($value),
                    1), array(count($value))), array_values($value));
        $updateData[$key] = $value;
    }
    $_SESSION['updateData'] = $updateData;
} else {

    //Проверяем есть ли еще бренды для проверкм
    $idsBrendComplite[]           = $id;
    $_SESSION['idsBrendComplite'] = $idsBrendComplite;
    $_SESSION['idsBrend']         = array_values(array_diff($_SESSION['idsBrend'],
            $_SESSION['idsBrendComplite']));
    //var_dump($_SESSION['idsBrend']);
    if (count($_SESSION['idsBrend']) == 0) {
        $_SESSION = array();

        //Закрываем соединение
        $mysqli->close();
        die("Скрипт закочил Работу!!!");
    }
    header("Refresh: 5;http://".filter_input(INPUT_SERVER, 'HTTP_HOST',
            FILTER_SANITIZE_STRING)."/parser/vir_start.php?id=".$_SESSION['idsBrend'][0]);
    die('Товар для проверки по бренду ('.$id.') нет в БД!!!');
}
//var_dump($updateData);
header("Location: http://".filter_input(INPUT_SERVER, 'HTTP_HOST',
        FILTER_SANITIZE_STRING)."/parser/vir_main.php?step=0");
