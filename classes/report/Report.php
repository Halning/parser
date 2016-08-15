<?php

namespace Parser\Report;

interface iReport
{
    const FILE_CREATE_MASSEGE = "<meta charset='utf-8'><pre><?php<h4 style='color:green'>Файл создан</h4>\n\n";
    const STRING_LINE_BOLD    = "====================================================================\n";
    const STRING_LINE_SLIMM   = "--------------------------------------------------------------------\n\n\n";
    const STRING_START        = "Всем пристегнуть ремни начало работы!!!\n";

    public function createFileReport();

    public function reportStart();

    public function reportEnd();
}

abstract class Report implements iReport
{
    /**
     * path to report file
     * @var string
     */
    protected $fileName;

    /**
     * catId brend
     * @var int
     */
    protected $catId;

    /**
     * count links all
     * @var int
     */
    protected $countLinks;

    /**
     * remeind links perse
     * @var int
     */
    protected $remeindLinks;

    /**
     * step of parse
     * @var int
     */
    protected $step;

    /**
     * url parse
     * @var string
     */
    protected $curLink;

    /**
     * creating file report
     */
    public function createFileReport()
    {
        $fp = fopen($this->fileName, "w");
        fwrite($fp, self::FILE_CREATE_MASSEGE);
        fclose($fp);
    }

    /**
     * start write report
     */
    public function reportStart()
    {
        ob_start();
    }

    /**
     * end write report
     * @return string - Writed echo
     */
    public function reportEnd()
    {
        $memoryBytes     = memory_get_usage();
        $memoryBytesPeak = memory_get_peak_usage();
        $memoryMb        = round($memoryBytes / 1048576, 5);
        $memoryMbPeak    = round($memoryBytesPeak / 1048576, 5);
        register_shutdown_function('myShutdown');
        echo "Final: {$memoryBytes}  bytes - {$memoryMb} mb\n";
        echo "Peak:  {$memoryBytesPeak} bytes - {$memoryMbPeak} mb \n";
        $content         = ob_get_contents();
        file_put_contents($this->fileName, $content, FILE_APPEND);
        ob_flush();
        ob_end_clean();
        return $content;
    }

    /**
     * begin of all reports
     */
    public function echoStart()
    {
        echo "\nОтсалось: {$this->countLinks}\n"
        .self::STRING_START
        .self::STRING_LINE_BOLD;
    }

    /**
     * get path to report end create dir if no exist
     * @param string const $type
     */
    protected function getFileName($type)
    {
        $date        = new \DateTime('now');
        $day         = $date->format('d_M');
        $dir_to_save = 'reports/'.$day;
        if (!is_dir($dir_to_save)) {
            mkdir($dir_to_save);
        }
        $this->fileName = $dir_to_save.DIRECTORY_SEPARATOR.'catid_'.$this->catId.$type;
    }

    /**
     * echo all arrays for all reports
     * @param array $linkArray
     * @param string $wathString
     */
    protected function echoArray($linkArray, $wathString)
    {
        foreach ($linkArray as $key => $link) {
            $key  = $key + 1;
            $link = trim($link);
            echo $wathString.$key.' => '." <a href={$link} target='_blank' >{$link}</a>\n";
        }
    }
}