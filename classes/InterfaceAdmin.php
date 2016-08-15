<?php

namespace Parser;

use Modules\MySQLi;

class InterfaceAdmin
{
    /**
     * object InterfaceAdmin
     * @var object
     */
    private static $_instance;

    /**
     * id-brand from parser table
     * @var int
     */
    private $idBrand;

    /**
     * count links when parser start
     * @var int
     */
    private $couLinks;

    /**
     * connect to DB
     * @var object
     */
    private $db;

    /**
     * step of parser
     * @var int
     */
    private $step;

    /**
     * reports content
     * @var string
     */
    private $content;

    /**
     * give proggres in html
     * @var float
     */
    private $progress;

    /**
     * date end of parsing
     * @var string
     */
    private $dateEnd;

    /**
     * date start parsing
     * @var string
     */
    private $dateStart;

    /**
     * count updates
     * @var int
     */
    private $updateAdd;

    /**
     * count visible update
     * @var int
     */
    private $visibleUpdate;

    /**
     * count new com
     * @var int 
     */
    private $addNewCom;

    private function __construct($idBrand, $couLinks)
    {
        $this->idBrand  = $idBrand;
        $this->couLinks = $couLinks;
        $this->db       = MySQLi::getInstance()->getConnect();
    }

    /**
     * create object self
     * @param int $idBrand
     * @param int $couLinks
     * @return object self
     */
    public static function init($idBrand, $couLinks)
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($idBrand, $couLinks);
        }
        return self::$_instance;
    }

    /**
     * setInterfaceSpider
     * @param int $countNewLinks
     */
    public function setInterfaceSpider($countNewLinks)
    {
        if (!($stmt = $this->db->prepare("UPDATE parser_interface SET
                                                                count_found_url=?,
                                                                count_new_url=?
                                                                WHERE par_id=?"))) {
            die('Update parser_interface Error ('.$this->db->errno.') '.$this->db->error);
        }
        $stmt->bind_param("iii", $this->couLinks, $countNewLinks, $this->idBrand);
        $stmt->execute();
        $stmt->close();
    }

    /**
     *  setInterfaceVerify
     * @param int $step
     * @param string $content
     * @param bool $updated
     * @param bool $visibleUpdate
     */
    public function setInterfaceVerify($step, $content, $updated, $visibleUpdate)
    {
        $this->step          = $step;
        $this->content       = $content;
        $onePercent          = $this->couLinks / 100;
        $this->progress      = round($this->step / $onePercent, 2);
        $this->updateAdd     = 0;
        $this->visibleUpdate = 0;
        if ($this->step == 0) {
            $this->dateStart = date("d-m-Y H:i:s");
            $this->updateVerifyStart();
        }
        if ($updated == TRUE) {
            $this->updateAdd = 1;
        }
        if ($visibleUpdate == TRUE) {
            $this->visibleUpdate = 1;
        }
        if ($this->step == $this->couLinks) {
            $this->dateEnd = date("d-m-Y H:i:s");
            $this->updateVerifyFinish();
        } else {
            $this->updateVerifyProggress();
        }
    }

    /**
     * updateVerifyStart
     */
    private function updateVerifyStart()
    {
        if (!($stmt = $this->db->prepare("UPDATE parser_interface SET
                                                                start_time=?,
                                                                update_add=0,
                                                                par_hide=0
                                                                WHERE par_id=?"))) {
            die('Update parser_interface Error ('.$this->db->errno.') '.$this->db->error);
        }
        $stmt->bind_param("si", $this->dateStart, $this->idBrand);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * updateVerifyFinish
     */
    private function updateVerifyFinish()
    {
        $this->content = " ";
        if (!($stmt          = $this->db->prepare("UPDATE parser_interface SET
                                                                update_prog=100,
                                                                check_prog=?,
                                                                text=?,
                                                                update_date=?,
                                                                `update_add`=`update_add`+?,
                                                                `par_hide`=`par_hide`+?
                                                                WHERE par_id=?"))) {
            die('Update parser_interface Error ('.$this->db->errno.') '.$this->db->error);
        }
        $stmt->bind_param("sssisi", $this->step, $this->content, $this->dateEnd,
            $this->updateAdd, $this->visibleUpdate, $this->idBrand);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * updateVerifyProggress
     */
    private function updateVerifyProggress()
    {
        if (!($stmt = $this->db->prepare("UPDATE parser_interface SET
                                                                update_prog=?,
                                                                check_prog=?,
                                                                text=?,
                                                                `update_add`=`update_add`+?,
                                                                `par_hide`=`par_hide`+?
                                                                WHERE par_id=?"))) {
            die('Update parser_interface Error ('.$this->db->errno.') '.$this->db->error);
        }
        $stmt->bind_param("sssisi", $this->progress, $this->step,
            $this->content, $this->updateAdd, $this->visibleUpdate,
            $this->idBrand);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * setInterfaceParser
     * @param int $step
     * @param string $content
     * @param bool $insert
     */
    public function setInterfaceParser($step, $content, $insert)
    {
        $this->step     = $step;
        $this->content  = $content;
        $onePercent     = $this->couLinks / 100;
        $this->progress = round($this->step / $onePercent, 2);
        if ($this->step == 0) {
            $this->addNewCom = 0;
            $this->dateStart = date("d-m-Y H:i:s");
            $this->updateParserStart();
        }
        if ($insert == TRUE) {
            $this->addNewCom = 1;
        } else {
            $this->addNewCom = 0;
        }
        if ($this->step == $this->couLinks) {
            $this->dateEnd = date("d-m-Y H:i:s");
            $this->updateParserFinish();
        } else {
            $this->updateParserProggress();
        }
    }

    /**
     * updateParserStart
     */
    private function updateParserStart()
    {
        if (!($stmt = $this->db->prepare("UPDATE parser_interface SET
                                                                start_time=?,
                                                                add_new_com=0
                                                                WHERE par_id=?"))) {
            die('Update parser_interface Error ('.$this->db->errno.') '.$this->db->error);
        }
        $stmt->bind_param("si", $this->dateStart, $this->idBrand);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * updateParserFinish
     */
    private function updateParserFinish()
    {
        $this->content = "Complete! - ".$this->dateEnd;
        if (!($stmt          = $this->db->prepare("UPDATE parser_interface SET
                                                                add_prog=100,
                                                                text=?,
                                                                add_date=?,
                                                                `add_new_com`=`add_new_com`+?
                                                                WHERE par_id=?"))) {
            die('Update parser_interface Error ('.$this->db->errno.') '.$this->db->error);
        }
        $stmt->bind_param("ssii", $this->content, $this->dateEnd,
            $this->addNewCom, $this->idBrand);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * updateParserProggress
     */
    private function updateParserProggress()
    {
        if (!($stmt = $this->db->prepare("UPDATE parser_interface SET
                                                                add_prog=?,
                                                                text=?,
                                                                `add_new_com`=`add_new_com`+?
                                                                WHERE par_id=?"))) {
            die('Update parser_interface Error ('.$this->db->errno.') '.$this->db->error);
        }
        $stmt->bind_param("ssii", $this->progress, $this->content,
            $this->addNewCom, $this->idBrand);
        $stmt->execute();
        $stmt->close();
    }
}