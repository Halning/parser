<?php
//==============================================================================
//			Dembo Hause   	34-218           		
//==============================================================================
$existUrl   = FALSE;
//--------------------------------Get Links-------------------------------2-----
$arrayLinks = checkEmptyOrChangeSelector($_SESSION["a_href"], $saw,
    'a_href - ссылки');
//var_dump($arrayLinks);

if (isset($arrayLinks)) {
    $countNewLinks = count($arrayLinks);
    echo "\nУРЛ текущей ссылки:  <a href={$curLink} target='_blank' >{$curLink}</a>\n";
    echo "Запарсено новыч ссылок: {$countNewLinks}\n";
    foreach ($arrayLinks as $key => $value) {
        $linksArr[] = trim($arrayLinks[$key]['href']);
    }
} 
//var_dump($linksArr);
//var_dump($existUrl);
//die;