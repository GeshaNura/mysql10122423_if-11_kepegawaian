<?php
require '../config/database.php'; // Koneksi ke database



// Ambil data pegawai beserta jabatan
$query = "SELECT pegawai.id_pegawai, pegawai.nama, COALESCE(jabatan.nama_jabatan, 'Tidak Ada Jabatan') AS nama_jabatan, pegawai.gaji 
          FROM pegawai 
          LEFT JOIN jabatan ON pegawai.id_jabatan = jabatan.id_jabatan";

$result = $conn->query($query);

// Ambil data jabatan untuk dropdown filter
$queryJabatan = "SELECT nama_jabatan FROM jabatan";
$resultJabatan = $conn->query($queryJabatan);

// Hapus pegawai jika ada permintaan
if (isset($_POST['hapus_id'])) {
    $id_pegawai = $_POST['hapus_id'];

    // Ambil id_users sebelum menghapus pegawai
    $resultUser = $conn->query("SELECT id_users FROM pegawai WHERE id_pegawai = '$id_pegawai'");
    if ($resultUser->num_rows > 0) {
        $row = $resultUser->fetch_assoc();
        $id_users = $row['id_users'];

        // Hapus data dari tabel yang berelasi
        $conn->query("DELETE FROM kehadiran WHERE id_pegawai = '$id_pegawai'");
        $conn->query("DELETE FROM gaji WHERE id_pegawai = '$id_pegawai'");
        $conn->query("DELETE FROM pegawai WHERE id_pegawai = '$id_pegawai'");

        // Hapus data users hanya jika id_users ditemukan
        if (!empty($id_users)) {
            $conn->query("DELETE FROM users WHERE id_users = '$id_users'");
        }

        echo "<script>alert('Data pegawai berhasil dihapus!'); window.location.href='manajemen_data_pegawai.php';</script>";
    } else {
        echo "<script>alert('Pegawai tidak ditemukan!'); window.location.href='manajemen_data_pegawai.php';</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
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
            <h1>Manajemen Data Pegawai</h1>
        </div>

        <div class="dashboard-container">
            <!-- Pencarian & Filter -->
            <div class="filter-container">
                <input type="text" id="searchBox" placeholder="Cari pegawai..." onkeyup="filterData()">
                <select id="filterJabatan" class="filter-select" onchange="filterData()">
                    <option value="">Semua Jabatan</option>
                    <?php while ($row = $resultJabatan->fetch_assoc()): ?>
                    <option value="<?= $row['nama_jabatan'] ?>"><?= $row['nama_jabatan'] ?></option>
                    <?php endwhile; ?>
                </select>
                
            </div>

            <!-- Tabel Data Pegawai -->
            <div class="pegawai-table-container">
                <table class="pegawai-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Gaji</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pegawaiTableBody">
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['nama_jabatan']) ?></td>
                            <td>Rp <?= number_format($row['gaji'], 0, ',', '.') ?></td>
                            <td class="action-buttons">
                                <a href="form_pegawai.php?id=<?= $row['id_pegawai']; ?>" class="btn-action btn-edit">Edit</a>
                                <button class="btn-action btn-delete" onclick="confirmDelete(<?= $row['id_pegawai']; ?>, '<?= $row['nama']; ?>')">Hapus</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <a href="form_pegawai.php" class="btn-add">+ Tambah Pegawai</a>
            </div>
        </div>
    </div>

    <!-- Form Hapus Data (Hidden) -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="hapus_id" id="hapus_id">
    </form>

    <script>
    function filterData() {
        let searchValue = document.getElementById("searchBox").value.toLowerCase();
        let filterValue = document.getElementById("filterJabatan").value.toLowerCase();
        let rows = document.querySelectorAll("#pegawaiTableBody tr");

        rows.forEach(row => {
            let nama = row.children[0].innerText.toLowerCase(); // Kolom Nama
            let jabatan = row.children[1].innerText.toLowerCase(); // Kolom Jabatan

            let matchSearch = nama.includes(searchValue);
            let matchFilter = filterValue === "" || jabatan === filterValue;

            row.style.display = matchSearch && matchFilter ? "table-row" : "none";
        });
    }

    function confirmDelete(id, nama) {
        if (confirm(`Anda yakin akan menghapus data pegawai ${nama}?`)) {
            document.getElementById("hapus_id").value = id;
            document.getElementById("deleteForm").submit();
        }
    }
    </script>

</body>

</html>
