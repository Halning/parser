<?php
//==============================================================================
//                          Glem	6-15	         		
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


/*$existUrl = TRUE;
$pagination = array();
//Exist page
$arrayPagination = checkEmptyOrChangeSelector('.split-pages', $saw, 'pagination - страницы');
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
$arrayLinks = checkEmptyOrChangeSelector($_SESSION["a_href"], $saw, 'a_href - ссылки');
//var_dump($arrayLinks);
//die;
if (isset($arrayLinks)) {
    $countNewLinks = count($arrayLinks);
    echo "\nУРЛ текущей ссылки:  <a href={$curLink} target='_blank' >{$curLink}</a>\n";
    echo "Запарсено новыч ссылок: {$countNewLinks}\n";
    foreach ($arrayLinks as $key => $value) {
        $linksArr[] = "http://www.glem.com.ua/" . trim($arrayLinks[$key]['href']);
    }
}
//var_dump($linksArr);
//var_dump($existUrl);
//die;*/



