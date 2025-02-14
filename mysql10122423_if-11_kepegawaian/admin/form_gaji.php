<?php
include '../config/database.php';

date_default_timezone_set('Asia/Jakarta');

$pegawai_query = "SELECT p.id_pegawai, p.nama, p.gaji, j.tunjangan 
                  FROM pegawai p 
                  JOIN jabatan j ON p.id_jabatan = j.id_jabatan";
$pegawai_result = mysqli_query($conn, $pegawai_query);

if (isset($_GET['id_pegawai'])) {
    $id_pegawai = $_GET['id_pegawai'];
    $query = "SELECT p.gaji, j.tunjangan 
              FROM pegawai p 
              JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
              WHERE p.id_pegawai = '$id_pegawai'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    echo json_encode($data);
    exit;
}

if (isset($_POST['simpan'])) {
    $id_pegawai = $_POST['id_pegawai'];
    $bulan = intval($_POST['bulan']);  
    $tahun = intval($_POST['tahun']);  

    function formatAngka($angka) {
        $angka = str_replace(['Rp. ', '.', ','], '', $angka);
        return floatval($angka); 
    }

    $gaji_pokok = formatAngka($_POST['gaji_pokok']);
    $tunjangan = formatAngka($_POST['tunjangan']);
    $potongan = formatAngka($_POST['potongan']);
    $total_gaji = formatAngka($_POST['total_gaji']);
    $created_at = date('Y-m-d H:i:s');

    $query = "INSERT INTO gaji (id_pegawai, bulan, tahun, gaji_pokok, tunjangan, potongan, total_gaji, created_at) 
              VALUES ('$id_pegawai', '$bulan', '$tahun', '$gaji_pokok', '$tunjangan', '$potongan', '$total_gaji', '$created_at')";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Data gaji berhasil disimpan!');
                window.location.href = 'manajemen_gaji.php';
              </script>";
        exit();
    } else {
        die("Gagal menyimpan data: " . mysqli_error($conn));
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<style>
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            text-decoration: none;
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Gaji - Kepegawaian</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<a href="manajemen_gaji.php" class="back-button">⬅ Kembali</a>
    <div class="pegawai-form-container">
        <h2>Form Gaji</h2>
        <form id="pegawaiForm" method="post">
            <label for="id_pegawai">Nama Pegawai</label>
            <select name="id_pegawai" id="id_pegawai" required>
                <option value="">Pilih Pegawai</option>
                <?php while ($pegawai = mysqli_fetch_assoc($pegawai_result)) { ?>
                <option value="<?= $pegawai['id_pegawai']; ?>"><?= $pegawai['nama']; ?></option>
                <?php } ?>
            </select>

            <label for="bulan">Bulan</label>
            <input type="number" name="bulan" id="bulan" min="1" max="12" placeholder="Masukkan Bulan (1-12)" required>

            <label for="tahun">Tahun</label>
            <input type="number" name="tahun" id="tahun" min="2000" max="2099" placeholder="Masukkan Tahun" required>

            <label for="gaji_pokok">Gaji Pokok</label>
            <input type="text" id="gaji_pokok" name="gaji_pokok" placeholder="Masukkan Gaji Pokok"
                oninput="formatRupiah(this); hitungTotalGaji();" required>

            <label for="tunjangan">Tunjangan</label>
            <input type="text" id="tunjangan" name="tunjangan" placeholder="Masukkan Tunjangan"
                oninput="formatRupiah(this); hitungTotalGaji();" required>

            <label for="potongan">Potongan</label>
            <input type="text" id="potongan" name="potongan" placeholder="Masukkan Potongan"
                oninput="this.value = formatRupiahInput(this.value); hitungTotalGaji();" required>

            <label for="total_gaji">Total Gaji</label>
            <input type="text" id="total_gaji" name="total_gaji" placeholder="Total Gaji" readonly>

            <button type="submit" name="simpan">Simpan</button>
        </form>
    </div>

    <script>
    document.getElementById("id_pegawai").addEventListener("change", function() {
        let idPegawai = this.value;
        if (idPegawai) {
            fetch("?id_pegawai=" + idPegawai)
                .then(response => response.json())
                .then(data => {
                    let gajiPokok = data.gaji ? parseInt(data.gaji) : 0;
                    let tunjangan = data.tunjangan ? parseInt(data.tunjangan) : 0;

                    document.getElementById("gaji_pokok").value = formatRupiah(gajiPokok);
                    document.getElementById("tunjangan").value = formatRupiah(tunjangan);
                    hitungTotalGaji();
                })
                .catch(error => console.error("Error:", error));
        }
    });

    function formatRupiahInput(value) {
        let angka = value.replace(/\D/g, "");
        return "Rp. " + (angka ? parseInt(angka).toLocaleString("id-ID") : "0");
    }

    function formatRupiah(angka) {
        return "Rp. " + angka.toLocaleString("id-ID");
    }

    function hitungTotalGaji() {
        let gajiPokok = document.getElementById("gaji_pokok").value.replace(/\D/g, "") || 0;
        let tunjangan = document.getElementById("tunjangan").value.replace(/\D/g, "") || 0;
        let potongan = document.getElementById("potongan").value.replace(/\D/g, "") || 0;

        let totalGaji = parseInt(gajiPokok) + parseInt(tunjangan) - parseInt(potongan);

        if (parseInt(potongan) > parseInt(gajiPokok)) {
            alert("⚠️ Peringatan: Potongan tidak boleh lebih besar dari Gaji Pokok!");
            document.getElementById("potongan").value = "Rp. 0";
            potongan = 0;
        }

        document.getElementById("total_gaji").value = "Rp. " + totalGaji.toLocaleString("id-ID");
    }
    </script>
</body>

</html>
