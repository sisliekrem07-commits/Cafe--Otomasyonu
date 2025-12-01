<?php
include("../config/db.php");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>İletişim</title>
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

/* İçerik Alanı */
.container{
    max-width:900px;
    margin:40px auto;
    padding:20px;
    background:var(--coffee-mid);
    border-radius:14px;
}

h1{
    color:var(--coffee-4);
    margin-bottom:18px;
}

.contact-box{
    display:flex;
    flex-direction:column;
    gap:18px;
    font-size:17px;
}

.contact-box div{
    font-weight:500;
}

.contact-box a{
    color:var(--coffee-4);
    text-decoration:none;
}
.contact-box a:hover{
    text-decoration:underline;
}

.footer{
    text-align:center;
    padding:16px 0;
    color:#aaa;
    margin-top:35px;
    font-size:13px;
}
</style>
</head>
<body>

<!-- NAVBAR + TOP MENU -->
<div class="navbar">
    <div class="logo">Cafe Menüsü</div>

    <div class="nav-links">
        <a href="menu.php">MENÜ</a>
        <a href="hakkimda.php">HAKKIMIZDA</a>
        <a href="sosyal.php">SOSYAL MEDYA</a>
        <a href="iletisim.php" class="active">İLETİŞİM</a>
    </div>

    <button class="btn-reserve">Rezervasyon için garsona bildiriniz</button>
</div>

<!-- İÇERİK -->
<div class="container">
    <h1>İletişim Bilgilerimiz</h1>

    <div class="contact-box">
        <div>📍 <b>Adres:</b> Mandabatmaz, Asmalı Mescit, Olivya Gç. 1/A, 34430 Beyoğlu/İstanbul</div>
        <div>📞 <b>Telefon:</b> 0 (535) 325 33 57</div>
        <div>📧 <b>E-posta:</b> info@kosebucakkafe.com</div>
        <div>🕒 <b>Çalışma Saatleri:</b> 09:00 – 23:00</div>
        <div>📌 <b>Harita:</b> 
            <a href="https://www.google.com/maps/dir//Mandabatmaz,+Asmal%C4%B1+Mescit,+Olivya+G%C3%A7.+1%2FA,+34430+Beyo%C4%9Flu%2F%C4%B0stanbul/"
               target="_blank">Google Maps Konumu</a>
        </div>
    </div>
</div>

<div class="footer">
    © <?= date("Y") ?> Cafe Otomasyonu – İletişim
</div>

</body>
</html>