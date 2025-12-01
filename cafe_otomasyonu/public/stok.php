<?php
session_start();
include("../config/db.php");

// 🧩 Oturum kontrolü
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// 🧮 Stok güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['urun_id']) && isset($_POST['stok'])) {
    $urun_id = intval($_POST['urun_id']);
    $stok = intval($_POST['stok']);

    // Stok değerini güncelle
    sqlsrv_query($conn, "UPDATE Urun SET Stok = ? WHERE UrunID = ?", [$stok, $urun_id]);

    // Stok kontrolü -> 0 ise pasif, büyükse aktif yap
    if ($stok > 0) {
        sqlsrv_query($conn, "UPDATE Urun SET Aktif = 1 WHERE UrunID = ?", [$urun_id]);
    } else {
        sqlsrv_query($conn, "UPDATE Urun SET Aktif = 0 WHERE UrunID = ?", [$urun_id]);
    }

    // Sayfayı yenile
    header("Location: stok.php");
    exit;
}

// 🧾 Ürünleri çek (fiyat kaldırıldı!)
$sql = "SELECT UrunID, UrunAdi, Stok, Aktif FROM Urun ORDER BY UrunAdi ASC";
$stmt = sqlsrv_query($conn, $sql);
$urunler = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $urunler[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Stok Yönetimi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f5f6fa; padding:40px; }
.container { max-width:900px; }
.badge-warning { background-color: #f39c12 !important; }
</style>
</head>
<body>
<div class="container">
    <h3>📦 Stok Yönetimi</h3>
    <hr>

    <table class="table table-bordered table-hover align-middle text-center">
        <thead class="table-dark">
            <tr>
                <th>Ürün</th>
                <th>Stok</th>
                <th>Kaydet</th>
                <th>Durum</th>
                <th>Uyarı</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($urunler as $u): ?>
            <tr class="<?= ($u['Stok'] <= 10) ? 'table-warning' : '' ?>">
                <td><?= htmlspecialchars($u['UrunAdi']) ?></td>

                <td>
                    <form method="POST" style="display:inline-flex; justify-content:center;">
                        <input type="hidden" name="urun_id" value="<?= $u['UrunID'] ?>">
                        <input type="number" 
                               name="stok" 
                               value="<?= $u['Stok'] ?>" 
                               min="0" 
                               class="form-control form-control-sm text-center" 
                               style="width:80px; margin-right:10px;">
                </td>

                <td>
                        <button type="submit" class="btn btn-sm btn-primary">Kaydet</button>
                    </form>
                </td>

                <td>
                    <?php if ($u['Aktif']): ?>
                        <span class="badge bg-success">Aktif</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Pasif</span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if ($u['Stok'] <= 0): ?>
                        <span class="text-danger fw-bold">Stok Bitti!</span>
                    <?php elseif ($u['Stok'] <= 10): ?>
                        <span class="text-warning fw-bold">⚠ Kritik Seviye</span>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="text-center mt-4">
        <a href="dashboard.php" class="btn btn-secondary">← Geri Dön</a>
    </div>
</div>
</body>
</html>