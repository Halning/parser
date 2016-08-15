<?php
//==============================================================================
//                       Lavana Fashion	39-242	         		
//==============================================================================
$existUrl        = TRUE;
$pagination      = array();
//--------------------------------Exist page------------------------------1-----
$arrayPagination = checkEmptyOrChangeSelector('.pages', $saw,
    'pagination - страницы');
//var_dump($arrayPagination);
if (isset($arrayPagination)) {
    foreach ($arrayPagination as $value) {
        $regexp = '/[0-9]/';
        if (preg_match($regexp, $value)) {
            $pagination[] = (int) $value;
        }
    }
    $pagination = max($pagination);
    if ($i == $pagination) {
        $existUrl = FALSE;
    }
} else {
    $existUrl = FALSE;
}
//var_dump($pagination);
//die;
//--------------------------------Get Links-------------------------------2-----
$arrayLinks = checkEmptyOrChangeSelector($_SESSION["a_href"], $saw,
    'a_href - ссылки');
//var_dump($arrayLinks);

if (isset($arrayLinks)) {
    $countNewLinks = count($arrayLinks);
    echo "\nУРЛ текущей ссылки:  <a href={$curLink} target='_blank' >{$curLink}</a>\n";
    echo "Запарсено новыч ссылок: {$countNewLinks}\n";
    foreach ($arrayLinks as $key => $value) {
        $link       = trim($arrayLinks[$key]['href']);
        $linksArr[] = $link;
    }
}
//var_dump($linksArr);
//var_dump($existUrl);
//die;


