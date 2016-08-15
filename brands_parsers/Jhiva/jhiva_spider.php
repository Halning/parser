<?php
//==============================================================================
//			Jhiva   	35-219          		
//==============================================================================
$existUrl   = TRUE;
//--------------------------------Get Links-------------------------------2-----
$arrayLinks = checkEmptyOrChangeSelector($_SESSION["a_href"], $saw,
    'a_href - ссылки');
//var_dump($arrayLinks);
//die;
if (isset($arrayLinks)) {
    $countNewLinks = count($arrayLinks);
    echo "\nУРЛ текущей ссылки:  <a href={$curLink} target='_blank' >{$curLink}</a>\n";
    echo "Запарсено новыч ссылок: {$countNewLinks}\n";
    foreach ($arrayLinks as $key => $value) {
        $linksArr[] = 'http://jhiva.com.ua'.trim($value['href']);
    }
} else {
    $existUrl = FALSE;
}
//var_dump($linksArr);
//var_dump($existUrl);
//die;
