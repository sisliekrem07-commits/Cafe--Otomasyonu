<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include("../config/db.php");

$tur = $_GET['tur'] ?? 'gunluk';

$labels = [];
$data = [];
$baslik = "";

/* -------------------------
   GÜNLÜK
-------------------------- */
if ($tur == 'gunluk') {
    $sql = "
        SELECT FORMAT(s.Tarih, 'yyyy-MM-dd') AS Gun,
               SUM(u.Fiyat * s.Adet) AS Ciro
        FROM Siparisler s
        JOIN Urun u ON u.UrunID = s.UrunID
        WHERE s.Tarih >= DATEADD(day, -6, CAST(GETDATE() AS date))
        GROUP BY FORMAT(s.Tarih, 'yyyy-MM-dd')
        ORDER BY Gun ASC
    ";

    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $labels[] = $row['Gun'];
        $data[] = $row['Ciro'];
    }

    $baslik = "📅 Günlük Ciro (Son 7 Gün)";
}

/* -------------------------
   HAFTALIK
-------------------------- */
if ($tur == 'haftalik') {
    $sql = "
        SELECT DATEPART(week, s.Tarih) AS Hafta,
               SUM(u.Fiyat * s.Adet) AS Ciro
        FROM Siparisler s
        JOIN Urun u ON u.UrunID = s.UrunID
        WHERE s.Tarih >= DATEADD(week, -4, GETDATE())
        GROUP BY DATEPART(week, s.Tarih)
        ORDER BY Hafta ASC
    ";

    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $labels[] = "Hafta " . $row['Hafta'];
        $data[] = $row['Ciro'];
    }

    $baslik = "📆 Haftalık Ciro (Son 4 Hafta)";
}

/* -------------------------
   AYLIK
-------------------------- */
if ($tur == 'aylik') {
    $sql = "
        SELECT FORMAT(s.Tarih, 'MM') AS Ay,
               SUM(u.Fiyat * s.Adet) AS Ciro
        FROM Siparisler s
        JOIN Urun u ON u.UrunID = s.UrunID
        GROUP BY FORMAT(s.Tarih, 'MM')
        ORDER BY Ay ASC
    ";

    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $labels[] = $row['Ay'];
        $data[] = $row['Ciro'];
    }

    $baslik = "📈 Aylık Ciro (12 Ay)";
}

/* -------------------------
   YILLIK
-------------------------- */
if ($tur == 'yillik') {
    $sql = "
        SELECT DATEPART(year, s.Tarih) AS Yil,
               SUM(u.Fiyat * s.Adet) AS Ciro
        FROM Siparisler s
        JOIN Urun u ON u.UrunID = s.UrunID
        WHERE s.Tarih >= DATEADD(year, -5, GETDATE())
        GROUP BY DATEPART(year, s.Tarih)
        ORDER BY Yil ASC
    ";

    $stmt = sqlsrv_query($conn, $sql);
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $labels[] = $row['Yil'];
        $data[] = $row['Ciro'];
    }

    $baslik = "📅 Yıllık Ciro (Son 5 Yıl)";
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Satış Grafikleri</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body { background:#f0f2f5; padding:25px; }
    .card { padding:20px; border-radius:12px; margin-bottom:25px; }
    .menü a { margin:5px; }
</style>
</head>

<body>

<div class="container">

    <h3 class="mb-4 text-center">📊 Satış Raporları</h3>

    <!-- MENÜ -->
    <div class="text-center menü mb-4">
        <a href="grafik.php?tur=gunluk" class="btn btn-primary">Günlük</a>
        <a href="grafik.php?tur=haftalik" class="btn btn-success">Haftalık</a>
        <a href="grafik.php?tur=aylik" class="btn btn-warning">Aylık</a>
        <a href="grafik.php?tur=yillik" class="btn btn-dark">Yıllık</a>
    </div>

    <!-- GRAFİK -->
    <div class="card shadow">
        <h5><?= $baslik ?></h5>
        <canvas id="grafik1"></canvas>
    </div>

    <a href="dashboard.php" class="btn btn-secondary">← Geri Dön</a>

</div>

<script>
new Chart(document.getElementById('grafik1'), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Ciro (₺)',
            data: <?= json_encode($data) ?>,
            borderColor: 'rgb(54,162,235)',
            backgroundColor: 'rgba(54,162,235,0.4)',
            borderWidth: 3,
            tension: 0.3,
            fill: true
        }]
    }
});
</script>

</body>
</html>