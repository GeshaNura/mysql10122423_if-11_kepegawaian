<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['id_pegawai'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href = '../login.php';</script>";
    exit();
}

$id_pegawai = $_SESSION['id_pegawai'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $_POST['status'];
    $tanggal = date("Y-m-d");

    $cek_query = "SELECT COUNT(*) FROM kehadiran WHERE id_pegawai = ? AND DATE(tanggal) = ?";
    $stmt_cek = $conn->prepare($cek_query);
    $stmt_cek->bind_param("is", $id_pegawai, $tanggal);
    $stmt_cek->execute();
    $stmt_cek->bind_result($jumlah);
    $stmt_cek->fetch();
    $stmt_cek->close(); 

    if ($jumlah > 0) {
        echo "<script>alert('Anda sudah melakukan kehadiran hari ini!');</script>";
    } else {
        $query = "INSERT INTO kehadiran (id_pegawai, tanggal, status, updated_at) VALUES (?, NOW(), ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $id_pegawai, $status);

        if ($stmt->execute()) {
            echo "<script>alert('Kehadiran berhasil dicatat!'); window.location.href='kehadiran.php';</script>";
        } else {
            echo "<script>alert('Gagal mencatat kehadiran.');</script>";
        }
        $stmt->close();
    }
}

// Filter data berdasarkan bulan & tahun
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');

$query = "SELECT tanggal, status FROM kehadiran 
          WHERE id_pegawai = ? AND YEAR(tanggal) = ? AND MONTH(tanggal) = ?
          ORDER BY tanggal DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $id_pegawai, $tahun, $bulan);
$stmt->execute();
$result = $stmt->get_result();

$kehadiran = [];
while ($row = $result->fetch_assoc()) {
    $kehadiran[] = $row;
}

$stmt->close();
$conn->close(); 
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pegawai</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="index_pegawai.php">📊 Dashboard</a>
        <a href="gaji.php">💵 Gaji</a>
        <a href="kehadiran.php">🕒 Kehadiran</a>
        <a href="../login.php" style="color: red;">🚪 Keluar</a>
    </div>

    <!-- Konten Utama -->
    <div class="main-content">
        <div class="header">
            <h1>Kehadiran Pegawai</h1>
        </div>

        <div class="absensi-container">

            <!-- Ringkasan Absensi -->
            <div class="absensi-summary">
                <h3>Kehadiran Pegawai</h3>
                <form method="POST">
                    <div class="absensi-action">
                        <select name="status" class="absensi-select">
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Absen">Absen</option>
                        </select>
                        <button type="submit" class="btn-absensi">Lakukan Kehadiran</button>
                    </div>
                </form>
            </div>


            <!-- Filter Absensi -->
            <div class="filter-container">
                <label for="filterTahun">Tahun:</label>
                <select id="filterTahun" class="filter-select">
                    <?php
                    $tahunSekarang = date('Y');
                    for ($i = $tahunSekarang; $i >= $tahunSekarang - 5; $i--) {
                        echo "<option value='$i' " . ($tahun == $i ? "selected" : "") . ">$i</option>";
                    }
                    ?>
                </select>

                <label for="filterBulan">Bulan:</label>
                <select id="filterBulan" class="filter-select">
                    <?php
                    $bulanArray = [
                        "01" => "Januari", "02" => "Februari", "03" => "Maret", "04" => "April", 
                        "05" => "Mei", "06" => "Juni", "07" => "Juli", "08" => "Agustus", 
                        "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember"
                    ];
                    foreach ($bulanArray as $key => $value) {
                        echo "<option value='$key' " . ($bulan == $key ? "selected" : "") . ">$value</option>";
                    }
                    ?>
                </select>

                <button id="btnFilter" class="btn-filter" onclick="filterAbsensi()">Filter</button>
            </div>


            <!-- Tabel Riwayat Absensi -->
            <div class="gaji-history">
                <h3>Riwayat Absensi</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($kehadiran)) : ?>
                            <tr>
                                <td colspan="2" style="text-align:center;">Tidak ada data absensi</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($kehadiran as $row) : ?>
                                <tr>
                                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                                    <td><?= $row['status'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <script>
function filterAbsensi() {
            var tahun = document.getElementById("filterTahun").value;
            var bulan = document.getElementById("filterBulan").value;
            window.location.href = "kehadiran.php?tahun=" + tahun + "&bulan=" + bulan;
        }
    </script>

</body>

</html>