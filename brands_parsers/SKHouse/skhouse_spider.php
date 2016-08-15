<?php
//==============================================================================
//			SKHouse   	14-49            		
//==============================================================================
$existUrl = FALSE;

//Get Links
preg_match("/artList:(.*)'/", $pageBody, $matches);
//var_dump($matches);

if (isset($matches[1])) {
    $matches[1] = trim(preg_replace("/\\s'/", "", $matches[1]));

    $articliArray = explode("|", $matches[1]);

    foreach ($articliArray as $value) {
        $linksArr[] = "http://sk-house.ua/Products/Product/".trim($value);
    }
}
//var_dump($linksArr);
//var_dump($existUrl);
//die;

