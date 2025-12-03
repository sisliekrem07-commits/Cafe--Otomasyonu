<?php
session_start();
include("../config/db.php");

// Garson kontrolü
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] !== 'Garson') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$garsonID = $user['id'];

// Masaları çek
$sql = "SELECT * FROM Masalar ORDER BY MasaID ASC";
$stmt = sqlsrv_query($conn, $sql);

$masalar = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $masalar[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Garson Masalar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background:#f5f6fa; padding:30px; }
.card-masa {
    padding:18px;
    border-radius:12px;
    text-align:center;
    cursor:pointer;
    font-size:18px;
    transition:0.2s;
}
.card-masa:hover {
    transform: scale(1.05);
}
.masa-bos { background:#dcfce7; border:2px solid #16a34a; }
.masa-dolu { background:#fee2e2; border:2px solid #dc2626; }
.masa-pasif { background:#e5e7eb; border:2px dashed #6b7280; cursor:not-allowed; }
</style>
</head>

<body>

<div class="container">
    <h3 class="mb-4">🍽 Masalar</h3>

    <div class="row g-3">

        <?php foreach ($masalar as $m): ?>

            <?php
            $aktif  = $m['Aktif'];
            $durum  = $m['Durum'];
            $class  = '';
            $link   = '';
            
            if (!$aktif) {
                $class = "masa-pasif";
            } else {
                if ($durum === "Boş") {
                    $class = "masa-bos";
                    $link  = "siparis_olustur.php?masa=" . $m['MasaID'];
                } else {
                    $class = "masa-dolu";
                    $link  = "siparis_olustur.php?masa=" . $m['MasaID'];
                }
            }
            ?>

            <div class="col-md-3">
                <?php if ($aktif): ?>
                    <a href="<?= $link ?>" style="text-decoration:none; color:black;">
                        <div class="card-masa <?= $class ?>">
                            <b><?= htmlspecialchars($m['MasaAdi']) ?></b><br>
                            <span class="text-muted"><?= $durum ?></span>
                        </div>
                    </a>
                <?php else: ?>
                    <div class="card-masa <?= $class ?>">
                        <b><?= htmlspecialchars($m['MasaAdi']) ?></b><br>
                        <span class="text-muted">Pasif</span>
                    </div>
                <?php endif; ?>
            </div>

        <?php endforeach; ?>

    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-4">← Geri Dön</a>
</div>

</body>
</html>