<?php
include("../config/db.php");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Sosyal Medya</title>
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

/* TOP MENU */
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

/* Rezervasyon Butonu */
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
}
.social-box{
    display:flex;
    flex-direction:column;
    gap:16px;
    font-size:18px;
}
.social-box a{
    color:var(--coffee-4);
    font-weight:600;
    text-decoration:none;
}
.social-box a:hover{
    color:#fff;
}

/* Footer */
.footer{
    text-align:center;
    padding:14px 0;
    color:#aaa;
    margin-top:35px;
    font-size:13px;
}
</style>
</head>
<body>

<!-- NAVBAR + MENÜ -->
<div class="navbar">
    <div class="logo">Cafe Menüsü</div>

    <div class="nav-links">
        <a href="menu.php">MENÜ</a>
        <a href="hakkimda.php">HAKKIMIZDA</a>
        <a href="sosyal.php" class="active">SOSYAL MEDYA</a>
        <a href="iletisim.php">İLETİŞİM</a>
    </div>

    <button class="btn-reserve">Rezervasyon için garsona bildiriniz</button>
</div>

<!-- İÇERİK -->
<div class="container">
    <h1>Sosyal Medya Hesaplarımız</h1>

    <div class="social-box">
        <a href="#" target="_blank">📸 Instagram: @Köşebucakkafe</a>
        <a href="#" target="_blank">👍 Facebook: Köşe Bucak Kafe Resmi Sayfası</a>
        <a href="https://www.google.com/maps/dir//Mandabatmaz,+Asmal%C4%B1+Mescit,+Olivya+G%C3%A7.+1%2FA,+34430+Beyo%C4%9Flu%2F%C4%B0stanbul/"
           target="_blank">📍 Google Haritalar: Konumumuz</a>
        <a href="#" target="_blank">🎥 TikTok: @kosebucakkafe</a>
    </div>
</div>

<div class="footer">
    © <?= date("Y") ?> Cafe Otomasyonu – Sosyal Medya
</div>

</body>
</html>