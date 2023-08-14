<?php
require_once("./vendor/autoload.php");

$db = new MysqliDb(array(
    'host' => 'localhost',
    'username' => 'data',
    'password' => 'data',
    'db' => 'data',
    'port' => 3306,
    'charset' => 'utf8'
));

if (empty($_POST)) {
    echo json_encode(array('status' => false, 'message' => 'Data tidak sesuai'));
} else {
    if (empty($_POST['token']) || !isset($_POST['token'])) {
        echo json_encode(array('status' => false, 'message' => 'Token Tidak Ada'));
    } else {
        if (!isset($_POST['pesan']) ||  !isset($_POST['no'])) {
            echo json_encode(array('status' => false, 'message' => 'Data tidak lengkap'));
        } else {
            $token = $_POST['token'];
            $pesan = $_POST['pesan'];
            $kirim_ke = $_POST['no'];

            $db->where("token", $token)
                ->where("status", 1);
            $tokendata = $db->getOne("token");
            if ($tokendata !== NULL) {
                if (isset($_POST['jenis_pesan']) && isset($_POST['file'])) {
                    $data = array(
                        "id" => NULL,
                        "pesan" => $pesan,
                        "jenis_pesan" => $_POST['jenis_pesan'],
                        "file" => $_POST['file'],
                        "kirim_ke" => $kirim_ke,
                        "status" => "gagal",
                    );
                } else {
                    $data = array(
                        "id" => NULL,
                        "pesan" => $pesan,
                        "kirim_ke" => $kirim_ke,
                        "status" => "gagal",
                    );
                }
                $data['token'] = $token;
                $id = $db->insert('antrian', $data);
                echo json_encode(array('status' => true, 'message' => "Data berhasil diinput ke antrian dengan id : {$id}"));
            } else {
                echo json_encode(array('status' => false, 'message' => 'Token Tidak Valid'));
            }
        }
        // echo $user['id'];
    }
}
