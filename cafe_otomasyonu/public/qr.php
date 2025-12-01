<?php
require_once "qr/qrlib.php";

// QR kodun yönlendireceği URL (menu.php)
$url = "http://localhost/cafe_otomasyonu/public/menu.php";

// QR kodun kaydedileceği dosya
$qrFile = "qr/qr_menu.png";

// QR kod oluştur
QR::png($url, $qrFile);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>QR Menü</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body{
    font-family: Arial, sans-serif;
    background:#f4f4f4;
    text-align:center;
    padding-top:40px;
}
.card{
    background:white;
    padding:30px;
    margin:auto;
    width:340px;
    border-radius:16px;
    box-shadow:0 0 15px rgba(0,0,0,.1);
}
.qr-img{
    width:260px;
    height:260px;
}
</style>
</head>
<body>

<div class="card">
    <h2>Cafe QR Menü</h2>
    <p>Telefon kameranızla tarayın</p>

    <img src="qr/qr_menu.png" class="qr-img" alt="QR Kod">

    <p style="margin-top:20px;">
        <a href="menu.php" style="text-decoration:none; font-weight:bold; color:#444;">
            Menüye Git →
        </a>
    </p>
</div>

</body>
</html>