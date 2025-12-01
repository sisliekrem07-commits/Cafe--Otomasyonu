<?php
session_start();
include("../config/db.php");

$mesaj = "";
$DEBUG = false; // Sorun olursa true yap, detayları gösterir

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Değerleri güvenli şekilde al
    $kullaniciAdi = $_POST['kullanici_adi'] ?? '';
    $sifre        = $_POST['sifre'] ?? '';

    $kullaniciAdi = trim($kullaniciAdi);
    $sifre        = trim($sifre);

    if ($kullaniciAdi === '' || $sifre === '') {
        $mesaj = "⚠ Lütfen kullanıcı adı ve şifre giriniz!";
    } else {

        // 1) Kullanıcıyı çek
        $sqlUser = "
            SELECT TOP 1 KullaniciID, AdSoyad, KullaniciAdi, Sifre, Rol, Aktif
            FROM Kullanici
            WHERE LTRIM(RTRIM(KullaniciAdi)) COLLATE Turkish_CI_AS = LTRIM(RTRIM(?)) COLLATE Turkish_CI_AS
              AND Aktif = 1
        ";
        $stmtUser = sqlsrv_query($conn, $sqlUser, [$kullaniciAdi]);

        if ($stmtUser && sqlsrv_has_rows($stmtUser)) {

            $user = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC);

            // 2) Şifre doğru mu?
            $sqlPass = "
                SELECT 1
                FROM Kullanici
                WHERE KullaniciID = ?
                  AND LTRIM(RTRIM(CONVERT(NVARCHAR(100), Sifre))) = LTRIM(RTRIM(?))
            ";

            $stmtPass = sqlsrv_query($conn, $sqlPass, [$user['KullaniciID'], $sifre]);

            if ($stmtPass && sqlsrv_has_rows($stmtPass)) {

                // Giriş BAŞARILI
                $_SESSION['user'] = [
                    'id'      => $user['KullaniciID'],
                    'adsoyad' => $user['AdSoyad'],
                    'rol'     => $user['Rol']
                ];

                header("Location: dashboard.php");
                exit;

            } else {
                $mesaj = "❌ Şifre hatalı!";
                if ($DEBUG) {
                    echo "<pre>Şifre DB: {$user['Sifre']} - Girilen: {$sifre}</pre>";
                }
            }

        } else {
            $mesaj = "❌ Kullanıcı bulunamadı veya pasif!";
            if ($DEBUG) {
                echo "<pre>Kullanıcı bulunamadı: '$kullaniciAdi'</pre>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Cafe Otomasyonu - Giriş</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{
            background:#f5f6fa;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            font-family:"Segoe UI",sans-serif;
        }
        .login-box{
            background:#fff;
            padding:40px;
            border-radius:15px;
            box-shadow:0 4px 15px rgba(0,0,0,.1);
            width:360px
        }
        h2{
            text-align:center;
            margin-bottom:25px;
            color:#2c3e50;
            font-weight:600
        }
        .form-control{
            margin-bottom:15px
        }
        .btn-primary{
            width:100%;
            background-color:#2c3e50;
            border:none
        }
        .btn-primary:hover{
            background-color:#1a242f
        }
        .alert{
            text-align:center;
            margin-top:15px
        }
    </style>
</head>
<body>
<div class="login-box">
    <h2>☕ Cafe Otomasyonu</h2>
    <form method="POST">
        <input type="text" name="kullanici_adi" class="form-control" placeholder="Kullanıcı Adı" required>
        <input type="password" name="sifre" class="form-control" placeholder="Şifre" required>
        <button type="submit" class="btn btn-primary">Giriş Yap</button>
    </form>

    <?php if ($mesaj): ?>
        <div class="alert alert-danger"><?= $mesaj ?></div>
    <?php endif; ?>
</div>
</body>
</html>