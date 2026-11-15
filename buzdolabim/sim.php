<?php

$simUrunler = [];
foreach (tariheGoreSirala($urunler) as $u) {
    $simUrunler[] = [
        'ad'       => $u['urun'],
        'kategori' => $u['kategori'],
        'durum'    => urunDurumu($u['skt']),
        'yuzde'    => tazelikYuzdesi($u['skt']),
    ];
}

$simKategoriler = [];
foreach ($kategoriler as $k) {
    $adet = 0;
    foreach ($urunler as $u) {
        if ($u['kategori'] === $k) {
            $adet++;
        }
    }
    $simKategoriler[] = ['ad' => $k, 'adet' => $adet];
}

$simKapasite = 24;
$simDoluluk  = min(100, (int) round(($toplamSayi / $simKapasite) * 100));
$simDolBlok  = (int) round($simDoluluk / 10);
$simOperator = mb_strtoupper($oturumKullanici['ad_soyad'] ?? 'Operatör');
$simMaxAdet  = max(1, max(array_column($simKategoriler, 'adet')));
$simBlok     = (int) round($saglikSkoru / 10);

$simVeri = [
    'urunler'  => array_slice($simUrunler, 0, 12),
    'saglik'   => $saglikSkoru,
    'taze'     => $tazeSayi,
    'yaklasan' => $yaklasanSayi,
    'bozuk'    => $bozukSayi,
];
?>
<section class="sim" id="sim" aria-label="Buzdolabı robotu 3D simülatörü">
    <div class="sim-ust">
        <div class="sim-logo">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2"><circle cx="16" cy="15" r="6"/><circle cx="16" cy="15" r="2" fill="currentColor"/><path d="M11 9 8 3M21 9l3-6M10 20l-4 7M22 20l4 7M16 21v7"/></svg>
            BZ-7
        </div>
        <div class="sim-baslik">
            <small>🧊 3D Buzdolabı Simülatörü &amp; Raf Telemetrisi</small>
            <h2>Buzdolabım FR-7 <span>/</span></h2>
            <small>Optik sensörlü buzdolabı robotu / saha simülatörü</small>
        </div>
        <div class="sim-metrikler">
            <div class="sim-metrik sim-doluluk">
                <small style="display:flex;justify-content:space-between"><span>Raf Doluluğu</span><span><?= $simDoluluk ?>%</span></small>
                <div class="cubuk">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <i class="<?= $i <= $simDolBlok ? 'dolu' : '' ?> <?= $i === $simDolBlok ? 'son' : '' ?>"></i>
                    <?php endfor; ?>
                </div>
                <small style="display:flex;justify-content:space-between;margin-top:4px"><span>Boş</span><span>Kapasite <?= $simKapasite ?></span></small>
            </div>
            <div class="sim-metrik gizle-orta"><small>Seri No</small><b>FR7-<?= str_pad((string) ((((int) ($oturumKullanici['id'] ?? 1)) * 4127) % 10000), 4, '0', STR_PAD_LEFT) ?></b></div>
            <div class="sim-metrik"><small>Çalışma Süresi</small><b id="simSure">00:00:00</b></div>
            <div class="sim-metrik"><small>Durum</small><b class="durum" id="simDurumYazi">MONTAJLI</b></div>
            <div class="sim-metrik gizle-orta"><small>Operatör</small><b><?= guvenli(mb_substr($simOperator, 0, 16)) ?></b></div>
        </div>
    </div>

    <div class="sim-govde">
        <div class="sim-sahne" id="simSahne">
            <div class="sim-yukleniyor" id="simYukleniyor">3D sahne yükleniyor…</div>
        </div>
        <div class="sim-etiketler" id="simEtiketler"><svg id="simEtiketSvg"></svg></div>

        <div class="sim-kolon">
            <div class="sim-panel">
                <div class="sim-panel-bas"><span>Soğutma Döngüsü</span><span>01</span></div>
                <div class="sim-buyuk">
                    <strong id="simFrekans">3.8<sup>°C</sup></strong>
                    <div class="sag" id="simGenlik">±0.4°</div>
                </div>
                <div class="sim-alt-etiketler"><span>İç Sıcaklık</span><span>Sapma</span></div>
                <canvas class="sim-dalga" id="simDalga"></canvas>
                <div class="sim-alt-etiketler"><span>Kompresör 1.0×</span><span>Defrost 1/60</span></div>
            </div>

            <div class="sim-panel">
                <div class="sim-panel-bas"><span>Tazelik</span><span>02</span></div>
                <div class="sim-buyuk" style="align-items:center">
                    <strong><?= $saglikSkoru ?><sup>%</sup></strong>
                    <div class="sim-blok-cubuk" style="width:58%">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <i class="<?= $i > $simBlok ? 'kapali' : ($simBlok < 6 ? 'uyar' : '') ?>"></i>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="sim-uclu">
                    <div><small>Taze</small><b><?= $tazeSayi ?></b></div>
                    <div><small>Yaklaşan</small><b style="color:var(--hud-turuncu)"><?= $yaklasanSayi ?></b></div>
                    <div><small>Bozuk</small><b style="color:var(--hud-kirmizi)"><?= $bozukSayi ?></b></div>
                </div>
            </div>

            <div class="sim-panel">
                <div class="sim-panel-bas"><span>Raf Servoları</span><span class="sim-etiket-yesil">Aktif</span></div>
                <small style="font-size:.54rem;color:var(--soluk)">Kategori başına yük · <?= $toplamSayi ?> ürün</small>
                <?php foreach ($simKategoriler as $i => $k): ?>
                    <div class="sim-servo" title="<?= guvenli($k['ad']) ?>">
                        <span><?= $i < 3 ? 'L' . ($i + 1) : 'R' . ($i - 2) ?></span>
                        <div class="iz"><i style="width: <?= round(($k['adet'] / $simMaxAdet) * 100) ?>%"></i></div>
                        <span><?= $k['adet'] ?> ad</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="sim-orta">
            <div class="sim-kose-not" style="left:0">Model FR-7 · Rev C</div>
            <div class="sim-kose-not" style="right:0" id="simIzoleNot">İzole · —</div>
            <div class="sim-mod">
                <small class="m">Çalışma Modu</small>
                <strong id="simMod">Montajlı</strong>
                <span id="simModAlt">Tüm modüller kilitli</span>
            </div>
        </div>

        <div class="sim-kolon">
            <div class="sim-panel">
                <div class="sim-panel-bas"><span>Bölme Dengesi</span><span>04</span></div>
                <div class="sim-tutum">
                    <div class="sim-tutum-daire"><div class="ufuk" id="simUfuk"></div></div>
                    <div class="sim-satirlar">
                        <div><span>Dondurucu</span><b>-18.2°C</b></div>
                        <div><span>Ana Bölme</span><b id="simAnaSicak">+3.8°C</b></div>
                        <div><span>Sebzelik</span><b>+5.1°C</b></div>
                        <div><span>Nem</span><b>62%</b></div>
                    </div>
                </div>
            </div>

            <div class="sim-panel">
                <div class="sim-panel-bas"><span>Montajlar</span><span>05</span></div>
                <div class="sim-chipler" id="simMontajlar"></div>
            </div>

            <div class="sim-panel">
                <div class="sim-panel-bas"><span>Görünüm</span><span>06</span></div>
                <div class="sim-chipler">
                    <button type="button" class="sim-chip aktif" data-gorunum="etiket">Etiketler</button>
                    <button type="button" class="sim-chip" data-gorunum="patlat">Patlatılmış</button>
                    <button type="button" class="sim-chip aktif" data-gorunum="dondur">Döner Tabla</button>
                </div>
            </div>

            <div class="sim-panel">
                <div class="sim-panel-bas"><span>Alt Sistemler</span><span>07</span></div>
                <ul class="sim-altsistem" id="simAltSistem"></ul>
            </div>
        </div>

        <div class="sim-kayit">
            <button type="button" class="sim-oynat" id="simOynat" aria-label="Duraklat">
                <svg viewBox="0 0 14 14" fill="currentColor" id="simOynatIkon"><rect x="2" y="1" width="3.5" height="12"/><rect x="8.5" y="1" width="3.5" height="12"/></svg>
            </button>
            <div>
                <div class="sim-kayit-ust"><span>Görev Kaydı</span><b id="simAdim">Operatör · Montaj kontrolü</b><span>Tur</span></div>
                <div class="sim-ray" id="simRay"><div class="dolu" id="simRayDolu"></div><div class="tutamak" id="simRayTutamak"></div></div>
                <div class="sim-kayit-alt"><span id="simZaman">00:00 / 00:24</span><span><?= count($simUrunler) ?> ürün izleniyor</span></div>
            </div>
        </div>
    </div>

    <div class="sim-altbar">
        <span>Saha Simülatörü · Prosedürel Model · 4 Bacak · 1 Optik · 3 Raf</span>
        <span>Sürükle: döndür · Tekerlek: yakınlaş</span>
        <span>Buzdolabım Robotik · Soğuk Zincir Lab ////</span>
    </div>
</section>

<script type="application/json" id="simVeri"><?= json_encode($simVeri, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script type="importmap">
{ "imports": {
    "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
    "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
} }
</script>
<script type="module" src="assets/robot.js?v=<?= filemtime(__DIR__ . '/assets/robot.js') ?>"></script>
