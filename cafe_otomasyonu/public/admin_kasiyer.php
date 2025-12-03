<?php
session_start();
include("../config/db.php");
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$mesaj = "";
$tip = "";

/*KASİYER EKLEME*/
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['ekle'])) {

    $adsoyad = trim($_POST['adsoyad']);
    $kadi    = trim($_POST['kadi']);
    $sifre   = trim($_POST['sifre']);

    if ($adsoyad == "" || $kadi == "" || $sifre == "") {
        $mesaj = "Lütfen tüm alanları doldurun!";
        $tip   = "danger";
    } else {

        $sql = "INSERT INTO Kullanici (AdSoyad, KullaniciAdi, Sifre, Rol, Aktif)
                VALUES (?,?,?,?,1)";

        $ok = sqlsrv_query($conn, $sql, [$adsoyad, $kadi, $sifre, 'Kasiyer']);

        if ($ok) {
            $mesaj = "Kasiyer başarıyla eklendi!";
            $tip   = "success";
        } else {
            $mesaj = "SQL Hatası: " . print_r(sqlsrv_errors(), true);
            $tip   = "danger";
        }
    }
}

/*KASİYER SİLME*/
if (isset($_GET['sil'])) {
    $id = intval($_GET['sil']);
    sqlsrv_query($conn, 
        "DELETE FROM Kullanici WHERE KullaniciID=? AND Rol='Kasiyer'", 
        [$id]
    );
    header("Location: admin_kasiyer.php");
    exit;
}

/* KASİYER LİSTELEME*/
$sql = "SELECT KullaniciID, AdSoyad, KullaniciAdi 
        FROM Kullanici 
        WHERE Rol='Kasiyer'
        ORDER BY AdSoyad ASC";

$stmt = sqlsrv_query($conn, $sql);
$liste = [];

if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $liste[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Kasiyer Yönetimi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f3f4f6; padding:20px; }
.panel { background:white; padding:20px; border-radius:12px; }
</style>
</head>
<body>

<div class="container" style="max-width:900px;">

    <h3 class="mb-3">💼 Kasiyer Yönetimi</h3>
    <a href="dashboard.php" class="btn btn-secondary mb-3">← Panele Dön</a>

    <?php if ($mesaj): ?>
        <div class="alert alert-<?= $tip ?>"><?= $mesaj ?></div>
    <?php endif; ?>

    <!-- EKLE -->
    <div class="panel mb-4">
        <h5>Kasiyer Ekle</h5>
        <form method="POST" class="row g-3 mt-2">

            <div class="col-md-4">
                <input type="text" name="adsoyad" class="form-control" placeholder="Ad Soyad" required>
            </div>

            <div class="col-md-4">
                <input type="text" name="kadi" class="form-control" placeholder="Kullanıcı Adı" required>
            </div>

            <div class="col-md-4">
                <input type="password" name="sifre" class="form-control" placeholder="Şifre" required>
            </div>

            <div class="col-md-12 text-end">
                <button class="btn btn-success" name="ekle">Kaydet</button>
            </div>

        </form>
    </div>

    <!-- LİSTE -->
    <div class="panel">
        <h5>Kasiyer Listesi</h5>
        <table class="table table-striped text-center mt-3">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Ad Soyad</th>
                    <th>Kullanıcı Adı</th>
                    <th>İşlem</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($liste as $k): ?>
                <tr>
                    <td><?= $k['KullaniciID'] ?></td>
                    <td><?= htmlspecialchars($k['AdSoyad']) ?></td>
                    <td><?= htmlspecialchars($k['KullaniciAdi']) ?></td>
                    <td>
                        <a href="admin_kasiyer.php?sil=<?= $k['KullaniciID'] ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Kasiyer silinsin mi?')">
                           Sil
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>
    </div>

</div>

</body>
</html>