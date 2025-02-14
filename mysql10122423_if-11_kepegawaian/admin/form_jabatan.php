<?php
require_once "../config/database.php"; // Koneksi ke database

// Atur zona waktu ke WIB
date_default_timezone_set('Asia/Jakarta');

$id_jabatan = "";
$nama_jabatan = "";
$gaji_awal = "";
$tunjangan = "";

// **Cek apakah ini mode edit (ada id di URL)**
if (isset($_GET['id'])) {
    $id_jabatan = $_GET['id'];

    // Ambil data jabatan berdasarkan ID
    $query = "SELECT * FROM jabatan WHERE id_jabatan = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_jabatan);
    $stmt->execute();
    $result = $stmt->get_result();
    $jabatan = $result->fetch_assoc();

    if ($jabatan) {
        $nama_jabatan = $jabatan['nama_jabatan'];
        $gaji_awal = number_format($jabatan['gaji_awal'], 0, ',', '.');
        $tunjangan = number_format($jabatan['tunjangan'], 0, ',', '.');
    }

    $stmt->close();
}

// **Jika Form Disubmit**
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_jabatan = $_POST['id_jabatan'] ?? ""; // Ambil ID jika ada
    $nama_jabatan = trim($_POST['nama']);
    $gaji_awal = preg_replace("/[^0-9]/", "", $_POST['gajiawal']);
    $tunjangan = preg_replace("/[^0-9]/", "", $_POST['tunjangan']);

    // Validasi tidak boleh kosong
    if (empty($nama_jabatan) || $gaji_awal <= 0 || $tunjangan < 0) {
        echo "<script>alert('Semua kolom harus diisi dengan benar!'); window.history.back();</script>";
        exit;
    }

    // **UPDATE jika ID ada, INSERT jika tidak ada**
    if (!empty($id_jabatan)) {
        $sql = "UPDATE jabatan SET nama_jabatan = ?, gaji_awal = ?, tunjangan = ? WHERE id_jabatan = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sddi", $nama_jabatan, $gaji_awal, $tunjangan, $id_jabatan);
    } else {
        $sql = "INSERT INTO jabatan (nama_jabatan, gaji_awal, tunjangan) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdd", $nama_jabatan, $gaji_awal, $tunjangan);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Data berhasil disimpan!'); window.location='manajemen_jabatan.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan data!');</script>";
    }

    $stmt->close();
    $conn->close();
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
    <title>Form Jabatan - Kepegawaian</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<a href="manajemen_jabatan.php" class="back-button">⬅ Kembali</a>

<div class="pegawai-form-container">
    <h2><?= empty($id_jabatan) ? "Tambah Jabatan" : "Edit Jabatan" ?></h2>

    <form method="POST" action="">
        <input type="hidden" name="id_jabatan" value="<?= htmlspecialchars($id_jabatan) ?>">

        <div class="form-group">
            <label for="nama">Jabatan</label>
            <input type="text" name="nama" id="nama" value="<?= htmlspecialchars($nama_jabatan) ?>" placeholder="Masukkan nama jabatan" required>
        </div>

        <div class="form-group">
            <label for="gajiawal">Gaji Awal</label>
            <input type="text" name="gajiawal" id="gajiawal" value="<?= htmlspecialchars($gaji_awal) ?>" placeholder="Masukkan gaji awal" required oninput="formatRupiah(this)">
        </div>

        <div class="form-group">
            <label for="tunjangan">Tunjangan</label>
            <input type="text" name="tunjangan" id="tunjangan" value="<?= htmlspecialchars($tunjangan) ?>" placeholder="Masukkan tunjangan" required oninput="formatRupiah(this)">
        </div>

        <button type="submit"><?= empty($id_jabatan) ? "Simpan" : "Update" ?></button>
    </form>
</div>

<script>
function formatRupiah(input) {
    let angka = input.value.replace(/\D/g, ""); // Hapus semua karakter non-angka

    if (angka === "") {
        input.value = "";
        return;
    }

    let formatted = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0
    }).format(angka);

    input.value = formatted.replace("Rp", "Rp. ").trim();
}
</script>

</body>
</html>
