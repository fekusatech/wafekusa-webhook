<?php
$conndb =  mysqli_connect('localhost', 'u1734578_userglobal', 'u1734578_userglobal', 'u1734578_userglobal');
$ipwa = "http://{ipanda}:4003";
$ipwanonport = "http://{ipanda}";
$groupdefault = "idgroupandauntuknotif";

function get_antrian()
{
    global $conndb;
    $query = "select *from antrian where status ='gagal' order by created_at asc limit 30 ";
    $exec = mysqli_query($conndb, $query);
    if (mysqli_num_rows($exec) > 0) {
        while ($data = mysqli_fetch_assoc($exec)) {
            $datamessage = $data['pesan'];
            $datato = $data['kirim_ke'];
            $id = $data['id'];
            if (strpos($datato, '@g.us') !== false) {
                $data = sendmessage($datamessage, $datato);
            } else {
                if ($data['jenis_pesan'] == "file") {
                    $data = sendMEDIA($datato, $data['file'], $datamessage);
                } else {
                    $data = sendmessage($datamessage, $datato, "number");
                }
            }
            if (isset($data['status']) || $data['status'] == true) {
                //  echo json_encode($data); exit;
                $update = "UPDATE antrian set status ='kirim' where id = '{$id}'";
                mysqli_query($conndb, $update);
            }
        }
    }
}
function sendmessage($message, $to, $mode = "group")
{
    global $ipwa;
    if ($mode == "number") {
        $url = $ipwa.'/send';
        $post = "number={$to}&message={$message}";
    } else {
        $url = $ipwa.'/send-group-message';
        $post = "id={$to}&message={$message}";
    }
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    // }
    return json_decode($response, true);
}
function get_user($username, $unit)
{
    global $conndb;
    $unitfinal = strtoupper($unit);
    $query = "SELECT * FROM user_collect where username = '{$username}' and unit = '$unitfinal'";
    $exec = mysqli_query($conndb, $query);
    if (mysqli_num_rows($exec) > 0) {
        $f = mysqli_fetch_assoc($exec);
        return "Data User Anda : {$username} Unit {$unitfinal} dengan password *{$f['password']}*";
    }
    // else{
    //     return "Data tidak ditemukan";
    // }
}
function get_logger($unit, $username, $mode = 'all')
{
    global $conndb;
    if ($mode == "all") {
        $query = "SELECT *FROM logger where username ='{$username}' order by created_at desc limit 100";
    } else {
        $convert = date('Y-m-d', strtotime($mode)) . " 00:01";
        $end = date('Y-m-d', strtotime($mode)) . " 23:59";
        $query = "SELECT *FROM logger where username ='{$username}' AND created_at between '$convert' AND '$end' order by created_at desc limit
100";
    }
    $exec = mysqli_query($conndb, $query);
    $data = array();
    if (mysqli_num_rows($exec) > 0) {
        $no = 1;
        $tampungdata = "";
        while ($data2 = mysqli_fetch_assoc($exec)) {
            $tampungdata .= $no++ . ".($mode) " . urlencode($data2['logger']) . "\n";
        }
        // var_dump($data);
        // $explode_arr = implode('',$data);
        return $tampungdata;
    } else {
        $tampungdata = "Data {$username} dengan mode {$mode} tidak tersedia.";
        return $tampungdata;
    }
}
function sendMSG($number, $message, $port = '4003')
{
    global $ipwanonport;
    $url = "{$ipwanonport}:{$port}/send";
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => "number=$number&message=$message",
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}

function getdatabyurl($mode, $company, $jobdesk, $nomorhp, $ads)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://resume.fekusa.xyz/result.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => "mode=$mode&company=$company&jobdesk=$jobdesk&nomorhp=$nomorhp&ads=$ads",
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}
function sendtocs($number, $message, $port,$author = null)
{
    global $groupdefault;
    $groupid = $groupdefault;
    $messagenya = "Pesan Dari : $author\nUrl : https://wa.me/{$number} \nMessage : {$message}";
    sendGRP($groupid, $messagenya);
    return true;
}
function sendMEDIA($number, $file, $message)
{
    global $ipwa;
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $ipwa."/send-media",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => array('number' => $number, 'file' => $file, 'caption' => $message),

    ));

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function getpdf($unit, $tanggal)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://resume.fekusa.xyz/generatepdf.php?unit={$unit}&tanggal={$tanggal}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}
function gitstatus()
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://peternak.id/sync",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $data =  json_decode($response, true);
    // {"id":"31","message":"fix sync","sha":"b2d7ba74d74a83eb87d7d0b488eeff4fff3664fb","updateby":"mea","updated_at":"2022-05-27 22:37:23"}
    $message = "Pembaruan : *" . strtoupper($data['message']) . "*\nID : {$data['sha']}\nOleh : {$data['updateby']}\nJam : " . date('d M Y H:i', strtotime($data['updated_at']));
    return $message;
}
function simsimi($message)
{
    $agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.xteam.xyz/simsimi?kata=apa%2520sih&APIKEY=25ec40547ebfb96a",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_USERAGENT => $agent,
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}
function getcontact($number)
{
    $agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://unilearning.my.id/cronjobnext/getdatacontact/{$number}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_USERAGENT => $agent,
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}
function darkjoke($number, $caption, $port = '4003')
{
    global $ipwanonport;
    $agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
    $url = "https://api.xteam.xyz/asupan/darkjoke?APIKEY=25ec40547ebfb96a";
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "{$ipwanonport}:{$port}/send-media",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => "number=$number&file=" . urlencode($url) . "&caption=$caption",
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
}
function sendGRP($dataid, $message)
{
    global $ipwa;
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $ipwa.'/send-group-message',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => "id={$dataid}&message={$message}",
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));

    $response = curl_exec($curl);
    // echo $response;
    curl_close($curl);
}
function flashsale()
{
    $agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)";
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.xteam.xyz/search/shopeeflashsale?APIKEY=25ec40547ebfb96a",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_USERAGENT => $agent,
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}
function getkpimea($date)
{

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://appdmc.com/cmms/getexcel?date={$date}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Accept: */*",
            "User-Agent: Thunder Client (https://www.thunderclient.com)"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    $responsedata = json_decode($response, true);
    return $responsedata;
}