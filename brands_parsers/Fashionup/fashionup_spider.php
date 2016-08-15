<?php
//==============================================================================
//			Fashion up   	1-2         		
//==============================================================================
$existUrl = FALSE;
//Get Links
//var_dump($saw);
if (isset($saw)) {
    foreach ($saw as $key => $value) {
        //var_dump($value->url);
        if (trim($value->url) !== '') {
            $linksArr[] = trim($value->url);
        }
    }
    $countNewLinks = count($linksArr);
    echo "\nУРЛ текущей ссылки:  <a href={$curLink} target='_blank' >{$curLink}</a>\n";
    echo "Запарсено новыч ссылок: {$countNewLinks}\n";
}
//var_dump($linksArr);
//var_dump($existUrl);
//die;
