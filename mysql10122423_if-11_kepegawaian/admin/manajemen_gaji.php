<?php
include '../config/database.php';

// Proses Hapus Data Jika Ada Permintaan GET 'hapus'
if (isset($_GET['hapus'])) {
    $id_gaji = $_GET['hapus'];
    $query = "DELETE FROM gaji WHERE id_gaji = '$id_gaji'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "<script>
            alert('Data berhasil dihapus!');
            window.location.href = 'manajemen_gaji.php';
        </script>";
    } else {
        echo "<script>
            alert('Gagal menghapus data.');
            window.location.href = 'manajemen_gaji.php';
        </script>";
    }
}

// Query untuk mengambil data gaji dengan relasi pegawai dan jabatan
$query = "SELECT gaji.id_gaji, pegawai.nama AS nama_pegawai, jabatan.nama_jabatan, 
                 gaji.bulan, gaji.tahun, gaji.total_gaji
          FROM gaji
          JOIN pegawai ON gaji.id_pegawai = pegawai.id_pegawai
          JOIN jabatan ON pegawai.id_jabatan = jabatan.id_jabatan";

$result = mysqli_query($conn, $query);

// Query untuk mengambil data jabatan untuk filter
$queryJabatan = "SELECT nama_jabatan FROM jabatan";
$resultJabatan = mysqli_query($conn, $queryJabatan);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Gaji</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="index_admin.php">📊 Dashboard</a>
        <a href="manajemen_data_pegawai.php">👔 Manajemen Pegawai</a>
        <a href="manajemen_gaji.php">📑 Manajemen Gaji</a>
        <a href="manajemen_jabatan.php">🎖️ Manajemen Jabatan</a>
        <a href="../login.php" style="color: red;">🚪 Keluar</a>
    </div>

    <!-- Konten Utama -->
    <div class="main-content">
        <div class="header">
            <h1>Manajemen Gaji</h1>
        </div>

        <div class="dashboard-container">
            <!-- Filter dan Pencarian -->
            <div class="filter-container">
                <input type="text" id="searchGaji" placeholder="Cari pegawai..." onkeyup="filterData()">
                <select id="filterJabatan" class="filter-select" onchange="filterData()">
                    <option value="">Semua Jabatan</option>
                    <?php while ($rowJabatan = mysqli_fetch_assoc($resultJabatan)): ?>
                    <option value="<?= $rowJabatan['nama_jabatan'] ?>"><?= $rowJabatan['nama_jabatan'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Tabel Gaji Pegawai -->
            <div class="pegawai-table-container">
                <table class="pegawai-table" id="tabelGaji">
                    <thead>
                        <tr>
                            <th>Nama Pegawai</th>
                            <th>Jabatan</th>
                            <th>Bulan</th>
                            <th>Tahun</th>
                            <th>Total Gaji</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $bulan_nama = [
                            1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 
                            5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus", 
                            9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
                        ];

                        while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr class="data-row">
                            <td class="nama-pegawai"><?= htmlspecialchars($row['nama_pegawai']) ?></td>
                            <td class="jabatan"><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                            <td><?= $bulan_nama[$row['bulan']] ?></td>
                            <td><?= htmlspecialchars($row['tahun']) ?></td>
                            <td>Rp <?= number_format($row['total_gaji'], 0, ',', '.') ?></td>
                            <td class="action-buttons">
                                <a href="form_gaji_edit.php?id_gaji=<?= $row['id_gaji']; ?>" class="btn-action btn-edit">Edit</a>
                                <a href="#" class="btn-action btn-delete" onclick="konfirmasiHapus(<?= $row['id_gaji']; ?>)">Hapus</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <a href="form_gaji.php" class="btn-add">+ Tambah Gaji</a>
            </div>
        </div>
    </div>

    <script>
    function konfirmasiHapus(id_gaji) {
        let konfirmasi = confirm("Apakah Anda yakin ingin menghapus data ini?");
        if (konfirmasi) {
            window.location.href = "manajemen_gaji.php?hapus=" + id_gaji;
        }
    }

    function filterData() {
        let searchInput = document.getElementById("searchGaji").value.toLowerCase();
        let jabatanFilter = document.getElementById("filterJabatan").value.toLowerCase();
        let table = document.getElementById("tabelGaji");
        let rows = table.getElementsByClassName("data-row");

        for (let i = 0; i < rows.length; i++) {
            let namaPegawai = rows[i].getElementsByClassName("nama-pegawai")[0].innerText.toLowerCase();
            let jabatan = rows[i].getElementsByClassName("jabatan")[0].innerText.toLowerCase();

            let namaCocok = namaPegawai.includes(searchInput);
            let jabatanCocok = (jabatanFilter === "" || jabatan === jabatanFilter);

            if (namaCocok && jabatanCocok) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
    </script>

</body>

</html>
