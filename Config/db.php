<?php
// db.php — SQL Server bağlantı dosyası 
$serverName = "DESKTOP-F71CA5J\\MSSQLSERVER01"; 
$connectionOptions = array(
    "Database" => "CafeOtomasyonu",  
    "CharacterSet" => "UTF-8"       
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    echo "<pre style='color:red; font-family:monospace'>";
    echo " Veritabanı bağlantısı başarısız:\n";
    print_r(sqlsrv_errors());
    echo "</pre>";
    exit;
}
?>