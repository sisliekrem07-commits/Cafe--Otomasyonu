<?php
include("../config/db.php");
session_start();

// 🔐 Yetki kontrolü
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['rol'], ['Admin', 'Mutfak'])) {
    header("Location: login.php");
    exit;
}

/* ---------------------------------------------------
   1) DURUM GÜNCELLEME + STOK DÜŞÜRME
--------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['siparis_id']) && !empty($_POST['durum'])) {

        $siparisID = (int)$_POST['siparis_id'];
        $durum     = trim($_POST['durum']);

        // 🔥 1) Yeni durum kaydedilir
        $sqlUpdate = "UPDATE Siparisler SET Durum = ? WHERE SiparisID = ?";
        sqlsrv_query($conn, $sqlUpdate, [$durum, $siparisID]);


        /* ----------------------------------------------------
           🔥 2) Eğer DURUM = 'Servise Hazır' ise STOK DÜŞÜR
              ve daha önce düşürülmemişse (StokDusuruldu=0)
        ---------------------------------------------------- */
        if ($durum === "Servise Hazır") {

            // Sipariş bilgilerini al
            $sqlGet = "SELECT UrunID, Adet, StokDusuruldu FROM Siparisler WHERE SiparisID = ?";
            $stmtGet = sqlsrv_query($conn, $sqlGet, [$siparisID]);
            $siparis = sqlsrv_fetch_array($stmtGet, SQLSRV_FETCH_ASSOC);

            if ($siparis && $siparis['StokDusuruldu'] == 0) {

                $urunID = $siparis['UrunID'];
                $adet   = (int)$siparis['Adet'];

                // 🔥 Ürün stok düş
                $sqlStok = "UPDATE Urun SET Stok = Stok - ? WHERE UrunID = ?";
                sqlsrv_query($conn, $sqlStok, [$adet, $urunID]);

                // 🔥 Tekrar düşmesin diye işaret bırak
                sqlsrv_query($conn,
                    "UPDATE Siparisler SET StokDusuruldu = 1 WHERE SiparisID = ?",
                    [$siparisID]
                );
            }
        }
    }

    header("Location: mutfak.php");
    exit;
}

/* ---------------------------------------------------
   2) SİPARİŞLERİ ÇEK — STOK DA EKLENDİ
--------------------------------------------------- */
$sql = "
    SELECT 
        s.SiparisID,
        m.MasaAdi,
        u.UrunAdi,
        u.Stok AS UrunStok,
        s.Adet,
        FORMAT(s.Tarih, 'HH:mm') AS Saat,
        s.Durum,
        k.AdSoyad AS Garson
    FROM Siparisler s
    JOIN Masalar m ON m.MasaID = s.MasaID
    JOIN Urun u    ON u.UrunID = s.UrunID
    LEFT JOIN Kullanici k ON k.KullaniciID = s.GarsonID
    ORDER BY s.SiparisID DESC
";

$stmt = sqlsrv_query($conn, $sql);
$siparisler = [];

if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

        $durum = $row['Durum'] ?? "Bekliyor";

        if (in_array($durum, ["Bekliyor", "Hazırlanıyor", "Servise Hazır"])) {
            $row['Durum'] = $durum;
            $siparisler[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Mutfak Paneli</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script> setInterval(() => { location.reload(); }, 5000); </script>

<style>
    body { background:#f5f6fa; padding:20px; }
    .table td, .table th { vertical-align: middle; }
</style>

</head>
<body>

<h3>🍴 Mutfak Sipariş Paneli</h3>
<hr>

<div class="card p-3">

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Masa</th>
            <th>Ürün</th>
            <th>Adet</th>
            <th>Stok</th>
            <th>Saat</th>
            <th>Garson</th>
            <th>Durum</th>
            <th>Güncelle</th>
        </tr>
    </thead>

    <tbody>
        <?php if (empty($siparisler)): ?>
            <tr><td colspan="9" class="text-center text-muted">Aktif sipariş yok.</td></tr>
        <?php else: ?>
            <?php foreach ($siparisler as $s): ?>
            <tr>
                <td><b><?= $s['SiparisID'] ?></b></td>
                <td><?= $s['MasaAdi'] ?></td>
                <td><?= $s['UrunAdi'] ?></td>
                <td><?= $s['Adet'] ?></td>

                <!-- Stok renkli gösterim -->
                <td>
                    <?php if ($s['UrunStok'] <= 0): ?>
                        <span class="badge bg-danger">Stok Bitti</span>
                    <?php elseif ($s['UrunStok'] <= 10): ?>
                        <span class="badge bg-warning text-dark">Az (<?= $s['UrunStok'] ?>)</span>
                    <?php else: ?>
                        <span class="badge bg-success"><?= $s['UrunStok'] ?></span>
                    <?php endif; ?>
                </td>

                <td><?= $s['Saat'] ?></td>
                <td><?= $s['Garson'] ?: "—" ?></td>

                <td>
                    <?php if ($s['Durum']=="Hazırlanıyor"): ?>
                        <span class="badge bg-warning text-dark">Hazırlanıyor</span>
                    <?php elseif ($s['Durum']=="Servise Hazır"): ?>
                        <span class="badge bg-success">Servise Hazır</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Bekliyor</span>
                    <?php endif; ?>
                </td>

                <td>
                    <form method="POST" style="display:flex;gap:5px;">
                        <input type="hidden" name="siparis_id" value="<?= $s['SiparisID'] ?>">
                        <select name="durum" class="form-select form-select-sm">
                            <option value="Bekliyor"      <?= $s['Durum']=='Bekliyor'?'selected':'' ?>>Bekliyor</option>
                            <option value="Hazırlanıyor"  <?= $s['Durum']=='Hazırlanıyor'?'selected':'' ?>>Hazırlanıyor</option>
                            <option value="Servise Hazır" <?= $s['Durum']=='Servise Hazır'?'selected':'' ?>>Servise Hazır</option>
                        </select>
                        <button class="btn btn-primary btn-sm">Kaydet</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>

</table>
</div>

<a href="dashboard.php" class="btn btn-secondary mt-3">← Geri Dön</a>

</body>
</html>