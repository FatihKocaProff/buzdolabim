<?php

$durum          = urunDurumu($urun['skt']);
$durumCss       = durumSinifi($durum);
$katCss         = kategoriSinifi($urun['kategori']);
$yuzde          = rafOmruDolulukYuzdesi($urun['skt']);
$dolguTon       = max(0, min(120, (int) round(120 * (100 - $yuzde) / 94)));
$kartAramaMetni = mb_strtolower($urun['urun'] . ' ' . $urun['kategori'] . ' ' . $urun['miktar'] . ' ' . $urun['aciklama'], 'UTF-8');
$sutunGecikme   = ($sira % 3) * 210;
$mevcutSayfa    = basename($_SERVER['SCRIPT_NAME'] ?? 'urunler.php');
?>
<article
    class="vitrin-kart sayfa-acilir-kart <?= $durum === 'Bozuldu' ? 'kart-bozuk' : '' ?>"
    data-arama="<?= guvenli($kartAramaMetni) ?>"
    data-kategori="<?= guvenli($urun['kategori']) ?>"
    data-durum="<?= guvenli($durum) ?>"
    data-sira="<?= (int) $sira ?>"
    style="--kart-gecikme: <?= $sutunGecikme ?>ms;"
>
    <div class="onizleme-cerceve <?= $katCss ?>">
        <div class="onizleme-ekran">
            <div class="kart-renk-serit <?= $katCss ?>"></div>

            <div class="ekran-ust">
                <span class="kategori-simge <?= $katCss ?>"><?= kategoriIkonu($urun['kategori']) ?></span>
                <span class="rozet <?= $durumCss ?>">
                    <span class="nokta nokta-<?= $durumCss ?>"></span>
                    <?= guvenli($durum) ?>
                </span>
            </div>

            <div class="ekran-orta">
                <div class="urun-baslik-satir">
                    <h2 class="urun-baslik"><?= guvenli($urun['urun']) ?></h2>
                    <span class="miktar-hap"><?= guvenli($urun['miktar']) ?></span>
                </div>
                <?php if ($urun['aciklama'] !== ''): ?>
                    <p class="urun-not">💬 <?= guvenli($urun['aciklama']) ?></p>
                <?php else: ?>
                    <p class="urun-not urun-not-bos">Ekstra saklama notu yok.</p>
                <?php endif; ?>
            </div>

            <div class="tazelik-bar-kapsayici">
                <div class="tazelik-bar-ust">
                    <span>Raf Ömrü Durumu</span>
                    <strong class="kalan-yazi-<?= $durumCss ?>"><?= kalanGunMetni($urun['skt']) ?></strong>
                </div>
                <div class="tazelik-iz">
                    <div class="tazelik-dolgu dolgu-<?= $durumCss ?>" style="--hedef-genislik: <?= $yuzde ?>%; width: <?= $yuzde ?>%; background: hsl(<?= $dolguTon ?>, 78%, 46%) !important;"></div>
                </div>
            </div>

            <div class="ekran-alt">
                <div class="skt-kutusu">
                    <span class="skt-etiket">SON KULLANMA TARİHİ</span>
                    <span class="skt-tarih">📅 <?= tarihGoster($urun['skt']) ?></span>
                </div>
                <span class="kategori-mini-etiket <?= $katCss ?>"><?= guvenli($urun['kategori']) ?></span>
            </div>
        </div>
    </div>

    <div class="vitrin-alt">
        <div class="vitrin-meta">
            <span class="meta-ikon"><?= kategoriIkonu($urun['kategori']) ?></span>
            <div>
                <div class="meta-kategori"><?= guvenli($urun['kategori']) ?></div>
                <div class="meta-id">Envanter No: #<?= (int) $urun['id'] ?></div>
            </div>
        </div>

        <div class="vitrin-aksiyonlar">
            <a href="ekle.php?duzenle=<?= (int) $urun['id'] ?>" class="dugme-sil dugme-duzenle" title="Ürünü düzenle">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span>Düzenle</span>
            </a>

            <form method="post" action="sil.php">
                <?= csrfAlani() ?>
                <input type="hidden" name="id" value="<?= (int) $urun['id'] ?>">
                <input type="hidden" name="donus" value="<?= guvenli($mevcutSayfa) ?>">
                <?php if ($durum === 'Bozuldu'): ?>
                    <button type="submit" class="dugme-sil dugme-temizle-xp" title="Bozulmuş ürünü dolaptan temizle ve +<?= XP_BOZUK_TEMIZLE ?> XP kazan">
                        <span>🧹 Temizle (+<?= XP_BOZUK_TEMIZLE ?> XP)</span>
                    </button>
                <?php else: ?>
                    <button type="submit" class="dugme-sil dugme-tuket-xp" title="Bu ürünü ziyan etmeden tükettim olarak işaretle ve +<?= XP_URUN_TUKET ?> XP kazan">
                        <span>😋 Tükettim (+<?= XP_URUN_TUKET ?> XP)</span>
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</article>
