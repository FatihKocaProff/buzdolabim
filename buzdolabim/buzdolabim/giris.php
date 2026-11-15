<?php

require 'functions.php';

if (aktifKullanici() !== null) {
    header('Location: index.php');
    exit;
}

$sayfaBasligi = 'Giriş Yap';
$hatalar = [];
$aktifSekme = ($_GET['mod'] ?? 'giris') === 'kayit' ? 'kayit' : 'giris';
$kullaniciAdi = '';
$adSoyad = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $islem = $_POST['islem'] ?? 'giris';

    if ($islem === 'kayit') {
        $aktifSekme   = 'kayit';
        $adSoyad      = trim($_POST['ad_soyad'] ?? '');
        $kullaniciAdi = trim($_POST['kullanici_adi'] ?? '');
        $sifre        = $_POST['sifre'] ?? '';

        if (!mb_check_encoding($adSoyad . $kullaniciAdi . $sifre, 'UTF-8')) {
            $hatalar[] = 'Girilen bilgilerde geçersiz karakter var.';
        } elseif ($adSoyad === '' || mb_strlen($adSoyad) < 2) {
            $hatalar[] = 'Ad Soyad en az 2 karakter olmalıdır.';
        }
        if ($kullaniciAdi === '' || mb_strlen($kullaniciAdi) < 3) {
            $hatalar[] = 'Kullanıcı adı en az 3 karakter olmalıdır.';
        }
        if (mb_strlen($sifre) < 4) {
            $hatalar[] = 'Şifre en az 4 karakter olmalıdır.';
        }

        if (mb_strlen($kullaniciAdi) > 50) {
            $hatalar[] = 'Kullanıcı adı en fazla 50 karakter olabilir.';
        }

        if (count($hatalar) === 0 && kullaniciBul($kullaniciAdi) !== null) {
            $hatalar[] = 'Bu kullanıcı adı zaten kullanılıyor.';
        }

        if (count($hatalar) === 0) {
            kullaniciEkle($kullaniciAdi, $sifre, $adSoyad, $kullaniciAdi . '@buzdolabim.local');
            session_regenerate_id(true);
            $_SESSION['kullanici_hafta2'] = oturumKullanicisi(kullaniciBul($kullaniciAdi));
            mesajKaydet('basari', 'Hoş geldiniz, ' . $adSoyad . '! Hesabınız oluşturuldu ve giriş yapıldı.');
            header('Location: index.php');
            exit;
        }
    } else {
        $aktifSekme   = 'giris';
        $kullaniciAdi = trim($_POST['kullanici_adi'] ?? '');
        $sifre        = $_POST['sifre'] ?? '';

        if ($kullaniciAdi === '' || $sifre === '') {
            $hatalar[] = 'Lütfen kullanıcı adı ve şifrenizi girin.';
        } else {
            $bulunan = kullaniciDogrula($kullaniciAdi, $sifre);

            if ($bulunan !== null) {
                session_regenerate_id(true);
                $_SESSION['kullanici_hafta2'] = oturumKullanicisi($bulunan);
                mesajKaydet('basari', 'Tekrar hoş geldiniz, ' . $bulunan['ad_soyad'] . '!');
                header('Location: index.php');
                exit;
            } else {
                $hatalar[] = 'Kullanıcı adı veya şifre hatalı.';
            }
        }
    }
}

require 'header.php';
?>

<section class="giris-split-sarmal">
    <div class="giris-split-panel">

        <div class="giris-split-sol">
            <div class="giris-marka-ikon" aria-hidden="true">
                <span class="cizgi-1"></span>
                <span class="cizgi-2"></span>
                <span class="nokta-vurgu"></span>
            </div>

            <div class="giris-baslik-kutu">
                <h1><?= $aktifSekme === 'kayit' ? 'Yeni Hesap Oluştur' : 'Buzdolabım’a Hoş Geldiniz' ?></h1>
                <p>Mutfak envanterinizi yönetmek için hesap bilgilerinizi girin</p>
            </div>

            <div class="giris-mod-sekmeler">
                <a href="giris.php?mod=giris" class="giris-mod-sekme <?= $aktifSekme === 'giris' ? 'aktif' : '' ?>">GİRİŞ YAP</a>
                <a href="giris.php?mod=kayit" class="giris-mod-sekme <?= $aktifSekme === 'kayit' ? 'aktif' : '' ?>">KAYIT OL</a>
            </div>

            <?php if (count($hatalar) > 0): ?>
                <div class="uyari uyari-hata giris-hata-kutu">
                    <ul>
                        <?php foreach ($hatalar as $hata): ?>
                            <li><?= guvenli($hata) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($aktifSekme === 'kayit'): ?>
                <form method="post" action="giris.php?mod=kayit" class="giris-modern-form" novalidate>
                    <?= csrfAlani() ?>
                    <input type="hidden" name="islem" value="kayit">

                    <label class="giris-hap-etiket">
                        <span>Ad Soyad</span>
                        <input type="text" name="ad_soyad" class="giris-hap-girdi" value="<?= guvenli($adSoyad) ?>" placeholder="örn. Fatih Koca" required>
                    </label>

                    <label class="giris-hap-etiket">
                        <span>Kullanıcı Adı</span>
                        <input type="text" name="kullanici_adi" class="giris-hap-girdi" value="<?= guvenli($kullaniciAdi) ?>" placeholder="örn. fatih" required>
                    </label>

                    <label class="giris-hap-etiket">
                        <span>Şifre</span>
                        <input type="password" name="sifre" class="giris-hap-girdi" placeholder="••••••••" required>
                    </label>

                    <div class="giris-ara-satir">
                        <label class="giris-hatirla">
                            <input type="checkbox" checked>
                            <span>Oturumu açık tut</span>
                        </label>
                        <a href="giris.php?mod=giris" class="giris-unuttum-link">Zaten hesabım var</a>
                    </div>

                    <button type="submit" class="giris-hap-buton">Kayıt Ol ve Başla →</button>
                </form>
            <?php else: ?>
                <form method="post" action="giris.php" class="giris-modern-form" id="girisForm" novalidate>
                    <?= csrfAlani() ?>
                    <input type="hidden" name="islem" value="giris">

                    <label class="giris-hap-etiket">
                        <span>Kullanıcı Adı</span>
                        <input type="text" name="kullanici_adi" id="girisKullanici" class="giris-hap-girdi" value="<?= guvenli($kullaniciAdi) ?>" placeholder="admin veya ogrenci" required autofocus>
                    </label>

                    <label class="giris-hap-etiket">
                        <span>Şifre</span>
                        <input type="password" name="sifre" id="girisSifre" class="giris-hap-girdi" placeholder="••••••••" required>
                    </label>

                    <div class="giris-ara-satir">
                        <label class="giris-hatirla">
                            <input type="checkbox" checked>
                            <span>Oturumu açık tut</span>
                        </label>
                    </div>

                    <button type="submit" class="giris-hap-buton">Giriş Yap →</button>
                </form>
            <?php endif; ?>

            <p class="giris-alt-gecis" style="margin-top:22px;text-align:center;font-size:.95rem">
                <?php if ($aktifSekme === 'kayit'): ?>
                    Zaten hesabın var mı? <a href="giris.php?mod=giris" style="font-weight:700;color:inherit;text-decoration:underline">Giriş yap</a>
                <?php else: ?>
                    Hesabın yok mu? <a href="giris.php?mod=kayit" style="font-weight:700;color:inherit;text-decoration:underline">Yeni hesap oluştur</a>
                <?php endif; ?>
            </p>
        </div>

        <div class="giris-split-sag">
            <div class="giris-vitrin-ic-kart">
                <h2>Akıllı Mutfak<br>Sistemi Ne Sağlar?</h2>

                <div class="giris-tirnak-isareti" aria-hidden="true">“</div>

                <p class="giris-vitrin-alinti" id="vitrinAlintiMetin">
                    “Buzdolabındaki ürünleri ve son kullanma tarihlerini takip etmek artık her zamankinden daha kolay. Tarihi yaklaşan ürünleri öncelikli tüketerek israfı sıfıra indirin.”
                </p>

                <div class="giris-vitrin-yazar">
                    <strong id="vitrinYazarAd">Fatih Koca</strong>
                    <span id="vitrinYazarUnvan">Merkez Mutfak · Akıllı Envanter Yöneticisi</span>
                </div>

                <div class="giris-vitrin-oklar">
                    <button type="button" class="vitrin-ok-buton ok-turuncu" id="vitrinOncekiBtn" aria-label="Önceki görüş">←</button>
                    <button type="button" class="vitrin-ok-buton ok-koyu" id="vitrinSonrakiBtn" aria-label="Sonraki görüş">→</button>
                </div>

                <svg class="giris-yildiz-vektor" viewBox="0 0 200 200" fill="none" aria-hidden="true">
                    <g stroke="currentColor" stroke-width="1.3">
                        <polygon points="100,8 112,82 186,50 124,96 192,125 116,114 152,184 100,126 48,184 84,114 8,125 76,96 14,50 88,82" />
                    </g>
                </svg>
            </div>

            <div class="giris-yuzen-alt-kart">
                <div class="yuzen-kart-icerik">
                    <h3>Sıfır israf mutfak yönetimine hemen başlayın</h3>
                    <p>Ürünlerinizi ekleyin, raf ömrü çubuklarını canlı izleyin ve mutfak sağlık skorunuzu yükseltin.</p>
                </div>
                <div class="yuzen-kart-avatarlar">
                    <span class="mini-avatar av-1">FK</span>
                    <span class="mini-avatar av-2">ÖK</span>
                    <span class="mini-avatar av-3">BZ</span>
                    <span class="mini-avatar av-art">+2</span>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
(function () {
    var kullaniciInput = document.getElementById('girisKullanici');
    var sifreInput = document.getElementById('girisSifre');
    var girisForm = document.getElementById('girisForm');
    var alintilar = [
        {
            metin: '“Buzdolabındaki ürünleri ve son kullanma tarihlerini takip etmek artık her zamankinden daha kolay. Tarihi yaklaşan ürünleri öncelikli tüketerek israfı sıfıra indirin.”',
            ad: 'Fatih Koca',
            unvan: 'Merkez Mutfak · Akıllı Envanter Yöneticisi'
        },
        {
            metin: '“Canlı tazelik çubukları ve kategori filtreleri sayesinde dolapta neyin ne zaman biteceğini tek bakışta görebiliyorum. Ev ekonomisi için harika bir kontrol paneli.”',
            ad: 'Öğrenci Kullanıcı',
            unvan: 'Öğrenci Evi Mutfağı · Günlük Takip'
        },
        {
            metin: '“BZ-7 soğuk zincir telemetrisi ve otomatik SKT hesaplama motoru ile süt ürünlerinden et grubuna kadar tüm raflar gerçek zamanlı izlenir.”',
            ad: 'BZ-7 Optik Modül',
            unvan: 'Buzdolabım.tech · Otonom Mutfak Asistanı'
        }
    ];

    var aktifIndis = 0;
    var metinEl = document.getElementById('vitrinAlintiMetin');
    var adEl = document.getElementById('vitrinYazarAd');
    var unvanEl = document.getElementById('vitrinYazarUnvan');
    var oncekiBtn = document.getElementById('vitrinOncekiBtn');
    var sonrakiBtn = document.getElementById('vitrinSonrakiBtn');

    function alintiGoster(indis) {
        if (!metinEl || !adEl || !unvanEl) return;
        aktifIndis = (indis + alintilar.length) % alintilar.length;
        metinEl.textContent = alintilar[aktifIndis].metin;
        adEl.textContent = alintilar[aktifIndis].ad;
        unvanEl.textContent = alintilar[aktifIndis].unvan;
    }

    if (oncekiBtn) {
        oncekiBtn.addEventListener('click', function () {
            alintiGoster(aktifIndis - 1);
        });
    }
    if (sonrakiBtn) {
        sonrakiBtn.addEventListener('click', function () {
            alintiGoster(aktifIndis + 1);
        });
    }
})();
</script>

<?php require 'footer.php'; ?>
