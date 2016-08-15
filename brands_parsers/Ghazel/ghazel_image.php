<?php
//var_dump($_SESSION["imgMain"]);
//var_dump($_SESSION["imgDop"]);
//==============================================================================
//			Ghazel	43-311
//==============================================================================
$existIm      = FALSE;
$srcProdArray = array('mainSrcImg' => "", 'dopSrcImg' => "");
$photoIdArray = array();

//Image
$srcProd = "";
if (isset($_SESSION["imgMain"])) {
    $lowSrc                     = $_SESSION["imgMain"][0];
    $srcProd                    = filterUrlImage($lowSrc, $curLink);
    $srcProdArray['mainSrcImg'] = $srcProd;
    $existIm                    = TRUE;
}
//var_dump($srcProd);
//DopImage
$srcDopIm = "";
if (isset($_SESSION["imgDop"]) && $_SESSION["imgDop"][0] !== "") {
    if ($verify == "verify") {
        deleteDopImgFromDB($commodityID, $mysqli);
    }
    foreach ($_SESSION["imgDop"] as $value) {
        $srcDopIm = filterUrlImage($value, $curLink);
        if ($srcProdArray['mainSrcImg'] !== $srcDopIm && $srcDopIm !== 'http://www.glem.com.ua/') {
            $srcProdArray['dopSrcImg'][] = $srcDopIm;
            $existIm                     = TRUE;
            $photoIdArray[]              = insertInShopImBd($commodityID,
                $mysqli);
        }
    }

    foreach ($_SESSION["imgMain"] as $value) {
        $srcDopIm = filterUrlImage($value, $curLink);
        if ($srcProdArray['mainSrcImg'] !== $srcDopIm && $srcDopIm !== "http://www.glem.com.ua/") {
            $srcProdArray['dopSrcImg'][] = $srcDopIm;
            $existIm                     = TRUE;
        }
    }
} else {
    unset($srcProdArray['dopSrcImg']);
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

