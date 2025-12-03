<?php
session_start();
include("../config/db.php");

// Kasiyer kontrolü
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['rol'], ['Kasiyer','Admin'])) {
    header("Location: login.php");
    exit;
}

// ÖDEME ALMA İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['siparis_id'])) {

    $siparisID   = intval($_POST['siparis_id']);
    $odemTipi    = $_POST['odeme_tipi'];

    // 1) Sipariş tutarını çekelim
    $sql = "
        SELECT s.SiparisID, s.MasaID, s.Adet, u.Fiyat, (s.Adet * u.Fiyat) AS Tutar
        FROM Siparisler s
        JOIN Urun u ON u.UrunID = s.UrunID
        WHERE s.SiparisID = ?
    ";
    $sp = sqlsrv_query($conn, $sql, [$siparisID]);
    $s = sqlsrv_fetch_array($sp, SQLSRV_FETCH_ASSOC);

    if ($s) {
        $tutar  = $s['Tutar'];
        $masaID = $s['MasaID'];

        // 2) Ödeme kaydını ekle
        $sqlOdeme = "
            INSERT INTO Odeme (SiparisID, Tutar, OdemeTipi, Tarih)
            VALUES (?, ?, ?, GETDATE())
        ";
        $stmtOdeme = sqlsrv_query($conn, $sqlOdeme, [$siparisID, $tutar, $odemTipi]);

        if ($stmtOdeme) {

            // 2.1) Son eklenen ödeme ID'sini al
            $sqlLast = "SELECT TOP 1 OdemeID FROM Odeme ORDER BY OdemeID DESC";
            $qLast   = sqlsrv_query($conn, $sqlLast);
            $last    = sqlsrv_fetch_array($qLast, SQLSRV_FETCH_ASSOC);
            $odemeID = $last['OdemeID'] ?? null;

            // 3) Siparişi ödendi yap
            sqlsrv_query($conn, "UPDATE Siparisler SET Durum='Ödendi' WHERE SiparisID=?", [$siparisID]);

            // 4) Masayı BOŞ yap
            sqlsrv_query($conn, "UPDATE Masalar SET Durum='Boş' WHERE MasaID=?", [$masaID]);

            // 5) Fiş yazdırma sayfasına yönlendir
            if ($odemeID) {
                header("Location: hesap_yazdir.php?id=" . $odemeID);
                exit;
            } else {
                // Her ihtimale karşı, ID alınamazsa ekranda mesaj göster
                $mesaj = "Ödeme alındı fakat fiş ID'si alınamadı.";
                $tip   = "warning";
            }

        } else {
            $mesaj = "Ödeme kaydedilirken hata oluştu!";
            $tip   = "danger";
        }
    }
}

// AÇIK siparişleri çek (henüz ödenmemiş)
$sql = "
    SELECT s.SiparisID, m.MasaAdi, u.UrunAdi, s.Adet, u.Fiyat, (s.Adet*u.Fiyat) AS Tutar, s.Durum
    FROM Siparisler s
    JOIN Masalar m ON m.MasaID = s.MasaID
    JOIN Urun u ON u.UrunID = s.UrunID
    WHERE s.Durum != 'Ödendi'
    ORDER BY s.SiparisID DESC
";

$stmt = sqlsrv_query($conn, $sql);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Kasa – Ödeme Al</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f3f4f6; padding:25px; }
.card { padding:20px; }
</style>
</head>
<body>

<h3>💰 Ödeme Alma</h3>
<hr>

<?php if (isset($mesaj)): ?>
    <div class="alert alert-<?= $tip ?>"><?= $mesaj ?></div>
<?php endif; ?>

<table class="table table-bordered table-hover">
<thead class="table-dark">
    <tr>
        <th>ID</th>
        <th>Masa</th>
        <th>Ürün</th>
        <th>Adet</th>
        <th>Tutar (₺)</th>
        <th>Ödeme</th>
    </tr>
</thead>
<tbody>

<?php while ($s = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
<tr>
    <td><?= $s['SiparisID'] ?></td>
    <td><?= $s['MasaAdi'] ?></td>
    <td><?= $s['UrunAdi'] ?></td>
    <td><?= $s['Adet'] ?></td>
    <td><b><?= $s['Tutar'] ?> ₺</b></td>

    <td>
        <form method="POST" class="d-flex gap-2">
            <input type="hidden" name="siparis_id" value="<?= $s['SiparisID'] ?>">
            <select name="odeme_tipi" class="form-select form-select-sm" required>
                <option value="Nakit">Nakit</option>
                <option value="Kart">Kart</option>
            </select>
            <button class="btn btn-success btn-sm">Ödeme Al</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>

</tbody>
</table>

<a href="dashboard.php" class="btn btn-secondary">← Geri Dön</a>

</body>
</html>