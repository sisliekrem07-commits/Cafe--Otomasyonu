<?php
session_start();
include("../config/db.php");

// 🔐 Sadece Garson
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'Garson') {
    header("Location: login.php");
    exit;
}

// Masa kontrolü
if (!isset($_GET['masa'])) {
    die("Masa seçilmedi!");
}

$masaID = intval($_GET['masa']);
$garsonID = $_SESSION['user']['id'];

/* --------------------------------------------------------
   📌 ÜRÜNLER + STOK BİLGİSİ
-------------------------------------------------------- */
$sql = "SELECT UrunID, UrunAdi, Fiyat, Stok FROM Urun WHERE Aktif = 1 ORDER BY UrunAdi ASC";
$stmt = sqlsrv_query($conn, $sql);

$urunler = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $urunler[] = $row;
}

/* --------------------------------------------------------
   📌 SİPARİŞ EKLEME
-------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $urunID = intval($_POST['urun']);
    $adet   = intval($_POST['adet']);

    if ($adet < 1) $adet = 1;

    // Stok kontrolü
    $stokSorgu = sqlsrv_query($conn, "SELECT Stok FROM Urun WHERE UrunID = ?", [$urunID]);
    $stokVeri = sqlsrv_fetch_array($stokSorgu, SQLSRV_FETCH_ASSOC);
    $stok = $stokVeri['Stok'];

    if ($stok < $adet) {
        $hata = "⚠ Bu ürün için yeterli stok yok!";
    } else {

        $sqlInsert = "
            INSERT INTO Siparisler (MasaID, UrunID, Adet, Durum, GarsonID, Tarih)
            VALUES (?, ?, ?, 'Bekliyor', ?, GETDATE())
        ";

        $ok = sqlsrv_query($conn, $sqlInsert, [$masaID, $urunID, $adet, $garsonID]);

        if ($ok) {
            // Masayı dolu yap
            sqlsrv_query($conn, "UPDATE Masalar SET Durum='Dolu' WHERE MasaID=?", [$masaID]);

            header("Location: garson_siparisleri.php");
            exit;
        } else {
            $hata = "Sipariş eklenirken hata oluştu!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Sipariş Oluştur</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#f7f7f7; padding:30px; }
.disabled-option { color:#999; }
</style>
</head>

<body>

<div class="container" style="max-width:600px;">
    <h3 class="mb-3">🧾 Yeni Sipariş</h3>
    <p class="text-muted">Masa: <b><?= $masaID ?></b></p>

    <?php if (!empty($hata)): ?>
        <div class="alert alert-danger"><?= $hata ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4">

        <label class="form-label">Ürün Seç</label>
        <select name="urun" class="form-select mb-3" required>

            <?php foreach ($urunler as $u): ?>

                <?php
                    $stok = intval($u['Stok']);
                    $stokYazi = "";

                    if ($stok <= 0) {
                        $stokYazi = " — Tükendi ❌";
                    } elseif ($stok <= 10) {
                        $stokYazi = " — Az: $stok ⚠";
                    } else {
                        $stokYazi = " — Stok: $stok";
                    }
                ?>

                <option value="<?= $u['UrunID'] ?>" 
                        <?= $stok <= 0 ? "disabled class='disabled-option'" : "" ?>>

                    <?= htmlspecialchars($u['UrunAdi']) ?>
                    — <?= number_format($u['Fiyat'], 2) ?> ₺
                    <?= $stokYazi ?>

                </option>

            <?php endforeach; ?>

        </select>

        <label class="form-label">Adet</label>
        <input type="number" name="adet" class="form-control mb-3" value="1" min="1" required>

        <button class="btn btn-primary w-100">Sipariş Oluştur</button>
    </form>

    <a href="garson_masalar.php" class="btn btn-secondary mt-3">← Geri Dön</a>
</div>

</body>
</html>