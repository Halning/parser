<?php
//==============================================================================
//			Crisma 29-87	         		
//==============================================================================
$existUrl   = TRUE;
//--------------------------------Get Links-------------------------------2-----
$arrayLinks = checkEmptyOrChangeSelector($_SESSION["a_href"], $saw,
    'a_href - ссылки');
//var_dump($arrayLinks);

if (isset($arrayLinks)) {
    $countNewLinks = count($arrayLinks);
    echo "\nУРЛ текущей ссылки:  <a href={$curLink} target='_blank' >{$curLink}</a>\n";
    echo "Запарсено новыч ссылок: {$countNewLinks}\n";
    foreach ($arrayLinks as $key => $value) {
        $wovels     = array("?limit=100", "&limit=100");
        $link       = trim(str_replace($wovels, "", $arrayLinks[$key]['href']));
        $linksArr[] = $link;
    }
} else {
    $existUrl = FALSE;
}
//var_dump($linksArr);
//var_dump($existUrl);
//die;




