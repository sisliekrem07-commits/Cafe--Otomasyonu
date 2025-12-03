<?php
session_start();
include("../config/db.php");

// 🔐 Oturum kontrolü
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

/********************************************
🟢 RESİM YÜKLEME FONKSİYONU
*********************************************/
function resimYukle($inputName, $eskiYol = null) {

    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== 0) {
        return $eskiYol; // resim yüklenmemişse eski resim kalsın
    }

    $klasor = "../uploads/urunler/";
    if (!is_dir($klasor)) mkdir($klasor, 0777, true);

    $dosyaAdi = time() . "_" . basename($_FILES[$inputName]['name']);
    $hedef = $klasor . $dosyaAdi;

    if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $hedef)) {

        // Eski resim varsa ve dosya mevcutsa sil
        if ($eskiYol && file_exists("../" . $eskiYol)) {
            unlink("../" . $eskiYol);
        }

        return "uploads/urunler/" . $dosyaAdi;
    }

    return $eskiYol;
}

/********************************************
🟡 ÜRÜN GÜNCELLEME
*********************************************/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['islem']) && $_POST['islem'] == "duzenle") {

    $id     = intval($_POST['urun_id']);
    $adi    = trim($_POST['urun_adi']);
    $fiyat  = floatval($_POST['fiyat']);
    $stok   = intval($_POST['stok']);
    $katID  = intval($_POST['kategori']);

    // Eski resmi çek
    $res = sqlsrv_query($conn, "SELECT Resim FROM Urun WHERE UrunID=?", [$id]);
    $old = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
    $eskiResim = $old['Resim'] ?? null;

    // Yeni resim yükle
    $yeniResim = resimYukle("resim_duzenle", $eskiResim);

    $sqlGuncelle = "UPDATE Urun SET UrunAdi=?, Fiyat=?, Stok=?, KategoriID=?, Resim=? WHERE UrunID=?";
    sqlsrv_query($conn, $sqlGuncelle, [$adi, $fiyat, $stok, $katID, $yeniResim, $id]);

    header("Location: urunler.php");
    exit;
}

/********************************************
🟢 ÜRÜN EKLEME
*********************************************/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ekle'])) {

    $adi = trim($_POST['urun_adi']);
    $fiyat = floatval($_POST['fiyat']);
    $stok = intval($_POST['stok']);
    $kategori = intval($_POST['kategori']);

    // Resim yükleme
    $resimYolu = resimYukle("resim");

    if ($adi !== "" && $fiyat > 0) {
        $sqlEkle = "INSERT INTO Urun (UrunAdi, Fiyat, Stok, Aktif, KategoriID, Resim)
                    VALUES (?, ?, ?, 1, ?, ?)";
        sqlsrv_query($conn, $sqlEkle, [$adi, $fiyat, $stok, $kategori, $resimYolu]);
        header("Location: urunler.php");
        exit;
    }
}

/********************************************
❌ ÜRÜN SİLME
*********************************************/
if (isset($_GET['sil'])) {

    $id = intval($_GET['sil']);

    // Eski resmi sil
    $res = sqlsrv_query($conn, "SELECT Resim FROM Urun WHERE UrunID=?", [$id]);
    $old = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
    if ($old && $old['Resim'] && file_exists("../" . $old['Resim'])) {
        unlink("../" . $old['Resim']);
    }

    sqlsrv_query($conn, "DELETE FROM Urun WHERE UrunID = ?", [$id]);
    header("Location: urunler.php");
    exit;
}

/********************************************
🧾 ÜRÜNLERİ ÇEK
*********************************************/
$sql = "SELECT u.UrunID, u.UrunAdi, u.Fiyat, u.Stok, u.Aktif, u.KategoriID, u.Resim, k.KategoriAdi
        FROM Urun u
        LEFT JOIN Kategori k ON u.KategoriID = k.KategoriID
        ORDER BY u.UrunAdi ASC";
$stmt = sqlsrv_query($conn, $sql);
$urunler = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $urunler[] = $row;
}

/********************************************
🧩 KATEGORİLERİ ÇEK
*********************************************/
$katStmt = sqlsrv_query($conn, "SELECT KategoriID, KategoriAdi FROM Kategori ORDER BY KategoriAdi ASC");
$kategoriler = [];
while ($kat = sqlsrv_fetch_array($katStmt, SQLSRV_FETCH_ASSOC)) {
    $kategoriler[] = $kat;
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Ürün Yönetimi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f5f6fa; padding:40px; }
.container { max-width:1100px; }
.img-thumb { width:70px; height:70px; object-fit:cover; border-radius:8px; }
</style>
</head>
<body>
<div class="container">
    <h3>☕ Ürün Yönetimi</h3>
    <hr>

    <!-- ➕ Ürün ekleme formu -->
    <form method="POST" enctype="multipart/form-data" class="row g-3 mb-4 p-3 border rounded bg-white shadow-sm">

        <div class="col-md-3">
            <input type="text" name="urun_adi" class="form-control" placeholder="Ürün Adı" required>
        </div>

        <div class="col-md-2">
            <input type="number" step="0.01" name="fiyat" class="form-control" placeholder="Fiyat" required>
        </div>

        <div class="col-md-2">
            <input type="number" name="stok" class="form-control" placeholder="Stok" required>
        </div>

        <div class="col-md-3">
            <select name="kategori" class="form-select" required>
                <option value="">Kategori Seç</option>
                <?php foreach ($kategoriler as $k): ?>
                    <option value="<?= $k['KategoriID'] ?>"><?= htmlspecialchars($k['KategoriAdi']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- 📸 Resim seç -->
        <div class="col-md-2">
            <input type="file" name="resim" class="form-control">
        </div>

        <div class="col-md-12 text-end">
            <button type="submit" name="ekle" class="btn btn-success">➕ Ekle</button>
        </div>
    </form>

    <!-- 📋 Ürün listesi -->
    <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>Resim</th>
                <th>Ürün Adı</th>
                <th>Kategori</th>
                <th>Fiyat (₺)</th>
                <th>Stok</th>
                <th>Durum</th>
                <th>Düzenle</th>
                <th>Sil</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($urunler as $u): ?>
            <tr>
                <td>
                    <?php if ($u['Resim']): ?>
                        <img src="../<?= $u['Resim'] ?>" class="img-thumb">
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($u['UrunAdi']) ?></td>
                <td><?= htmlspecialchars($u['KategoriAdi'] ?? '—') ?></td>
                <td><?= number_format($u['Fiyat'], 2) ?></td>
                <td><?= $u['Stok'] ?></td>

                <td>
                    <?php if ($u['Aktif']): ?>
                        <span class="badge bg-success">Aktif</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Pasif</span>
                    <?php endif; ?>
                </td>

                <!-- ✏ Düzenle -->
                <td>
                    <button class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#editModal<?= $u['UrunID'] ?>">
                        ✏
                    </button>

                    <!-- MODAL -->
                    <div class="modal fade" id="editModal<?= $u['UrunID'] ?>">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="POST" enctype="multipart/form-data">

                            <input type="hidden" name="islem" value="duzenle">
                            <input type="hidden" name="urun_id" value="<?= $u['UrunID'] ?>">

                            <div class="modal-header">
                              <h5 class="modal-title">Ürünü Düzenle</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <label>Ürün Adı</label>
                                <input type="text" class="form-control mb-2" name="urun_adi"
                                       value="<?= htmlspecialchars($u['UrunAdi']) ?>" required>

                                <label>Fiyat</label>
                                <input type="number" step="0.01" class="form-control mb-2"
                                       name="fiyat" value="<?= $u['Fiyat'] ?>" required>

                                <label>Stok</label>
                                <input type="number" class="form-control mb-2"
                                       name="stok" value="<?= $u['Stok'] ?>" required>

                                <label>Kategori</label>
                                <select class="form-select mb-2" name="kategori">
                                    <?php foreach ($kategoriler as $k): ?>
                                        <option value="<?= $k['KategoriID'] ?>"
                                            <?= ($u['KategoriID'] == $k['KategoriID']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k['KategoriAdi']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <label>Yeni Resim (İsteğe bağlı)</label>
                                <input type="file" name="resim_duzenle" class="form-control">

                                <?php if ($u['Resim']): ?>
                                    <img src="../<?= $u['Resim'] ?>" class="img-thumb mt-2">
                                <?php endif; ?>

                            </div>

                            <div class="modal-footer">
                              <button type="submit" class="btn btn-primary">Kaydet</button>
                            </div>

                          </form>
                        </div>
                      </div>
                    </div>

                </td>

                <!-- 🗑 Sil -->
                <td>
                    <a href="urunler.php?sil=<?= $u['UrunID'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Bu ürünü silmek istiyor musunuz?')">🗑</a>
                </td>

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="btn btn-secondary mt-4">← Geri Dön</a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>