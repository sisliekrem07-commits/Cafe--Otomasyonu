<?php
session_start();
include("../config/db.php");

// 🔐 Admin + Garson erişebilir
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['rol'], ['Admin', 'Garson'])) {
    header("Location: login.php");
    exit;
}

$mesaj = "";
$tip = "info";

/* =======================================================
   🟢 1) MASA EKLEME (SADECE ADMIN)
======================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['islem']) && $_POST['islem'] === 'ekle') {

    if ($_SESSION['user']['rol'] !== 'Admin') {
        die("Bu işlem sadece admin tarafından yapılabilir.");
    }

    $masaAdi = trim($_POST['masa_adi']);

    if ($masaAdi === "") {
        $mesaj = "Masa adı boş olamaz!";
        $tip = "danger";
    } else {
        $sql = "INSERT INTO Masalar (MasaAdi, Durum, Aktif) VALUES (?, 'Boş', 1)";
        $ok = sqlsrv_query($conn, $sql, [$masaAdi]);

        if ($ok) {
            $mesaj = "Masa başarıyla eklendi!";
            $tip = "success";
        } else {
            $mesaj = "Hata oluştu!";
            $tip = "danger";
        }
    }
}

/* =======================================================
   🔄 2) MASA DÜZENLE (SADECE ADMIN)
======================================================= */
if (isset($_POST['islem']) && $_POST['islem'] === 'duzenle') {

    if ($_SESSION['user']['rol'] !== 'Admin') {
        die("Bu işlem sadece admin tarafından yapılabilir.");
    }

    $id  = intval($_POST['masa_id']);
    $adi = trim($_POST['masa_adi']);

    if ($adi !== "") {
        sqlsrv_query($conn, "UPDATE Masalar SET MasaAdi = ? WHERE MasaID = ?", [$adi, $id]);
        $mesaj = "Masa adı güncellendi.";
        $tip = "success";
    }
}

/* =======================================================
   🟡 3) AKTİF / PASİF YAP (SADECE ADMIN)
======================================================= */
if (isset($_GET['toggle'])) {

    if ($_SESSION['user']['rol'] !== 'Admin') {
        die("Bu işlem sadece admin tarafından yapılabilir.");
    }

    $id = intval($_GET['toggle']);

    $sql = "SELECT Aktif FROM Masalar WHERE MasaID = ?";
    $st  = sqlsrv_query($conn, $sql, [$id]);
    $row = sqlsrv_fetch_array($st, SQLSRV_FETCH_ASSOC);

    $yeni = ($row['Aktif'] == 1) ? 0 : 1;

    sqlsrv_query($conn, "UPDATE Masalar SET Aktif = ? WHERE MasaID = ?", [$yeni, $id]);

    header("Location: admin_masalar.php");
    exit;
}

/* =======================================================
   ❌ 4) MASA SİL (SADECE ADMIN)
======================================================= */
if (isset($_GET['sil'])) {

    if ($_SESSION['user']['rol'] !== 'Admin') {
        die("Bu işlem sadece admin tarafından yapılabilir.");
    }

    $id = intval($_GET['sil']);
    sqlsrv_query($conn, "DELETE FROM Masalar WHERE MasaID = ?", [$id]);

    header("Location: admin_masalar.php");
    exit;
}

/* =======================================================
   📌 5) MASA LİSTELE (HERKES)
======================================================= */
$sql = "SELECT * FROM Masalar ORDER BY MasaID ASC";
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
body { background:#f3f4f6; padding:20px; }
.panel { background:white; padding:20px; border-radius:15px; box-shadow:0 10px 30px rgba(0,0,0,.1); }
</style>
</head>
<body>

<div class="container" style="max-width:900px;">

    <h3 class="mb-3">🪑 Masa Yönetimi</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm mb-3">← Panele Dön</a>

    <?php if ($mesaj): ?>
        <div class="alert alert-<?= $tip ?>"><?= $mesaj ?></div>
    <?php endif; ?>

    <!-- Admin değilse ekleme paneli gizlenir -->
    <?php if ($_SESSION['user']['rol'] === 'Admin'): ?>
    <div class="panel mb-4">
        <h5 class="mb-2">➕ Yeni Masa Ekle</h5>

        <form method="POST" class="row g-3">
            <input type="hidden" name="islem" value="ekle">

            <div class="col-md-8">
                <input type="text" name="masa_adi" class="form-control" placeholder="Masa Adı" required>
            </div>
            <div class="col-md-4">
                <button class="btn btn-success w-100">Kaydet</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="panel">
        <h5 class="mb-3">📋 Kayıtlı Masalar</h5>

        <table class="table table-hover align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Masa Adı</th>
                    <th>Durum</th>
                    <th>Aktif</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($masalar as $m): ?>
                <tr>
                    <td><?= $m['MasaID'] ?></td>
                    <td>
                        <?php if ($_SESSION['user']['rol'] === 'Admin'): ?>
                        <form method="POST" class="d-flex gap-2 justify-content-center">
                            <input type="hidden" name="islem" value="duzenle">
                            <input type="hidden" name="masa_id" value="<?= $m['MasaID'] ?>">
                            <input type="text" name="masa_adi" class="form-control form-control-sm text-center" 
                                   value="<?= htmlspecialchars($m['MasaAdi']) ?>" required>
                            <button class="btn btn-sm btn-primary">Kaydet</button>
                        </form>
                        <?php else: ?>
                            <?= htmlspecialchars($m['MasaAdi']) ?>
                        <?php endif; ?>
                    </td>

                    <td><?= htmlspecialchars($m['Durum']) ?></td>

                    <td>
                        <?php if ($m['Aktif']): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Pasif</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ($_SESSION['user']['rol'] === 'Admin'): ?>
                        <a href="admin_masalar.php?toggle=<?= $m['MasaID'] ?>" class="btn btn-sm <?= $m['Aktif'] ? 'btn-outline-danger':'btn-outline-success' ?>">
                           <?= $m['Aktif'] ? 'Pasif Yap' : 'Aktif Yap' ?>
                        </a>

                        <a href="admin_masalar.php?sil=<?= $m['MasaID'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('Bu masayı silmek istiyor musun?')">
                           Sil
                        </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>
</body>
</html>