<?php

require 'functions.php';
girisKontrol();

$sayfaBasligi = 'Envanter';
$urunler = urunleriGetir(aktifKullaniciId());

$seciliDurum    = $_GET['durum'] ?? '';
$seciliKategori = $_GET['kategori'] ?? '';
$aranan         = trim($_GET['ara'] ?? '');

if (!in_array($seciliDurum, $durumlar)) {
    $seciliDurum = '';
}
if (!in_array($seciliKategori, $kategoriler)) {
    $seciliKategori = '';
}

$listelenecek = tariheGoreSirala(urunleriFiltrele($urunler, $seciliDurum, $seciliKategori, $aranan));
$toplamSayi   = count($urunler);
$tazeSayi     = durumaGoreSay($urunler, 'Taze');
$yaklasanSayi = durumaGoreSay($urunler, 'Yaklaşıyor');
$bozukSayi    = durumaGoreSay($urunler, 'Bozuldu');

$tazeOran     = $toplamSayi > 0 ? round(($tazeSayi / $toplamSayi) * 100) : 0;
$yaklasanOran = $toplamSayi > 0 ? round(($yaklasanSayi / $toplamSayi) * 100) : 0;
$bozukOran    = $toplamSayi > 0 ? max(0, 100 - $tazeOran - $yaklasanOran) : 0;

require 'header.php';
?>

<section class="mobbin-hero envanter-baslik">
    <div class="hero-sol">
        <div class="hero-ust-etiket">
            <span class="canli-nokta"></span>
            <span>Envanter · <?= guvenli($oturumKullanici['mutfak_adi'] ?? 'Merkez Mutfak') ?></span>
        </div>
        <h1>📦 Buzdolabındaki Ürünler</h1>
        <p>Toplam <strong><?= $toplamSayi ?></strong> ürün. Duruma veya kategoriye göre süz, ara, düzenle ya da tükettiklerini çıkar.</p>
    </div>
    <a href="ekle.php" class="dugme envanter-ekle">+ Ürün Ekle</a>
</section>

<section class="kpi-izgara">
    <a href="<?= guvenli(filtreLinki('', $seciliKategori, $aranan)) ?>" class="kpi-kart kpi-toplam <?= $seciliDurum === '' ? 'kpi-secili' : '' ?>">
        <div class="kpi-ust">
            <span class="kpi-ikon">📦</span>
            <span class="kpi-etiket-hap">Tüm Envanter</span>
        </div>
        <div class="kpi-sayi"><?= $toplamSayi ?></div>
        <div class="kpi-alt">Toplam Kayıtlı Ürün</div>
    </a>

    <a href="<?= guvenli(filtreLinki('Taze', $seciliKategori, $aranan)) ?>" class="kpi-kart kpi-yesil <?= $seciliDurum === 'Taze' ? 'kpi-secili' : '' ?>">
        <div class="kpi-ust">
            <span class="kpi-ikon">🌿</span>
            <span class="kpi-etiket-hap">%<?= $tazeOran ?> Oran</span>
        </div>
        <div class="kpi-sayi"><?= $tazeSayi ?></div>
        <div class="kpi-alt">Taze &amp; Güvenli Ürün</div>
    </a>

    <a href="<?= guvenli(filtreLinki('Yaklaşıyor', $seciliKategori, $aranan)) ?>" class="kpi-kart kpi-turuncu <?= $seciliDurum === 'Yaklaşıyor' ? 'kpi-secili' : '' ?>">
        <div class="kpi-ust">
            <span class="kpi-ikon">⏳</span>
            <span class="kpi-etiket-hap">≤ <?= UYARI_GUN ?> Gün</span>
        </div>
        <div class="kpi-sayi"><?= $yaklasanSayi ?></div>
        <div class="kpi-alt">Tarihi Yaklaşan Ürün</div>
    </a>

    <a href="<?= guvenli(filtreLinki('Bozuldu', $seciliKategori, $aranan)) ?>" class="kpi-kart kpi-kirmizi <?= $seciliDurum === 'Bozuldu' ? 'kpi-secili' : '' ?>">
        <div class="kpi-ust">
            <span class="kpi-ikon">🚨</span>
            <span class="kpi-etiket-hap">%<?= $bozukOran ?> Kayıp</span>
        </div>
        <div class="kpi-sayi"><?= $bozukSayi ?></div>
        <div class="kpi-alt">Süresi Geçmiş / Bozuk</div>
    </a>
</section>

<div class="mobbin-filtre-bar" id="urunBolumu">
    <div class="durum-sekmeleri" role="tablist">
        <a class="durum-sekme <?= $seciliDurum === '' ? 'aktif' : '' ?>" href="<?= guvenli(filtreLinki('', $seciliKategori, $aranan)) ?>">
            <span>Tümü</span>
            <span class="sekme-rozet"><?= $toplamSayi ?></span>
        </a>
        <?php foreach ($durumlar as $durum): ?>
            <a class="durum-sekme <?= $seciliDurum === $durum ? 'aktif' : '' ?>" href="<?= guvenli(filtreLinki($durum, $seciliKategori, $aranan)) ?>">
                <span class="nokta nokta-<?= durumSinifi($durum) ?>"></span>
                <span><?= guvenli($durum) ?></span>
                <span class="sekme-rozet"><?= durumaGoreSay($urunler, $durum) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="kategori-chipler">
        <a class="chip <?= $seciliKategori === '' ? 'chip-aktif' : '' ?>" href="<?= guvenli(filtreLinki($seciliDurum, '', $aranan)) ?>">
            ✨ Tüm Kategoriler
        </a>
        <?php foreach ($kategoriler as $kategori): ?>
            <a class="chip <?= $seciliKategori === $kategori ? 'chip-aktif' : '' ?>" href="<?= guvenli(filtreLinki($seciliDurum, $kategori, $aranan)) ?>">
                <span><?= kategoriIkonu($kategori) ?></span>
                <span><?= guvenli($kategori) ?></span>
            </a>
        <?php endforeach; ?>

        <?php if ($seciliDurum !== '' || $seciliKategori !== '' || $aranan !== ''): ?>
            <a href="urunler.php" class="chip chip-sifirla">✕ Filtreyi Temizle</a>
        <?php endif; ?>
    </div>
</div>

<div class="sonuc-ust-bilgi">
    <span>
        Gösterilen: <strong id="gorunenUrunSayisi"><?= count($listelenecek) ?></strong> ürün
        <?php if ($aranan !== ''): ?>
            (<strong>"<?= guvenli($aranan) ?>"</strong> araması için)
        <?php endif; ?>
    </span>
    <span>📅 Sıralama: Son kullanma tarihi (En yakın → En uzak)</span>
</div>

<?php if (count($listelenecek) === 0): ?>
    <div class="bos">
        <?php if ($toplamSayi === 0): ?>
            <div class="bos-ikon">🧊</div>
            <h3>Buzdolabı şu an boş</h3>
            <p>Takibe başlamak için ilk ürününüzü ekleyin.</p>
            <a href="ekle.php" class="dugme">+ İlk Ürünü Ekle</a>
        <?php else: ?>
            <div class="bos-ikon">🔍</div>
            <h3>Eşleşen ürün bulunamadı</h3>
            <p>Seçtiğiniz arama, durum veya kategori filtresine uygun kayıt yok.</p>
            <a href="urunler.php" class="dugme dugme-ikincil">Tüm Ürünleri Göster</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="vitrin-izgara" id="vitrinIzgara">
        <?php foreach ($listelenecek as $sira => $urun): ?>
            <?php require 'urun_kart.php'; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script src="assets/kartlar.js?v=<?= filemtime(__DIR__ . '/assets/kartlar.js') ?>"></script>

<?php require 'footer.php'; ?>
