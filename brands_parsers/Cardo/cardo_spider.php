<?php
//==============================================================================
//			Cardo   	5-1         		
//==============================================================================
$existUrl = TRUE;

//Get Links
$arrayLinks = checkEmptyOrChangeSelector($_SESSION["a_href"], $saw,
    'a_href - ссылки');
$arraySaled = checkEmptyOrChangeSelector('span.prodano', $saw,
    'prodano - на страницы нет продано');
//var_dump($arrayLinks);
//var_dump($arraySaled);
//die;
if (isset($arrayLinks) && !isset($arraySaled)) {
    $countNewLinks = count($arrayLinks);
    echo "\nУРЛ текущей ссылки:  <a href={$curLink} target='_blank' >{$curLink}</a>\n";
    echo "Запарсено новыч ссылок: {$countNewLinks}\n";
    foreach ($arrayLinks as $key => $value) {
        $linksArr[] = trim($arrayLinks[$key]['href']);
    }
} else {
    $existUrl = FALSE;
}
//var_dump($linksArr);
//var_dump($existUrl);
//die;


