<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$garsonID = isset($user['id']) ? intval($user['id']) : 0;

$sql = "
    SELECT 
        s.SiparisID,
        s.MasaID,
        u.UrunAdi,
        s.Adet,
        s.Durum,
        s.Tarih
    FROM Siparisler s
    LEFT JOIN Urun u ON u.UrunID = s.UrunID
    WHERE s.GarsonID = ?
    ORDER BY s.SiparisID DESC
";

$stmt = sqlsrv_query($conn, $sql, [$garsonID]);
$siparisler = [];

if ($stmt !== false) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $siparisler[] = $row;
    }
} else {
    // Hata durumu için debug (opsiyonel)
    $errors = sqlsrv_errors();
    error_log('garson_siparisleri sql error: ' . json_encode($errors));
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Garson Siparişleri</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f5f6fa; padding:20px; }
.card { padding:20px; }
</style>
</head>
<body>

<div class="container">
    <h3>🧾 Siparişlerim</h3>
    <hr>

    <?php if (empty($siparisler)): ?>
        <div class="alert alert-warning text-center">Henüz siparişiniz yok.</div>
    <?php else: ?>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Masa</th>
                <th>Ürün</th>
                <th>Adet</th>
                <th>Durum</th>
                <th>Tarih</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($siparisler as $s): ?>
            <tr>
                <td><?= (int)$s['SiparisID'] ?></td>
                <td><?= htmlspecialchars($s['MasaID']) ?></td>
                <td><?= htmlspecialchars($s['UrunAdi'] ?? '') ?></td>
                <td><?= (int)$s['Adet'] ?></td>
                <td><?= htmlspecialchars($s['Durum']) ?></td>
                <td>
                    <?php
                    // Tarih SQLSRV DATETIME olabilir; güvenli formatla yaz
                    if (isset($s['Tarih']) && is_object($s['Tarih'])) {
                        echo $s['Tarih']->format('Y-m-d H:i');
                    } elseif (!empty($s['Tarih'])) {
                        echo htmlspecialchars($s['Tarih']);
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary">← Geri Dön</a>
</div>

</body>
</html>
