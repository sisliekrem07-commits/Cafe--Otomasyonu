<?php
include("../config/db.php");

/* ------------------------------
   ✔ KATEGORİLERİ ÇEK
------------------------------ */
$katSql = "SELECT KategoriID, KategoriAdi, KategoriResim FROM Kategori ORDER BY KategoriAdi ASC";
$katStmt = sqlsrv_query($conn, $katSql);
$kategoriler = [];
if ($katStmt) {
    while ($row = sqlsrv_fetch_array($katStmt, SQLSRV_FETCH_ASSOC)) {
        $kategoriler[] = $row;
    }
}

/* ------------------------------
   ✔ ÜRÜNLERİ ÇEK
------------------------------ */
if (isset($_GET['kategori'])) {
    $katID = intval($_GET['kategori']);
    $urunSql = "SELECT UrunID, UrunAdi, Fiyat, Resim, KategoriID 
                FROM Urun 
                WHERE Aktif = 1 AND KategoriID = ?
                ORDER BY UrunAdi ASC";
    $urunStmt = sqlsrv_query($conn, $urunSql, [$katID]);
} else {
    $urunSql = "SELECT UrunID, UrunAdi, Fiyat, Resim, KategoriID 
                FROM Urun 
                WHERE Aktif = 1
                ORDER BY UrunAdi ASC";
    $urunStmt = sqlsrv_query($conn, $urunSql);
}

$urunler = [];
if ($urunStmt) {
    while ($row = sqlsrv_fetch_array($urunStmt, SQLSRV_FETCH_ASSOC)) {
        $urunler[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Cafe Menümüz</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
:root{
    --coffee-dark: #1a0f0a;
    --coffee-mid:  #2b1a14;
    --coffee-3:    #dd8519;
    --coffee-4:    #e4ca9e;
    --text-light:  #f9f5ef;
}

*{ margin:0; padding:0; box-sizing:border-box; }
body{ background:var(--coffee-dark); color:var(--text-light); font-family: system-ui; }

/* NAVBAR */
.navbar{
    background:#23140f;
    padding:14px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.logo{
    font-size:20px;
    font-weight:700;
    color:var(--coffee-3);
}

/* NAV MENÜ */
.nav-links{
    display:flex;
    gap:35px;
}

.nav-links a{
    color:#f9f5ef;
    text-decoration:none;
    font-size:15px;
    font-weight:600;
    opacity:.85;
}

.nav-links a:hover{
    opacity:1;
}

.nav-links a.active{
    color:var(--coffee-3);
}

/* Rezervasyon butonu */
.btn-reserve{
    background:var(--coffee-3);
    padding:8px 16px;
    border-radius:999px;
    border:none;
    cursor:pointer;
    color:#2b1a14;
}
.btn-reserve:hover{
    background:#b3630a;
    color:#fff;
}

/* HERO */
.hero{
    padding:60px 40px 80px;
    background:
        linear-gradient(to bottom, rgba(0,0,0,.55), rgba(0,0,0,.8)),
        url("https://images.pexels.com/photos/312418/pexels-photo-312418.jpeg?w=1200") center/cover;
    text-align:center;
}
.hero-title{ font-size:32px; margin-bottom:12px; }

/* MAIN */
.main{
    max-width:1100px;
    margin:-40px auto 40px;
    padding:0 16px;
}

.section-title{ text-align:center; margin:40px 0 20px; }
.section-title h2{ font-size:22px; margin-bottom:4px; }
.section-title span{ color:var(--coffee-4); font-size:13px; }

/* KATEGORİLER */
.category-strip{
    display:flex;
    gap:12px;
    overflow-x:auto;
    padding:12px 0;
}
.category-card{
    min-width:150px;
    background:var(--coffee-mid);
    border-radius:14px;
    padding:10px;
    text-align:center;
    text-decoration:none;
    color:#fff;
}
.category-card img{
    width:100%;
    height:80px;
    object-fit:cover;
    border-radius:10px;
}
.category-card span{ font-size:13px; }

/* ÜRÜNLER */
.product-grid{
    display:grid;
    gap:18px;
    grid-template-columns:repeat(auto-fit, minmax(220px,1fr));
}
.product-card{
    background:var(--coffee-mid);
    padding:10px;
    border-radius:16px;
}
.product-img{
    width:100%;
    height:150px;
    object-fit:cover;
    border-radius:12px;
}
.product-name{ font-size:15px; font-weight:700; }
.product-meta{
    margin-top:6px;
    display:flex;
    justify-content:space-between;
    font-size:13px;
}
.product-price{ font-weight:700; color:var(--coffee-3); }
.product-cat{ color:var(--coffee-4); }

/* FOOTER */
.footer{
    text-align:center;
    padding:14px 0;
    color:#aaa;
}
</style>
</head>
<body>

<!-- NAVBAR (YENİ MENÜ BURADA) -->
<div class="navbar">
    <div class="logo">Cafe Menüsü</div>

    <div class="nav-links">
        <a href="hakkimda.php">HAKKIMIZDA</a>
        <a href="sosyal.php">SOSYAL MEDYA</a>
        <a href="iletisim.php">İLETİŞİM</a>
    </div>

    <button class="btn-reserve">Rezervasyon için garsona bildiriniz</button>
</div>

<!-- HERO -->
<section class="hero">
    <h1 class="hero-title">İçinizi Isıtacak Lezzetler</h1>
    <p class="hero-text">Sıcak kahveler, soğuk içecekler ve taze atıştırmalıklarla sofranıza lezzet katıyoruz.</p>
</section>

<div class="main">

    <!-- KATEGORİLER -->
    <div id="kategoriler" class="section-title">
        <h2>Menü Kategorileri</h2>
        <span>Sıcak – soğuk içecekler ve tatlı çeşitleri</span>
    </div>

    <div class="category-strip">
        <?php foreach($kategoriler as $kat): ?>
            <a class="category-card" href="menu.php?kategori=<?= $kat['KategoriID'] ?>">
                <?php if(!empty($kat['KategoriResim'])): ?>
                    <img src="../uploads/Kategoriler/<?= htmlspecialchars($kat['KategoriResim']) ?>">
                <?php else: ?>
                    <img src="https://via.placeholder.com/300x200?text=Kategori">
                <?php endif; ?>
                <span><?= htmlspecialchars($kat['KategoriAdi']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ÜRÜNLER -->
    <div id="urunler" class="section-title">
        <h2>
            <?php
            if (isset($_GET['kategori'])) {
                foreach($kategoriler as $k) {
                    if ($k['KategoriID'] == $_GET['kategori']) {
                        echo htmlspecialchars($k['KategoriAdi']) . " Ürünleri";
                    }
                }
            } else echo "Tüm Ürünler";
            ?>
        </h2>
        <span>Güncel fiyatlar ve görseller</span>
    </div>

    <div class="product-grid">
        <?php if(empty($urunler)): ?>
            <p style="opacity:.8;">Bu kategoriye ait ürün bulunamadı.</p>
        <?php else: ?>
        <?php foreach($urunler as $u): ?>
            <?php
            $resim = trim($u["Resim"]);
            if ($resim == "" || $resim == null)
                $urunResimYol = "https://via.placeholder.com/400x300?text=Ürün";
            else if (str_starts_with($resim, "uploads/"))
                $urunResimYol = "../" . $resim;
            else if (str_contains($resim, "/"))
                $urunResimYol = "../" . $resim;
            else
                $urunResimYol = "../uploads/Urunler/" . $resim;
            ?>
            <div class="product-card">
                <img class="product-img" src="<?= $urunResimYol ?>">
                <div class="product-name"><?= htmlspecialchars($u['UrunAdi']) ?></div>
                <div class="product-meta">
                    <span class="product-price"><?= number_format($u['Fiyat'],2) ?> ₺</span>
                    <span class="product-cat">
                        <?php foreach ($kategoriler as $k) {
                            if ($k["KategoriID"] == $u["KategoriID"]) echo $k["KategoriAdi"];
                        } ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<div class="footer">
    © <?= date("Y") ?> Cafe Otomasyonu – Menü Görüntüleme
</div>

</body>
</html>