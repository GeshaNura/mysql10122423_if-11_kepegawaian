<?php
session_start();
require '../config/database.php'; 

if (!isset($_SESSION['id_pegawai'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href = '../login.php';</script>";
    exit();
}

$id_pegawai = $_SESSION['id_pegawai'];
$nama_pegawai = ""; 

$queryNama = "SELECT nama FROM pegawai WHERE id_pegawai = ?";
$stmtNama = $conn->prepare($queryNama);
$stmtNama->bind_param("i", $id_pegawai);
$stmtNama->execute();
$resultNama = $stmtNama->get_result();
if ($row = $resultNama->fetch_assoc()) {
    $nama_pegawai = $row['nama'];
}
$stmtNama->close();

$bulanSekarang = date('m');
$tahunSekarang = date('Y');

$queryMasuk = "SELECT COUNT(id_kehadiran) AS total_masuk FROM kehadiran 
               WHERE id_pegawai = ? AND status = 'Hadir' AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?";
$stmtMasuk = $conn->prepare($queryMasuk);
$stmtMasuk->bind_param("iii", $id_pegawai, $bulanSekarang, $tahunSekarang);
$stmtMasuk->execute();
$resultMasuk = $stmtMasuk->get_result();
$totalHariMasuk = $resultMasuk->fetch_assoc()['total_masuk'] ?? 0;
$stmtMasuk->close();

$queryGaji = "SELECT SUM(total_gaji) AS total_gaji FROM gaji 
              WHERE id_pegawai = ? AND bulan = ? AND tahun = ?";
$stmtGaji = $conn->prepare($queryGaji);
$stmtGaji->bind_param("iii", $id_pegawai, $bulanSekarang, $tahunSekarang);
$stmtGaji->execute();
$resultGaji = $stmtGaji->get_result();
$totalGaji = $resultGaji->fetch_assoc()['total_gaji'] ?? 0;
$stmtGaji->close();
$queryIzin = "SELECT COUNT(id_kehadiran) AS total_izin FROM kehadiran 
              WHERE id_pegawai = ? AND status = 'Izin' AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?";
$stmtIzin = $conn->prepare($queryIzin);
$stmtIzin->bind_param("iii", $id_pegawai, $bulanSekarang, $tahunSekarang);
$stmtIzin->execute();
$resultIzin = $stmtIzin->get_result();
$totalIzin = $resultIzin->fetch_assoc()['total_izin'] ?? 0;
$stmtIzin->close();

$querySakit = "SELECT COUNT(id_kehadiran) AS total_sakit FROM kehadiran 
               WHERE id_pegawai = ? AND status = 'Sakit' AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?";
$stmtSakit = $conn->prepare($querySakit);
$stmtSakit->bind_param("iii", $id_pegawai, $bulanSekarang, $tahunSekarang);
$stmtSakit->execute();
$resultSakit = $stmtSakit->get_result();
$totalSakit = $resultSakit->fetch_assoc()['total_sakit'] ?? 0;
$stmtSakit->close();

$queryGrafik = "SELECT bulan, total_gaji FROM gaji 
                WHERE id_pegawai = ? AND tahun = ? ORDER BY bulan ASC";
$stmtGrafik = $conn->prepare($queryGrafik);
$stmtGrafik->bind_param("ii", $id_pegawai, $tahunSekarang);
$stmtGrafik->execute();
$resultGrafik = $stmtGrafik->get_result();

$bulanArray = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];
$dataGaji = array_fill(0, 12, 0);

while ($row = $resultGrafik->fetch_assoc()) {
    $bulanIndex = (int)$row['bulan'] - 1;
    $dataGaji[$bulanIndex] = (int)$row['total_gaji'];
}

$stmtGrafik->close();
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
</head>

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
            <h1>Dashboard Pegawai</h1>
            <p id="namaPegawai">Selamat datang, <?= htmlspecialchars($nama_pegawai) ?>!</p>
        </div>

        <div class="dashboard-container">
            <!-- Statistik & Ringkasan -->
            <div class="stats-container">
                <div class="stat-box">
                    <h3>Total Hari Masuk</h3>
                    <p id="totalHariMasuk"><?= $totalHariMasuk ?> hari</p>
                </div>
                <div class="stat-box">
                    <h3>Total Gaji Bulan Ini</h3>
                    <p id="totalGaji">Rp <?= number_format($totalGaji, 0, ',', '.') ?></p>
                </div>
                <div class="stat-box">
                    <h3>Total Izin Bulan Ini</h3>
                    <p id="totalIzin"><?= $totalIzin ?> hari</p>
                </div>
                <div class="stat-box">
                    <h3>Total Sakit Bulan Ini</h3>
                    <p id="totalSakit"><?= $totalSakit ?> hari</p>
                </div>
            </div>

            <!-- Grafik Gaji -->
            <div class="chart-container">
                <canvas id="gajiChart" width="300" height="150"></canvas>
            </div>

        </div>
    </div>

    <script>
    var ctx = document.getElementById('gajiChart').getContext('2d');
    var gajiChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($bulanArray) ?>,
            datasets: [{
                label: 'Total Gaji',
                data: <?= json_encode($dataGaji) ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                fill: false,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>

</body>

</html>
