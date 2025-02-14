<?php
session_start();
session_destroy();

// Mencegah kembali ke halaman sebelumnya setelah logout
header("Location: login.php");
exit();
?>