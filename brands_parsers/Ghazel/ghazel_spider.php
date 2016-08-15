<?php
//==============================================================================
//                          ghazel	43-311
//==============================================================================
$existUrl = FALSE;

//Get Links
//var_dump($saw);
if (isset($saw)) {
    $countNewLinks = count($saw);
    echo "\nУРЛ текущей ссылки:  <a href={$curLink} target='_blank' >{$curLink}</a>\n";
    echo "Запарсено новыч ссылок: {$countNewLinks}\n";
    foreach ($saw->shop->offers->offer as $key => $offer) {
        $linksArr[] = trim($offer->url);
    }
    $linksArr = array_values(array_unique($linksArr));
} 
//var_dump($linksArr);
//var_dump($existUrl);
//die;



