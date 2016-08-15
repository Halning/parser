<?php

namespace Parser\Report;

class ReportVerify extends Report
{
    const STRING_CHANGE_PHOTO   = "<h4 style='color:blue'>Фото перезалито!!!</h4>";
    const STRING_UNVISIBLE_PROD = "<h4 style='color:blue'>Товар есть в заказах или провека опубликованых товаров, будет скрыт, не удален из БД!!! Code{6}</h4>";
    const STRING_DELETE_PROD    = "<h4 style='color:red'>Товар удален с БД!!! Code{7}</h4>";
    const STRING_UPDATE_PROD    = "<h4 style='color:red'>Данные товара изменены в БД!!! Code{1}</h4>";
    const STRING_NOUPDATE_PROD  = "<h4 style='color:green'>Проверщик не виявил изменений в товаре!!! Code{2}</h4>";
    const STRING_NOPRICE        = "<h4 style='color:red'>Нет цены!!! Code{5}\n";
    const STRING_NOEXIST        = "<h4 style='color:red'>Нет в наличии, товар будет скрыт!!! Code{3}</h4>";
    const STRING_EXIST_PROD     = "<h4 style='color:blue'>Есть в наличии, товар будет опубликован!!! Code{4}</h4>";
    const STRING_TYPE           = '_verify.html';

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
     * echo report when cenged photo
     * @param string $mainSrcImg
     * @param array $dopSrcImg
     */
    public function echoChangePphoto($mainSrcImg, $dopSrcImg)
    {
        echo self::STRING_LINE_BOLD
        .self::STRING_CHANGE_PHOTO
        .self::STRING_LINE_BOLD
        ."УРЛ Главной картинки: <a href={$mainSrcImg} target='_blank' >{$mainSrcImg}</a>\n";
        if (!empty($dopSrcImg)) {
            $this->echoArray($dopSrcImg, "УРЛЫ Доп. картинки:");
        }
    }

    /**
     * echo report when prod deleted
     * @param boolean $delete
     * @param int $commodityID
     */
    public function echoDeleteProd($delete, $commodityID)
    {
        echo self::STRING_LINE_BOLD;
        if ($delete == false) {
            echo self::STRING_UNVISIBLE_PROD;
        } else {
            echo self::STRING_DELETE_PROD;
        }
        echo self::STRING_LINE_BOLD
        ."Отсалось: {$this->remeindLinks} \n"
        ."Пропарсено:  {$this->step}\n"
        ."URL: <a href={$this->curLink} target='_blank' >{$this->curLink}</a>\n"
        ."CommodityID: {$commodityID}\n"
        .self::STRING_LINE_SLIMM;
    }

    /**
     * echo report when ubdate prod
     * @param bool $update
     * @param int $commodityID
     * @param string $code
     * @param string $comName
     * @param int $priceNew
     * @param int $price2New
     * @param string $comSizesNew
     * @param string $comOptionsNew
     * @param string $comCountNew
     */
    public function echoUpdateProd($update, $commodityID, $code, $comName,
                                   $priceNew, $price2New, $comSizesNew,
                                   $comOptionsNew, $comCountNew)
    {
        echo self::STRING_LINE_BOLD;
        if ($update == true) {
            echo self::STRING_UPDATE_PROD;
        } else {
            echo self::STRING_NOUPDATE_PROD;
        }
        echo self::STRING_LINE_BOLD
        ."Отсалось: {$this->remeindLinks} \n"
        ."Пропарсено:  {$this->step}\n"
        ."URL: <a href={$this->curLink} target='_blank' >{$this->curLink}</a>\n"
        ."CommodityID: {$commodityID}\n"
        ."CatId: {$this->catId}\n"
        ."Cod: {$code}\n"
        ."Name: {$comName}\n"
        ."Цена: {$priceNew}\n"
        ."Цена оптовая: {$price2New}\n";
        if ($comSizesNew !== "") {
            echo "Размеры: {$comSizesNew}\n";
        }
        if ($comOptionsNew !== "") {
            echo "Цвета-размеры или опции: {$comOptionsNew}\n";
        }
        if ($comCountNew !== "") {
            echo "Размеры-количество: {$comCountNew}\n";
        }
        echo self::STRING_LINE_SLIMM;
    }
}