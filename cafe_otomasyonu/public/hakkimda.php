<?php
include("../config/db.php");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Hakkımızda</title>
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
    font-size:13px;
    font-weight:600;
}
.btn-reserve:hover{
    background:#b3630a;
    color:#fff;
}

/* İçerik */
.container{
    max-width:900px;
    margin:40px auto;
    padding:20px;
    background:var(--coffee-mid);
    border-radius:14px;
}

h1{
    color:var(--coffee-4);
    margin-bottom:20px;
    font-size:26px;
}

p{
    line-height:1.65;
    font-size:16px;
    margin-bottom:16px;
    opacity:.9;
}

.footer{
    text-align:center; 
    padding:16px 0; 
    color:#aaa;
    margin-top:30px;
    font-size:13px;
}
</style>
</head>
<body>

<!-- NAVBAR (YENİ MENÜ) -->
<div class="navbar">
    <div class="logo">Cafe Menüsü</div>

    <div class="nav-links">
        <a href="menu.php">MENÜ</a>
        <a href="hakkimizda.php" class="active">HAKKIMIZDA</a>
        <a href="sosyal.php">SOSYAL MEDYA</a>
        <a href="iletisim.php">İLETİŞİM</a>
    </div>

    <button class="btn-reserve">Rezervasyon için garsona bildiriniz</button>
</div>

<!-- SAYFA İÇERİĞİ -->
<div class="container">
    <h1>Hakkımızda</h1>

    <p><b>Cafe’miz</b>, yılların deneyimi, modern işletmecilik anlayışı ve misafir memnuniyetini merkeze alan hizmet yaklaşımıyla kurulmuş; sıcak ve samimi atmosferiyle bölgenin sevilen buluşma noktalarından biri haline gelmiştir.</p>

    <p>Her gün taze olarak seçilen ürünlerimizle hazırlanan menümüz; kahve çeşitlerinden soğuk içeceklere, tatlılardan atıştırmalıklara kadar geniş bir lezzet yelpazesi sunar. Misafirlerimizin damak tadına hitap eden bu ürünler, hijyen kurallarına titizlikle uyularak özenle hazırlanır.</p>

    <p>İşletmemizin en güçlü yönlerinden biri, güler yüzlü ve alanında uzman ekibimizdir. Servis kalitemizi her zaman en üst seviyede tutmak için ekip içi eğitimlerimizi düzenli olarak sürdürmekte, misafirlerimizin kendilerini özel hissetmeleri için profesyonel bir hizmet anlayışı benimsemekteyiz.</p>

    <p>Vizyonumuz; yalnızca bir cafe değil, aynı zamanda insanların keyifle vakit geçirebileceği, huzur bulabileceği ve kaliteli zaman yaratabileceği bir yaşam alanı oluşturmak. Misyonumuz ise lezzet, kalite, samimiyet ve modernliği tek bir çatı altında buluşturarak sürdürülebilir bir hizmet sunmaktır.</p>

    <p>Cafe’miz, yenilikçi yaklaşımı, estetik dekorasyonu ve zengin menü içerikleriyle hem bireysel hem de sosyal buluşmalar için ideal bir ortam sunmaya devam etmektedir.</p>
</div>

<div class="footer">
    © <?= date("Y") ?> Cafe Otomasyonu – Hakkımızda
</div>

</body>
</html>