<?php

namespace Parser;

use PHPExcel_IOFactory;

class PHPExcelParser
{
    const PATH_JHIVA_EXEL  = '../parser/excel/Jhiva2016.xls';
    const PATH_JHIVA_JSON  = 'brands_parsers/Jhiva/price.json';
    const PATH_DH_EXEL     = '../parser/excel/db.xls';
    const PATH_DH_JSON     = 'brands_parsers/DemboHouse/price.json';
    const PATH_CRISMA_EXEL = '../parser/excel/Crisma_new.xls';
    const PATH_CRISMA_JSON = 'brands_parsers/Crisma/price.json';
    const PATH_MAJALY_EXEL = '../parser/excel/majaly2016.xls';
    const PATH_MAJALY_JSON = 'brands_parsers/Majaly/price.json';
    const PATH_FASHIONLOOK_EXEL = '../parser/excel/fashion_look.xlsx';
    const PATH_FASHIONLOOK_JSON = 'brands_parsers/FashionLook/data.json';

    /**
     * id of brend
     * @var int
     */
    private $idBrand;

    /**
     *  Прайс exel в виде массива
     * @var array
     */
    private $exelDocArray;

    /**
     * json представление exel
     * @var json
     */
    private $exelJson;

    /**
     *  путь к файлу json для записи
     * @var string
     */
    private $jsonPath;

    /**
     *  path to exel file price
     * @var string
     */
    private $exelPath;

    public function __construct($idBrand)
    {
        $this->idBrand = $idBrand;
        $this->selectConstPath();
    }

    /**
     * Читаем прайс exel
     * @param string $filepath
     * @return array
     */
    private function getExcelToArray()
    {
        $inputFileType      = PHPExcel_IOFactory::identify($this->exelPath);
        $objReader          = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel        = $objReader->load($this->exelPath);
        $this->exelDocArray = $objPHPExcel->getActiveSheet()->toArray();
    }

    /**
     * Кодируем массив в json
     * @param array exelDocArray
     * @return json exelJson
     */
    private function exelJsonDecode()
    {
        $this->exelJson = json_encode($this->exelDocArray);
    }

    /**
     * Записываем файл json
     */
    public function writeJsonFile()
    {
        $this->getExcelToArray();
        $this->exelJsonDecode();
        file_put_contents($this->jsonPath, $this->exelJson);
    }

    /**
     * select path to files for write and read exel
     */
    private function selectConstPath()
    {
        switch ($this->idBrand) {
            case 35:
                $this->exelPath = self::PATH_JHIVA_EXEL;
                $this->jsonPath = self::PATH_JHIVA_JSON;
                break;
            case 34:
                $this->exelPath = self::PATH_DH_EXEL_EXEL;
                $this->jsonPath = self::PATH_DH_JSON;
                break;
            case 29:
                $this->exelPath = self::PATH_CRISMA_EXEL;
                $this->jsonPath = self::PATH_CRISMA_JSON;
                break;
            case 25:
                $this->exelPath = self::PATH_MAJALY_EXEL;
                $this->jsonPath = self::PATH_MAJALY_JSON;
                break;
            case 44:
                $this->exelPath = self::PATH_FASHIONLOOK_EXEL;
                $this->jsonPath = self::PATH_FASHIONLOOK_JSON;
                break;
            default:
                break;
        }
    }
}
