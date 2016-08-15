<?php

namespace Parser\Report;

class ReportParser extends Report
{
    const STRING_NOPRICE        = "<h4 style='color:red'>Нет цены!!!</h4>\n";
    const STRING_INSERT_NEW     = "<h4 style='color:green'>Новый товар записан в БД!!!</h4>";
    const STRING_DUPLICATE_PROD = "<h4 style='color:red'>Дубликат!!!Товар с таким URL-главной картинки уже есть в БД</h4>";
    const STRING_NOEXIST_PROD   = "<h4 style='color:red'>Товара нет в наличии. Он не будет записан в БД!!!</h4>";
    const STRING_EXIST_PROD     = "<h4 style='color:blue'>Товар по этой ссылки уже есть в БД!!!</h4>";
    const STRING_TYPE           = '_import.html';

    public function __construct($catId, $remeindLinks, $step, $curLink,
                                $countLinks)
    {
        $this->catId        = $catId;
        $this->remeindLinks = $remeindLinks;
        $this->step         = $step;
        $this->curLink      = $curLink;
        $this->countLinks   = $countLinks;
        $this->getFileName(self::STRING_TYPE);
    }

    /**
     * echo report when insert new prod into DB
     * @param type $commodityID
     * @param type $code
     * @param type $comName
     * @param type $price
     * @param type $price2
     * @param type $mainSrcImg
     * @param type $dopSrcImg
     * @param type $comSizes
     * @param type $comOptions
     * @param type $comCount
     * @param type $comFullDesc
     */
    public function echoInsertProd($commodityID, $code, $comName, $price,
                                   $price2, $mainSrcImg, $dopSrcImg, $comSizes,
                                   $comOptions, $comCount, $comFullDesc)
    {
        echo self::STRING_LINE_BOLD
        .self::STRING_INSERT_NEW
        .self::STRING_LINE_BOLD
        ."Отсалось: {$this->remeindLinks} \n"
        ."Пропарсено:  {$this->step}\n"
        ."URL: <a href={$this->curLink} target='_blank' >{$this->curLink}</a>\n"
        ."CommodityID: {$commodityID}\n"
        ."CatId: {$this->catId}\n"
        ."Cod: {$code}\n"
        ."Name: {$comName}\n"
        ."Цена: {$price}\n"
        ."Цена оптовая: {$price2}\n"
        ."УРЛ Главной картинки: <a href={$mainSrcImg} target='_blank' >{$mainSrcImg}</a>\n";
        if (!empty($dopSrcImg)) {
            $this->echoArray($dopSrcImg, "УРЛЫ Доп. картинки:");
        }
        if ($comSizes !== "") {
            echo "Размеры: {$comSizes}\n";
        }
        if ($comOptions !== "") {
            echo "Цвета-размеры или опции: {$comOptions}\n";
        }
        if ($comCount !== "") {
            echo "Размеры-количество: {$comCount}\n";
        }
        if ($comFullDesc !== "") {
            $from        = array('<', '>');
            $to          = array('&lt;', '&gt;');
            $comFullDesc = str_replace($from, $to, $comFullDesc);
            echo "Описание товара: {$comFullDesc}\n";
        }
        echo self::STRING_LINE_SLIMM;
    }

    /**
     * echo report when prod is dublicate or is not exist
     * @param bool $exist
     * @param string $code
     * @param string $comName
     */
    public function echoDublicatOrNotExist($exist, $code, $comName)
    {
        echo self::STRING_LINE_BOLD;
        if ($exist == true) {
            echo self::STRING_DUPLICATE_PROD;
        } else {
            echo self::STRING_NOEXIST_PROD;
        }
        echo self::STRING_LINE_BOLD
        ."Отсалось: {$this->remeindLinks} \n"
        ."Пропарсено:  {$this->step}\n"
        ."URL: <a href={$this->curLink} target='_blank' >{$this->curLink}</a>\n"
        ."CatId: {$this->catId}\n"
        ."Cod: {$code}\n"
        ."Name: {$comName}\n"
        .self::STRING_LINE_SLIMM;
    }

    /**
     * echo when url-prod exist in DB
     * @param int $comExistId
     */
    public function echoExistProd($comExistId)
    {
        echo self::STRING_LINE_BOLD
        .self::STRING_EXIST_PROD
        .self::STRING_LINE_BOLD
        ."Отсалось: {$this->remeindLinks} \n"
        ."Пропарсено:  {$this->step}\n"
        ."URL: <a href={$this->curLink} target='_blank' >{$this->curLink}</a>\n"
        ."CommodityID: {$comExistId}\n"
        ."CatId: {$this->catId}\n"
        .self::STRING_LINE_SLIMM;
    }
}