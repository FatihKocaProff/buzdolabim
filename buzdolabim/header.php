<?php
$oturumKullanici = aktifKullanici();
$mevcutArama     = trim($_GET['ara'] ?? '');
$mevcutDurum     = $_GET['durum'] ?? '';
$mevcutKategori  = $_GET['kategori'] ?? '';

$oyun = $oturumKullanici !== null ? oyunDurumu(aktifKullaniciId()) : ['xp' => 0, 'seri' => 0, 'en_uzun_seri' => 0];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= guvenli($sayfaBasligi) ?> — Buzdolabım</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet">
    <script>
        (function () {
            var tema = localStorage.getItem('tema_hud');
            if (tema !== 'acik' && tema !== 'koyu') {
                tema = 'acik';
            }
            document.documentElement.setAttribute('data-tema', tema);
        })();
    </script>
    <link rel="stylesheet" href="assets/style.css?v=<?= filemtime(__DIR__ . '/assets/style.css') ?>">
    <link rel="stylesheet" href="assets/hud.css?v=<?= filemtime(__DIR__ . '/assets/hud.css') ?>">
    <script src="assets/tema.js?v=<?= filemtime(__DIR__ . '/assets/tema.js') ?>" defer></script>
</head>
<body>
<header class="ust">
    <div class="kapsayici ust-ic">
        <div class="ust-sol">
            <a href="index.php" class="logo">
                <span class="logo-kutu">🤖</span>
                <span class="logo-yazi">buzdolabım<span class="logo-nokta">.tech</span></span>
            </a>
            <?php if ($oturumKullanici !== null): ?>
                <div class="ust-sekmeler">
                    <a href="index.php" class="ust-sekme <?= ($sayfaBasligi ?? '') === 'Görev Paneli' ? 'aktif' : '' ?>">🎯 Görev Paneli</a>
                    <a href="urunler.php" class="ust-sekme <?= ($sayfaBasligi ?? '') === 'Envanter' ? 'aktif' : '' ?>">📦 Envanter</a>
                    <a href="ekle.php" class="ust-sekme <?= ($sayfaBasligi ?? '') === 'Ürün Ekle' ? 'aktif' : '' ?>">➕ Ürün Ekle</a>
                    <a href="profil.php" class="ust-sekme <?= ($sayfaBasligi ?? '') === 'Profil Düzenle' ? 'aktif' : '' ?>">🛡️ Profil</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($oturumKullanici !== null): ?>
            <div class="ust-orta">
                <form class="arama-hap" method="get" action="urunler.php">
                    <?php if ($mevcutDurum !== ''): ?>
                        <input type="hidden" name="durum" value="<?= guvenli($mevcutDurum) ?>">
                    <?php endif; ?>
                    <?php if ($mevcutKategori !== ''): ?>
                        <input type="hidden" name="kategori" value="<?= guvenli($mevcutKategori) ?>">
                    <?php endif; ?>
                    <svg class="arama-ikon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input
                        type="search"
                        name="ara"
                        id="ustAramaInput"
                        class="arama-girdi"
                        value="<?= guvenli($mevcutArama) ?>"
                        placeholder="Envanterde ürün veya görev ara..."
                        autocomplete="off"
                    >
                    <?php if ($mevcutArama !== ''): ?>
                        <a href="urunler.php" class="arama-temizle" title="Aramayı temizle">✕</a>
                    <?php endif; ?>
                    <button type="submit" class="arama-buton">ARA</button>
                </form>
            </div>
        <?php endif; ?>

        <nav class="ust-sag">
            <?php if ($oturumKullanici !== null): ?>
                <div class="oyun-cubugu">
                    <span class="oyun-rozet rozet-seri" title="Günlük seri: <?= $oyun['seri'] ?> gün · En uzun seri: <?= $oyun['en_uzun_seri'] ?> gün. Her gün girerek seriyi koru!">🔥 <?= $oyun['seri'] ?></span>
                    <span class="oyun-rozet rozet-xp" title="Toplam XP · Ürün ekle +15 · Bozulmadan tüket +20 · Günlük giriş +10…50">⚡ <?= number_format($oyun['xp'], 0, ',', '.') ?> XP</span>
                </div>
            <?php endif; ?>

            <button type="button" class="tema-dugme" id="temaDugme" aria-label="Temayı değiştir" title="Temayı değiştir">☀️</button>

            <?php if ($oturumKullanici !== null): ?>
                <a href="profil.php" class="kullanici-rozet" title="Profili Düzenle">
                    <span class="kullanici-avatar" style="background: <?= guvenli($oturumKullanici['avatar_renk']) ?>; color: #fff;"><?= guvenli(mb_strtoupper(mb_substr($oturumKullanici['ad_soyad'], 0, 1))) ?></span>
                    <span class="kullanici-ad"><?= guvenli($oturumKullanici['ad_soyad']) ?></span>
                </a>
                <a href="cikis.php?csrf=<?= csrfToken() ?>" class="cikis-dugme" title="Oturumu Kapat">ÇIKIŞ</a>
            <?php else: ?>
                <a href="giris.php" class="dugme">GİRİŞ YAP</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="kapsayici">
<?php
$mesaj = mesajAl();
if ($mesaj !== null):
?>
    <div class="uyari uyari-<?= guvenli($mesaj['tur']) ?>"<?= $mesaj['tur'] === 'basari' ? ' data-otomatik-kapat' : '' ?>><?= guvenli($mesaj['metin']) ?></div>
<?php endif; ?>
<?php foreach (xpBildirimleriAl() as $xpMetni): ?>
    <div class="uyari uyari-basari xp-bildirim" data-otomatik-kapat><?= guvenli($xpMetni) ?></div>
<?php endforeach; ?>
    <script>
        (function () {
            document.querySelectorAll('[data-otomatik-kapat]').forEach(function (kutu) {
            setTimeout(function () {
                kutu.style.overflow = 'hidden';
                kutu.style.maxHeight = kutu.offsetHeight + 'px';
                kutu.style.transition = 'opacity .5s ease, max-height .5s ease, margin .5s ease, padding .5s ease';
                requestAnimationFrame(function () {
                    kutu.style.opacity = '0';
                    kutu.style.maxHeight = '0';
                    kutu.style.marginTop = kutu.style.marginBottom = '0';
                    kutu.style.paddingTop = kutu.style.paddingBottom = '0';
                });
                setTimeout(function () { kutu.remove(); }, 550);
            }, 5000);
            });
        })();
    </script>
