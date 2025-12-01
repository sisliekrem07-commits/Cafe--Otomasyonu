<?php
session_start();
include("../config/db.php");

// Yetki kontrolü: sadece admin
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$mesaj = "";
$tip = "info";

/* =============================
   1. Mutfak çalışanı ekle
============================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['islem'] === 'ekle') {

    $adsoyad = trim($_POST['adsoyad']);
    $kadi = trim($_POST['kullanici_adi']);
    $sifre = trim($_POST['sifre']);

    if ($adsoyad === "" || $kadi === "" || $sifre === "") {
        $mesaj = "Lütfen tüm alanları doldurun!";
        $tip = "danger";
    } else {

        // kullanıcı adı var mı?
        $sqlC = "SELECT 1 FROM Kullanici WHERE KullaniciAdi = ?";
        $st = sqlsrv_query($conn, $sqlC, [$kadi]);

        if ($st && sqlsrv_has_rows($st)) {
            $mesaj = "Bu kullanıcı adı zaten var!";
            $tip = "warning";
        } else {
            $sqlEkle = "INSERT INTO Kullanici (AdSoyad, KullaniciAdi, Sifre, Rol, Aktif) 
                        VALUES (?, ?, ?, 'Mutfak', 1)";
            $ok = sqlsrv_query($conn, $sqlEkle, [$adsoyad, $kadi, $sifre]);

            if ($ok) {
                $mesaj = "Mutfak çalışanı başarıyla eklendi!";
                $tip = "success";
            } else {
                $mesaj = "Hata oluştu!";
                $tip = "danger";
            }
        }
    }
}

/* =============================
   2. Aktif/Pasif yap
============================= */
if (isset($_GET['toggle'])) {

    $id = intval($_GET['toggle']);
    $s = sqlsrv_query($conn, "SELECT Aktif FROM Kullanici WHERE KullaniciID=? AND Rol='Mutfak'", [$id]);
    $row = sqlsrv_fetch_array($s, SQLSRV_FETCH_ASSOC);

    if ($row) {
        $yeni = $row['Aktif'] ? 0 : 1;
        sqlsrv_query($conn, "UPDATE Kullanici SET Aktif=? WHERE KullaniciID=?", [$yeni, $id]);
    }

    header("Location: admin_mutfak.php");
    exit;
}

/* =============================
   3. Silme İşlemi
============================= */
if (isset($_GET['sil'])) {
    $id = intval($_GET['sil']);

    // Sadece mutfak rolündeki kullanıcıyı sil
    $check = sqlsrv_query($conn, "SELECT 1 FROM Kullanici WHERE KullaniciID=? AND Rol='Mutfak'", [$id]);

    if ($check && sqlsrv_fetch_array($check)) {
        sqlsrv_query($conn, "DELETE FROM Kullanici WHERE KullaniciID=?", [$id]);
        $mesaj = "Kullanıcı silindi!";
        $tip = "success";
    } else {
        $mesaj = "Silme işlemi başarısız!";
        $tip = "danger";
    }
}

/* =============================
   4. Liste
============================= */
$sqlList = "
    SELECT KullaniciID, AdSoyad, KullaniciAdi, Aktif
    FROM Kullanici
    WHERE Rol='Mutfak'
    ORDER BY Aktif DESC, AdSoyad ASC
";
$stmt = sqlsrv_query($conn, $sqlList);
$liste = [];
while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $liste[] = $r;
}

?>
<!DOCTYPE html>
<html lang='tr'>
<head>
<meta charset='UTF-8'>
<title>Mutfak Çalışanı Yönetimi</title>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
<style>
    body { background:#f3f4f6; padding:20px; }
    .panel { background:white; padding:22px; border-radius:18px; box-shadow:0 10px 25px rgba(0,0,0,.1); }
    .badge-aktif{background:#22c55e;}
    .badge-pasif{background:#6b7280;}
</style>
</head>

<body>

<div class="container" style="max-width: 1000px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>👨‍🍳 Mutfak Çalışanı Yönetimi</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">← Panele Dön</a>
    </div>

    <?php if ($mesaj): ?>
        <div class="alert alert-<?= $tip ?>"><?= $mesaj ?></div>
    <?php endif; ?>

    <!-- EKLEME PANELİ -->
    <div class="panel mb-4">
        <h5 class="mb-3">➕ Mutfak Çalışanı Ekle</h5>
        <form method="POST" class="row g-3">
            <input type="hidden" name="islem" value="ekle">

            <div class="col-md-4">
                <label class="form-label">Ad Soyad</label>
                <input type="text" name="adsoyad" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Kullanıcı Adı</label>
                <input type="text" name="kullanici_adi" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Şifre</label>
                <input type="text" name="sifre" class="form-control" required>
            </div>

            <div class="col-12 text-end">
                <button class="btn btn-primary px-4">Kaydet</button>
            </div>
        </form>
    </div>

    <!-- LİSTE PANELİ -->
    <div class="panel">
        <h5 class="mb-3">📋 Kayıtlı Mutfak Çalışanları</h5>

        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Ad Soyad</th>
                    <th>Kullanıcı Adı</th>
                    <th>Durum</th>
                    <th style="width:180px;">İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($liste)): ?>
                    <tr><td colspan="5" class="text-center text-muted">Kayıt yok.</td></tr>
                <?php else: ?>
                    <?php foreach ($liste as $g): ?>
                        <tr>
                            <td><?= $g['KullaniciID'] ?></td>
                            <td><?= htmlspecialchars($g['AdSoyad']) ?></td>
                            <td><?= htmlspecialchars($g['KullaniciAdi']) ?></td>
                            <td>
                                <?php if ($g['Aktif']): ?>
                                    <span class="badge badge-aktif">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-pasif">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="admin_mutfak.php?toggle=<?= $g['KullaniciID'] ?>"
                                   class="btn btn-sm <?= $g['Aktif'] ? 'btn-outline-danger':'btn-outline-success' ?>">
                                   <?= $g['Aktif'] ? 'Pasif Yap' : 'Aktif Yap' ?>
                                </a>

                                <a href="admin_mutfak.php?sil=<?= $g['KullaniciID'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bu çalışanı silmek istediğinize emin misiniz?')">
                                   Sil
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

</div>

</body>
</html>