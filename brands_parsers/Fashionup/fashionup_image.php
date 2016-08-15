<?php

use Parser\Provader\ProvaderPageFactory;

//var_dump($_SESSION["imgMain"]);
//var_dump($_SESSION["imgDop"]);
//==============================================================================
//			Fashion up   	1-2  				
//==============================================================================
try {
    $provaderPage = ProvaderPageFactory::build(0, 0, $curLink);
    $sawNok       = $provaderPage->nokogiriObject;
} catch (Exception $ex) {
    var_dump($ex->getMessage());
}


$existIm      = FALSE;
$srcProdArray = array('mainSrcImg' => "", 'dopSrcImg' => "");
$photoIdArray = array();

//Image
$srcProd = "";
if (isset($_SESSION["imgMain"])) {
    $lowSrc                     = $_SESSION["imgMain"];
    $srcProd                    = filterUrlImage($lowSrc, $curLink);
    $srcProdArray['mainSrcImg'] = $srcProd;
    $existIm                    = TRUE;
}
//var_dump($srcProd);
//DopImage
$arrayDopImage = checkEmptyOrChangeSelector($_SESSION["dopimg"], $sawNok,
    'dopimg - дополнительны картинки');

$srcDopIm = "";
if (isset($arrayDopImage)) {
    if ($verify == "verify") {
        deleteDopImgFromDB($commodityID, $mysqli);
    }
    foreach ($arrayDopImage as $value) {
        $srcDopIm = filterUrlImage(str_replace("tov/204_", "tov/", $value['src']),
            $curLink);
        if ($srcProdArray['mainSrcImg'] !== $srcDopIm) {
            $srcProdArray['dopSrcImg'][] = $srcDopIm;
            $existIm                     = TRUE;
            $photoIdArray[]              = insertInShopImBd($commodityID,
                $mysqli);
        }
    }
}
//var_dump($srcProdArray);
//die;
//CropandWrite images
if ($existIm == TRUE) {
    if (!empty($srcProdArray['dopSrcImg'])) {
        $srcProdArray['dopSrcImg'] = array_values(array_unique($srcProdArray['dopSrcImg']));
    }
    $nameImArray = array('title', 's_title', $photoIdArray);
    $brendName   = "jhiva_images/";
    cropAndWriteImageBegin($srcProdArray, $commodityID, $nameImArray,
        $brendName, $idBrand);
}

