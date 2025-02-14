<?php
include '../config/database.php';

date_default_timezone_set('Asia/Jakarta');

if (!isset($_GET['id_gaji'])) {
    die("ID Gaji tidak ditemukan.");
}

$id_gaji = intval($_GET['id_gaji']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $bulan = intval($_POST['bulan']);
    $tahun = intval($_POST['tahun']);
    $gaji_pokok = intval(str_replace(".", "", str_replace("Rp. ", "", $_POST['gaji_pokok'])));
    $tunjangan = intval(str_replace(".", "", str_replace("Rp. ", "", $_POST['tunjangan'])));
    $potongan = intval(str_replace(".", "", str_replace("Rp. ", "", $_POST['potongan'])));
    $total_gaji = $gaji_pokok + $tunjangan - $potongan;
    $query = "UPDATE gaji SET bulan='$bulan', tahun='$tahun', 
              gaji_pokok='$gaji_pokok', tunjangan='$tunjangan', potongan='$potongan', 
              total_gaji='$total_gaji' WHERE id_gaji='$id_gaji'";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('✅ Data gaji berhasil diperbarui!');
                window.location.href = 'manajemen_gaji.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('❌ Gagal memperbarui data gaji.');
              </script>";
    }
}

$query = "SELECT g.*, p.nama FROM gaji g 
          JOIN pegawai p ON g.id_pegawai = p.id_pegawai
          WHERE g.id_gaji = $id_gaji";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Data gaji tidak ditemukan.");
}

$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gaji - Kepegawaian</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<a href="manajemen_gaji.php" class="back-button">⬅ Kembali</a>
<div class="pegawai-form-container">
    <h2>Edit Gaji</h2>
    <form method="post" onsubmit="return confirmUpdate();">
        <label for="nama_pegawai">Nama Pegawai</label>
        <input type="text" value="<?= $data['nama']; ?>" readonly>

        <label for="bulan">Bulan</label>
        <input type="number" name="bulan" id="bulan" min="1" max="12" value="<?= $data['bulan']; ?>" required>

        <label for="tahun">Tahun</label>
        <input type="number" name="tahun" id="tahun" min="2000" max="2099" value="<?= $data['tahun']; ?>" required>

        <label for="gaji_pokok">Gaji Pokok</label>
        <input type="text" id="gaji_pokok" name="gaji_pokok" 
            value="<?= 'Rp. ' . number_format($data['gaji_pokok'], 0, ',', '.'); ?>"
            oninput="this.value = formatRupiahInput(this.value); hitungTotalGaji();" required>

        <label for="tunjangan">Tunjangan</label>
        <input type="text" id="tunjangan" name="tunjangan" 
            value="<?= 'Rp. ' . number_format($data['tunjangan'], 0, ',', '.'); ?>"
            oninput="this.value = formatRupiahInput(this.value); hitungTotalGaji();" required>

        <label for="potongan">Potongan</label>
        <input type="text" id="potongan" name="potongan" 
            value="<?= 'Rp. ' . number_format($data['potongan'], 0, ',', '.'); ?>"
            oninput="this.value = formatRupiahInput(this.value); hitungTotalGaji();" required>

        <label for="total_gaji">Total Gaji</label>
        <input type="text" id="total_gaji" name="total_gaji" 
            value="<?= 'Rp. ' . number_format($data['total_gaji'], 0, ',', '.'); ?>" readonly>

        <button type="submit" name="update">Update</button>
    </form>
</div>

<script>
function formatRupiahInput(value) {
    let angka = value.replace(/\D/g, "");
    return "Rp. " + (angka ? parseInt(angka).toLocaleString("id-ID") : "0");
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

function confirmUpdate() {
    return confirm("Apakah Anda yakin ingin memperbarui data gaji ini?");
}
</script>

</body>
</html>
