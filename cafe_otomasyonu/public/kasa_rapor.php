<?php
session_start();
include("../config/db.php");

// Yetki kontrolü (Admin veya Kasiyer)
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['rol'], ['Admin', 'Kasiyer'])) {
    header("Location: login.php");
    exit;
}

/* ----------------------------------------------------------
   1) BUGÜN TOPLAM CİRO
---------------------------------------------------------- */
$sqlBugun = "
    SELECT SUM(s.Adet * u.Fiyat) AS Ciro
    FROM Siparisler s
    JOIN Urun u ON u.UrunID = s.UrunID
    WHERE CAST(s.Tarih AS DATE) = CAST(GETDATE() AS DATE)
";
$stmt = sqlsrv_query($conn, $sqlBugun);
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$bugunCiro = $row['Ciro'] ?? 0;

/* ----------------------------------------------------------
   2) SON 7 GÜN CİRO
---------------------------------------------------------- */
$sql7Gun = "
    SELECT 
        FORMAT(s.Tarih, 'yyyy-MM-dd') AS Gun,
        SUM(s.Adet * u.Fiyat) AS Ciro
    FROM Siparisler s
    JOIN Urun u ON u.UrunID = s.UrunID
    WHERE s.Tarih >= DATEADD(day, -6, CAST(GETDATE() AS DATE))
    GROUP BY FORMAT(s.Tarih, 'yyyy-MM-dd')
    ORDER BY Gun DESC
";
$stmt7 = sqlsrv_query($conn, $sql7Gun);

$rapor7gun = [];
while ($r = sqlsrv_fetch_array($stmt7, SQLSRV_FETCH_ASSOC)) {
    $rapor7gun[] = $r;
}

/* ----------------------------------------------------------
   3) SON 30 GÜN CİRO
---------------------------------------------------------- */
$sql30Gun = "
    SELECT 
        FORMAT(s.Tarih, 'yyyy-MM-dd') AS Gun,
        SUM(s.Adet * u.Fiyat) AS Ciro
    FROM Siparisler s
    JOIN Urun u ON u.UrunID = s.UrunID
    WHERE s.Tarih >= DATEADD(day, -29, CAST(GETDATE() AS DATE))
    GROUP BY FORMAT(s.Tarih, 'yyyy-MM-dd')
    ORDER BY Gun DESC
";
$stmt30 = sqlsrv_query($conn, $sql30Gun);

$rapor30gun = [];
while ($r = sqlsrv_fetch_array($stmt30, SQLSRV_FETCH_ASSOC)) {
    $rapor30gun[] = $r;
}

/* ----------------------------------------------------------
   4) TÜM ZAMANLAR TOPLAM CİRO
---------------------------------------------------------- */
$sqlTumu = "
    SELECT SUM(s.Adet * u.Fiyat) AS Ciro
    FROM Siparisler s
    JOIN Urun u ON u.UrunID = s.UrunID
";
$stmt = sqlsrv_query($conn, $sqlTumu);
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$tumuCiro = $row['Ciro'] ?? 0;

/* ----------------------------------------------------------
   5) ÖDEME DETAY LİSTESİ
---------------------------------------------------------- */
$sqlDetay = "
    SELECT 
        s.SiparisID,
        m.MasaAdi,
        u.UrunAdi,
        s.Adet,
        (s.Adet * u.Fiyat) AS Toplam,
        s.Tarih
    FROM Siparisler s
    JOIN Masalar m ON m.MasaID = s.MasaID
    JOIN Urun u ON u.UrunID = s.UrunID
    ORDER BY s.SiparisID DESC
";
$stmtDetay = sqlsrv_query($conn, $sqlDetay);

$detaylar = [];
while ($r = sqlsrv_fetch_array($stmtDetay, SQLSRV_FETCH_ASSOC)) {
    $detaylar[] = $r;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kasa Raporu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#eef1f5; padding:25px; }
        .card { border-radius:12px; }
    </style>
</head>

<body>

<div class="container">

    <h3 class="mb-4">📊 Kasa Raporu</h3>

    <!-- BUGÜN CİRO -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card p-3 bg-success text-white">
                <h5>Bugün</h5>
                <h2><?= number_format($bugunCiro, 2) ?> ₺</h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 bg-primary text-white">
                <h5>Son 30 Gün</h5>
                <h2><?= number_format($tumuCiro, 2) ?> ₺</h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 bg-dark text-white">
                <h5>Tüm Zamanlar</h5>
                <h2><?= number_format($tumuCiro, 2) ?> ₺</h2>
            </div>
        </div>
    </div>

    <!-- SON 7 GÜN -->
    <div class="card p-3 mb-4">
        <h5>📅 Son 7 Gün Cirosu</h5>
        <table class="table table-bordered mt-2">
            <thead class="table-dark">
                <tr>
                    <th>Tarih</th>
                    <th>Ciro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rapor7gun as $r): ?>
                <tr>
                    <td><?= $r['Gun'] ?></td>
                    <td><?= number_format($r['Ciro'], 2) ?> ₺</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ÖDEME LİSTESİ -->
    <div class="card p-3">
        <h5>🧾 Ödeme Detayları</h5>

        <table class="table table-striped mt-2">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Masa</th>
                    <th>Ürün</th>
                    <th>Adet</th>
                    <th>Toplam</th>
                    <th>Tarih</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detaylar as $d): ?>
                <tr>
                    <td><?= $d['SiparisID'] ?></td>
                    <td><?= htmlspecialchars($d['MasaAdi']) ?></td>
                    <td><?= htmlspecialchars($d['UrunAdi']) ?></td>
                    <td><?= $d['Adet'] ?></td>
                    <td><?= number_format($d['Toplam'], 2) ?> ₺</td>
                    <td>
                        <?= (is_object($d['Tarih']) ? $d['Tarih']->format('Y-m-d H:i') : $d['Tarih']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-3">← Geri Dön</a>

</div>

</body>
</html>