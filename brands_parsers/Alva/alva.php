<?php

use Modules\MySQLi;

require_once("../../../vendor/autoload.php");

error_reporting(-1);
$result    = file_get_contents('colors');
$alvaArray = unserialize($result);

$alvaNewArr = array();
foreach ($alvaArray as $key => $value) {
    $valueAll = "";
    foreach ($value as $value1) {
        $valueAll .= $value1.'=;';
    }
    $alvaNewArr[$key] = substr($valueAll, 0, -1);
}
var_dump($alvaNewArr);
foreach ($alvaNewArr as $key => $value) {
    $mysqli = MySQLi::getInstance()->getConnect();
    if (!($stmt   = $mysqli->prepare("UPDATE shop_commodity SET commodity_select=?
                                                    WHERE commodity_ID=?"))) {
        die('Update shop_commodity Error ('.$mysqli->errno.') '.$mysqli->error);
    }
    $stmt->bind_param("si", $value, $key);
    $stmt->execute();
    $stmt->close();
}

