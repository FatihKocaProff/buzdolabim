# Buzdolabım — Akıllı Mutfak Envanter ve SKT Takip Sistemi

**Ders:** İnternet Programcılığı I (PHP)
**Öğrenci:** _Ad Soyad: Fatih Koca Öğrenci No: 254309053

## Projenin Amacı

Buzdolabım, evdeki buzdolabında bulunan ürünlerin kaydını tutan ve son kullanma tarihlerini (SKT) takip eden PHP tabanlı bir **Kayıt ve Takip Sistemi**dir. Tarihi yaklaşan ürünleri öne çıkararak gıda israfını azaltmayı amaçlar.

## Özellikler

### Zorunlu özellikler
| Özellik | Nerede |
|---|---|
| Ana sayfa (uygulama adı, açıklama, özet panel) | `index.php` |
| Kayıt listeleme (`foreach` ile; ürün adı, kategori, miktar, SKT, açıklama, durum) | `urunler.php`, `urun_kart.php` |
| Yeni kayıt ekleme (HTML form, **POST**) | `ekle.php` |
| Veri doğrulama (boş alan, uzunluk, geçersiz kategori/tarih için açıklayıcı mesajlar) | `urunDogrula()` — `functions.php` |
| Kayıt silme (**POST**) | `sil.php` |
| Filtreleme (durum: Taze / Yaklaşıyor / Bozulmuş, kategori, arama — **GET**) | `urunler.php`, `urunleriFiltrele()` |
| Özet sayaçlar (toplam, taze, yaklaşan, bozulmuş) | `index.php`, `durumaGoreSay()` |
| Güvenli çıktı (`htmlspecialchars()`) | `guvenli()` — `functions.php` |

### Ek özellikler
- **MySQL (PDO) veritabanı:** Kayıtlar kalıcı olarak saklanır. Veritabanı ve tablolar ilk açılışta otomatik oluşturulur.
- **Kullanıcı giriş / kayıt sistemi:** Şifreler `password_hash()` ile saklanır, her kullanıcı yalnızca kendi ürünlerini görür.
- **Kayıt düzenleme:** `ekle.php?duzenle=ID`
- **CSRF koruması:** Formlarda gizli token bulunur.
- **Oyunlaştırma:** XP, günlük seri, seviye/unvan ve rozetler (profil sayfası).
- **Profil sayfası:** Ad, mutfak adı, avatar rengi, şifre değiştirme, örnek verileri sıfırlama.
- **3D simülatör:** Three.js ile ana sayfada buzdolabı/robot görselleştirmesi.
- Açık/koyu tema, mobil uyumlu arayüz.

## Klasör Yapısı

```
buzdolabim-hafta22/
├── index.php        Ana sayfa: sağlık telemetrisi, günlük görevler, acil/bozuk ürünler
├── urunler.php      Envanter listesi ve filtreleme (GET)
├── urun_kart.php    Tek bir ürün kartının şablonu (listelemede include edilir)
├── ekle.php         Yeni ürün ekleme ve düzenleme formu (POST)
├── sil.php          Ürün silme / tüketildi işlemi (POST)
├── profil.php       Profil, seviye ve rozetler, veri sıfırlama
├── giris.php        Giriş ve kayıt ekranı
├── cikis.php        Oturumu kapatma
├── sim.php          3D simülatör bölümü (index.php içine eklenir)
├── header.php       Ortak üst kısım ve menü
├── footer.php       Ortak alt kısım
├── functions.php    Yardımcı fonksiyonlar (doğrulama, filtreleme, sayaçlar, güvenli çıktı)
├── db.php           MySQL bağlantısı, tablolar, kullanıcı/ürün/XP sorguları
├── db_ayar.php      Veritabanı bağlantı ayarları
├── assets/
│   ├── style.css    Genel stiller
│   ├── hud.css      Panel/HUD stilleri
│   ├── tema.js      Açık/koyu tema
│   ├── kartlar.js   Kart animasyonları
│   └── robot.js     3D sahne (Three.js)
└── README.md
```

## XAMPP Üzerinde Çalıştırma

1. XAMPP Control Panel'den **Apache** ve **MySQL**'i başlatın.
2. Proje klasörünü `C:\xampp\htdocs\` içine kopyalayın (ör. `C:\xampp\htdocs\buzdolabim-hafta22`).
3. Tarayıcıda açın: `http://localhost/buzdolabim/giris.php`
4. Veritabanı (`buzdolabim`) ve tablolar ilk açılışta **otomatik** oluşturulur; SQL dosyası içe aktarmaya gerek yoktur.
5. Hazır demo hesapla giriş yapabilir veya **“Yeni hesap oluştur”** ile kayıt olabilirsiniz:

| Kullanıcı adı | Şifre |
|---|---|
| `admin` | `1234` |
| `ogrenci` | `1234` |

> MySQL `root` kullanıcısının şifresi boş değilse `db_ayar.php` içindeki `DB_SIFRE` değerini değiştirin.

## Kullanılan PHP Yapıları

- **Diziler:** `$kategoriler`, `ornekUrunler()` örnek kayıtları
- **Döngüler:** `foreach` ile ürün listeleme, sayaçlar, rozetler
- **Koşullar:** SKT'ye göre durum (`urunDurumu()`), doğrulama, filtreler
- **Kullanıcı tanımlı fonksiyonlar:** `urunDogrula()`, `urunleriFiltrele()`, `durumaGoreSay()`, `kalanGun()`, `guvenli()` vb.
- **$_POST / $_GET / $_SESSION:** form verisi, filtreler, oturum ve bildirim mesajları
- **PDO hazır sorgular:** SQL injection'a karşı

## Bilinen Eksikler

- İnternet bağlantısı yoksa 3D simülatör (Three.js CDN) yüklenmez; uygulamanın geri kalanı çalışır.
- Şifre sıfırlama (e-posta ile) yoktur.
- Yetkilendirme (rol bazlı yetki) yoktur; tüm kullanıcılar aynı yetkilere sahiptir.

## Yapay Zekâ Kullanımı

**Kullanılan araçlar:** Claude (Claude Code) 

**Kullanım amaçları:**
- Arayüz tasarımı ve CSS düzenlemeleri
- MySQL (PDO) geçişi ve hata ayıklama
- 3D simülatör (Three.js) performans iyileştirmeleri
- XP / rozet sistemi gibi ek özelliklerin geliştirilmesi
- README hazırlanması

**Örnek istem:**
> “Verileri sıfırla dediğimiz zaman günlük seri ile XP de sıfırlansın mı diye sorsun, şimdilik test aşamasında olduğu için.”

**Kendi katkılarım:** _Proje konusunu seçme,giriş.php,ekleme.php,ürünler.php Alt yapısını geliştirdim, sayfa yapısını ve özellikleri belirleme, kodları test etme, hataları bildirme ve düzeltmeleri kontrol etme, ... (kendi cümlelerinizle doldurun)_


