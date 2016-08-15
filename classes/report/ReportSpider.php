<?php

namespace Parser\Report;

class ReportSpider extends Report
{
    const STRING_NEW_URL   = "<h4 style='color:green'>Паук залил новые ссылки!!!</h4>";
    const STRING_EXIST_URL = "<h4 style='color:blue'>товары ссылки которых есть в БД!!!</h4>";
    const STRING_TYPE      = '_spider.html';

    public function __construct($catId, $countLinks, $remeindLinks, $step)
    {
        $this->catId        = $catId;
        $this->countLinks   = $countLinks;
        $this->remeindLinks = $remeindLinks;
        $this->step         = $step;
        $this->getFileName(self::STRING_TYPE);
    }

    /**
     * echo strings wnen spider start
     * @param array $linkArray
     */
    public function echoSpiderStart($linkArray)
    {
        $this->echoArray($linkArray, "");
        echo "\nОтсалось: {$this->countLinks}\n"
        .self::STRING_START
        .self::STRING_LINE_BOLD;
    }

    /**
     *
     */
    public function echoNewLinks()
    {
        echo self::STRING_LINE_BOLD
        ."Отсалось: {$this->remeindLinks} \n"
        ."Пропарсено:  {$this->step}\n";
    }

    /**
     * echo fanal report of parser
     * @param int $couLinks
     * @param int $countNewLinks
     * @param array $arrNewLinks
     */
    public function echoSpiderFinal($couLinks, $countNewLinks, $arrNewLinks)
    {
        echo self::STRING_LINE_BOLD
        .self::STRING_NEW_URL
        .self::STRING_LINE_BOLD
        ."Количество найденных ссылок: {$couLinks}\n"
        ."Количество новых ссылок: {$countNewLinks}\n";
        $this->echoArray($arrNewLinks, "УРЛЫ Новыч ссылок:");
        echo self::STRING_LINE_BOLD
        .self::STRING_EXIST_URL
        .self::STRING_LINE_BOLD;
    }
}