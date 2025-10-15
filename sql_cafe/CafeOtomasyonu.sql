CREATE DATABASE CafeOtomasyonu;
go
USE CafeOtomasyonu;
go

/* ===============================
    KULLANICI TABLOSU
   - Rol sadece belirlenen deðerler olabilir
   - Aktif 0 veya 1 olucak
================================= */
CREATE TABLE Kullanici (
    KullaniciID int identity(1,1) primary key,
    AdSoyad nvarchar(100),
    KullaniciAdi nvarchar(50) unique,
    Sifre nvarchar(100),
    Rol nvarchar(50)
        check (Rol in ('Yönetici', 'Garson', 'Kasiyer', 'Mutfak')),
    Aktif bit default 1
        check (Aktif in (0,1))
);

/* ===============================
    MASA TABLOSU
   - Durum sadece Boþ, Dolu veya Rezerve olabilir
================================= */
CREATE TABLE Masa (
    MasaID int identity(1,1) primary key,
	GarsonID int null,
    MasaAdi nvarchar(50),
    Durum nvarchar(20) default 'Boþ'
        check (Durum in ('Boþ', 'Dolu', 'Rezerve'))
);

/* ===============================
    URUN TABLOSU
   - Fiyat sýfýrdan büyük olmalý
   - Stok sýfýrdan küçük olamaz
   - Aktif 0 veya 1 olmalý
================================= */
CREATE TABLE Urun (
    UrunID int identity(1,1) primary key,
	KategoriID int ,
    UrunAdi nvarchar(100),
    Fiyat decimal(10,2)
        check (Fiyat >= 0),
    Stok int default 0
        check (Stok >= 0),
    Aktif bit default 1
        check (Aktif IN (0,1))
);

/* ===============================
    SÝPARÝÞ TABLOSU
   - Durum belirli deðerler arasýnda olmalý
================================= */
CREATE TABLE Siparis (
    SiparisID int identity(1,1) primary key,
    MasaID int ,
    GarsonID int ,
    Tarih datetime default getdate(),
    Durum nvarchar(20) default 'Hazýrlanýyor'
        check (Durum in ('Hazýrlanýyor', 'Tamamlandý', 'Ýptal'))
);

/* ===============================
    SÝPARÝÞ DETAY TABLOSU
   - Adet ve Fiyat pozitif olmalý
================================= */
CREATE TABLE SiparisDetay (
    DetayID int identity(1,1) primary key,
    SiparisID int ,
    UrunID int ,
    Adet int check (Adet > 0),
    Fiyat decimal(10,2) check (Fiyat >= 0),
    Tutar as (Adet * Fiyat)
);

/* ===============================
    ÖDEME TABLOSU
   - Ödeme tipi sadece Nakit veya Kart
   - Tutar sýfýrdan büyük olmalý
================================= */
CREATE TABLE Odeme (
    OdemeID int identity(1,1) primary key,
    SiparisID int ,
    OdemeTipi nvarchar(20)
        check (OdemeTipi in ('Nakit', 'Kart')),
    Tutar decimal(10,2) check (Tutar > 0),
    Tarih datetime default getdate()
);

/* ===============================
    STOK HAREKET TABLOSU
   - Ýþlem tipi Giriþ veya Çýkýþ olmalý
   - Miktar pozitif olmalý
================================= */
CREATE TABLE StokHareket (
    HareketID int identity(1,1) primary key,
    UrunID int ,
    Miktar int check (Miktar > 0),
    IslemTipi nvarchar(20)
        check (IslemTipi in ('Giriþ', 'Çýkýþ')),
    Tarih datetime default getdate(),
    Aciklama nvarchar(100)
);
/* ===============================
    KATEGORÝ TABLOSU
================================= */
CREATE TABLE Kategori (
    KategoriID int identity(1,1) primary key,
    KategoriAdi nvarchar(50)
);