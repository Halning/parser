<?php
//==============================================================================
//				Dajs	33-215				
//==============================================================================
$existIm      = FALSE;
$srcProdArray = array('mainSrcImg' => "", 'dopSrcImg' => "");
$photoIdArray = array();
//--------------------------------Image-------------------------------------8---
$arrayImage   = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');

$srcProd = "";
if (isset($arrayImage)) {
    $lowSrc                     = str_replace("-320x480.jpg", ".jpg",
        $arrayImage[0]['src']);
    $lowSrc                     = str_replace("cache/", "", $lowSrc);
    $srcProd                    = filterUrlImage($lowSrc, $curLink);
    $srcProdArray['mainSrcImg'] = $srcProd;
    $existIm                    = TRUE;
}
//var_dump($srcProd);
//----------------------------CropandWrite images----------------------------10-
if ($existIm == TRUE) {
    $nameImArray = array('title', 's_title', $photoIdArray);
    $brendName   = "dajs_images/";
    cropAndWriteImageBegin($srcProdArray, $commodityID, $nameImArray,
        $brendName, $idBrand);
}
