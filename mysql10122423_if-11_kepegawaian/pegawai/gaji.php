<?php
session_start();
require_once "../config/database.php"; // Sesuaikan dengan path koneksi database

// Cek apakah pegawai sudah login
if (!isset($_SESSION["id_pegawai"])) {
    header("Location: ../login.php");
    exit();
}

$id_pegawai = $_SESSION["id_pegawai"];
$tahun_filter = isset($_GET['tahun']) ? $_GET['tahun'] : date("Y");
$bulan_filter = isset($_GET['bulan']) ? $_GET['bulan'] : date("m");

// Query untuk mengambil riwayat gaji pegawai berdasarkan ID pegawai
$query = "SELECT bulan, tahun, total_gaji FROM gaji WHERE id_pegawai = ? AND tahun = ? AND bulan = ? ORDER BY tahun DESC, bulan DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $id_pegawai, $tahun_filter, $bulan_filter);
$stmt->execute();
$result = $stmt->get_result();

// Query untuk mengambil gaji terbaru berdasarkan updated_at
$query_detail = "SELECT gaji_pokok, tunjangan, potongan, total_gaji FROM gaji WHERE id_pegawai = ? ORDER BY updated_at DESC LIMIT 1";
$stmt_detail = $conn->prepare($query_detail);
$stmt_detail->bind_param("i", $id_pegawai);
$stmt_detail->execute();
$detail_result = $stmt_detail->get_result();
$detail_gaji = $detail_result->fetch_assoc();

// Array untuk konversi bulan angka ke nama bulan
$bulanNama = [
    1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April",
    5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus",
    9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pegawai - Gaji</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
    .filter-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .filter-container label {
        font-weight: bold;
    }

    .filter-container select {
        width: 150px;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
    }

    .btn-filter {
        background: #28a745;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }

    .btn-filter:hover {
        background: #218838;
    }

    /* Pop-up Styling */
    .popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 350px;
        max-height: 400px;
        overflow-y: auto;
        background: white;
        padding: 15px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
        border-radius: 8px;
        text-align: center;
    }

    .popup-content {
        padding: 10px;
    }

    .close-popup {
        position: absolute;
        top: 5px;
        right: 10px;
        font-size: 18px;
        cursor: pointer;
    }

    .btn-container {
        margin-top: 10px;
    }

    .btn-detail-gaji {
        background: #28a745;
        padding: 8px 12px;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        cursor: pointer;
    }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="index_pegawai.php">📊 Dashboard</a>
        <a href="gaji.php">💵 Gaji</a>
        <a href="kehadiran.php">🕒 Kehadiran</a>
        <a href="../logout.php" style="color: red;">🚪 Keluar</a>
    </div>

    <!-- Konten Utama -->
    <div class="main-content">
        <div class="header">
            <h1>Gaji Pegawai</h1>
        </div>

        <!-- Ringkasan Gaji -->
        <div class="gaji-summary">
            <h3>Gaji Bulan Ini</h3>
            <div class="btn-container">
                <button class="btn-detail-gaji" id="btnDetailGaji">Detail Gaji</button>
            </div>
        </div>

        <!-- Pop-up Detail Gaji -->
        <div class="popup" id="popupDetailGaji">
            <div class="popup-content">
                <span class="close-popup" id="closePopup">&times;</span>
                <h3>Detail Gaji</h3>
                <p><strong>Gaji Pokok:</strong> Rp <?= number_format($detail_gaji['gaji_pokok'], 0, ',', '.'); ?></p>
                <p><strong>Tunjangan:</strong> Rp <?= number_format($detail_gaji['tunjangan'], 0, ',', '.'); ?></p>
                <p><strong>Potongan:</strong> Rp <?= number_format($detail_gaji['potongan'], 0, ',', '.'); ?></p>
                <p><strong>Total Gaji:</strong> Rp <?= number_format($detail_gaji['total_gaji'], 0, ',', '.'); ?></p>
            </div>
        </div>

        <!-- Filter Tahun dan Bulan -->
        <div class="filter-container">
    <form method="GET" action="" style="display: flex; align-items: center; gap: 20px;">
        <label for="filterTahun">Tahun:</label>
        <select name="tahun" id="filterTahun" class="filter-select">
            <?php
            $tahunSekarang = date("Y");
            for ($i = $tahunSekarang; $i >= 2020; $i--) {
                echo "<option value='$i' " . ($tahun_filter == $i ? "selected" : "") . ">$i</option>";
            }
            ?>
        </select>

        <label for="filterBulan">Bulan:</label>
        <select name="bulan" id="filterBulan" class="filter-select">
            <?php
            foreach ($bulanNama as $key => $value) {
                echo "<option value='$key' " . ($bulan_filter == $key ? "selected" : "") . ">$value</option>";
            }
            ?>
        </select>

        <button type="submit" class="btn-filter">Filter</button>
    </form>
</div>

        <!-- Riwayat Gaji -->
        <div class="gaji-history">
            <h3>Riwayat Gaji</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tahun</th>
                        <th>Bulan</th>
                        <th>Gaji</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['tahun']}</td>
                                <td>{$bulanNama[$row['bulan']]}</td>
                                <td>Rp " . number_format($row['total_gaji'], 0, ',', '.') . "</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center;'>Data tidak ditemukan</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.getElementById("btnDetailGaji").addEventListener("click", function() {
        document.getElementById("popupDetailGaji").style.display = "block";
    });

    document.getElementById("closePopup").addEventListener("click", function() {
        document.getElementById("popupDetailGaji").style.display = "none";
    });
    </script>
</body>

</html>