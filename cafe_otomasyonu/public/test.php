<?php
require_once __DIR__ . "/../config/db.php";

echo "<h3>✅ PHP - MSSQL bağlantı testi</h3>";

$query = "SELECT COUNT(*) AS KategoriSayisi FROM Kategori";
$result = sqlsrv_query($conn, $query);

if ($result === false) {
    die("<b>❌ Sorgu hatası:</b><br>" . print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
echo "<p><b>Kategorilerde toplam: {$row['KategoriSayisi']} kayıt var.</b></p>";
?>