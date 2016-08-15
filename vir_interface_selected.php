<?php
//==============================================================================
//                           входные данные 
//==============================================================================
$visibl    = (int) filter_input(INPUT_POST, 'visibl', FILTER_SANITIZE_STRING);
$domenName = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_STRING);
//var_dump($visibl);
if (isset($visibl)) {

    $changeIm = filter_input(INPUT_POST, 'img', FILTER_SANITIZE_STRING);
    if (!isset($changeIm)) {
        $changeIm = FALSE;
    }
    $changeCod = filter_input(INPUT_POST, 'cod', FILTER_SANITIZE_STRING);
    if (!isset($changeCod)) {
        $changeCod = FALSE;
    }
    $changeName = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    if (!isset($changeName)) {
        $changeName = FALSE;
    }
    $changeDesc = filter_input(INPUT_POST, 'desc', FILTER_SANITIZE_STRING);
    if (!isset($changeDesc)) {
        $changeDesc = FALSE;
    }
    $delOrHide = filter_input(INPUT_POST, 'del', FILTER_SANITIZE_STRING);
    if (!isset($delOrHide)) {
        $delOrHide = FALSE;
    }
    $updatePrice = filter_input(INPUT_POST, 'add_price', FILTER_SANITIZE_STRING);
    if (!isset($updatePrice)) {
        $updatePrice = 1;
    }

    //var_dump($changeIm);
    $idsBrend = array();
    if (isset($_POST['fu']) && !empty($_POST['fu']) && $_POST['fu'] !== NULL) {
        $id         = (int) filter_input(INPUT_POST, 'fu',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['sbs']) && !empty($_POST['sbs'])) {
        $id         = (int) filter_input(INPUT_POST, 'sbs',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['car']) && !empty($_POST['car'])) {
        $id         = (int) filter_input(INPUT_POST, 'car',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['gl']) && !empty($_POST['gl'])) {
        $id         = (int) filter_input(INPUT_POST, 'gl',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['len']) && !empty($_POST['len'])) {
        $id         = (int) filter_input(INPUT_POST, 'len',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['sel']) && !empty($_POST['sel'])) {
        $id         = (int) filter_input(INPUT_POST, 'sel',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['meg']) && !empty($_POST['meg'])) {
        $id         = (int) filter_input(INPUT_POST, 'meg',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['alva']) && !empty($_POST['alva'])) {
        $id         = (int) filter_input(INPUT_POST, 'alva',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['flf']) && !empty($_POST['flf'])) {
        $id         = (int) filter_input(INPUT_POST, 'flf',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['skh']) && !empty($_POST['skh'])) {
        $id         = (int) filter_input(INPUT_POST, 'skh',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['sev']) && !empty($_POST['sev'])) {
        $id         = (int) filter_input(INPUT_POST, 'sev',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['sl']) && !empty($_POST['sl'])) {
        $id         = (int) filter_input(INPUT_POST, 'sl',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['ols']) && !empty($_POST['ols'])) {
        $id         = (int) filter_input(INPUT_POST, 'ols',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['nec']) && !empty($_POST['nec'])) {
        $id         = (int) filter_input(INPUT_POST, 'nec',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['fst']) && !empty($_POST['fst'])) {
        $id         = (int) filter_input(INPUT_POST, 'fst',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['arm']) && !empty($_POST['arm'])) {
        $id         = (int) filter_input(INPUT_POST, 'arm',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['b1']) && !empty($_POST['b1'])) {
        $id         = (int) filter_input(INPUT_POST, 'b1',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['maj']) && !empty($_POST['maj'])) {
        $id         = (int) filter_input(INPUT_POST, 'maj',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['set']) && !empty($_POST['set'])) {
        $id         = (int) filter_input(INPUT_POST, 'set',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['ali']) && !empty($_POST['ali'])) {
        $id         = (int) filter_input(INPUT_POST, 'ali',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['crs']) && !empty($_POST['crs'])) {
        $id         = (int) filter_input(INPUT_POST, 'crs',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['vit']) && !empty($_POST['vit'])) {
        $id         = (int) filter_input(INPUT_POST, 'vit',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['vik']) && !empty($_POST['vik'])) {
        $id         = (int) filter_input(INPUT_POST, 'vik',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['hel']) && !empty($_POST['hel'])) {
        $id         = (int) filter_input(INPUT_POST, 'hel',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['daj']) && !empty($_POST['daj'])) {
        $id         = (int) filter_input(INPUT_POST, 'daj',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['dem']) && !empty($_POST['dem'])) {
        $id         = (int) filter_input(INPUT_POST, 'dem',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['jhi']) && !empty($_POST['jhi'])) {
        $id         = (int) filter_input(INPUT_POST, 'jhi',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['zds']) && !empty($_POST['zds'])) {
        $id         = (int) filter_input(INPUT_POST, 'zds',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['vid']) && !empty($_POST['vid'])) {
        $id         = (int) filter_input(INPUT_POST, 'vid',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['laf']) && !empty($_POST['laf'])) {
        $id         = (int) filter_input(INPUT_POST, 'laf',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['ref']) && !empty($_POST['ref'])) {
        $id         = (int) filter_input(INPUT_POST, 'ref',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['ttt']) && !empty($_POST['ttt'])) {
        $id         = (int) filter_input(INPUT_POST, 'ttt',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }
    if (isset($_POST['gha']) && !empty($_POST['gha'])) {
        $id         = (int) filter_input(INPUT_POST, 'gha',
                FILTER_SANITIZE_STRING);
        $idsBrend[] = $id;
    }

    session_start();
    $_SESSION                = array();
    $_SESSION['comVisibl']   = $visibl;
    $_SESSION['changeIm']    = $changeIm;
    $_SESSION['changeCod']   = $changeCod;
    $_SESSION['changeName']  = $changeName;
    $_SESSION['changeDesc']  = $changeDesc;
    $_SESSION['deleteCom']   = $delOrHide;
    $_SESSION['updatePrice'] = $updatePrice;
    $_SESSION['idsBrend']    = $idsBrend;
    //var_dump($idsBrend);
    header("Location: http://".$domenName."/parser/vir_start.php");
} else {
    die("Нет данных выбора опубл. или неопуб.!!!");
}


