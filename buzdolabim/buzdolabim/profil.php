<?php

require 'functions.php';
girisKontrol();

$sayfaBasligi = 'Profil Düzenle';
$kullanici = aktifKullanici();
$urunler = urunleriGetir(aktifKullaniciId());
$hatalar = [];

$renkSecenekleri = [
    '#141414' => 'Obsidyen Siyah',
    '#16a34a' => 'Zümrüt Yeşili',
    '#4f46e5' => 'İndigo Mavi',
    '#e11d48' => 'Mercan Kırmızı',
    '#d97706' => 'Kehribar Turuncu',
    '#0284c7' => 'Okyanus Mavisi',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $islem = $_POST['islem'] ?? 'profil_guncelle';

    if ($islem === 'verileri_sifirla') {
        ornekUrunleriYukle(aktifKullaniciId());
        $mesaj = '16 örnek ürün başarıyla sıfırlandı ve buzdolabına geri yüklendi.';
        if (($_POST['xp_sifirla'] ?? '0') === '1') {
            $id = aktifKullaniciId();
            db()->prepare('UPDATE kullanicilar SET xp = 0, seri = 0, en_uzun_seri = 0, son_aktif_gun = CURDATE() WHERE id = ?')->execute([$id]);
            db()->prepare('DELETE FROM xp_gecmisi WHERE kullanici_id = ?')->execute([$id]);
            $mesaj .= ' XP ve günlük seri de sıfırlandı.';
        }
        mesajKaydet('basari', $mesaj);
        header('Location: profil.php');
        exit;
    }

    $eskiKullaniciAdi = $kullanici['kullanici_adi'];
    $yeniAdSoyad      = trim($_POST['ad_soyad'] ?? '');
    $yeniKullaniciAdi = trim($_POST['kullanici_adi'] ?? '');
    $yeniEposta       = trim($_POST['eposta'] ?? '');
    $yeniMutfakAdi    = trim($_POST['mutfak_adi'] ?? '');
    $yeniAvatarRenk   = $_POST['avatar_renk'] ?? '#141414';
    $yeniSifre        = $_POST['yeni_sifre'] ?? '';
    $yeniSifreTekrar  = $_POST['yeni_sifre_tekrar'] ?? '';

    if (!mb_check_encoding($yeniAdSoyad . $yeniKullaniciAdi . $yeniEposta . $yeniMutfakAdi . $yeniSifre, 'UTF-8')) {
        $hatalar[] = 'Girilen bilgilerde geçersiz karakter var.';
    } elseif ($yeniAdSoyad === '' || mb_strlen($yeniAdSoyad) < 2) {
        $hatalar[] = 'Ad Soyad en az 2 karakter olmalıdır.';
    }
    if ($yeniKullaniciAdi === '' || mb_strlen($yeniKullaniciAdi) < 3) {
        $hatalar[] = 'Kullanıcı adı en az 3 karakter olmalıdır.';
    }
    if ($yeniEposta !== '' && !filter_var($yeniEposta, FILTER_VALIDATE_EMAIL)) {
        $hatalar[] = 'Lütfen geçerli bir e-posta adresi girin.';
    }
    if (!array_key_exists($yeniAvatarRenk, $renkSecenekleri)) {
        $yeniAvatarRenk = '#141414';
    }

    if (mb_strlen($yeniKullaniciAdi) > 50) {
        $hatalar[] = 'Kullanıcı adı en fazla 50 karakter olabilir.';
    }
    if (mb_strtolower($yeniKullaniciAdi) !== mb_strtolower($eskiKullaniciAdi) && kullaniciBul($yeniKullaniciAdi) !== null) {
        $hatalar[] = 'Bu kullanıcı adı başka bir hesap tarafından kullanılıyor.';
    }

    $dbKayit = kullaniciBul($eskiKullaniciAdi);
    if ($dbKayit === null) {
        $hatalar[] = 'Hesabınız veritabanında bulunamadı. Lütfen çıkış yapıp tekrar giriş yapın.';
    }

    if ($yeniSifre !== '' || $yeniSifreTekrar !== '') {
        if (mb_strlen($yeniSifre) < 4) {
            $hatalar[] = 'Yeni şifre en az 4 karakter olmalıdır.';
        } elseif ($yeniSifre !== $yeniSifreTekrar) {
            $hatalar[] = 'Yeni şifre ve şifre tekrarı birbiriyle eşleşmiyor.';
        }
    }

    if (count($hatalar) === 0) {
        kullaniciGuncelle(
            $dbKayit['id'],
            $yeniKullaniciAdi,
            $yeniAdSoyad,
            $yeniEposta,
            $yeniMutfakAdi !== '' ? $yeniMutfakAdi : 'Merkez Mutfak',
            $yeniAvatarRenk,
            $yeniSifre
        );

        $_SESSION['kullanici_hafta2'] = oturumKullanicisi(kullaniciBul($yeniKullaniciAdi));

        mesajKaydet('basari', 'Profil bilgileriniz başarıyla güncellendi.');
        header('Location: profil.php');
        exit;
    } else {
        $kullanici['ad_soyad']      = $yeniAdSoyad;
        $kullanici['kullanici_adi'] = $yeniKullaniciAdi;
        $kullanici['eposta']        = $yeniEposta;
        $kullanici['mutfak_adi']    = $yeniMutfakAdi;
        $kullanici['avatar_renk']   = $yeniAvatarRenk;
    }
}

$oyun    = oyunDurumu(aktifKullaniciId());
$seviye  = seviyeBilgisi($oyun['xp']);
$rozetler = rozetListesi(aktifKullaniciId(), $oyun);
$acikRozet = count(array_filter($rozetler, fn($r) => $r['acik']));

require 'header.php';
?>

<section class="mobbin-hero">
    <div class="hero-sol">
        <div class="hero-ust-etiket">
            <span class="canli-nokta"></span>
            <span>Hesap &amp; Mutfak Ayarları</span>
        </div>
        <h1>Profilini Özelleştir</h1>
        <p>Kişisel bilgilerinizi, mutfak adınızı, avatar renginizi ve güvenlik ayarlarınızı buradan yönetin.</p>
    </div>
</section>

<?php if (count($hatalar) > 0): ?>
    <div class="uyari uyari-hata">
        <strong>Profil güncellenemedi:</strong>
        <ul>
            <?php foreach ($hatalar as $hata): ?>
                <li><?= guvenli($hata) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="profil-izgara">
    <aside class="profil-yan">
        <div class="onizleme-cerceve">
            <div class="onizleme-ekran profil-ozet-kart">
                <div class="profil-buyuk-avatar" id="onizlemeAvatar" style="background: <?= guvenli($kullanici['avatar_renk']) ?>;">
                    <?= guvenli(mb_strtoupper(mb_substr($kullanici['ad_soyad'], 0, 1))) ?>
                </div>
                <h2 class="profil-isim"><?= guvenli($kullanici['ad_soyad']) ?></h2>
                <span class="profil-kullanici-adi">@<?= guvenli($kullanici['kullanici_adi']) ?></span>
                <span class="profil-mutfak-etiket">🏠 <?= guvenli($kullanici['mutfak_adi']) ?></span>

                <div class="profil-istatistikler">
                    <div>
                        <strong><?= count($urunler) ?></strong>
                        <span>Toplam</span>
                    </div>
                    <div>
                        <strong style="color: var(--yesil);"><?= durumaGoreSay($urunler, 'Taze') ?></strong>
                        <span>Taze</span>
                    </div>
                    <div>
                        <strong style="color: var(--turuncu);"><?= durumaGoreSay($urunler, 'Yaklaşıyor') ?></strong>
                        <span>Yaklaşan</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="onizleme-cerceve">
            <div class="onizleme-ekran odul-kart">
                <div class="odul-ust">
                    <div class="odul-seviye-rozet">Sv. <?= $seviye['seviye'] ?></div>
                    <div>
                        <strong><?= guvenli($seviye['unvan']) ?></strong>
                        <small><?= $oyun['xp'] ?> XP · 🔥 <?= $oyun['seri'] ?> gün seri (en uzun <?= $oyun['en_uzun_seri'] ?>)</small>
                    </div>
                </div>
                <div class="odul-iz" title="Sonraki seviye: <?= $seviye['ilerleme'] ?>/<?= $seviye['gereken'] ?> XP">
                    <div class="odul-dolgu" style="width: <?= $seviye['yuzde'] ?>%"></div>
                </div>
                <small class="odul-alt">Sonraki seviyeye <?= $seviye['gereken'] - $seviye['ilerleme'] ?> XP kaldı</small>

                <div class="odul-baslik">Rozetler <span><?= $acikRozet ?>/<?= count($rozetler) ?></span></div>
                <div class="odul-rozetler">
                    <?php foreach ($rozetler as $r): ?>
                        <div class="odul-rozet <?= $r['acik'] ? 'acik' : 'kilitli' ?>" title="<?= guvenli($r['aciklama']) ?> (<?= $r['deger'] ?>/<?= $r['hedef'] ?>)">
                            <span class="odul-ikon"><?= $r['acik'] ? $r['ikon'] : '🔒' ?></span>
                            <span class="odul-ad"><?= guvenli($r['ad']) ?></span>
                            <span class="odul-durum"><?= $r['acik'] ? 'Kazanıldı' : $r['deger'] . '/' . $r['hedef'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <style>
            .odul-kart{display:flex;flex-direction:column;gap:10px;text-align:left}
            .odul-ust{display:flex;align-items:center;gap:12px}
            .odul-ust strong{display:block;font-size:1.05rem}
            .odul-ust small,.odul-alt{opacity:.7;font-size:.8rem}
            .odul-seviye-rozet{flex:none;width:52px;height:52px;border-radius:50%;display:grid;place-items:center;font-weight:800;font-size:.85rem;background:var(--turuncu,#f97316);color:#fff}
            .odul-iz{height:8px;border-radius:99px;background:rgba(127,127,127,.2);overflow:hidden}
            .odul-dolgu{height:100%;background:var(--yesil,#16a34a);border-radius:99px}
            .odul-baslik{margin-top:6px;font-weight:700;font-size:.85rem;letter-spacing:.06em;text-transform:uppercase;display:flex;justify-content:space-between}
            .odul-baslik span{opacity:.6}
            .odul-rozetler{display:grid;grid-template-columns:repeat(auto-fill,minmax(92px,1fr));gap:8px}
            .odul-rozet{border:1px solid rgba(127,127,127,.25);border-radius:12px;padding:10px 6px;text-align:center;display:flex;flex-direction:column;gap:3px}
            .odul-rozet.kilitli{opacity:.45;filter:grayscale(1)}
            .odul-rozet.acik{border-color:var(--yesil,#16a34a)}
            .odul-ikon{font-size:1.5rem}
            .odul-ad{font-size:.75rem;font-weight:700;line-height:1.2}
            .odul-durum{font-size:.7rem;opacity:.7}
        </style>

        <div class="profil-sifirla-kutu">
            <div>
                <strong>Örnek Verileri Yenile</strong>
                <p>Silinen veya değişen ürünleri ilk 16 örnek veriye geri döndürür.</p>
            </div>
            <form method="post" action="profil.php" onsubmit="if (!confirm('Mevcut ürün listesi 16 örnek ürünle sıfırlansın mı?')) return false; this.xp_sifirla.value = confirm('Günlük seri ve XP de sıfırlansın mı?') ? '1' : '0'; return true;">
                <input type="hidden" name="xp_sifirla" value="0">
                <?= csrfAlani() ?>
                <input type="hidden" name="islem" value="verileri_sifirla">
                <button type="submit" class="dugme dugme-ikincil">Verileri Sıfırla</button>
            </form>
        </div>
    </aside>

    <div class="onizleme-cerceve">
        <form method="post" action="profil.php" class="onizleme-ekran profil-form" novalidate>
            <?= csrfAlani() ?>
            <input type="hidden" name="islem" value="profil_guncelle">

            <h3 class="bolum-baslik">Kişisel Bilgiler</h3>

            <div class="satir">
                <label>
                    Ad Soyad *
                    <input type="text" name="ad_soyad" value="<?= guvenli($kullanici['ad_soyad']) ?>" maxlength="50" required>
                </label>

                <label>
                    Kullanıcı Adı *
                    <input type="text" name="kullanici_adi" value="<?= guvenli($kullanici['kullanici_adi']) ?>" maxlength="30" required>
                </label>
            </div>

            <div class="satir">
                <label>
                    E-posta Adresi
                    <input type="email" name="eposta" value="<?= guvenli($kullanici['eposta']) ?>" placeholder="ornek@eposta.com">
                </label>

                <label>
                    Mutfak / Ev Adı
                    <input type="text" name="mutfak_adi" value="<?= guvenli($kullanici['mutfak_adi']) ?>" maxlength="40" placeholder="örn. Merkez Mutfak">
                </label>
            </div>

            <div class="renk-secim-alani">
                <span class="etiket-metin">Avatar Rengi</span>
                <div class="renk-paleti">
                    <?php foreach ($renkSecenekleri as $kod => $isim): ?>
                        <label class="renk-secenek" title="<?= guvenli($isim) ?>">
                            <input
                                type="radio"
                                name="avatar_renk"
                                value="<?= guvenli($kod) ?>"
                                <?= $kullanici['avatar_renk'] === $kod ? 'checked' : '' ?>
                                onchange="document.getElementById('onizlemeAvatar').style.background = this.value;"
                            >
                            <span class="renk-daire" style="background: <?= guvenli($kod) ?>;"></span>
                            <span class="renk-isim"><?= guvenli($isim) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <hr class="bolum-ayirici">

            <h3 class="bolum-baslik">Şifre Değiştir <small>(Değiştirmek istemiyorsanız boş bırakın)</small></h3>

            <div class="satir">
                <label>
                    Yeni Şifre
                    <input type="password" name="yeni_sifre" placeholder="En az 4 karakter">
                </label>

                <label>
                    Yeni Şifre (Tekrar)
                    <input type="password" name="yeni_sifre_tekrar" placeholder="Yeni şifreyi tekrar yazın">
                </label>
            </div>

            <div class="dugmeler">
                <button type="submit" class="dugme">Değişiklikleri Kaydet</button>
                <a href="index.php" class="dugme dugme-ikincil">Ana Sayfaya Dön</a>
            </div>
        </form>
    </div>
</div>

<?php require 'footer.php'; ?>
