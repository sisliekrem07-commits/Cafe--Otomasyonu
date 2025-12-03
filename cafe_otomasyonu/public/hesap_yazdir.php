<?php
session_start();
include("../config/db.php");

// Fiş ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Geçersiz fiş numarası!");
}

$odemeID = intval($_GET['id']);

// 1) Ödeme + Sipariş + Masa + Ürün bilgilerini çek
$sql = "
SELECT 
    o.OdemeID,
    o.Tutar,
    o.OdemeTipi,
    o.Tarih,
    s.SiparisID,
    s.Adet,
    m.MasaAdi,
    u.UrunAdi,
    u.Fiyat
FROM Odeme o
JOIN Siparisler s ON s.SiparisID = o.SiparisID
JOIN Masalar m ON m.MasaID = s.MasaID
JOIN Urun u ON u.UrunID = s.UrunID
WHERE o.OdemeID = ?
";

$stmt = sqlsrv_query($conn, $sql, [$odemeID]);

// SQL hatasını yakala
if ($stmt === false) {
    echo "<pre>SQL HATASI:\n";
    print_r(sqlsrv_errors());
    echo "</pre>";
    die;
}

$data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$data) {
    die("Fiş bulunamadı!");
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Fiş Yazdır</title>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
}
.fis {
    width: 300px;
    border: 1px dashed #000;
    padding: 15px;
}
h3 { text-align: center; }
</style>

</head>
<body onload="window.print();">

<div class="fis">
    <h3>CAFEMATİK<br>Ödeme Fişi</h3>
    <hr>

    <p><b>Fiş No:</b> <?= $data['OdemeID'] ?></p>
    <p><b>Masa:</b> <?= $data['MasaAdi'] ?></p>
    <p><b>Ürün:</b> <?= $data['UrunAdi'] ?></p>
    <p><b>Adet:</b> <?= $data['Adet'] ?></p>
    <p><b>Birim Fiyat:</b> <?= $data['Fiyat'] ?> ₺</p>

    <hr>
    <h4>Toplam: <?= $data['Tutar'] ?> ₺</h4>
    <p><b>Ödeme Şekli:</b> <?= $data['OdemeTipi'] ?></p>
    <p><b>Tarih:</b> 
        <?= $data['Tarih'] instanceof DateTime ? $data['Tarih']->format('Y-m-d H:i') : $data['Tarih'] ?>
    </p>

    <hr>
    <p style="text-align:center;">Teşekkür ederiz ❤️</p>
</div>

</body>
</html>