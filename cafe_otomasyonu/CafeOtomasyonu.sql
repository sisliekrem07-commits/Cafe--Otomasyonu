CREATE DATABASE CafeOtomasyonu;
go
USE CafeOtomasyonu;
go

/* ===============================
    KULLANICI TABLOSU
   - Rol sadece belirlenen de�erler olabilir
   - Aktif 0 veya 1 olucak
================================= */
CREATE TABLE Kullanici (
    KullaniciID int identity(1,1) primary key,
    AdSoyad nvarchar(100),
    KullaniciAdi nvarchar(50) unique,
    Sifre nvarchar(100),
    Rol nvarchar(50)
        check (Rol in ('Y�netici', 'Garson', 'Kasiyer', 'Mutfak')),
    Aktif bit default 1
        check (Aktif in (0,1))
);

/* ===============================
    MASA TABLOSU
   - Durum sadece Bo�, Dolu veya Rezerve olabilir
================================= */
CREATE TABLE Masa (
    MasaID int identity(1,1) primary key,
	GarsonID int null,
    MasaAdi nvarchar(50),
    Durum nvarchar(20) default 'Bo�'
        check (Durum in ('Bo�', 'Dolu', 'Rezerve'))
);

/* ===============================
    URUN TABLOSU
   - Fiyat s�f�rdan b�y�k olmal�
   - Stok s�f�rdan k���k olamaz
   - Aktif 0 veya 1 olmal�
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
    S�PAR�� TABLOSU
   - Durum belirli de�erler aras�nda olmal�
================================= */
CREATE TABLE Siparis (
    SiparisID int identity(1,1) primary key,
    MasaID int ,
    GarsonID int ,
    Tarih datetime default getdate(),
    Durum nvarchar(20) default 'Haz�rlan�yor'
        check (Durum in ('Haz�rlan�yor', 'Tamamland�', '�ptal'))
);

/* ===============================
    S�PAR�� DETAY TABLOSU
   - Adet ve Fiyat pozitif olmal�
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
    �DEME TABLOSU
   - �deme tipi sadece Nakit veya Kart
   - Tutar s�f�rdan b�y�k olmal�
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
   - ��lem tipi Giri� veya ��k�� olmal�
   - Miktar pozitif olmal�
================================= */
CREATE TABLE StokHareket (
    HareketID int identity(1,1) primary key,
    UrunID int ,
    Miktar int check (Miktar > 0),
    IslemTipi nvarchar(20)
        check (IslemTipi in ('Giri�', '��k��')),
    Tarih datetime default getdate(),
    Aciklama nvarchar(100)
);
/* ===============================
    KATEGOR� TABLOSU
================================= */
CREATE TABLE Kategori (
    KategoriID int identity(1,1) primary key,
    KategoriAdi nvarchar(50)
);