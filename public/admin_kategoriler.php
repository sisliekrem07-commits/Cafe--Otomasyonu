<?php
session_start();
include("../config/db.php");

// Sadece Admin erişebilir
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$mesaj = "";
$tip   = "info";

/* ==========================
   1) KATEGORİ EKLE (RESİMLİ)
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kategori_adi'])) {

    $ad = trim($_POST['kategori_adi']);
    $kategoriResim = null;

    if ($ad === "") {
        $mesaj = "Kategori adı boş olamaz.";
        $tip   = "danger";
    } else {

        /* ---- RESİM YÜKLEME ---- */
        if (!empty($_FILES['kategori_resim']['name'])) {

            // Klasör yoksa oluştur
            if (!is_dir("../uploads/kategoriler")) {
                mkdir("../uploads/kategoriler", 0777, true);
            }

            // Dosya adını benzersiz yap
            $dosyaAdi = time() . "_" . basename($_FILES['kategori_resim']['name']);
            $hedef = "../uploads/kategoriler/" . $dosyaAdi;

            if (move_uploaded_file($_FILES['kategori_resim']['tmp_name'], $hedef)) {
                $kategoriResim = $dosyaAdi;
            }
        }

        // Veritabanına kayıt
        $sql = "INSERT INTO Kategori (KategoriAdi, KategoriResim) VALUES (?, ?)";
        $ok  = sqlsrv_query($conn, $sql, [$ad, $kategoriResim]);

        if ($ok) {
            $mesaj = "Kategori başarıyla eklendi!";
            $tip   = "success";
        } else {
            $mesaj = "SQL Hatası: " . print_r(sqlsrv_errors(), true);
            $tip   = "danger";
        }
    }
}

/* ==========================
   2) SİLME
========================== */
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $id = (int)$_GET['sil'];

    // önce resim yolunu alalım
    $res = sqlsrv_query($conn, "SELECT KategoriResim FROM Kategori WHERE KategoriID=?", [$id]);
    $row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);

    if ($row && !empty($row['KategoriResim'])) {
        $dosya = "../uploads/kategoriler/" . $row['KategoriResim'];
        if (file_exists($dosya)) unlink($dosya);
    }

    sqlsrv_query($conn, "DELETE FROM Kategori WHERE KategoriID = ?", [$id]);

    header("Location: admin_kategoriler.php");
    exit;
}

/* ==========================
   3) LİSTELEME
========================== */
$sqlList = "SELECT KategoriID, KategoriAdi, KategoriResim FROM Kategori ORDER BY KategoriAdi ASC";
$stmt    = sqlsrv_query($conn, $sqlList);

$liste = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $liste[] = $row;
    }
} else {
    die("SQL Hatası: " . print_r(sqlsrv_errors(), true));
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Kategori Yönetimi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background:#f5f6fa; padding:20px; }
    .panel {
        background:white;
        padding:20px;
        border-radius:16px;
        box-shadow:0 10px 25px rgba(0,0,0,.08);
    }
    .cat-img {
        width:60px; height:60px;
        object-fit:cover; border-radius:8px;
        border:1px solid #ddd;
    }
</style>
</head>
<body>

<div class="container" style="max-width:900px;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>📁 Kategori Yönetimi</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">← Panele Dön</a>
    </div>

    <?php if ($mesaj): ?>
        <div class="alert alert-<?= htmlspecialchars($tip) ?>">
            <?= htmlspecialchars($mesaj) ?>
        </div>
    <?php endif; ?>

    <!-- EKLEME PANELİ -->
    <div class="panel mb-4">
        <h5>➕ Yeni Kategori Ekle</h5>

        <!-- enctype eklendi -->
        <form method="POST" enctype="multipart/form-data" class="row g-3 mt-2">

            <div class="col-md-8">
                <input type="text" name="kategori_adi" class="form-control"
                       placeholder="Kategori adı..." required>
            </div>

            <div class="col-md-8">
                <input type="file" name="kategori_resim" accept="image/*" class="form-control">
            </div>

            <div class="col-md-4">
                <button class="btn btn-primary w-100">Kaydet</button>
            </div>

        </form>
    </div>

    <!-- LİSTE PANELİ -->
    <div class="panel">
        <h5>📋 Kategoriler</h5>

        <table class="table table-striped mt-3 align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Resim</th>
                    <th>Kategori Adı</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($liste)): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Kategori bulunmuyor.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($liste as $k): ?>
                        <tr>
                            <td><?= $k['KategoriID'] ?></td>

                            <td>
                                <?php if (!empty($k['KategoriResim'])): ?>
                                    <img src="../uploads/kategoriler/<?= $k['KategoriResim'] ?>" class="cat-img">
                                <?php else: ?>
                                    <span class="text-muted">Yok</span>
                                <?php endif; ?>
                            </td>

                            <td><?= htmlspecialchars($k['KategoriAdi']) ?></td>

                            <td>
                                <a href="admin_kategoriler.php?sil=<?= $k['KategoriID'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Bu kategoriyi silmek istiyor musun?');">
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