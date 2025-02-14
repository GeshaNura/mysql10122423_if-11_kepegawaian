<?php
include '../config/database.php';

if (isset($_GET['id_pegawai'])) {
    $id_pegawai = $_GET['id_pegawai'];

    $query = "SELECT gaji FROM pegawai WHERE id_pegawai = '$id_pegawai'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $data = mysqli_fetch_assoc($result);
        echo json_encode(["gaji_pokok" => $data['gaji']]); 
    } else {
        echo json_encode(["gaji_pokok" => ""]);
    }
}
?>
