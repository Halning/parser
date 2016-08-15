<?php
//==============================================================================
//                          B1   	24 (64,66)    				
//==============================================================================
$existIm      = FALSE;
$srcProdArray = array('mainSrcImg' => "", 'dopSrcImg' => "");
$photoIdArray = array();

//Image
$arrayImage = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');

$srcProd = "";
if (isset($arrayImage)) {
    $lowSrc  = $arrayImage[0]['src'];
    $srcProd = filterUrlImage($lowSrc, $curLink);
    //$srcProdArray['mainSrcImg'] = $srcProd;
    $existIm = TRUE;
    $srcCod  = str_replace("http://b-1.ua/content/shop/products/", "", $srcProd);
    $srcCod  = strstr($srcCod, "-", true);
    $srcCod  = strstr($srcCod, "/");
}
//var_dump($srcCod);
//die;
//DopImage
$arrayDopImage = checkEmptyOrChangeSelector($_SESSION["dopimg"], $saw,
    'dopimg - дополнительны картинки');
//var_dump($arrayDopImage);

$srcDopIm = "";
if (isset($arrayDopImage)) {
    if ($verify == "verify") {
        deleteDopImgFromDB($commodityID, $mysqli);
    }
    foreach ($arrayDopImage as $value) {
        if (isset($value['href'])) {
            $srcDopIm = filterUrlImage($value['href'], $curLink);
            if (strstr($srcDopIm, $srcCod)) {
                $srcProdArray['mainSrcImg'] = $srcDopIm;
            }
            if ($srcProdArray['mainSrcImg'] !== $srcDopIm) {
                $srcProdArray['dopSrcImg'][] = $srcDopIm;
                $existIm                     = TRUE;
                $photoIdArray[]              = insertInShopImBd($commodityID,
                    $mysqli);
            }
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
    $brendName   = "vitalityKIDS_images/";
    cropAndWriteImageBegin($srcProdArray, $commodityID, $nameImArray,
        $brendName, $idBrand);
}