<?php
include '../config/database.php';

$queryPegawai = "SELECT COUNT(id_pegawai) AS total_pegawai FROM pegawai";
$resultPegawai = mysqli_query($conn, $queryPegawai);
$totalPegawai = mysqli_fetch_assoc($resultPegawai)['total_pegawai'];

$queryGaji = "SELECT SUM(total_gaji) AS total_gaji 
              FROM gaji 
              WHERE MONTH(updated_at) = MONTH(CURRENT_DATE()) 
              AND YEAR(updated_at) = YEAR(CURRENT_DATE())";
$resultGaji = mysqli_query($conn, $queryGaji);
$totalGaji = mysqli_fetch_assoc($resultGaji)['total_gaji'] ?: 0;

$queryJabatan = "SELECT j.nama_jabatan, COUNT(p.id_pegawai) AS jumlah 
                 FROM pegawai p 
                 JOIN jabatan j ON p.id_jabatan = j.id_jabatan 
                 GROUP BY j.nama_jabatan";
$resultJabatan = mysqli_query($conn, $queryJabatan);
$jabatanData = [];
while ($row = mysqli_fetch_assoc($resultJabatan)) {
    $jabatanData[$row['nama_jabatan']] = $row['jumlah'];
}


$queryAktivitas = "
    (SELECT p.updated_at AS waktu, CONCAT('👤 Pegawai baru ditambahkan: <b>', p.nama, '</b>') AS keterangan
     FROM pegawai p WHERE DATE(p.updated_at) = CURDATE())

    UNION ALL

    (SELECT k.updated_at AS waktu, CONCAT('⏳ Kehadiran: <b>', p.nama, '</b> (', k.status, ')') AS keterangan
     FROM kehadiran k
     JOIN pegawai p ON k.id_pegawai = p.id_pegawai
     WHERE DATE(k.updated_at) = CURDATE())

    UNION ALL

    (SELECT g.updated_at AS waktu, CONCAT('💰 Gaji diperbarui: <b>', p.nama, '</b> (Rp ', FORMAT(g.total_gaji, 0, 'id_ID'), ',-)') AS keterangan
     FROM gaji g
     JOIN pegawai p ON g.id_pegawai = p.id_pegawai
     WHERE DATE(g.updated_at) = CURDATE())

    UNION ALL

    (SELECT p.updated_at AS waktu, CONCAT('🔄 Ganti Jabatan: <b>', p.nama, '</b> menjadi <b>', j.nama_jabatan, '</b>') AS keterangan
     FROM pegawai p
     JOIN jabatan j ON p.id_jabatan = j.id_jabatan
     WHERE DATE(p.updated_at) = CURDATE())

    ORDER BY waktu DESC
    LIMIT 10
";

$resultAktivitas = $conn->query($queryAktivitas);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="sidebar">
        <a href="index_admin.php">📊 Dashboard</a>
        <a href="manajemen_data_pegawai.php">👔 Manajemen Pegawai</a>
        <a href="manajemen_gaji.php">📑 Manajemen Gaji</a>
        <a href="manajemen_jabatan.php">🎖️ Manajemen Jabatan</a>
        <a href="../login.php" style="color: red;">🚪 Keluar</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Dashboard Admin</h1>
        </div>

        <div class="dashboard-container">
            <div class="stats-container">
                <div class="stat-box">
                    <h3>Total Pegawai</h3>
                    <p id="totalPegawai"><?= $totalPegawai ?></p>
                </div>
                <div class="stat-box">
                    <h3>Total Gaji Bulan Ini</h3>
                    <p id="totalGaji">Rp <?= number_format($totalGaji, 0, ',', '.') ?></p>
                </div>
                <div class="stat-box">
                    <h3>Pegawai per Jabatan</h3>
                    <canvas id="jabatanChart"></canvas>
                </div>

            </div>
            <!-- Aktivitas Terbaru -->
            <div class="activity-container">
                <h3>Aktivitas Terbaru</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="activityTable">
                        <?php while ($row = $resultAktivitas->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('H:i', strtotime($row['waktu'])) ?></td>
                            <td><?= $row['keterangan'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <script>
    let jabatanLabels = <?= json_encode(array_keys($jabatanData)); ?>;
    let jabatanValues = <?= json_encode(array_values($jabatanData)); ?>;

    let ctx = document.getElementById("jabatanChart").getContext("2d");
    new Chart(ctx, {
        type: "pie",
        data: {
            labels: jabatanLabels,
            datasets: [{
                data: jabatanValues,
                backgroundColor: ["#007bff", "#28a745", "#ffc107", "#dc3545", "#17a2b8", "#6f42c1"]
            }]
        }
    });
    </script>
</body>

</html>