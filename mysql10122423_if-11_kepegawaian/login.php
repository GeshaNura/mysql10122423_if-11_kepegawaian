<?php
session_start();
require_once "config/database.php"; // Sesuaikan dengan koneksi database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Cek apakah username ada di tabel users
    $query = "SELECT id_users, username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $storedPassword = $user["password"];
        $role = $user["role"];
        $userId = $user["id_users"];

        // Cek apakah password sudah di-hash atau belum
        if (password_verify($password, $storedPassword) || $password === $storedPassword) {
            // Simpan sesi user
            $_SESSION["user_id"] = $userId;
            $_SESSION["role"] = $role;

            if ($role === "admin") {
                header("Location: admin/index_admin.php");
                exit();
            } elseif ($role === "pegawai") {
                // Cari id_pegawai berdasarkan id_users
                $queryPegawai = "SELECT id_pegawai FROM pegawai WHERE id_users = ?";
                $stmtPegawai = $conn->prepare($queryPegawai);
                $stmtPegawai->bind_param("i", $userId);
                $stmtPegawai->execute();
                $resultPegawai = $stmtPegawai->get_result();

                if ($resultPegawai->num_rows === 1) {
                    $pegawai = $resultPegawai->fetch_assoc();
                    $_SESSION["id_pegawai"] = $pegawai["id_pegawai"];
                    header("Location: pegawai/index_pegawai.php");
                    exit();
                } else {
                    $error = "Pegawai tidak ditemukan.";
                }
            } else {
                $error = "Role tidak dikenali.";
            }
        } else {
            $error = "Password salah.";
        }
    } else {
        $error = "Username tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kepegawaian</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)) : ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="Masukkan username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                    <span class="toggle-password" onclick="togglePassword('password', this)">👁</span>
                </div>
            </div>

            <button type="submit">Login</button>
        </form>
    </div>

    <script>
        function togglePassword(fieldId, iconElement) {
            let passwordInput = document.getElementById(fieldId);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                iconElement.textContent = "🔒"; 
            } else {
                passwordInput.type = "password";
                iconElement.textContent = "👁"; 
            }
        }
    </script>
</body>
</html>
