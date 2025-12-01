<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$rol  = $user['rol'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetim Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#f5f6fa; }
        .btn-main {
            width: 190px;
            margin: 6px;
            border-radius: 999px;
            font-weight: 500;
        }
        .panel {
            background:white;
            padding:25px;
            border-radius:18px;
            box-shadow:0 10px 25px rgba(15,23,42,0.12);
            max-width:650px;
            margin:auto;
        }
        .hello-icon {
            font-size: 32px;
        }
    </style>
</head>

<body>
<div class="container mt-5">

    <div class="panel text-center">
        <div class="mb-2 hello-icon">👋</div>
        <h3>Hoş geldin, <b><?= htmlspecialchars($user['adsoyad']) ?></b></h3>
        <p class="text-muted mb-3">
            Rolün: <span class="badge bg-primary"><?= htmlspecialchars($rol) ?></span>
        </p>
        <hr>

        <!-- 🔹 Admin: Masalar Yönetimi -->
        <?php if ($rol === 'Admin'): ?>
            <a href="admin_masalar.php" class="btn btn-success btn-main">🍽 Masalar</a>
        <?php endif; ?>

        <!-- 🔹 Garson: Masalar -->
        <?php if ($rol === 'Garson'): ?>
            <a href="garson_masalar.php" class="btn btn-success btn-main">🍽 Masalar</a>
        <?php endif; ?>

        <!-- 🔹 Admin: Ürünler -->
        <?php if ($rol === 'Admin'): ?>
            <a href="urunler.php" class="btn btn-warning btn-main">🥤 Ürünler</a>
        <?php endif; ?>

        <!-- 🔹 Admin ve Kasiyer: Ödemeler -->
        <?php if (in_array($rol, ['Admin','Kasiyer'])): ?>
            <a href="kasa.php" class="btn btn-primary btn-main">💵 Ödemeler</a>
        <?php endif; ?>

        <!-- 🔹 Admin ve Kasiyer: Kasa Raporu -->
        <?php if (in_array($rol, ['Admin','Kasiyer'])): ?>
            <a href="kasa_rapor.php" class="btn btn-outline-dark btn-main">📊 Kasa Raporu</a>
        <?php endif; ?>

        <!-- 🔹 Admin: Satış Grafikleri -->
        <?php if ($rol === 'Admin'): ?>
            <a href="grafik.php" class="btn btn-dark btn-main">📊 Satış Grafikleri</a>
        <?php endif; ?>

        <!-- 🔹 Admin + Mutfak -->
        <?php if (in_array($rol, ['Admin', 'Mutfak'])): ?>
            <a href="mutfak.php" class="btn btn-outline-warning btn-main">🍳 Mutfak</a>
        <?php endif; ?>

        <!-- 🔹 Admin: Mutfak Çalışanları -->
        <?php if ($rol === 'Admin'): ?>
            <a href="admin_mutfak.php" class="btn btn-outline-dark btn-main">👨‍🍳 Mutfak Çalışanları</a>
        <?php endif; ?>

        <!-- 🔹 Garson: Siparişlerim -->
        <?php if ($rol === 'Garson'): ?>
            <a href="garson_siparisleri.php" class="btn btn-primary btn-main">🧾 Siparişlerim</a>
        <?php endif; ?>

        <!-- 🔹 Admin: Garson Yönetimi -->
        <?php if ($rol === 'Admin'): ?>
            <a href="admin_garsonlar.php" class="btn btn-outline-dark btn-main">👤 Garson Yönetimi</a>
        <?php endif; ?>

        <!-- 🔹 Admin: Kasiyer Yönetimi -->
        <?php if ($rol === 'Admin'): ?>
            <a href="admin_kasiyer.php" class="btn btn-outline-dark btn-main">💼 Kasiyer Yönetimi</a>
        <?php endif; ?>

        <!-- 🔹 Admin: Kategori Yönetimi -->
        <?php if ($rol === 'Admin'): ?>
            <a href="admin_kategoriler.php" class="btn btn-outline-secondary btn-main">📂 Kategori Yönetimi</a>
        <?php endif; ?>

        <!-- 🔹 Çıkış -->
        <div class="mt-3">
            <a href="logout.php" class="btn btn-danger btn-main">🚪 Çıkış Yap</a>
        </div>

    </div>

</div>
</body>
</html>