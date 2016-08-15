<?php
//==============================================================================
//                          Lenida	7-16	         		
//==============================================================================
$existUrl   = TRUE;
$pagination = array();

//Exist page
$arrayPagination = checkEmptyOrChangeSelector('.breadcrumbs', $saw,
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
//Get Links
$arrayLinks = checkEmptyOrChangeSelector($_SESSION["a_href"], $saw,
    'a_href - ссылки');
//var_dump($arrayLinks);

if (isset($arrayLinks)) {
    $countNewLinks = count($arrayLinks) / 2;
    echo "\nУРЛ текущей ссылки:  <a href={$curLink} target='_blank' >{$curLink}</a>\n";
    echo "Запарсено новыч ссылок: {$countNewLinks}\n";
    foreach ($arrayLinks as $key => $value) {
        if (substr_count(trim($arrayLinks[$key]['href']), ".html") > 0) {
            $link       = "http://www.lenida.com.ua".trim($arrayLinks[$key]['href']);
            $linksArr[] = $link;
        }
    }
}
//var_dump($linksArr);
//var_dump($existUrl);
//die;


