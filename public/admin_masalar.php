<?php
session_start();
include("../config/db.php");

// Sadece Admin erişebilir
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$mesaj = "";
$tip = "";

/* ============================
   MASA EKLE
============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['islem']) && $_POST['islem'] === "ekle") {

    $masaAdi = trim($_POST['masa_adi']);

    if ($masaAdi === "") {
        $mesaj = "Masa adı boş bırakılamaz!";
        $tip = "danger";
    } else {

        $sql = "INSERT INTO Masalar (MasaAdi, Durum, Aktif) VALUES (?, 'Boş', 1)";
        $stmt = sqlsrv_query($conn, $sql, [$masaAdi]);

        if ($stmt) {
            $mesaj = "Masa başarıyla eklendi!";
            $tip = "success";
        } else {
            $mesaj = "Hata oluştu!";
            $tip = "danger";
        }
    }
}

/* ============================
   MASA SİL
============================ */
if (isset($_GET['sil'])) {
    $id = intval($_GET['sil']);

    sqlsrv_query($conn, "DELETE FROM Masalar WHERE MasaID = ?", [$id]);

    header("Location: admin_masalar.php");
    exit;
}

/* ============================
   MASA GÜNCELLE
============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['islem']) && $_POST['islem'] === "duzenle") {

    $id = intval($_POST['masa_id']);
    $masaAdi = trim($_POST['masa_adi']);

    if ($masaAdi !== "") {
        sqlsrv_query($conn, "UPDATE Masalar SET MasaAdi = ? WHERE MasaID = ?", [$masaAdi, $id]);
    }

    header("Location: admin_masalar.php");
    exit;
}

/* ============================
   MASA LİSTELE
============================ */
$sql = "SELECT MasaID, MasaAdi, Durum, Aktif FROM Masalar ORDER BY MasaID ASC";
$stmt = sqlsrv_query($conn, $sql);

$masalar = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $masalar[] = $row;
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Masa Yönetimi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#f5f6fa; padding:30px; }
.panel {
    background:white;
    padding:25px;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(0,0,0,.1);
}
</style>
</head>

<body>

<div class="container" style="max-width:1000px;">
    
    <div class="d-flex justify-content-between mb-3">
        <h3>🪑 Masa Yönetimi</h3>
        <a href="dashboard.php" class="btn btn-secondary">← Panele Dön</a>
    </div>

    <?php if ($mesaj): ?>
        <div class="alert alert-<?= $tip ?>"><?= $mesaj ?></div>
    <?php endif; ?>

    <!-- MASA EKLE FORMU -->
    <div class="panel mb-4">
        <h5>➕ Yeni Masa Ekle</h5>
        <form method="POST" class="row g-3 mt-2">
            <input type="hidden" name="islem" value="ekle">

            <div class="col-md-6">
                <input type="text" name="masa_adi" class="form-control" placeholder="Masa Adı" required>
            </div>

            <div class="col-md-3">
                <button class="btn btn-primary w-100">Kaydet</button>
            </div>
        </form>
    </div>

    <!-- MASA LİSTESİ -->
    <div class="panel">
        <h5 class="mb-3">📋 Kayıtlı Masalar</h5>

        <table class="table table-hover align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Masa Adı</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>

            <tbody>
            <?php if (empty($masalar)): ?>
                <tr><td colspan="4" class="text-muted">Hiç masa eklenmemiş.</td></tr>

            <?php else: foreach ($masalar as $m): ?>
                <tr>
                    <td><?= $m['MasaID'] ?></td>
                    <td><?= htmlspecialchars($m['MasaAdi']) ?></td>
                    <td>
                        <?php if ($m['Durum'] === "Dolu"): ?>
                            <span class="badge bg-danger">Dolu</span>
                        <?php else: ?>
                            <span class="badge bg-success">Boş</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <!-- Düzenle Butonu -->
                        <button class="btn btn-sm btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#modal<?= $m['MasaID'] ?>">
                            Düzenle
                        </button>

                        <!-- Sil -->
                        <a href="admin_masalar.php?sil=<?= $m['MasaID'] ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Masa silinsin mi?')">
                           Sil
                        </a>
                    </td>
                </tr>

                <!-- Düzenleme Modal -->
                <div class="modal fade" id="modal<?= $m['MasaID'] ?>">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            
                            <form method="POST">
                                <input type="hidden" name="islem" value="duzenle">
                                <input type="hidden" name="masa_id" value="<?= $m['MasaID'] ?>">

                                <div class="modal-header">
                                    <h5>Masa Düzenle</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <label class="form-label">Masa Adı</label>
                                    <input type="text" name="masa_adi" value="<?= htmlspecialchars($m['MasaAdi']) ?>" class="form-control">
                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-primary">Kaydet</button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>

            <?php endforeach; endif; ?>
            </tbody>

        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>