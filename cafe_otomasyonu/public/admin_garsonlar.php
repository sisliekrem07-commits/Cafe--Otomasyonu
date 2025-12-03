<?php
session_start();
include("../config/db.php");

// Sadece Admin erişsin
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$mesaj = "";
$mesajTip = "info";

/* ============================
   1) GARSON EKLEME
============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['islem'] === 'ekle') {

    $adsoyad = trim($_POST['adsoyad']);
    $kadi    = trim($_POST['kullanici_adi']);
    $sifre   = trim($_POST['sifre']);

    if ($adsoyad === "" || $kadi === "" || $sifre === "") {
        $mesaj = "Lütfen tüm alanları doldurun.";
        $mesajTip = "danger";
    } else {

        // kullanıcı var mı kontrol
        $sqlC = "SELECT 1 FROM Kullanici WHERE KullaniciAdi = ?";
        $st = sqlsrv_query($conn, $sqlC, [$kadi]);

        if ($st && sqlsrv_has_rows($st)) {
            $mesaj = "Bu kullanıcı adı zaten kayıtlı.";
            $mesajTip = "warning";
        } else {

            $sqlIns = "INSERT INTO Kullanici (AdSoyad, KullaniciAdi, Sifre, Rol, Aktif)
                       VALUES (?, ?, ?, 'Garson', 1)";
            $ok = sqlsrv_query($conn, $sqlIns, [$adsoyad, $kadi, $sifre]);

            if ($ok) {
                $mesaj = "Garson başarıyla eklendi.";
                $mesajTip = "success";
            } else {
                $mesaj = "Garson eklenirken bir hata oluştu!";
                $mesajTip = "danger";
            }
        }
    }
}

/* ============================
   2) GARSONU AKTİF / PASİF YAP
============================ */
if (isset($_GET['toggle']) && ctype_digit($_GET['toggle'])) {

    $id = intval($_GET['toggle']);

    $s = sqlsrv_query($conn, "SELECT Aktif FROM Kullanici WHERE KullaniciID = ? AND Rol='Garson'", [$id]);
    $row = sqlsrv_fetch_array($s, SQLSRV_FETCH_ASSOC);

    if ($row) {
        $yeni = $row['Aktif'] ? 0 : 1;

        sqlsrv_query($conn, "UPDATE Kullanici SET Aktif = ? WHERE KullaniciID = ?", [$yeni, $id]);
    }

    header("Location: admin_garsonlar.php");
    exit;
}

/* ============================
   3) GARSON SİLME
============================ */
if (isset($_GET['sil']) && ctype_digit($_GET['sil'])) {

    $id = intval($_GET['sil']);

    sqlsrv_query($conn, "DELETE FROM Kullanici WHERE KullaniciID = ? AND Rol='Garson'", [$id]);

    header("Location: admin_garsonlar.php");
    exit;
}

/* ============================
   4) GARSON LİSTESİ
============================ */
$sqlList = "
    SELECT KullaniciID, AdSoyad, KullaniciAdi, Aktif
    FROM Kullanici
    WHERE Rol='Garson'
    ORDER BY Aktif DESC, AdSoyad ASC
";
$res = sqlsrv_query($conn, $sqlList);

$garsonlar = [];
while ($r = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
    $garsonlar[] = $r;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Garson Yönetimi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f5f6fa; padding:20px; }
.card-main { border-radius:16px; box-shadow:0 10px 25px rgba(0,0,0,.1); }
.badge-aktif { background:#22c55e; }
.badge-pasif { background:#6b7280; }
</style>
</head>

<body>

<div class="container" style="max-width:1000px;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>👤 Garson Yönetimi</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">← Panele Dön</a>
    </div>

    <?php if ($mesaj): ?>
        <div class="alert alert-<?= $mesajTip ?>"><?= $mesaj ?></div>
    <?php endif; ?>

    <!-- Ekleme Paneli -->
    <div class="card card-main mb-4 p-3">
        <h5>➕ Yeni Garson Ekle</h5>

        <form method="POST" class="row g-3 mt-1">
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

    <!-- Liste Paneli -->
    <div class="card card-main p-3">
        <h5>📋 Kayıtlı Garsonlar</h5>

        <table class="table mt-3 table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Ad Soyad</th>
                    <th>Kullanıcı Adı</th>
                    <th>Durum</th>
                    <th style="width:160px;">İşlem</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($garsonlar) === 0): ?>
                    <tr><td colspan="5" class="text-center text-muted">Henüz garson yok.</td></tr>
                <?php else: ?>
                    <?php foreach ($garsonlar as $g): ?>
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
                                <!-- toggle -->
                                <a class="btn btn-sm <?= $g['Aktif'] ? 'btn-outline-danger':'btn-outline-success' ?>"
                                   href="admin_garsonlar.php?toggle=<?= $g['KullaniciID'] ?>">
                                    <?= $g['Aktif'] ? 'Pasif Yap' : 'Aktif Yap' ?>
                                </a>

                                <!-- sil -->
                                <a class="btn btn-sm btn-danger"
                                   href="admin_garsonlar.php?sil=<?= $g['KullaniciID'] ?>"
                                   onclick="return confirm('Bu garsonu silmek istiyor musunuz?');">
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