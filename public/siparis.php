<?php
session_start();
include("../config/db.php");

// 1) OTURUM KONTROLÜ
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$garsonID = intval($user['id']); // GarsonID

// 2) MASA ID KONTROLÜ
$masa_id = isset($_GET['masa_id']) ? intval($_GET['masa_id']) : 0;
if ($masa_id <= 0) {
    die("❌ Geçersiz masa ID!");
}

// Bildirim mesajı
$mesaj = $_GET['msg'] ?? "";

// 3) ÜRÜNLERİ ÇEK (aktif ürünler)
$sqlUrun = "SELECT UrunID, UrunAdi, Fiyat, Stok FROM Urun WHERE Aktif = 1 ORDER BY UrunAdi ASC";
$stmtUrun = sqlsrv_query($conn, $sqlUrun);
$urunler = [];

while ($row = sqlsrv_fetch_array($stmtUrun, SQLSRV_FETCH_ASSOC)) {
    $urunler[] = $row;
}

// 4) MASA BOŞALTMA
if (isset($_GET['bosalt']) && $_GET['bosalt'] == 1) {

    sqlsrv_begin_transaction($conn);

    $okDel = sqlsrv_query($conn, "DELETE FROM Siparisler WHERE MasaID = ?", [$masa_id]);
    $okUpd = sqlsrv_query($conn, "UPDATE Masalar SET Durum = N'Boş' WHERE MasaID = ?", [$masa_id]);

    if ($okDel && $okUpd) {
        sqlsrv_commit($conn);
        header("Location: siparis.php?masa_id=$masa_id&msg=✅ Masa boşaltıldı");
    } else {
        sqlsrv_rollback($conn);
        header("Location: siparis.php?masa_id=$masa_id&msg=❌ Hata oluştu!");
    }
    exit;
}

// 5) SİPARİŞ EKLEME (MUTFAK UYUMLU HALE GETİRİLDİ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['urun_id'])) {

    $urun_id = intval($_POST['urun_id']);
    $adet    = max(1, intval($_POST['adet']));

    // Ürün detaylarını çek
    $stokSorgu = sqlsrv_query($conn, "SELECT Stok, Fiyat FROM Urun WHERE UrunID = ? AND Aktif = 1", [$urun_id]);
    $urun = sqlsrv_fetch_array($stokSorgu, SQLSRV_FETCH_ASSOC);

    if (!$urun) {
        header("Location: siparis.php?masa_id=$masa_id&msg=❌ Ürün bulunamadı!");
        exit;
    }

    if ($adet > $urun['Stok']) {
        header("Location: siparis.php?masa_id=$masa_id&msg=⚠ Yetersiz stok!");
        exit;
    }

    sqlsrv_begin_transaction($conn);

    // 🔥 DURUM EKLENDİ — Artık sipariş mutfakta görünecek
    $durum = "Bekliyor";

    // Sipariş ekle
    $ok1 = sqlsrv_query(
        $conn,
        "INSERT INTO Siparisler (MasaID, UrunID, Adet, Durum, GarsonID) VALUES (?, ?, ?, ?, ?)",
        [$masa_id, $urun_id, $adet, $durum, $garsonID]
    );

    // Stok düş
    $ok2 = sqlsrv_query(
        $conn,
        "UPDATE Urun SET Stok = Stok - ? WHERE UrunID = ? AND Stok >= ?",
        [$adet, $urun_id, $adet]
    );

    // Biten stokları pasif yap
    $ok3 = sqlsrv_query(
        $conn,
        "UPDATE Urun SET Aktif = 0 WHERE UrunID = ? AND Stok <= 0",
        [$urun_id]
    );

    if ($ok1 && $ok2 && $ok3) {
        sqlsrv_commit($conn);
        sqlsrv_query($conn, "UPDATE Masalar SET Durum = N'Dolu' WHERE MasaID = ?", [$masa_id]);
        header("Location: siparis.php?masa_id=$masa_id&msg=✅ Sipariş eklendi");
    } else {
        sqlsrv_rollback($conn);
        header("Location: siparis.php?masa_id=$masa_id&msg=❌ Sipariş eklenemedi!");
    }

    exit;
}

// 6) MASANIN SİPARİŞLERİNİ ÇEK
$sqlSiparis = "
    SELECT 
        s.SiparisID,
        s.UrunID,
        s.Adet,
        s.Durum,
        s.GarsonID,
        u.UrunAdi,
        u.Fiyat,
        (s.Adet * u.Fiyat) AS Toplam
    FROM Siparisler s
    INNER JOIN Urun u ON s.UrunID = u.UrunID
    WHERE s.MasaID = ?
    ORDER BY s.SiparisID ASC
";

$stmtSip = sqlsrv_query($conn, $sqlSiparis, [$masa_id]);
$siparisler = [];
$genelToplam = 0;

while ($row = sqlsrv_fetch_array($stmtSip, SQLSRV_FETCH_ASSOC)) {
    $siparisler[] = $row;
    $genelToplam += floatval($row['Toplam']);
}

// 7) MASA DURUMUNU GÜNCELLE
$countQuery = "SELECT COUNT(*) AS Say FROM Siparisler WHERE MasaID = ?";
$countStmt = sqlsrv_query($conn, $countQuery, [$masa_id]);
$row = sqlsrv_fetch_array($countStmt, SQLSRV_FETCH_ASSOC);

sqlsrv_query(
    $conn,
    "UPDATE Masalar SET Durum = ? WHERE MasaID = ?",
    [($row['Say'] > 0 ? "Dolu" : "Boş"), $masa_id]
);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Masa <?= $masa_id ?> | Sipariş</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f5f6fa; padding:40px; }
        .container { max-width:900px; }
    </style>
</head>
<body>

<div class="container">

    <h3>☕ Masa <?= htmlspecialchars($masa_id) ?> Siparişleri</h3>
    <hr>

    <?php if (!empty($mesaj)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($mesaj) ?></div>
    <?php endif; ?>

    <!-- Ürün ekleme formu -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-6">
            <select name="urun_id" class="form-select" required>
                <option value="">Ürün Seç</option>
                <?php foreach ($urunler as $u): ?>
                    <option value="<?= $u['UrunID'] ?>">
                        <?= htmlspecialchars($u['UrunAdi']) ?> 
                        (<?= number_format($u['Fiyat'], 2) ?> ₺ • Stok: <?= $u['Stok'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <input type="number" name="adet" class="form-control" min="1" value="1" required>
        </div>

        <div class="col-md-3">
            <button type="submit" class="btn btn-success w-100">Ekle</button>
        </div>
    </form>

    <!-- Sipariş tablosu -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Ürün</th>
                <th>Adet</th>
                <th>Birim Fiyat</th>
                <th>Toplam</th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($siparisler)): ?>
                <tr><td colspan="4" class="text-center">Henüz sipariş yok.</td></tr>
            <?php else: ?>
                <?php foreach ($siparisler as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['UrunAdi']) ?></td>
                        <td><?= $s['Adet'] ?></td>
                        <td><?= number_format($s['Fiyat'], 2) ?> ₺</td>
                        <td><?= number_format($s['Toplam'], 2) ?> ₺</td>
                    </tr>
                <?php endforeach; ?>

                <tr class="fw-bold table-info">
                    <td colspan="3" class="text-end">Genel Toplam:</td>
                    <td><?= number_format($genelToplam, 2) ?> ₺</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="text-center mt-4">
        <a href="masalar.php" class="btn btn-secondary">← Geri Dön</a>
        <a href="hesap_yazdir.php?masa_id=<?= $masa_id ?>" class="btn btn-warning">🧾 Hesap Yazdır</a>
        <a href="siparis.php?masa_id=<?= $masa_id ?>&bosalt=1" class="btn btn-danger" onclick="return confirm('Masayı boşalt?')">🗑 Masayı Boşalt</a>
    </div>

</div>

</body>
</html>