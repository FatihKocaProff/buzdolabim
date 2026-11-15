<?php

require 'functions.php';
girisKontrol();

$sayfaBasligi = 'Görev Paneli';
$kullaniciId  = aktifKullaniciId();
$urunler      = urunleriGetir($kullaniciId);

$seciliDurum = $seciliKategori = $aranan = '';

$toplamSayi   = count($urunler);
$tazeSayi     = durumaGoreSay($urunler, 'Taze');
$yaklasanSayi = durumaGoreSay($urunler, 'Yaklaşıyor');
$bozukSayi    = durumaGoreSay($urunler, 'Bozuldu');

$tazeOran     = $toplamSayi > 0 ? round(($tazeSayi / $toplamSayi) * 100) : 0;
$yaklasanOran = $toplamSayi > 0 ? round(($yaklasanSayi / $toplamSayi) * 100) : 0;
$bozukOran    = $toplamSayi > 0 ? max(0, 100 - $tazeOran - $yaklasanOran) : 0;
$saglikSkoru  = $toplamSayi > 0 ? round((($tazeSayi + ($yaklasanSayi * 0.5)) / $toplamSayi) * 100) : 100;

$acilListe  = acilUrunler($urunler, 8);
$bozukListe = bozukUrunler($urunler);

$bugunXp      = bugunXpSayilari($kullaniciId);
$gorev1Tamam  = $bugunXp['urun_tuket'] > 0;
$gorev2Tamam  = $bugunXp['bozuk_temizle'] > 0 || ($toplamSayi > 0 && $bozukSayi === 0);
$gorev3Tamam  = $bugunXp['urun_ekle'] > 0;
$tamamSayisi  = ($gorev1Tamam ? 1 : 0) + ($gorev2Tamam ? 1 : 0) + ($gorev3Tamam ? 1 : 0);
$gorevYuzde   = round(($tamamSayisi / 3) * 100);

$sefTarifleri = akilliSefTarifleri($urunler);
$ilkTarif     = $sefTarifleri[0] ?? null;

require 'header.php';
?>

<section class="komuta-ust-bar">
    <div class="komuta-ust-sol">
        <div class="hero-ust-etiket">
            <span class="canli-nokta"></span>
            <span>Akıllı Mutfak Komuta Paneli · <?= guvenli($oturumKullanici['mutfak_adi'] ?? 'Merkez Mutfak') ?></span>
        </div>
        <h1>Buzdolabını keşfet, görevleri tamamla &amp; sıfır israfla yönet.</h1>
        <p>Gerçek zamanlı SKT telemetrisi, günlük XP görevleri ve dolaptaki malzemelere göre çalışan Akıllı Şef bir arada.</p>
    </div>

    <div class="komuta-ust-aksiyonlar">
        <button type="button" class="dugme komuta-ekle-tetik" id="hizliEkleTetik" aria-expanded="false">
            <span>➕ Hızlı Ürün Ekle (+<?= XP_URUN_EKLE ?> XP)</span>
            <i id="hizliEkleOk">▾</i>
        </button>
        <a href="#aksiyonBolumu" class="dugme dugme-ikincil" data-sekme-ac="acil">
            ⏳ Acil Ürünler (<?= $yaklasanSayi ?>)
        </a>
        <?php if ($bozukSayi > 0): ?>
            <a href="#aksiyonBolumu" class="dugme dugme-ikincil komuta-bozuk-kisayol" data-sekme-ac="bozuk">
                🚨 Bozuklar (<?= $bozukSayi ?>)
            </a>
        <?php endif; ?>
        <a href="urunler.php" class="dugme dugme-ikincil">
            📦 Envanter (<?= $toplamSayi ?>)
        </a>
    </div>
</section>

<section class="hizli-ekle-cekmece" id="hizliEkleCekmece" hidden>
    <div class="hizli-ekle-ust">
        <div>
            <strong>⚡ Hızlı Ürün Ekleme Çekmecesi</strong>
            <span>Sayfadan ayrılmadan yeni ürün ekleyin veya hazır demo şablonlarıyla sistemi canlı test edin.</span>
        </div>
        <div class="hizli-sablon-grup">
            <span class="hizli-sablon-etiket">Canlı Demo Şablonu:</span>
            <button type="button" class="hizli-sablon-chip" data-ad="Dana Kıyma" data-kat="Et & Tavuk" data-mik="500 g" data-gun="1" data-not="Akşama köfte / menemen yapılacak.">🥩 Kıyma (+1g Acil)</button>
            <button type="button" class="hizli-sablon-chip" data-ad="Günlük Süt" data-kat="Süt Ürünü" data-mik="1 litre" data-gun="2" data-not="Kahvaltıda tüketilmeli.">🥛 Süt (+2g Yaklaşan)</button>
            <button type="button" class="hizli-sablon-chip" data-ad="Salkım Domates" data-kat="Sebze & Meyve" data-mik="1 kg" data-gun="6" data-not="Sebzelikte taze saklanıyor.">🍅 Domates (+6g Taze)</button>
            <button type="button" class="hizli-sablon-chip chip-test-bozuk" data-ad="Açık Krema" data-kat="Süt Ürünü" data-mik="200 ml" data-gun="-2" data-not="Süresi geçmiş test ürünü.">🚨 Bozuk Ürün Testi (-2g)</button>
        </div>
    </div>

    <form method="post" action="ekle.php" class="hizli-ekle-form">
        <?= csrfAlani() ?>
        <input type="hidden" name="donus" value="index.php">

        <label class="hizli-alan">
            <span>Ürün Adı *</span>
            <input type="text" name="urun" id="hizliUrun" placeholder="Örn: Taze Kaşar Peyniri" maxlength="60" required>
        </label>

        <label class="hizli-alan">
            <span>Kategori *</span>
            <select name="kategori" id="hizliKategori" required>
                <?php foreach ($kategoriler as $kat): ?>
                    <option value="<?= guvenli($kat) ?>"><?= kategoriIkonu($kat) ?> <?= guvenli($kat) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="hizli-alan">
            <span>Miktar *</span>
            <input type="text" name="miktar" id="hizliMiktar" placeholder="Örn: 400 g, 1 litre" value="1 paket" required>
        </label>

        <div class="hizli-alan">
            <div class="hizli-skt-baslik">
                <span>Son Kullanma Tarihi *</span>
                <div class="hizli-gun-butonlar">
                    <button type="button" data-gun="1" title="Yarın bitiyor (Acil)">+1g</button>
                    <button type="button" data-gun="3" title="3 gün sonra (Yaklaşıyor)">+3g</button>
                    <button type="button" data-gun="7" class="aktif" title="1 hafta sonra (Taze)">+7g</button>
                    <button type="button" data-gun="-1" title="Dün bitti (Bozuk testi)">-1g</button>
                </div>
            </div>
            <input type="date" name="skt" id="hizliSkt" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
        </div>

        <label class="hizli-alan">
            <span>Kısa Not (İsteğe Bağlı)</span>
            <input type="text" name="aciklama" id="hizliNot" placeholder="Örn: Orta rafta saklanıyor" maxlength="200">
        </label>

        <div class="hizli-alan hizli-alan-buton">
            <button type="submit" class="dugme hizli-kaydet-buton">+ Kaydet (+<?= XP_URUN_EKLE ?> XP)</button>
        </div>
    </form>
</section>

<section class="bento-izgara" aria-label="Mutfak Durumu, Günlük Görevler ve Akıllı Şef">

    <article class="bento-kart bento-saglik">
        <div class="bento-kart-ust">
            <span class="bento-numara">01 · SAĞLIK TELEMETRİSİ</span>
            <a href="urunler.php" class="bento-ust-link">Tüm Envanter →</a>
        </div>

        <div class="bento-saglik-govde">
            <a href="urunler.php" class="skor-halka-kutu" title="Tüm envanteri gör" style="color:inherit;text-decoration:none;cursor:pointer">
                <?php
                $halkaDilimleri = [
                    ['sinif' => 'halka-taze',     'oran' => $tazeOran],
                    ['sinif' => 'halka-yaklasan', 'oran' => $yaklasanOran],
                    ['sinif' => 'halka-bozuk',    'oran' => $bozukOran],
                ];
                $halkaBaslangic = 0;
                ?>
                <svg viewBox="0 0 36 36" class="dairesel-grafik" role="img" aria-label="Taze %<?= $tazeOran ?>, Yaklaşan %<?= $yaklasanOran ?>, Bozuk %<?= $bozukOran ?>">
                    <path class="halka-arka" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <?php foreach ($halkaDilimleri as $dilim): ?>
                        <?php if ($dilim['oran'] > 0): ?>
                            <path class="halka-dilim <?= $dilim['sinif'] ?>"
                                  stroke-dasharray="<?= $dilim['oran'] ?>, <?= 100 - $dilim['oran'] ?>"
                                  stroke-dashoffset="<?= -$halkaBaslangic ?>"
                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <?php endif; ?>
                        <?php $halkaBaslangic += $dilim['oran']; ?>
                    <?php endforeach; ?>
                </svg>
                <div class="skor-ic-metin">
                    <strong>%<?= $saglikSkoru ?></strong>
                    <span>Tazelik</span>
                </div>
            </a>

            <div class="bento-durum-sayaclar">
                <a href="<?= guvenli(filtreLinki('Taze', '')) ?>" class="bento-durum-satir satir-yesil" title="Taze ürünleri gör">
                    <span class="durum-sol"><i class="renk-nokta nokta-yesil"></i> Taze</span>
                    <strong><?= $tazeSayi ?> <small>(%<?= $tazeOran ?>)</small></strong>
                </a>
                <a href="#aksiyonBolumu" data-sekme-ac="acil" class="bento-durum-satir satir-turuncu" title="Tarihi yaklaşan ürünleri aşağıda aç">
                    <span class="durum-sol"><i class="renk-nokta nokta-turuncu"></i> Yaklaşan</span>
                    <strong><?= $yaklasanSayi ?> <small>(%<?= $yaklasanOran ?>)</small></strong>
                </a>
                <a href="#aksiyonBolumu" data-sekme-ac="bozuk" class="bento-durum-satir satir-kirmizi" title="Bozulmuş ürünleri aşağıda aç">
                    <span class="durum-sol"><i class="renk-nokta nokta-kirmizi"></i> Bozulmuş</span>
                    <strong><?= $bozukSayi ?> <small>(%<?= $bozukOran ?>)</small></strong>
                </a>
            </div>
        </div>

        <div class="bento-dagilim-alt">
            <div class="bento-dagilim-etiketler">
                <span>Envanter Dağılımı (<?= $toplamSayi ?> Ürün)</span>
                <span><?= $yaklasanSayi > 0 ? '⚠️ ' . $yaklasanSayi . ' acil ürün' : '✨ Kritik uyarı yok' ?></span>
            </div>
            <div class="dagilim-cubuk">
                <a href="<?= guvenli(filtreLinki('Taze', '')) ?>" class="dilim dilim-yesil" style="width: <?= $tazeOran ?>%" title="Taze: <?= $tazeSayi ?> ürün"></a>
                <a href="#aksiyonBolumu" data-sekme-ac="acil" class="dilim dilim-turuncu" style="width: <?= $yaklasanOran ?>%" title="Yaklaşan: <?= $yaklasanSayi ?> ürün"></a>
                <a href="#aksiyonBolumu" data-sekme-ac="bozuk" class="dilim dilim-kirmizi" style="width: <?= $bozukOran ?>%" title="Bozuk: <?= $bozukSayi ?> ürün"></a>
            </div>
        </div>
    </article>

    <article class="bento-kart bento-gorevler">
        <div class="bento-kart-ust">
            <span class="bento-numara">02 · GÜNLÜK MUTFAK GÖREVLERİ</span>
            <span class="gorev-durum-hap <?= $tamamSayisi === 3 ? 'hepsi-tamam' : '' ?>">
                🎯 <?= $tamamSayisi ?>/3 Görev · Bugün +<?= (int) $bugunXp['bugun_xp'] ?> XP
            </span>
        </div>

        <div class="gorev-ilerleme-iz" title="Günlük görev ilerlemesi: %<?= $gorevYuzde ?>">
            <div class="gorev-ilerleme-dolgu" style="width: <?= $gorevYuzde ?>%"></div>
        </div>

        <ul class="gorev-listesi">
            <li class="gorev-satir <?= $gorev1Tamam ? 'gorev-tamam' : '' ?>">
                <span class="gorev-ikon"><?= $gorev1Tamam ? '✅' : '⏳' ?></span>
                <div class="gorev-metin">
                    <strong>Tarihi yaklaşan <?= $yaklasanSayi > 0 ? $yaklasanSayi . ' üründen birini' : 'bir ürünü' ?> ziyan etmeden tüket</strong>
                    <small><?= $gorev1Tamam ? 'Harika! Bugün ' . $bugunXp['urun_tuket'] . ' ürün ziyan olmadan tüketildi.' : 'Aşağıdaki listeden "Tükettim" butonuna basarak görevi tamamla.' ?></small>
                </div>
                <?php if ($gorev1Tamam): ?>
                    <span class="gorev-odul odul-alindi">+<?= XP_URUN_TUKET ?> XP ✓</span>
                <?php else: ?>
                    <a href="#aksiyonBolumu" data-sekme-ac="acil" class="gorev-aksiyon-link">+<?= XP_URUN_TUKET ?> XP →</a>
                <?php endif; ?>
            </li>

            <li class="gorev-satir <?= $gorev2Tamam ? 'gorev-tamam' : '' ?>">
                <span class="gorev-ikon"><?= $gorev2Tamam ? '✅' : '🧹' ?></span>
                <div class="gorev-metin">
                    <strong>Dolaptaki bozulmuş ürünleri temizle</strong>
                    <small>
                        <?php if ($bozukSayi === 0): ?>
                            Dolapta süresi geçmiş ürün kalmadı, mutfak hijyeni tam puan!
                        <?php else: ?>
                            Dolapta temizlenmeyi bekleyen <?= $bozukSayi ?> bozulmuş ürün var.
                        <?php endif; ?>
                    </small>
                </div>
                <?php if ($gorev2Tamam): ?>
                    <span class="gorev-odul odul-alindi">+<?= XP_BOZUK_TEMIZLE ?> XP ✓</span>
                <?php else: ?>
                    <a href="#aksiyonBolumu" data-sekme-ac="bozuk" class="gorev-aksiyon-link">+<?= XP_BOZUK_TEMIZLE ?> XP →</a>
                <?php endif; ?>
            </li>

            <li class="gorev-satir <?= $gorev3Tamam ? 'gorev-tamam' : '' ?>">
                <span class="gorev-ikon"><?= $gorev3Tamam ? '✅' : '➕' ?></span>
                <div class="gorev-metin">
                    <strong>Bugün aldığın yeni bir ürünü dolaba kaydet</strong>
                    <small><?= $gorev3Tamam ? 'Bugün envantere ' . $bugunXp['urun_ekle'] . ' yeni ürün kaydettin.' : 'Üstteki Hızlı Ekle çekmecesinden yeni bir ürün ekle.' ?></small>
                </div>
                <?php if ($gorev3Tamam): ?>
                    <span class="gorev-odul odul-alindi">+<?= XP_URUN_EKLE ?> XP ✓</span>
                <?php else: ?>
                    <button type="button" class="gorev-aksiyon-link" id="gorevHizliEkleBtn">+<?= XP_URUN_EKLE ?> XP →</button>
                <?php endif; ?>
            </li>
        </ul>
    </article>

    <article class="bento-kart bento-sef">
        <div class="bento-kart-ust">
            <span class="bento-numara">03 · 👨‍🍳 AKILLI ŞEF · BUGÜN NE PİŞİRSEM?</span>
            <?php if (count($sefTarifleri) > 1): ?>
                <button type="button" class="sef-degistir-buton" id="sefDegistirButon" title="Dolaptaki malzemelerle başka bir tarif öner">
                    🎲 Başka Tarif (<span id="sefSayac">1/<?= count($sefTarifleri) ?></span>)
                </button>
            <?php endif; ?>
        </div>

        <?php if ($ilkTarif === null): ?>
            <div class="sef-bos">
                <span class="sef-bos-ikon">🍳</span>
                <strong>Dolapta taze malzeme kalmadı</strong>
                <p>Yeni ürün eklediğinde Akıllı Şef son kullanma tarihi yaklaşan malzemelere göre otomatik yemek tarifi çıkarır.</p>
            </div>
        <?php else: ?>
            <div class="sef-icerik" id="sefIcerik">
                <div class="sef-baslik-satir">
                    <span class="sef-yemek-ikon" id="sefIkon"><?= guvenli($ilkTarif['ikon']) ?></span>
                    <div>
                        <span class="sef-etiket-hap" id="sefEtiket"><?= guvenli($ilkTarif['etiket']) ?> · ⏱️ <?= guvenli($ilkTarif['sure']) ?></span>
                        <h3 id="sefBaslik"><?= guvenli($ilkTarif['baslik']) ?></h3>
                    </div>
                </div>

                <p class="sef-oneri-metin" id="sefAciklama">
                    <?= guvenli($ilkTarif['aciklama']) ?>
                </p>

                <div class="sef-malzeme-chipler" id="sefMalzemeler">
                    <?php foreach ($ilkTarif['malzemeler'] as $m): ?>
                        <?php $mDurum = urunDurumu($m['skt']); ?>
                        <span class="sef-malzeme-hap <?= $mDurum === 'Yaklaşıyor' ? 'malzeme-acil' : '' ?>">
                            <?= kategoriIkonu($m['kategori']) ?>
                            <b><?= guvenli($m['urun']) ?></b>
                            <small>(<?= kalanGunMetni($m['skt']) ?>)</small>
                        </span>
                    <?php endforeach; ?>
                </div>

                <div class="sef-alt-aksiyon">
                    <form method="post" action="sil.php" class="sef-tuket-form">
                        <?= csrfAlani() ?>
                        <input type="hidden" name="donus" value="index.php">
                        <input type="hidden" name="id" id="sefOncelikUrunId" value="<?= (int) $ilkTarif['oncelik_urun_id'] ?>">
                        <button type="submit" class="dugme sef-pisir-buton" title="Öncelikli malzemeyi kullanarak pişirdim ve tükettim (+<?= XP_URUN_TUKET ?> XP)">
                            😋 <span id="sefOncelikUrunAd"><?= guvenli($ilkTarif['oncelik_urun_ad']) ?></span> Kullan &amp; Pişir (+<?= XP_URUN_TUKET ?> XP)
                        </button>
                    </form>
                </div>
            </div>
            <script type="application/json" id="sefTarifVeri"><?= json_encode($sefTarifleri, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
        <?php endif; ?>
    </article>

</section>

<?php require 'sim.php'; ?>

<section class="acil-bolum" id="aksiyonBolumu">
    <div class="aksiyon-ust-bar">
        <div class="aksiyon-sekmeler" role="tablist" aria-label="Mutfak Aksiyon Sekmeleri">
            <button type="button" class="aksiyon-sekme aktif" data-aksiyon-sekme="acil" role="tab" aria-selected="true">
                <span>⏳ Acil Tüketilmesi Gerekenler</span>
                <span class="aksiyon-rozet rozet-turuncu-hap"><?= count($acilListe) ?></span>
            </button>
            <button type="button" class="aksiyon-sekme <?= $bozukSayi > 0 ? 'sekme-uyari-var' : '' ?>" data-aksiyon-sekme="bozuk" role="tab" aria-selected="false">
                <span>🚨 Süresi Geçen / Temizlenecekler</span>
                <span class="aksiyon-rozet rozet-kirmizi-hap"><?= count($bozukListe) ?></span>
            </button>
        </div>

        <div class="aksiyon-sag-grup">
            <?php if ($bozukSayi > 0): ?>
                <form method="post" action="sil.php" id="topluBozukForm" hidden>
                    <?= csrfAlani() ?>
                    <input type="hidden" name="islem" value="bozuklari_temizle">
                    <input type="hidden" name="donus" value="index.php">
                    <button type="submit" class="dugme komuta-toplu-temizle">
                        🧹 Tüm Bozukları Temizle (<?= $bozukSayi ?> Ürün · +<?= $bozukSayi * XP_BOZUK_TEMIZLE ?> XP)
                    </button>
                </form>
            <?php endif; ?>
            <a href="urunler.php" class="hero-aksiyon-buton">Tüm Envanteri Gör (<?= $toplamSayi ?>) →</a>
        </div>
    </div>

    <div class="hizli-kategori-bar" id="hizliKategoriBar">
        <button type="button" class="chip chip-aktif" data-hizli-kat="">✨ Tüm Kategoriler</button>
        <?php foreach ($kategoriler as $kat): ?>
            <button type="button" class="chip" data-hizli-kat="<?= guvenli($kat) ?>">
                <span><?= kategoriIkonu($kat) ?></span>
                <span><?= guvenli($kat) ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="aksiyon-panel" id="panelAcil" data-panel="acil">
        <?php if (count($acilListe) === 0): ?>
            <div class="bos">
                <div class="bos-ikon">🧊</div>
                <h3><?= $toplamSayi === 0 ? 'Buzdolabı şu an boş' : 'Tüketilecek sağlam ürün kalmadı' ?></h3>
                <p>Üstteki Hızlı Ürün Ekle çekmecesinden yeni ürün ekleyerek takibe devam edebilirsin.</p>
                <button type="button" class="dugme" data-hizli-ekle-ac>+ Hızlı Ürün Ekle</button>
            </div>
        <?php else: ?>
            <div class="vitrin-izgara" id="vitrinIzgaraAcil">
                <?php foreach ($acilListe as $sira => $urun): ?>
                    <?php require 'urun_kart.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="aksiyon-panel" id="panelBozuk" data-panel="bozuk" hidden>
        <?php if (count($bozukListe) === 0): ?>
            <div class="bos">
                <div class="bos-ikon">✨</div>
                <h3>Harika! Dolapta bozulmuş ürün yok</h3>
                <p>Bütün ürünlerin son kullanma tarihi kontrol altında. Mutfak hijyen görevi tamamlandı!</p>
            </div>
        <?php else: ?>
            <div class="vitrin-izgara" id="vitrinIzgaraBozuk">
                <?php foreach ($bozukListe as $sira => $urun): ?>
                    <?php require 'urun_kart.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bos" id="hizliFiltreBos" hidden>
        <div class="bos-ikon">🔍</div>
        <h3>Bu kategoride ürün bulunamadı</h3>
        <p>Seçili sekmede bu kategoriye ait kayıt yok.</p>
        <button type="button" class="dugme dugme-ikincil" id="hizliFiltreSifirla">Tüm Kategorileri Göster</button>
    </div>
</section>

<script src="assets/kartlar.js?v=<?= filemtime(__DIR__ . '/assets/kartlar.js') ?>"></script>
<script>
(function () {
    var cekmece = document.getElementById('hizliEkleCekmece');
    var tetik = document.getElementById('hizliEkleTetik');
    var ok = document.getElementById('hizliEkleOk');
    var urunInp = document.getElementById('hizliUrun');
    var katSel = document.getElementById('hizliKategori');
    var mikInp = document.getElementById('hizliMiktar');
    var sktInp = document.getElementById('hizliSkt');
    var notInp = document.getElementById('hizliNot');

    function cekmeceDurum(acikMi) {
        if (!cekmece || !tetik) return;
        cekmece.hidden = !acikMi;
        tetik.setAttribute('aria-expanded', acikMi ? 'true' : 'false');
        if (ok) ok.textContent = acikMi ? '▴' : '▾';
        if (acikMi && urunInp) {
            setTimeout(function () { urunInp.focus(); }, 60);
        }
    }

    if (tetik) {
        tetik.addEventListener('click', function () {
            cekmeceDurum(cekmece.hidden);
        });
    }

    var gorevEkleBtn = document.getElementById('gorevHizliEkleBtn');
    if (gorevEkleBtn) {
        gorevEkleBtn.addEventListener('click', function () {
            cekmeceDurum(true);
            cekmece.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }

    document.querySelectorAll('[data-hizli-ekle-ac]').forEach(function (b) {
        b.addEventListener('click', function () {
            cekmeceDurum(true);
            cekmece.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

    function gunTarihi(gunFarki) {
        var d = new Date();
        d.setDate(d.getDate() + parseInt(gunFarki, 10));
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    document.querySelectorAll('.hizli-gun-butonlar button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.hizli-gun-butonlar button').forEach(function (b) { b.classList.remove('aktif'); });
            btn.classList.add('aktif');
            if (sktInp) sktInp.value = gunTarihi(btn.getAttribute('data-gun'));
        });
    });

    document.querySelectorAll('.hizli-sablon-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            if (urunInp) urunInp.value = chip.getAttribute('data-ad') || '';
            if (katSel) katSel.value = chip.getAttribute('data-kat') || 'Süt Ürünü';
            if (mikInp) mikInp.value = chip.getAttribute('data-mik') || '1 adet';
            if (notInp) notInp.value = chip.getAttribute('data-not') || '';
            if (sktInp) sktInp.value = gunTarihi(chip.getAttribute('data-gun') || 3);
            if (urunInp) urunInp.focus();
        });
    });

    var sefVeriEl = document.getElementById('sefTarifVeri');
    var sefDegistirBtn = document.getElementById('sefDegistirButon');
    if (sefVeriEl && sefDegistirBtn) {
        try {
            var tarifler = JSON.parse(sefVeriEl.textContent || '[]');
            var aktifIndeks = 0;
            sefDegistirBtn.addEventListener('click', function () {
                if (tarifler.length < 2) return;
                aktifIndeks = (aktifIndeks + 1) % tarifler.length;
                var t = tarifler[aktifIndeks];
                document.getElementById('sefSayac').textContent = (aktifIndeks + 1) + '/' + tarifler.length;
                document.getElementById('sefIkon').textContent = t.ikon;
                document.getElementById('sefEtiket').textContent = t.etiket + ' · ⏱️ ' + t.sure;
                document.getElementById('sefBaslik').textContent = t.baslik;
                document.getElementById('sefAciklama').textContent = t.aciklama;
                document.getElementById('sefOncelikUrunId').value = t.oncelik_urun_id;
                document.getElementById('sefOncelikUrunAd').textContent = t.oncelik_urun_ad;

                var malKutu = document.getElementById('sefMalzemeler');
                if (malKutu && Array.isArray(t.malzemeler)) {
                    malKutu.innerHTML = '';
                    t.malzemeler.forEach(function (m) {
                        var span = document.createElement('span');
                        span.className = 'sef-malzeme-hap';
                        var b = document.createElement('b');
                        b.textContent = m.urun;
                        var sm = document.createElement('small');
                        sm.textContent = '(' + m.skt + ')';
                        span.appendChild(b);
                        span.appendChild(document.createTextNode(' '));
                        span.appendChild(sm);
                        malKutu.appendChild(span);
                    });
                }
            });
        } catch (e) {}
    }

    var aktifSekme = 'acil';
    var aktifKategori = '';
    var panelAcil = document.getElementById('panelAcil');
    var panelBozuk = document.getElementById('panelBozuk');
    var topluBozukForm = document.getElementById('topluBozukForm');
    var filtreBos = document.getElementById('hizliFiltreBos');

    function kartlariSuz() {
        var aktifPanel = aktifSekme === 'bozuk' ? panelBozuk : panelAcil;
        if (!aktifPanel) return;
        var kartlar = aktifPanel.querySelectorAll('.vitrin-kart');
        if (kartlar.length === 0) {
            if (filtreBos) filtreBos.hidden = true;
            return;
        }
        var gorunen = 0;
        kartlar.forEach(function (k) {
            var kat = k.getAttribute('data-kategori') || '';
            var uydu = aktifKategori === '' || kat === aktifKategori;
            k.style.display = uydu ? '' : 'none';
            if (uydu) {
                gorunen++;
                k.classList.add('kart-acik');
            }
        });
        if (filtreBos) {
            filtreBos.hidden = gorunen > 0;
        }
    }

    function sekmeDegistir(hedef) {
        aktifSekme = hedef === 'bozuk' ? 'bozuk' : 'acil';
        document.querySelectorAll('[data-aksiyon-sekme]').forEach(function (btn) {
            var secili = btn.getAttribute('data-aksiyon-sekme') === aktifSekme;
            btn.classList.toggle('aktif', secili);
            btn.setAttribute('aria-selected', secili ? 'true' : 'false');
        });
        if (panelAcil) panelAcil.hidden = aktifSekme !== 'acil';
        if (panelBozuk) panelBozuk.hidden = aktifSekme !== 'bozuk';
        if (topluBozukForm) topluBozukForm.hidden = aktifSekme !== 'bozuk';
        kartlariSuz();
    }

    document.querySelectorAll('[data-aksiyon-sekme]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            sekmeDegistir(btn.getAttribute('data-aksiyon-sekme'));
        });
    });

    document.querySelectorAll('[data-sekme-ac]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var hedef = link.getAttribute('data-sekme-ac');
            sekmeDegistir(hedef);
            var bolum = document.getElementById('aksiyonBolumu');
            if (bolum) {
                e.preventDefault();
                bolum.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    document.querySelectorAll('[data-hizli-kat]').forEach(function (chip) {
        chip.addEventListener('click', function () {
            aktifKategori = chip.getAttribute('data-hizli-kat') || '';
            document.querySelectorAll('[data-hizli-kat]').forEach(function (c) {
                c.classList.toggle('chip-aktif', c === chip);
            });
            kartlariSuz();
        });
    });

    var sifirlaBtn = document.getElementById('hizliFiltreSifirla');
    if (sifirlaBtn) {
        sifirlaBtn.addEventListener('click', function () {
            var ilkChip = document.querySelector('[data-hizli-kat=""]');
            if (ilkChip) ilkChip.click();
        });
    }
})();
</script>

<?php require 'footer.php'; ?>
