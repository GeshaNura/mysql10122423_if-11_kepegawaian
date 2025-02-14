<?php
require '../config/database.php';

if (isset($_GET['hapus'])) {
    $id_jabatan = $_GET['hapus'];
    $delete_query = "DELETE FROM jabatan WHERE id_jabatan = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $id_jabatan);

    if ($delete_stmt->execute()) {
        echo "<script>alert('Jabatan berhasil dihapus!'); window.location='manajemen_jabatan.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus jabatan!'); window.location='manajemen_jabatan.php';</script>";
    }

    $delete_stmt->close();
    $conn->close();
}

$query = "SELECT id_jabatan, nama_jabatan, tunjangan, gaji_awal FROM jabatan";
$result = $conn->query($query);
$queryJabatan = "SELECT nama_jabatan FROM jabatan";
$resultJabatan = $conn->query($queryJabatan);
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
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="index_admin.php">📊 Dashboard</a>
        <a href="manajemen_data_pegawai.php">👔 Manajemen Pegawai</a>
        <a href="manajemen_gaji.php">📑 Manajemen Gaji</a>
        <a href="manajemen_jabatan.php">🎖️ Manajemen Jabatan</a>
        <a href="../login.php" style="color: red;">🚪 Keluar</a>
    </div>

    <!-- Konten Utama -->
    <div class="main-content" style="margin-top: 0;">
        <div class="header">
            <h1>Manajemen Jabatan</h1>
        </div>

        <div class="dashboard-container">
            <!-- Pencarian & Filter -->
            <div class="filter-container">
                <input type="text" id="searchBox" placeholder="Cari Jabatan..." onkeyup="filterData()">
                <select id="filterJabatan" class="filter-select" onchange="filterData()">
                    <option value="">Semua Jabatan</option>
                    <?php while ($row = $resultJabatan->fetch_assoc()): ?>
                    <option value="<?= $row['nama_jabatan'] ?>"><?= $row['nama_jabatan'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Tabel Data Jabatan -->
            <div class="pegawai-table-container">
                <table class="pegawai-table">
                    <thead>
                        <tr>
                            <th>Nama Jabatan</th>
                            <th>Tunjangan</th>
                            <th>Gaji Awal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="jabatanTable">
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="nama-jabatan"><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                            <td>Rp <?= number_format($row['tunjangan'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($row['gaji_awal'], 0, ',', '.') ?></td>
                            <td class="action-buttons">
                                <a href="form_jabatan.php?id=<?= $row['id_jabatan']; ?>" class="btn-action btn-edit">Edit</a>
                                <button class="btn-action btn-delete" onclick="confirmDelete(<?= $row['id_jabatan']; ?>, '<?= $row['nama_jabatan']; ?>')">Hapus</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="form_jabatan.php" class="btn-add">+ Tambah Jabatan</a>
            </div>
        </div>
    </div>

    <script>
    function filterData() {
        let searchInput = document.getElementById("searchBox").value.toLowerCase();
        let selectedJabatan = document.getElementById("filterJabatan").value.toLowerCase();
        let tableRows = document.querySelectorAll("#jabatanTable tr");

        tableRows.forEach(row => {
            let namaJabatan = row.querySelector(".nama-jabatan").textContent.toLowerCase();

            let matchesSearch = namaJabatan.includes(searchInput);
            let matchesJabatan = selectedJabatan === "" || namaJabatan === selectedJabatan;

            if (matchesSearch && matchesJabatan) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    function confirmDelete(id, nama) {
        let confirmation = confirm(`Apakah Anda yakin ingin menghapus jabatan "${nama}"?`);

        if (confirmation) {
            window.location.href = `manajemen_jabatan.php?hapus=${id}`;
        }
    }
    </script>
</body>
</html>
