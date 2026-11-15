# Buzdolabım — Akıllı Mutfak Envanter ve SKT Takip Sistemi

**Ders:** İnternet Programcılığı I (PHP)
**Öğrenci:** Fatih Koca — Öğrenci No: 254309053
**Canlı site:** https://buzdolabim.free.nf

---

## 1. Projenin Amacı

Buzdolabım, evdeki buzdolabında bulunan ürünlerin kaydını tutan ve son kullanma tarihlerini (SKT) takip eden PHP + MySQL tabanlı bir **kayıt ve takip sistemidir**.

Uygulamanın temel fikri basittir: tarihi yaklaşan ürünleri öne çıkarmak, kullanıcıyı zamanında uyarmak ve böylece **gıda israfını azaltmak**. Bunu sıkıcı bir listeden çıkarıp kullanıcıyı her gün geri getiren bir deneyime dönüştürmek için uygulamaya renkli raf ömrü çubukları, günlük görevler, tarif önerileri, XP / seviye / rozet sistemi ve 3D bir buzdolabı simülatörü eklenmiştir.

Her kullanıcı kendi hesabıyla giriş yapar ve yalnızca kendi buzdolabını görür. Şifresini unutan kullanıcı, e-posta adresine gönderilen bağlantıyla yeni şifre belirleyebilir.

---

## 2. Hızlı Başlangıç (Yerelde Çalıştırma)

1. XAMPP Control Panel'den **Apache** ve **MySQL**'i başlatın.
2. Proje klasörünü `C:\xampp\htdocs\buzdolabim` olacak şekilde kopyalayın.
3. Tarayıcıda açın: `http://localhost/buzdolabim/giris.php`
4. Veritabanı (`buzdolabim`) ve tüm tablolar ilk açılışta **otomatik** oluşturulur; SQL dosyası içe aktarmaya gerek yoktur.
5. MySQL `root` kullanıcısının şifresi boş değilse `db_ayar.php` içindeki `DB_SIFRE` değerini değiştirin.
6. **"Şifremi unuttum" özelliğini kullanacaksanız `mail_ayar.php` dosyasını doldurmanız gerekir** (ayrıntılar aşağıda, bölüm 3).

### Hazır demo hesaplar

| Kullanıcı adı | Şifre | Not |
|---|---|---|
| `admin` | `1234` | E-postası sahte olduğu için ilk girişte gerçek bir e-posta girmeniz istenir |
| `ogrenci` | `1234` | |

İsterseniz **"Yeni hesap oluştur"** ile kendi hesabınızı da açabilirsiniz.

---

## 3. ⚠️ Yerelde Çalıştırırken E-posta (Gmail) Ayarı

Güvenlik nedeniyle teslim edilen projede `mail_ayar.php` içindeki Gmail adresi ve uygulama şifresi **boş bırakılmıştır**. Bu yüzden projeyi kendi bilgisayarınızda (localhost) çalıştırdığınızda:

- Giriş, kayıt, ürün ekleme, profil gibi **tüm özellikler normal çalışır**.
- Yalnızca **"Şifremi unuttum"** sayfası e-posta gönderemez ve şu mesajı gösterir: *"E-posta gönderimi henüz ayarlanmamış."*

Şifre sıfırlama e-postasını yerelde de denemek istiyorsanız aşağıdaki ayarı **mutlaka** yapmanız gerekir:

1. Gönderici olarak kullanılacak bir Gmail hesabında **2 Adımlı Doğrulama**'yı açın: https://myaccount.google.com/security
2. https://myaccount.google.com/apppasswords adresinden bir **uygulama şifresi** oluşturun (16 karakter, örn. `abcd efgh ijkl mnop`). **Normal Gmail şifresi çalışmaz.**
3. Proje klasöründeki `mail_ayar.php` dosyasını açıp ilk iki satırı doldurun:
   ```php
   const MAIL_GMAIL_ADRES      = 'sizin.adresiniz@gmail.com';
   const MAIL_UYGULAMA_SIFRESI = 'abcd efgh ijkl mnop';
   ```
4. Dosyayı kaydedin. Başka bir ayar gerekmez; Apache'yi yeniden başlatmanıza da gerek yoktur.
5. Denemek için: sitede giriş yapıp **Profil** sayfasından hesabınıza gerçek bir e-posta girin → çıkış yapın → **Şifremi unuttum** → kullanıcı adınızı yazın → gelen kutunuzu (ve spam klasörünü) kontrol edin.

**Bilinmesi gerekenler:**
- Maildeki bağlantının adresi, sitenin açıldığı adrese göre `mail_ayar.php` içindeki `SITE_ADRESLERI` listesinden **otomatik** seçilir: bilgisayarda `http://localhost/buzdolabim`, yayındaki sitede `https://buzdolabim.free.nf`. Proje farklı bir klasör adıyla (örn. `htdocs/buzdolabim-proje`) ya da farklı bir adreste çalıştırılırsa bu listeye o adres eklenmelidir, yoksa maildeki bağlantı açılmaz.
- XAMPP'ın PHP'sinde `openssl` eklentisi açık olmalıdır (XAMPP'ta varsayılan olarak açıktır).
- Mail gelmezse sebep `C:\xampp\apache\logs\error.log` dosyasında `[buzdolabim] E-posta gönderilemedi` ile başlayan satırda yazar.
- Aynı hesaba 15 dakikada en fazla 3 sıfırlama bağlantısı gönderilir; çok deneme yapıldıysa biraz beklemek gerekir.
- Uygulama şifresi içeren `mail_ayar.php` başkasıyla paylaşılmamalı ve GitHub'a yüklenmemelidir. Yanlışlıkla paylaşılırsa aynı Google sayfasından silinip yenisi oluşturulabilir. Bu iş için kişisel değil, projeye özel bir Gmail hesabı kullanılması önerilir.

---

## 4. Sayfalar

| Sayfa | Dosya | Ne işe yarar |
|---|---|---|
| Giriş / Kayıt | `giris.php` | Kullanıcı adı **veya e-posta** ile giriş, yeni hesap oluşturma |
| Şifremi Unuttum | `sifremi_unuttum.php` | E-postaya şifre sıfırlama bağlantısı ister |
| Yeni Şifre Belirle | `sifre_sifirla.php` | E-postadaki bağlantıyla açılır, yeni şifre kaydedilir |
| Görev Paneli (Ana sayfa) | `index.php` | Mutfak sağlık skoru, özet sayaçlar, günlük görevler, Akıllı Şef tarifi, acil ve bozuk ürünler, 3D simülatör |
| Envanter | `urunler.php` | Tüm ürünlerin kart görünümü, durum / kategori / arama filtreleri |
| Ürün Ekle / Düzenle | `ekle.php` | Yeni ürün ekleme ve mevcut ürünü düzenleme formu |
| Profil | `profil.php` | Kişisel bilgiler, avatar rengi, şifre değiştirme, seviye ve rozetler, örnek verileri sıfırlama |
| Silme / Tüketme | `sil.php` | Ürünü "tükettim" ya da "bozuk, temizle" olarak envanterden düşer (yalnızca POST) |
| Çıkış | `cikis.php` | Oturumu kapatır |

---

## 5. Zorunlu Özellikler ve Karşılıkları

| Özellik | Nerede |
|---|---|
| Ana sayfa (uygulama adı, açıklama, özet panel) | `index.php` |
| Kayıt listeleme (`foreach` ile; ürün adı, kategori, miktar, SKT, açıklama, durum) | `urunler.php`, `urun_kart.php` |
| Yeni kayıt ekleme (HTML form, **POST**) | `ekle.php` |
| Veri doğrulama (boş alan, uzunluk, geçersiz kategori / tarih için açıklayıcı mesajlar) | `urunDogrula()` — `functions.php` |
| Kayıt silme (**POST**) | `sil.php` |
| Filtreleme (durum: Taze / Yaklaşıyor / Bozuldu, kategori, arama — **GET**) | `urunler.php`, `urunleriFiltrele()` |
| Özet sayaçlar (toplam, taze, yaklaşan, bozulmuş) | `index.php`, `durumaGoreSay()` |
| Güvenli çıktı (`htmlspecialchars()`) | `guvenli()` — `functions.php` |

---

## 6. Ek Özellikler (Detaylı)

### 6.1 Kullanıcı Hesapları
- **Kayıt:** Ad soyad, kullanıcı adı, e-posta, şifre ve şifre tekrarı istenir.
  - Kullanıcı adı en az 3, en fazla 50 karakterdir ve `@` içeremez (e-postayla karışmasın diye).
  - E-posta zorunludur, geçerli formatta olmalıdır ve başka bir hesapta kayıtlı olamaz.
  - Şifre en az 4 karakterdir; şifre ile şifre tekrarı aynı olmalıdır.
- **Giriş:** Tek alana kullanıcı adı **ya da** e-posta yazılabilir. Yazılan değerde `@` varsa e-postaya, yoksa kullanıcı adına bakılır. Büyük / küçük harf fark etmez.
- **E-posta zorunluluğu:** Eski hesaplarda e-posta yerine `kullanici@buzdolabim.local` gibi sahte bir adres bulunabilir. Böyle bir hesapla giriş yapan kullanıcı, gerçek e-posta girene kadar hangi sayfaya giderse gitsin Profil sayfasına yönlendirilir.
- Her yeni hesaba başlangıçta 16 örnek ürün yüklenir, böylece uygulama boş görünmez.

### 6.2 Şifremi Unuttum (E-posta ile Şifre Sıfırlama)
1. Kullanıcı giriş ekranındaki **"Şifremi unuttum"** bağlantısına tıklar, kullanıcı adını veya e-postasını yazar.
2. Hesap bulunursa kayıtlı e-postaya Gmail üzerinden **"Yeni Şifre Belirle"** butonlu bir e-posta gönderilir.
3. Bağlantı **30 dakika** geçerlidir ve **tek kullanımlıktır**. Şifre değiştirildiğinde o hesaba gönderilmiş diğer bütün bağlantılar da geçersiz olur.
4. Güvenlik önlemleri:
   - Bağlantıdaki anahtar (token) `random_bytes(32)` ile üretilir; veritabanında tokenın kendisi değil **SHA-256 özeti** saklanır. Veritabanı ele geçse bile bağlantılar kullanılamaz.
   - Hesap var olsun ya da olmasın ekranda **aynı mesaj** gösterilir; böylece dışarıdan hangi kullanıcı adlarının kayıtlı olduğu öğrenilemez.
   - Kötüye kullanımı önlemek için aynı hesaba **15 dakikada en fazla 3** bağlantı gönderilir.
   - Bağlantıdaki site adresi yalnızca ayar dosyasındaki izinli adresler listesinden seçilir; saldırgan sahte bir `Host` başlığı gönderse bile bağlantı başka bir siteye yönlendirilemez.
- E-posta gönderimi için ek kütüphane (PHPMailer, Composer) kullanılmamıştır. `mailer.php`, PHP'nin `stream_socket_client()` fonksiyonuyla `smtp.gmail.com:465` adresine SSL üzerinden bağlanır ve SMTP komutlarını (EHLO, AUTH LOGIN, MAIL FROM, RCPT TO, DATA) kendisi gönderir. E-posta hem düz metin hem HTML olarak, Türkçe karakterleri bozulmadan (UTF-8 / base64) gönderilir.

### 6.3 Profil
- Ad soyad, kullanıcı adı, e-posta (zorunlu), mutfak adı ve 6 seçenekli avatar rengi değiştirilebilir.
- **Şifre değiştirmek için mevcut şifre girilmelidir.** Mevcut şifre boş veya yanlışsa şifre değişmez. Böylece açık unutulmuş bir oturumdan şifre değiştirilemez.
- Şifre alanları tarayıcının kayıtlı şifreyi otomatik doldurmasına kapalıdır.
- **Verileri Sıfırla:** Ürün listesini 16 örnek ürüne geri döndürür; isteğe bağlı olarak XP ve günlük seri de sıfırlanır (iki aşamalı onay sorulur).

### 6.4 Ürün Yönetimi
- **Ekleme / düzenleme formu** (`ekle.php`): ürün adı, kategori, miktar, SKT ve not.
  - Hızlı miktar seçenekleri (500 g, 1 kg, 1 litre, 1 paket, 2 adet).
  - Hazır ürün şablonları (örn. Günlük Süt, Tavuk Bonfile) tek tıkla formu doldurur.
  - Saklama özellikleri (raf bölgesi vb.) seçildiğinde ürün notuna otomatik eklenir.
  - Seçilen tarihe göre ürünün durumu (Taze / Yaklaşıyor / Bozuldu) form üzerinde canlı gösterilir.
- **Doğrulama kuralları** (`urunDogrula()`):
  - Ürün adı boş olamaz, 2–60 karakter arasında olmalıdır.
  - Kategori yalnızca listedeki 6 kategoriden biri olabilir: Süt Ürünü, Sebze & Meyve, Et & Tavuk, İçecek, Sos & Konserve, Tatlı.
  - Miktar boş olamaz.
  - SKT geçerli bir tarih olmalıdır (`checkdate()`); 30 günden fazla geçmiş ürün eklenemez.
  - Not en fazla 200 karakterdir.
- **Tükettim / Temizle:** Bozulmamış ürün "Tükettim" ile envanterden düşülür (+20 XP). Bozulmuş ürün "Temizle" ile atılır (+5 XP). Ana sayfadan tüm bozuk ürünler tek tuşla temizlenebilir.

### 6.5 Ürün Durumu ve Raf Ömrü Çubuğu
Her ürünün durumu SKT'ye kalan güne göre hesaplanır (`urunDurumu()`):

| Kalan gün | Durum | Rozet |
|---|---|---|
| 4 gün ve üzeri | **Taze** | yeşil |
| 0 – 3 gün | **Yaklaşıyor** | sarı / turuncu |
| Tarihi geçmiş | **Bozuldu** | kırmızı |

Ürün kartlarındaki **raf ömrü çubuğu** zaman geçtikçe **dolar** (`rafOmruDolulukYuzdesi()`):
- 14 gün veya daha fazla kaldıysa çubuk ~%6 doludur.
- Aradaki günlerde `(14 − kalan gün) / 14` oranında dolar.
- Son gün ~%95, tarihi geçince %100 olur.

Çubuğun **rengi de kalan güne göre kademeli değişir**: uzak tarihte yeşil, yaklaştıkça sarı, son günlerde turuncu-kırmızı, tarihi geçince tam kırmızı. Renk, HSL renk modelinde ton değeri 120'den (yeşil) 0'a (kırmızı) kaydırılarak hesaplanır.

### 6.6 Görev Paneli (Ana Sayfa)
- **Mutfak sağlık skoru:** `(taze + yaklaşan × 0,5) / toplam × 100` formülüyle hesaplanır ve halka grafikle gösterilir.
- **Özet sayaçlar:** toplam, taze, yaklaşan ve bozulmuş ürün sayısı.
- **Acil tüketilmesi gerekenler:** tarihi en yakın 8 ürün.
- **Bozulmuş ürünler:** tek tuşla toplu temizleme.
- **Günlük görevler:** bugün yapılan işlemlere göre ilerleyen görevler.
- **Akıllı Şef:** tarihi yaklaşan malzemelere bakarak tarif önerir (örn. dolapta kıyma ve domates varsa "Kıymalı Menemen / Izgara Köfte"). "Başka Tarif" butonuyla diğer öneriler görülebilir.

### 6.7 Oyunlaştırma (XP, Seri, Seviye, Rozet)

| İşlem | Kazanılan XP |
|---|---|
| Güne ilk giriş (günlük seri) | 10 + seri başına 5 (en fazla +40 bonus) |
| Ürün ekleme | 15 |
| Ürünü bozulmadan tüketme | 20 |
| Bozuk ürünü temizleme | 5 |

- Her işlem türü için **günde en fazla 10 kez** XP verilir; ekle-sil yaparak sınırsız XP toplanamaz.
- **Seviye:** ilk seviye 100 XP ister, sonraki her seviye 50 XP daha fazla ister. Unvanlar: Çaylak Aşçı → Mutfak Çırağı → Kiler Ustası → Şef Yardımcısı → Baş Şef → Mutfak Efsanesi.
- **8 rozet:** İlk Kıvılcım, Haftalık Disiplin (7 gün seri), Aylık Efsane (30 gün seri), Kiler Dolduran (10 ürün), Sıfır İsraf (10 ürünü bozulmadan tüketme), Temizlik Görevlisi (5 bozuk ürün temizleme), XP Avcısı (500 XP), XP Ustası (2000 XP).
- Kazanılan XP ekranın üstünde bildirim olarak gösterilir; üst menüde seri (🔥) ve toplam XP (⚡) her zaman görünür.

### 6.8 Arayüz
- **Açık / koyu tema:** sağ üstteki butonla değişir, seçim tarayıcıda hatırlanır.
- **Mobil uyumlu** tasarım.
- **Giriş ekranı vitrini:** sağ panelde oklarla değişen kullanıcı yorumları vardır. Kayıt Ol ekranında bu panele her açılışta rastgele bir içerik daha eklenir (aynısı art arda gelmez):
  1. "Hesabınla neler kazanırsın" özellik listesi,
  2. örnek bir ürünün renkli raf ömrü çubuklu mini kartı,
  3. veritabanından canlı istatistikler (kullanıcı, takip edilen ürün ve toplam XP sayısı).
- **3D simülatör:** Three.js ile ana sayfada döndürülebilen bir buzdolabı / robot sahnesi.

---

## 7. Veritabanı Yapısı

Veritabanı ve tablolar ilk açılışta `db.php` tarafından otomatik oluşturulur.

| Tablo | Önemli sütunlar | Açıklama |
|---|---|---|
| `kullanicilar` | `id`, `kullanici_adi` (benzersiz), `sifre_hash`, `ad_soyad`, `eposta`, `mutfak_adi`, `avatar_renk`, `xp`, `seri`, `en_uzun_seri`, `son_aktif_gun` | Hesap bilgileri ve oyun durumu |
| `urunler` | `id`, `kullanici_id`, `urun`, `kategori`, `miktar`, `skt`, `aciklama` | Buzdolabındaki ürünler |
| `xp_gecmisi` | `kullanici_id`, `tur`, `puan`, `aciklama`, `tarih` | Kazanılan her XP'nin kaydı (rozetler ve günlük limit buradan hesaplanır) |
| `sifre_sifirlama` | `kullanici_id`, `token_hash` (benzersiz), `son_gecerlilik`, `kullanildi` | Şifre sıfırlama bağlantıları |

Diğer tablolar `kullanicilar` tablosuna **yabancı anahtarla** (`ON DELETE CASCADE`) bağlıdır; bir hesap silinirse ona ait ürünler, XP geçmişi ve sıfırlama kayıtları da silinir.

---

## 8. Güvenlik Önlemleri

| Önlem | Nasıl |
|---|---|
| SQL injection | Tüm sorgular PDO **hazır sorgu** (`prepare` / `execute`) ile çalışır |
| XSS | Ekrana yazılan her kullanıcı verisi `guvenli()` → `htmlspecialchars()` ile kaçırılır |
| Şifre saklama | `password_hash()` / `password_verify()` (bcrypt) |
| CSRF | Her POST formunda oturuma bağlı gizli token; eşleşmezse işlem yapılmaz |
| Oturum sabitleme | Giriş, kayıt ve şifre sıfırlamada `session_regenerate_id(true)` |
| Yetki | Her ürün sorgusunda `kullanici_id` kontrol edilir; kimse başkasının ürününü göremez / silemez |
| Silme işlemi | Yalnızca POST ile yapılır, GET bağlantısıyla ürün silinemez |
| Şifre sıfırlama | Hash'lenmiş, süreli, tek kullanımlık token; hesap varlığını belli etmeyen mesaj; istek sınırı; izinli site adresleri |
| Şifre değiştirme | Profilde mevcut şifre doğrulanmadan yeni şifre kaydedilmez |
| Gizli bilgiler | Gmail uygulama şifresi yalnızca `mail_ayar.php` içinde durur, tarayıcıya hiçbir zaman gönderilmez |
| Hata gösterimi | Hata ayrıntıları kullanıcıya gösterilmez, sunucu hata kaydına yazılır |

---

## 9. Klasör Yapısı

```
buzdolabim/
├── index.php                    Görev paneli: sağlık skoru, sayaçlar, görevler, Akıllı Şef, acil/bozuk ürünler
├── urunler.php                  Envanter listesi ve filtreleme (GET)
├── urun_kart.php                Tek bir ürün kartının şablonu (raf ömrü çubuğu dahil)
├── ekle.php                     Ürün ekleme ve düzenleme formu (POST)
├── sil.php                      Ürün tüketme / bozuk temizleme (POST)
├── profil.php                   Profil, şifre değiştirme, seviye ve rozetler, veri sıfırlama
├── giris.php                    Giriş ve kayıt ekranı, değişen vitrin paneli
├── sifremi_unuttum.php          Şifre sıfırlama bağlantısı isteme formu
├── sifre_sifirla.php            E-postadaki bağlantıyla yeni şifre belirleme
├── cikis.php                    Oturumu kapatma
├── sim.php                      3D simülatör bölümü (index.php içine eklenir)
├── header.php                   Ortak üst kısım, menü, XP / seri göstergesi, bildirimler
├── footer.php                   Ortak alt kısım
├── functions.php                Yardımcı fonksiyonlar: durum, doğrulama, filtreleme, sayaçlar, CSRF, giriş kontrolü
├── db.php                       MySQL bağlantısı, tablolar, kullanıcı / ürün / XP / sıfırlama sorguları
├── db_ayar.php                  Veritabanı bağlantı ayarları
├── mailer.php                   Gmail SMTP ile e-posta gönderimi (ek kütüphanesiz)
├── mail_ayar.php                Gmail adresi, uygulama şifresi ve izinli site adresleri  ← yerelde doldurulmalı
├── assets/
│   ├── style.css                Genel stiller
│   ├── hud.css                  Panel / HUD teması, giriş ekranı, kart ve çubuk stilleri
│   ├── tema.js                  Açık / koyu tema
│   ├── kartlar.js               Kart açılış animasyonları
│   └── robot.js                 3D sahne (Three.js)
├── buzdolabim_tanitim.mp4       Tanıtım videosu (3 dk, tüm özellikler)
├── buzdolabim_tanitim_30sn.mp4  Kısa tanıtım videosu (30 sn)
└── README.md
```

---

## 10. Tanıtım Videoları

| Dosya | Süre | İçerik |
|---|---|---|
| `buzdolabim_tanitim_30sn.mp4` | 30 sn | Giriş, görev paneli, raf ömrü çubukları, ürün ekleme ve XP, rozetler, şifre sıfırlama e-postası |
| `buzdolabim_tanitim.mp4` | 3 dk | Bütün özelliklerin altyazılı, adım adım gösterimi |

Videolar sitenin gerçek ekran kaydıdır; bir demo hesapla çekilmiştir. Şifre sıfırlama sahnesindeki gelen kutusu görüntüsü, sitenin gönderdiği e-postanın içeriğini gösteren bir canlandırmadır.

---

## 11. Kullanılan PHP Yapıları

- **Diziler:** `$kategoriler`, `$durumlar`, `ornekUrunler()`, rozet ve tarif tanımları, `SITE_ADRESLERI`
- **Döngüler:** `foreach` ile ürün listeleme, sayaçlar, rozetler, filtreleme
- **Koşullar:** SKT'ye göre durum (`urunDurumu()`), form doğrulama, filtreler, yetki kontrolleri
- **Kullanıcı tanımlı fonksiyonlar:** `urunDogrula()`, `urunleriFiltrele()`, `durumaGoreSay()`, `kalanGun()`, `rafOmruDolulukYuzdesi()`, `guvenli()`, `epostaGonder()` vb.
- **Süper global diziler:** `$_POST` (formlar), `$_GET` (filtreler, sıfırlama tokenı), `$_SESSION` (oturum, CSRF, bildirimler), `$_SERVER`
- **PDO ve hazır sorgular:** veritabanı işlemleri, işlem bloğu (`beginTransaction` / `commit` / `rollBack`)
- **Tarih işlemleri:** `strtotime()`, `date()`, `checkdate()`
- **Güvenlik fonksiyonları:** `password_hash()`, `password_verify()`, `random_bytes()`, `random_int()`, `hash_equals()`, `hash('sha256')`
- **Ağ / akış fonksiyonları:** `stream_socket_client()`, `fwrite()`, `fgets()` (SMTP istemcisi)
- **Dosya dahil etme:** `require`, `require_once` (header, footer, kart şablonu, ayar dosyaları)

---

## 12. Bilinen Eksikler

- `mail_ayar.php` doldurulmadan "Şifremi unuttum" e-posta gönderemez (bkz. bölüm 3).
- Şifre sıfırlama e-postası yalnızca Gmail SMTP ile gönderilebilir.
- İnternet bağlantısı yoksa 3D simülatör (Three.js CDN) yüklenmez; uygulamanın geri kalanı çalışır.
- Rol bazlı yetkilendirme yoktur; tüm kullanıcılar aynı yetkilere sahiptir.
- "Oturumu açık tut" kutucuğu şu an görseldir; oturum süresi PHP'nin varsayılan oturum süresidir.

---

## 13. Yapay Zekâ Kullanımı

**Kullanılan araç:** Claude (Claude Code)

**Kullanım amaçları:**
- Arayüz tasarımı ve CSS düzenlemeleri
- MySQL (PDO) geçişi ve hata ayıklama
- 3D simülatör (Three.js) performans iyileştirmeleri
- XP / rozet sistemi gibi ek özelliklerin geliştirilmesi
- Raf ömrü çubuğunun artan ve renk değiştiren hale getirilmesi
- "Şifremi unuttum" özelliği: Gmail SMTP ile e-posta gönderimi, token tablosu, sıfırlama sayfaları, yayındaki sitede doğru bağlantı adresinin seçilmesi
- Kayıt / giriş / profil güvenlik iyileştirmeleri (şifre tekrarı, mevcut şifre kontrolü, zorunlu e-posta, e-posta ile giriş)
- Giriş ekranındaki değişen vitrin paneli
- Tanıtım videolarının (3 dk ve 30 sn) hazırlanması
- README hazırlanması

**Örnek istemler:**
> “Verileri sıfırla dediğimiz zaman günlük seri ile XP de sıfırlansın mı diye sorsun, şimdilik test aşamasında olduğu için.”

> “Raf ömrü barı azalan değil de artan şekilde olsun; tarih yaklaştıkça yeşil, sonra sarı, sonra kırmızı olsun, tarihi geçene kadar.”

> “Profilde mevcut şifreyi girmeden yeni şifre oluşturulmasın.”

---

## 14. Kendi Katkılarım

Bu projenin fikri, kapsamı ve yönü bana aittir. Gıda israfı günlük hayatta sık karşılaştığım bir sorun olduğu için konuyu kendim seçtim ve uygulamanın "sadece bir liste" değil, insanları her gün geri getiren bir **mutfak asistanı** olmasını hedefledim.

- **Altyapı:** `giris.php`, `ekle.php` ve `urunler.php` sayfalarının temel yapısını ben geliştirdim; sayfaların nasıl birbirine bağlanacağını, hangi verilerin tutulacağını ve kullanıcının uygulamada nasıl dolaşacağını ben planladım.
- **Özellik tasarımı:** Hangi özelliklerin ekleneceğine ve nasıl davranacağına ben karar verdim. Örneğin raf ömrü çubuğunun zamanla dolması ve renginin yeşilden sarıya, sonra kırmızıya geçmesi; kayıtta şifre tekrarı istenmesi; e-postası olmayan hesapların e-posta girmeye zorlanması; profilde şifre değiştirmek için mevcut şifrenin sorulması; e-posta ile de giriş yapılabilmesi ve giriş ekranındaki boşluğun her açılışta değişen içerikle doldurulması benim isteklerim ve kararlarımdır.
- **Güvenlik bakışı:** Kullanıcı deneyimini düşünürken güvenlik açıklarını da fark edip düzeltilmesini istedim (örneğin mevcut şifre sorulmadan şifre değiştirilebilmesi ve Gmail uygulama şifresinin nasıl korunması gerektiği).
- **Test ve kalite kontrol:** Her değişikliği tarayıcıda kendim denedim; ekran görüntüleriyle hataları ve görsel bozuklukları (örneğin kayıt ekranındaki boşluk, çubuk renklerinin yanlış görünmesi) tespit edip bildirdim ve düzeltmelerin doğru çalıştığını kontrol ettim.
- **Yayına alma:** Projeyi https://buzdolabim.free.nf adresinde yayına aldım. Yayındaki sitede şifre sıfırlama bağlantısının `localhost` adresine gittiğini kendim fark edip bildirdim; düzeltmeden sonra dosyaları hostinge yükleyip yayındaki sitede de çalıştığını doğruladım.
- **Kurulum ve entegrasyon:** Şifre sıfırlama e-postaları için Gmail hesabını ve uygulama şifresini kendim oluşturup projeye bağladım; e-posta gönderiminin çalıştığını kendi gelen kutumda doğruladım.

Yapay zekâyı bir yardımcı geliştirici gibi kullandım: ne yapılacağını ben belirledim, ortaya çıkan sonucu ben denedim ve beğenmediğim her noktayı yeniden yaptırdım. Bu süreçte PHP'de oturum yönetimi, veritabanı ilişkileri, form doğrulama, CSRF ve şifre güvenliği, SMTP ile e-posta gönderimi ve bir projeyi yayına alma gibi konularda pratik deneyim kazandım.
