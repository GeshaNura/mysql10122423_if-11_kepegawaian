<?php
require_once "../config/database.php"; 
date_default_timezone_set('Asia/Jakarta');

$id_pegawai = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pegawai = [];
$editMode = false;
$message = "";

$sqlJabatan = "SELECT id_jabatan, nama_jabatan, gaji_awal FROM jabatan";
$resultJabatan = $conn->query($sqlJabatan);
$jabatanOptions = "";
while ($row = $resultJabatan->fetch_assoc()) {
    $selected = ($row['id_jabatan'] == ($pegawai['id_jabatan'] ?? '')) ? "selected" : "";
    $jabatanOptions .= "<option value='{$row['id_jabatan']}' data-gaji='{$row['gaji_awal']}' $selected>{$row['nama_jabatan']}</option>";
}

if ($id_pegawai > 0) {
    $editMode = true;
    $sqlPegawai = "SELECT p.*, u.username, u.id_users FROM pegawai p JOIN users u ON p.id_users = u.id_users WHERE p.id_pegawai = ?";
    $stmt = $conn->prepare($sqlPegawai);
    $stmt->bind_param("i", $id_pegawai);
    $stmt->execute();
    $result = $stmt->get_result();
    $pegawai = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pegawai = $_POST['id_pegawai'] ?? 0;
    $username = trim($_POST['username']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $confirm_password = trim($_POST['confirm-password']);
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['telepon']);
    $alamat = trim($_POST['alamat']);
    $id_jabatan = $_POST['jabatan'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $gaji = intval($_POST['gajiHidden']); 
    $created_at = date("Y-m-d H:i:s");
    $errors = [];

    if (empty($username)) {
        $errors[] = "Username wajib diisi.";
    }

    if (!$editMode && empty($_POST['password'])) {
        $errors[] = "Password wajib diisi.";
    } elseif (!empty($_POST['password']) && $_POST['password'] !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok.";
    }

    if (empty($nama)) {
        $errors[] = "Nama pegawai wajib diisi.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }

    if (!preg_match("/^[0-9]+$/", $no_hp)) {
        $errors[] = "Nomor telepon hanya boleh mengandung angka.";
    }

    if (empty($alamat)) {
        $errors[] = "Alamat wajib diisi.";
    }

    if (empty($id_jabatan)) {
        $errors[] = "Jabatan wajib dipilih.";
    }

    if (!is_numeric($gaji)) {
        $errors[] = "Gaji harus berupa angka.";
    }

    if (!$editMode) {
        $sqlCekUsername = "SELECT id_users FROM users WHERE username = ?";
        $stmtCek = $conn->prepare($sqlCekUsername);
        $stmtCek->bind_param("s", $username);
        $stmtCek->execute();
        $stmtCek->store_result();
        if ($stmtCek->num_rows > 0) {
            $errors[] = "Username sudah digunakan. Silakan pilih username lain.";
        }
        $stmtCek->close();
    }

    if (!empty($errors)) {
        $message = "<ul class='error'>";
        foreach ($errors as $error) {
            $message .= "<li>$error</li>";
        }
        $message .= "</ul>";
    } else {
        $conn->begin_transaction();
        try {
            if ($editMode) {
                $sqlUser = "UPDATE users SET username = ?" . ($password ? ", password = ?" : "") . " WHERE id_users = ?";
                $stmtUser = $conn->prepare($sqlUser);
                if ($password) {
                    $stmtUser->bind_param("ssi", $username, $password, $pegawai['id_users']);
                } else {
                    $stmtUser->bind_param("si", $username, $pegawai['id_users']);
                }
                $stmtUser->execute();

                $sqlPegawai = "UPDATE pegawai SET nama = ?, email = ?, no_hp = ?, alamat = ?, id_jabatan = ?, tanggal_masuk = ?, gaji = ? WHERE id_pegawai = ?";
                $stmtPegawai = $conn->prepare($sqlPegawai);
                $stmtPegawai->bind_param("ssssissi", $nama, $email, $no_hp, $alamat, $id_jabatan, $tanggal_masuk, $gaji, $id_pegawai);
                $stmtPegawai->execute();
                $message = "<p class='success'>Data pegawai berhasil diperbarui.</p>";
            } else {
                $sqlUser = "INSERT INTO users (username, password, role, created_at) VALUES (?, ?, 'pegawai', ?)";
                $stmtUser = $conn->prepare($sqlUser);
                $stmtUser->bind_param("sss", $username, $password, $created_at);
                $stmtUser->execute();
                $id_users = $conn->insert_id;

                $sqlPegawai = "INSERT INTO pegawai (id_users, nama, email, no_hp, alamat, id_jabatan, tanggal_masuk, gaji, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtPegawai = $conn->prepare($sqlPegawai);
                $stmtPegawai->bind_param("issssisis", $id_users, $nama, $email, $no_hp, $alamat, $id_jabatan, $tanggal_masuk, $gaji, $created_at);
                $stmtPegawai->execute();
                $message = "<p class='success'>Pegawai berhasil ditambahkan.</p>";
            }

            $conn->commit();
            header("Location: manajemen_data_pegawai.php");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $message = "<p class='error'>Gagal menyimpan data: " . $e->getMessage() . "</p>";
        }
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
    <title>Form Pegawai - Kepegawaian</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>

    </style>
</head>

<body>
<a href="manajemen_data_pegawai.php" class="back-button">⬅ Kembali</a>
    <div class="pegawai-form-container">
        <h2>Form Pegawai</h2>
        <?= $message; ?>
        <form method="POST">
            <input type="hidden" name="id_pegawai" value="<?= $pegawai['id_pegawai'] ?? ''; ?>">
            <input type="hidden" name="gajiHidden" id="gajiHidden"
                value="<?= isset($pegawai['gaji']) ? $pegawai['gaji'] : ''; ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?= $pegawai['username'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" placeholder="Masukkan password">
                    <span class="toggle-password" onclick="togglePassword('password', this)">👁</span>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm-password">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <input type="password" name="confirm-password" id="confirm-password" placeholder="Ulangi password">
                    <span class="toggle-password" onclick="togglePassword('confirm-password', this)">👁</span>
                </div>
                <p id="password-error" class="error-message" style="color: red; display: none;">Konfirmasi password
                    tidak cocok!</p>
            </div>

            <div class="form-group">
                <label for="nama">Nama Pegawai</label>
                <input type="text" name="nama" id="nama" value="<?= $pegawai['nama'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?= $pegawai['email'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="telepon">Nomor Telepon</label>
                <input type="tel" name="telepon" id="telepon" value="<?= $pegawai['no_hp'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat</label>
                <input type="text" name="alamat" id="alamat" value="<?= $pegawai['alamat'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="jabatan">Jabatan</label>
                <select name="jabatan" id="jabatan" required>
                    <option value="">-- Pilih Jabatan --</option>
                    <?= $jabatanOptions; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="tanggal_masuk">Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk" id="tanggal_masuk"
                    value="<?= $pegawai['tanggal_masuk'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="gaji">Gaji</label>
                <input type="text" name="gaji" id="gaji"
                    value="<?= isset($pegawai['gaji']) ? 'Rp ' . number_format($pegawai['gaji'], 0, ',', '.') : ''; ?>"
                    required>
            </div>

            <button type="submit"><?= $editMode ? "Update" : "Simpan"; ?></button>
        </form>

    </div>

    <script>
    document.getElementById("telepon").addEventListener("input", function() {
        this.value = this.value.replace(/\D/g, "");
    });

    let jabatanSelect = document.getElementById("jabatan");
    let gajiInput = document.getElementById("gaji");
    let gajiHidden = document.getElementById("gajiHidden");

    jabatanSelect.addEventListener("change", function() {
        let selectedOption = this.options[this.selectedIndex];
        let gajiBawaan = selectedOption.getAttribute("data-gaji");

        if (gajiBawaan) {
            gajiInput.value = formatRupiah(gajiBawaan);
            gajiHidden.value = gajiBawaan;
        } else {
            gajiInput.value = "";
            gajiHidden.value = "";
        }
    });

    gajiInput.addEventListener("input", function() {
        let angka = this.value.replace(/\D/g, "");
        gajiInput.value = formatRupiah(angka);
        gajiHidden.value = parseInt(angka, 10); 
    });


    function formatRupiah(angka) {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0
        }).format(angka).replace("Rp", "Rp ").trim();
    }

    function togglePassword(fieldId, toggleIcon) {
        let inputField = document.getElementById(fieldId);
        if (inputField.type === "password") {
            inputField.type = "text";
            toggleIcon.textContent = "🔒"; 
        } else {
            inputField.type = "password";
            toggleIcon.textContent = "👁"; 
        }
    }

    document.getElementById("confirm-password").addEventListener("input", function() {
        let password = document.getElementById("password").value;
        let confirmPassword = this.value;
        let errorText = document.getElementById("password-error");

        if (confirmPassword !== password) {
            errorText.style.display = "block";
            this.setCustomValidity("Konfirmasi password tidak cocok!");
        } else {
            errorText.style.display = "none";
            this.setCustomValidity(""); 
        }
    });
    </script>
</body>

</html>