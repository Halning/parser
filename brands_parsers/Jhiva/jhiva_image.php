<?php
//==============================================================================
//			Helen Laven	32-217				
//==============================================================================
$existIm      = FALSE;
$srcProdArray = array('mainSrcImg' => "", 'dopSrcImg' => "");
$photoIdArray = array();
//--------------------------------Image-------------------------------------8---
$arrayImage   = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');
//var_dump($arrayImage[0]['href']);

$srcProd = "";
if (isset($arrayImage)) {
    $srcProd                    = "http:{$arrayImage[0]['href']}";
    $srcProdArray['mainSrcImg'] = $srcProd;
    $existIm                    = TRUE;
}
//var_dump($srcProd);
//--------------------------------DopImage----------------------------------9---
$arrayDopImage = checkEmptyOrChangeSelector($_SESSION["dopimg"], $saw,
    'dopimg - дополнительны картинки');

$srcDopIm = "";
if (isset($arrayDopImage)) {
    if ($verify == "verify") {
        deleteDopImgFromDB($commodityID, $mysqli);
    }
    foreach ($arrayDopImage as $value) {
        $srcDopIm = "http:{$value['href']}";
        if ($srcProdArray['mainSrcImg'] !== $srcDopIm) {
            $srcProdArray['dopSrcImg'][] = $srcDopIm;
            $existIm                     = TRUE;
            $photoIdArray[]              = insertInShopImBd($commodityID,
                $mysqli);
        }
    }
}
//var_dump($srcProdArray);
//----------------------------CropandWrite images----------------------------10-
if ($existIm == TRUE) {
    if (!empty($srcProdArray['dopSrcImg'])) {
        $srcProdArray['dopSrcImg'] = array_values(array_unique($srcProdArray['dopSrcImg']));
    }
    $nameImArray = array('title', 's_title', $photoIdArray);
    $brendName   = "jhiva_images/";
    cropAndWriteImageBegin($srcProdArray, $commodityID, $nameImArray,
        $brendName, $idBrand);
}
//==============================================================================
