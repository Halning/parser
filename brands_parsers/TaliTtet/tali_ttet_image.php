<?php
//==============================================================================
//                      Tali Ttet   	41-300
//==============================================================================
$existIm      = FALSE;
$srcProdArray = array('mainSrcImg' => "", 'dopSrcImg' => "");
$photoIdArray = array();

//Image
$arrayImage = checkEmptyOrChangeSelector($_SESSION['img'], $saw,
    'img - главная картинка');
//var_dump($arrayImage);

$srcProd = "";
if (isset($arrayImage)) {
    $lowSrc                     = $arrayImage[0]['href'];
    $srcProd                    = filterUrlImage($lowSrc, $curLink);
    $srcProdArray['mainSrcImg'] = $srcProd;
    $existIm                    = TRUE;
}
//var_dump($srcProd);
//DopImage
$arrayDopImage = checkEmptyOrChangeSelector($_SESSION["dopimg"], $saw,
    'dopimg - дополнительны картинки');

$srcDopIm = "";
if (isset($arrayDopImage)) {
    if ($verify == "verify") {
        deleteDopImgFromDB($commodityID, $mysqli);
    }
    foreach ($arrayDopImage as $value) {
        $srcDopIm = filterUrlImage($value['href'], $curLink);
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
    $brendName   = "vitalityKIDS_images/";
    cropAndWriteImageBegin($srcProdArray, $commodityID, $nameImArray,
        $brendName, $idBrand);
}