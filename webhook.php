<?php
include_once("function.php");
$blacklist = array('6282140647578');
if (isset($_REQUEST['nomor'])) {
    $number = $_REQUEST['nomor'];
    $port = $_REQUEST['port'];
    $author = $_REQUEST['author'];
    //get message ;
    $message = $_REQUEST['msg'];
    if (strpos($message, '#') !== false) {
        //make array
        $arr = explode("#", $message);
        //Format unit;username;d-m-y
        $unit = $arr[0];
        $username = $arr[1];
        $date = $arr[2];
        $message = get_logger($unit, $username, $date);
        if (strpos($number, '@g.us') !== false) {
            echo sendGRP($number, $message);
        } else {
            echo sendMSG($number, $message, $port);
        }
    } else if (strpos($message, '*') !== false) {
        //make array
        $arr = explode("*", $message);
        //Format username*unit
        $username = $arr[0];
        $unit = $arr[1];
        $message = get_user($username, $unit);
        if (strpos($number, '@g.us') !== false) {
            echo sendGRP($number, $message);
        } else {
            echo sendMSG($number, $message, $port);
        }
    } else if (strpos(strtolower($message), "gitstatus") !== false) {
        $message = gitstatus();
        if (strpos($number, '@g.us') !== false) {
            echo sendGRP($number, $message);
        } else {
            echo sendMSG($number, $message, $port);
        }
    } else if (strpos(strtolower($message), "getpdf") !== false) {
        //getpdf|unit|mm/yyyy
        $arrd = explode("|", $message);
        $data = json_encode($arrd);
        $unit = $arrd[1];
        $tanggal = $arrd[2];
        $getpdf = json_decode(getpdf($unit, $tanggal), true);
        sendMEDIA($number, $getpdf['url'], "UNIT : $unit \nDate : $tanggal");
    } else if (strpos(strtolower($message), "kpimea") !== false) {
        //getpdf|unit|mm/yyyy
        $arrd = explode(" ", $message);
        $date = $arrd[1];
        $returndata = getkpimea($date);
        $filename = "KPI MEA " . date('M Y', strtotime($date));
        echo sendMEDIA($number, $returndata['linkdownload'], $filename);
    } else if (strpos(strtolower($message), "balaspesan") !== false) {
        //getpdf|unit|mm/yyyy
        $arrd = explode(" ", $message);
        $numberraw = $arrd[1];
        $number = str_replace("https://wa.me/", "", $numberraw);
        $message = str_replace("balaspesan {$numberraw}", "", $message);
        echo sendMSG($number, $message, $port);
    } else {
        if (strpos($number, '@g.us') == false) {
            sendtocs($number, $message, $port, $author);
        }
    }
} else {
    echo json_encode(array('status' => false, 'message' => 'cant access'));
}
