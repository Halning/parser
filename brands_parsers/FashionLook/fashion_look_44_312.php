<?php

namespace Parser;

use Modules\Products;
use Parser\Report\ReportParser;

require_once('../vendor/autoload.php');
require_once('import_verify_functions.php');

set_time_limit(0);

//$exelDoc = new PHPExcelParser(44);
//$exelDoc->writeJsonFile();

//get data from json file
$excelJson = file_get_contents('brands_parsers/FashionLook/data.json');

if ($excelJson) {
    $excelJsonArray = json_decode($excelJson, true);
} else {
    die('Con`t open Json file');
}

$product          = new Products();
$product->order   = 0;
$product->brandId = 312;

//get all articles for check on duplicates
$articles         = $product->getArticleOfAllProducts();

//get list of all images in source directory
$images           = scandir('uploads/images_fl');

$report = new ReportParser(312, 0, 0, "", 20);
$report->createFileReport();
$report->reportStart();
$report->echoStart(20);
$report->reportEnd();

if ($excelJsonArray) {
    foreach ($excelJsonArray as $excelJsonItem) {

        if (is_numeric($excelJsonItem[14]) &&
            is_numeric($excelJsonItem[15]) &&
            !in_array($excelJsonItem[1], $articles)
        ) {

            $product->order++;
            $product->article = $excelJsonItem[1];
            $product->name    = $excelJsonItem[5] ? $excelJsonItem[5] : $excelJsonItem[3];
            $product->alias   = transliterate($product->name).'_'.transliterate($product->article);
            $product->salePrice = ceil($excelJsonItem[14] * 1.2);
            $product->retailPrice = $excelJsonItem[15];
            $product->sizes       = $excelJsonItem[6] ? $excelJsonItem[6] : '';

            //description
            $composition = str_replace('пластик,', 'пластик', $excelJsonItem[11]);
            $composition = str_replace('металл.', 'металл', $composition);
            $description = $excelJsonItem[12];
            $wovels      = array('оправа:', 'линза:', 'материал линзы', '. материал оправы');
            $searchArray = array('оправа:', 'линза:', 'материал линзы:', 'материал оправы:');
            $product->description = str_replace('-', '', getDesc($composition, '', $wovels, $searchArray));

            if ('ж' === $excelJsonItem[9]) {
                $product->description .= '<p><span>Пол:</span> женский</p>';
            } elseif (strpos($excelJsonItem[9], 'ni')) {
                $product->description .= '<p><span>Пол:</span> Uni</p>';
            }

            if (strpos($composition, 'олиэст') || strpos($composition, 'олома')) {
                $product->description = '<p><span>Склад:</span> '.$composition.'</p>';
            }

            if (strpos($description, 'тепень защиты')) {
                $product->description .= '<p><span>Степень защиты:</span>'.strstr($description, ' UV').'</p>';
            }

            //images
            $product->additionalImagesIds = array();
            $srcProdArray = array('mainSrcImg' => "", 'dopSrcImg' => "");
            $article = '/'.$excelJsonItem[1].'/';
            $productImages = array_filter($images, function($image) use ($article) {
                return preg_match($article, $image);
            });

            if (count($productImages) > 0) {
                $imagesFined = 'Fined';
                $srcProdArray['mainSrcImg'] = 'uploads/images_fl/'.reset($productImages);

                //save product
                $product
                    ->persist()
                    ->addProductToCommodityCategories();


                if ($product->id) {

                    //additional photo
                    if (count($productImages) > 1) {
                        $i = 0;
                        foreach ($productImages as $key => $productImageAdditional) {

                            $i++;
                            if (1 === $i) {
                                continue;
                            }

                            $srcProdArray['dopSrcImg'][] = 'uploads/images_fl/'.$productImageAdditional;
                            $product->setAdditionalProductImage();
                        }
                    }

                    $nameImArray = array('title', 's_title', $product->additionalImagesIds);

                    cropAndWriteImageBegin($srcProdArray, $product->id, $nameImArray, '', $product->brandId);
                } else {
                    $product->id = '<p style="color: red">Error adding product to database</p>';
                }


            } else {
                $imagesFined =  '<p style="color: red">images not fined</p>';
            }


            $reportContent = <<<REPORT
            <hr>
<b>Step</b>        - $product->order<br>
<b>Id</b>          - $product->id<br>
<b>Article</b>     - $product->article<br>
<b>Name</b>        - $product->name<br>
<b>SalePrice</b>   - $product->salePrice<br>
<b>RetailPrice</b> - $product->retailPrice<br>
<b>Description</b> - <em>$product->description</em>
<b>Sizes</b>       - $product->sizes<br>
<b>Images</b>      - $imagesFined
REPORT;

            $report->reportStart();

            echo $reportContent;

            $report->reportEnd();
            usleep(100000);
//            break;
        }
    }
}
