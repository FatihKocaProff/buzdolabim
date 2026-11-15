<?php

require 'functions.php';
girisKontrol();

$sayfaBasligi = 'Ürün Ekle';
$hatalar = [];

$duzenleId = isset($_GET['duzenle']) ? (int) $_GET['duzenle'] : (int) ($_POST['duzenle_id'] ?? 0);
$kullaniciId = aktifKullaniciId();
$duzenlenen = null;

$veri = [
    'urun'     => '',
    'kategori' => '',
    'miktar'   => '',
    'skt'      => '',
    'aciklama' => '',
];

if ($duzenleId > 0) {
    $duzenlenen = urunGetir($kullaniciId, $duzenleId);
    if ($duzenlenen !== null) {
        $sayfaBasligi = 'Ürünü Düzenle';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $veri = [
                'urun'     => $duzenlenen['urun'],
                'kategori' => $duzenlenen['kategori'],
                'miktar'   => $duzenlenen['miktar'],
                'skt'      => $duzenlenen['skt'],
                'aciklama' => $duzenlenen['aciklama'],
            ];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $veri['urun']     = trim($_POST['urun'] ?? '');
    $veri['kategori'] = $_POST['kategori'] ?? '';
    $veri['miktar']   = trim($_POST['miktar'] ?? '');
    $veri['skt']      = trim($_POST['skt'] ?? '');
    $veri['aciklama'] = trim($_POST['aciklama'] ?? '');

    $hatalar = urunDogrula($veri, $kategoriler);

    if (!mb_check_encoding(implode('', $veri), 'UTF-8')) {
        $hatalar[] = 'Girilen bilgilerde geçersiz karakter var.';
    }

    $donus = in_array($_POST['donus'] ?? '', ['index.php', 'urunler.php'], true) ? $_POST['donus'] : 'urunler.php';
    if (count($hatalar) === 0) {
        if ($duzenlenen !== null) {
            urunGuncelle($kullaniciId, $duzenleId, $veri);
            mesajKaydet('basari', '"' . $veri['urun'] . '" başarıyla güncellendi. Durumu: ' . urunDurumu($veri['skt']) . ' (' . kalanGunMetni($veri['skt']) . ')');
        } else {
            urunEkle($kullaniciId, $veri);
            if (xpEkle($kullaniciId, 'urun_ekle', XP_URUN_EKLE, 'Ürün eklendi: ' . $veri['urun'], XP_GUNLUK_LIMIT) > 0) {
                xpBildir('⚡ Ürün eklendi! +' . XP_URUN_EKLE . ' XP');
            }
            mesajKaydet('basari', '"' . $veri['urun'] . '" buzdolabına eklendi. Durumu: ' . urunDurumu($veri['skt']) . ' (' . kalanGunMetni($veri['skt']) . ')');
        }
        header('Location: ' . $donus);
        exit;
    } elseif ($donus === 'index.php') {
        mesajKaydet('hata', implode(' ', $hatalar));
        header('Location: index.php');
        exit;
    }
}

require 'header.php';
?>

<div class="ekle-panel-sarmal">
    <?php if (count($hatalar) > 0): ?>
        <div class="uyari uyari-hata ekle-uyari">
            <strong>Ürün eklenemedi. Lütfen aşağıdaki hataları düzeltin:</strong>
            <ul>
                <?php foreach ($hatalar as $hata): ?>
                    <li><?= guvenli($hata) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="ekle.php<?= $duzenlenen !== null ? '?duzenle=' . (int) $duzenleId : '' ?>" id="urunEkleForm" novalidate>
        <?= csrfAlani() ?>
        <?php if ($duzenlenen !== null): ?>
            <input type="hidden" name="duzenle_id" value="<?= (int) $duzenleId ?>">
        <?php endif; ?>
        <div class="ekle-ana-panel">

            <div class="ekle-sutun ekle-sutun-sol">
                <div class="ekle-alan-grup">
                    <span class="ekle-etiket">Tüketim Tipi</span>
                    <div class="ekle-segment-grup" data-segment="tuketim">
                        <button type="button" class="ekle-segment-buton aktif" data-etiket="">Standart (Varsayılan)</button>
                        <button type="button" class="ekle-segment-buton" data-etiket="Günlük Tüketim">Günlük</button>
                        <button type="button" class="ekle-segment-buton" data-etiket="Uzun Ömürlü Stok">Stok / Paket</button>
                    </div>
                </div>

                <label class="ekle-alan-grup">
                    <span class="ekle-etiket">Kategori *</span>
                    <select name="kategori" id="ekleKategori" class="ekle-girdi" required>
                        <option value="">Kategori Seçiniz</option>
                        <?php foreach ($kategoriler as $kategori): ?>
                            <option value="<?= guvenli($kategori) ?>" <?= $kategori === $veri['kategori'] ? 'selected' : '' ?>>
                                <?= kategoriIkonu($kategori) ?> <?= guvenli($kategori) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label class="ekle-alan-grup">
                    <span class="ekle-etiket">Miktar &amp; Birim *</span>
                    <input type="text" name="miktar" id="ekleMiktar" class="ekle-girdi" value="<?= guvenli($veri['miktar']) ?>" placeholder="örn. 500 g, 1 litre, 2 adet" required>
                </label>

                <label class="ekle-alan-grup">
                    <span class="ekle-etiket">Ürün Adı *</span>
                    <input type="text" name="urun" id="ekleUrunAdi" class="ekle-girdi" value="<?= guvenli($veri['urun']) ?>" maxlength="60" placeholder="örn. Kaşar peyniri" required>
                </label>

                <div class="ekle-orta-aksiyon">
                    <button type="button" class="ekle-vurgu-hap" id="hizliMiktarTetik">Hızlı Miktar Seçenekleri</button>
                    <div class="ekle-hizli-chipler" id="hizliMiktarKutu" hidden>
                        <button type="button" class="ekle-mini-chip" data-miktar="500 g">500 g</button>
                        <button type="button" class="ekle-mini-chip" data-miktar="1 kg">1 kg</button>
                        <button type="button" class="ekle-mini-chip" data-miktar="1 litre">1 litre</button>
                        <button type="button" class="ekle-mini-chip" data-miktar="1 paket">1 paket</button>
                        <button type="button" class="ekle-mini-chip" data-miktar="2 adet">2 adet</button>
                    </div>
                </div>

                <div class="ekle-bolum-baslik-satir">
                    <span class="ekle-bolum-baslik">Saklama Özellikleri <span class="ekle-bilgi-ikon" title="Seçilen özellikler ürün notuna otomatik yansıtılır">ⓘ</span></span>
                    <button type="button" class="ekle-baglanti-buton" id="tumSaklamaAktif">Varsayılanları Seç</button>
                </div>

                <div class="ekle-ozellik-liste">
                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">❄️</span>
                            <span class="ekle-ozellik-ad">Raf Bölgesi</span>
                        </div>
                        <div class="ekle-coklu-hap" data-ozellik="raf">
                            <button type="button" class="hap-secenek" data-deger="Üst rafta">Üst Raf</button>
                            <button type="button" class="hap-secenek aktif" data-deger="Orta rafta">Orta</button>
                            <button type="button" class="hap-secenek" data-deger="Sebzelikte">Sebzelik</button>
                        </div>
                    </div>

                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">📦</span>
                            <span class="ekle-ozellik-ad">Ambalaj Durumu</span>
                        </div>
                        <div class="ekle-coklu-hap" data-ozellik="ambalaj">
                            <button type="button" class="hap-secenek" data-deger="Paketi açıldı">Açıldı</button>
                            <button type="button" class="hap-secenek" data-deger="Yarım paket">Yarım</button>
                            <button type="button" class="hap-secenek aktif" data-deger="">Kapalı</button>
                        </div>
                    </div>

                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">🌡️</span>
                            <span class="ekle-ozellik-ad">Soğutma Derecesi</span>
                        </div>
                        <div class="ekle-coklu-hap" data-ozellik="derece">
                            <button type="button" class="hap-secenek" data-deger="+4°C soğukta">+4°C</button>
                            <button type="button" class="hap-secenek" data-deger="-18°C dondurucuda">-18°C</button>
                            <button type="button" class="hap-secenek aktif" data-deger="">Standart</button>
                        </div>
                    </div>
                </div>

                <div class="ekle-bolum-baslik-satir">
                    <span class="ekle-bolum-baslik">Mutfak Etiketleri <span class="ekle-bilgi-ikon" title="Ürünün kullanım önceliğini belirler">ⓘ</span></span>
                    <button type="button" class="ekle-baglanti-buton" id="tumEtiketleriAc">Tüm Etiketleri Aç</button>
                </div>

                <div class="ekle-ozellik-liste">
                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">⚡</span>
                            <span class="ekle-ozellik-ad">Öncelikli Tüketilmeli</span>
                        </div>
                        <div class="ekle-ikili-anahtar" data-etiket-not="Öncelikli tüketilmeli.">
                            <button type="button" class="anahtar-secenek aktif" data-durum="acik">Açık</button>
                            <button type="button" class="anahtar-secenek" data-durum="kapali">Kapalı</button>
                        </div>
                    </div>

                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">🍳</span>
                            <span class="ekle-ozellik-ad">Kahvaltılık / Günlük</span>
                        </div>
                        <div class="ekle-ikili-anahtar" data-etiket-not="Kahvaltı ve günlük öğün için.">
                            <button type="button" class="anahtar-secenek" data-durum="acik">Açık</button>
                            <button type="button" class="anahtar-secenek aktif" data-durum="kapali">Kapalı</button>
                        </div>
                    </div>

                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">🍲</span>
                            <span class="ekle-ozellik-ad">Akşam Yemeği Malzemesi</span>
                        </div>
                        <div class="ekle-ikili-anahtar" data-etiket-not="Akşam yemeğinde kullanılacak.">
                            <button type="button" class="anahtar-secenek" data-durum="acik">Açık</button>
                            <button type="button" class="anahtar-secenek aktif" data-durum="kapali">Kapalı</button>
                        </div>
                    </div>

                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">🧊</span>
                            <span class="ekle-ozellik-ad">Dondurucuya Atılabilir</span>
                        </div>
                        <div class="ekle-ikili-anahtar" data-etiket-not="Artarsa dondurucuya konulabilir.">
                            <button type="button" class="anahtar-secenek" data-durum="acik">Açık</button>
                            <button type="button" class="anahtar-secenek aktif" data-durum="kapali">Kapalı</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ekle-sutun ekle-sutun-sag">
                <div class="ekle-alan-grup">
                    <span class="ekle-etiket">Hızlı Süre Ataması</span>
                    <div class="ekle-segment-grup" id="hizliSktGrup">
                        <button type="button" class="ekle-segment-buton" data-gun="2">+2 Gün (Acil)</button>
                        <button type="button" class="ekle-segment-buton aktif" data-gun="7">+1 Hafta</button>
                        <button type="button" class="ekle-segment-buton" data-gun="30">+1 Ay</button>
                    </div>
                </div>

                <label class="ekle-alan-grup">
                    <span class="ekle-etiket">Son Kullanma Tarihi *</span>
                    <input type="date" name="skt" id="ekleSkt" class="ekle-girdi" value="<?= guvenli($veri['skt'] !== '' ? $veri['skt'] : date('Y-m-d', strtotime('+7 days'))) ?>" required>
                </label>

                <div class="ekle-alan-grup">
                    <span class="ekle-etiket">Otomatik Hesaplanan Durum</span>
                    <div class="ekle-durum-onizleme" id="canliDurumKutu">
                        <span class="nokta nokta-rozet-yesil" id="canliDurumNokta"></span>
                        <span id="canliDurumMetin">Taze · 7 gün kaldı</span>
                    </div>
                </div>

                <label class="ekle-alan-grup">
                    <span class="ekle-etiket">Saklama Notu / Açıklama</span>
                    <input type="text" name="aciklama" id="ekleAciklama" class="ekle-girdi" value="<?= guvenli($veri['aciklama']) ?>" maxlength="200" placeholder="örn. Açıldı, 3 gün içinde tüketilmeli">
                </label>

                <div class="ekle-orta-aksiyon">
                    <button type="button" class="ekle-vurgu-hap" id="otomatikNotOlustur">Seçimlerden Otomatik Not Oluştur</button>
                </div>

                <div class="ekle-bolum-baslik-satir">
                    <span class="ekle-bolum-baslik">Tazelik &amp; Takip Parametreleri <span class="ekle-bilgi-ikon" title="Ürünün envanter görünüm ayarları">ⓘ</span></span>
                    <button type="button" class="ekle-baglanti-buton" id="tumTakipAktif">Tümünü Etkinleştir</button>
                </div>

                <div class="ekle-ozellik-liste">
                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">📊</span>
                            <span class="ekle-ozellik-ad">Tazelik Çubuğu</span>
                        </div>
                        <div class="ekle-coklu-hap">
                            <button type="button" class="hap-secenek aktif">Canlı Bar</button>
                            <button type="button" class="hap-secenek">Yüzde</button>
                        </div>
                    </div>

                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">🔔</span>
                            <span class="ekle-ozellik-ad">SKT Uyarı Eşiği</span>
                        </div>
                        <div class="ekle-coklu-hap">
                            <button type="button" class="hap-secenek aktif">≤ <?= UYARI_GUN ?> Gün</button>
                            <button type="button" class="hap-secenek">≤ 5 Gün</button>
                            <button type="button" class="hap-secenek">Kapalı</button>
                        </div>
                    </div>

                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">🏷️</span>
                            <span class="ekle-ozellik-ad">Kart Rozet Tipi</span>
                        </div>
                        <div class="ekle-coklu-hap">
                            <button type="button" class="hap-secenek">Minimal</button>
                            <button type="button" class="hap-secenek aktif">Detaylı</button>
                            <button type="button" class="hap-secenek">Gizli</button>
                        </div>
                    </div>
                </div>

                <div class="ekle-bolum-baslik-satir">
                    <span class="ekle-bolum-baslik">Hızlı Hazır Şablonlar <span class="ekle-bilgi-ikon" title="Tek tıkla örnek ürün değerlerini doldurur">ⓘ</span></span>
                    <button type="button" class="ekle-baglanti-buton" id="formuTemizleButon">Alanları Sıfırla</button>
                </div>

                <div class="ekle-ozellik-liste">
                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">🥛</span>
                            <span class="ekle-ozellik-ad">Günlük Süt (1 Litre)</span>
                        </div>
                        <div class="ekle-ikili-anahtar" data-sablon='{"urun":"Günlük Süt","kategori":"Süt Ürünü","miktar":"1 litre","gun":3,"not":"Kahvaltıda tüketilmeli."}'>
                            <button type="button" class="anahtar-secenek" data-durum="acik">Uygula</button>
                            <button type="button" class="anahtar-secenek aktif" data-durum="kapali">Kapalı</button>
                        </div>
                    </div>

                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">🥬</span>
                            <span class="ekle-ozellik-ad">Taze Yeşillik / Sebze</span>
                        </div>
                        <div class="ekle-ikili-anahtar" data-sablon='{"urun":"Akdeniz Yeşilliği","kategori":"Sebze & Meyve","miktar":"1 bağ","gun":5,"not":"Yıkanıp kurutuldu, sebzelikte."}'>
                            <button type="button" class="anahtar-secenek" data-durum="acik">Uygula</button>
                            <button type="button" class="anahtar-secenek aktif" data-durum="kapali">Kapalı</button>
                        </div>
                    </div>

                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">🍗</span>
                            <span class="ekle-ozellik-ad">Tavuk / Et Ürünü</span>
                        </div>
                        <div class="ekle-ikili-anahtar" data-sablon='{"urun":"Tavuk Bonfile","kategori":"Et & Tavuk","miktar":"600 g","gun":2,"not":"Akşam yemeği için +4°C rafta."}'>
                            <button type="button" class="anahtar-secenek" data-durum="acik">Uygula</button>
                            <button type="button" class="anahtar-secenek aktif" data-durum="kapali">Kapalı</button>
                        </div>
                    </div>

                    <div class="ekle-ozellik-satir">
                        <div class="ekle-ozellik-sol">
                            <span class="ekle-ozellik-ikon">🧃</span>
                            <span class="ekle-ozellik-ad">Soğuk İçecek</span>
                        </div>
                        <div class="ekle-ikili-anahtar" data-sablon='{"urun":"Taze Portakal Suyu","kategori":"İçecek","miktar":"1 şişe","gun":4,"not":"Kapak rafında soğuyor."}'>
                            <button type="button" class="anahtar-secenek" data-durum="acik">Uygula</button>
                            <button type="button" class="anahtar-secenek aktif" data-durum="kapali">Kapalı</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="ekle-alt-aksiyon-bar">
            <a href="urunler.php" class="dugme dugme-ikincil">Vazgeç</a>
            <button type="submit" class="dugme ekle-onay-dugme"><?= $duzenlenen !== null ? 'Değişiklikleri Kaydet' : 'Ürünü Kaydet' ?></button>
        </div>
    </form>
</div>

<script>
(function () {
    var urunInput = document.getElementById('ekleUrunAdi');
    var katSelect = document.getElementById('ekleKategori');
    var miktarInput = document.getElementById('ekleMiktar');
    var sktInput = document.getElementById('ekleSkt');
    var aciklamaInput = document.getElementById('ekleAciklama');
    var durumNokta = document.getElementById('canliDurumNokta');
    var durumMetin = document.getElementById('canliDurumMetin');

    function durumuGuncelle() {
        if (!sktInput || !sktInput.value) return;
        var bugun = new Date();
        bugun.setHours(0, 0, 0, 0);
        var parca = sktInput.value.split('-');
        if (parca.length !== 3) return;
        var hedef = new Date(parseInt(parca[0], 10), parseInt(parca[1], 10) - 1, parseInt(parca[2], 10));
        var farkGun = Math.round((hedef - bugun) / (1000 * 60 * 60 * 24));

        durumNokta.className = 'nokta';
        if (farkGun < 0) {
            durumNokta.classList.add('nokta-rozet-kirmizi');
            durumMetin.textContent = 'Bozuldu · ' + Math.abs(farkGun) + ' gün geçti';
        } else if (farkGun <= <?= (int) UYARI_GUN ?>) {
            durumNokta.classList.add('nokta-rozet-turuncu');
            durumMetin.textContent = 'Yaklaşıyor · ' + (farkGun === 0 ? 'Bugün son gün!' : farkGun + ' gün kaldı');
        } else {
            durumNokta.classList.add('nokta-rozet-yesil');
            durumMetin.textContent = 'Taze · ' + farkGun + ' gün kaldı';
        }
    }

    if (sktInput) {
        sktInput.addEventListener('change', durumuGuncelle);
        durumuGuncelle();
    }

    document.querySelectorAll('.ekle-segment-grup').forEach(function (grup) {
        grup.querySelectorAll('.ekle-segment-buton').forEach(function (btn) {
            btn.addEventListener('click', function () {
                grup.querySelectorAll('.ekle-segment-buton').forEach(function (b) {
                    b.classList.remove('aktif');
                });
                btn.classList.add('aktif');

                var gun = btn.getAttribute('data-gun');
                if (gun !== null && sktInput) {
                    var d = new Date();
                    d.setDate(d.getDate() + parseInt(gun, 10));
                    var yil = d.getFullYear();
                    var ay = String(d.getMonth() + 1).padStart(2, '0');
                    var g = String(d.getDate()).padStart(2, '0');
                    sktInput.value = yil + '-' + ay + '-' + g;
                    durumuGuncelle();
                }
            });
        });
    });

    document.querySelectorAll('.ekle-coklu-hap').forEach(function (grup) {
        grup.querySelectorAll('.hap-secenek').forEach(function (btn) {
            btn.addEventListener('click', function () {
                grup.querySelectorAll('.hap-secenek').forEach(function (b) {
                    b.classList.remove('aktif');
                });
                btn.classList.add('aktif');
            });
        });
    });

    document.querySelectorAll('.ekle-ikili-anahtar').forEach(function (anahtar) {
        anahtar.querySelectorAll('.anahtar-secenek').forEach(function (btn) {
            btn.addEventListener('click', function () {
                anahtar.querySelectorAll('.anahtar-secenek').forEach(function (b) {
                    b.classList.remove('aktif');
                });
                btn.classList.add('aktif');

                var sablonVeri = anahtar.getAttribute('data-sablon');
                if (sablonVeri && btn.getAttribute('data-durum') === 'acik') {
                    try {
                        var s = JSON.parse(sablonVeri);
                        if (urunInput) urunInput.value = s.urun || '';
                        if (katSelect) katSelect.value = s.kategori || '';
                        if (miktarInput) miktarInput.value = s.miktar || '';
                        if (aciklamaInput) aciklamaInput.value = s.not || '';
                        if (sktInput && s.gun) {
                            var d = new Date();
                            d.setDate(d.getDate() + parseInt(s.gun, 10));
                            sktInput.value = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                            durumuGuncelle();
                        }
                    } catch (err) {}
                }
            });
        });
    });

    var hizliMiktarTetik = document.getElementById('hizliMiktarTetik');
    var hizliMiktarKutu = document.getElementById('hizliMiktarKutu');
    if (hizliMiktarTetik && hizliMiktarKutu) {
        hizliMiktarTetik.addEventListener('click', function () {
            hizliMiktarKutu.hidden = !hizliMiktarKutu.hidden;
        });
        hizliMiktarKutu.querySelectorAll('.ekle-mini-chip').forEach(function (chip) {
            chip.addEventListener('click', function () {
                if (miktarInput) miktarInput.value = chip.getAttribute('data-miktar');
            });
        });
    }

    function secimlerdenNotUret() {
        var parcalar = [];
        document.querySelectorAll('.ekle-coklu-hap[data-ozellik]').forEach(function (grup) {
            var aktif = grup.querySelector('.hap-secenek.aktif');
            if (aktif) {
                var val = aktif.getAttribute('data-deger');
                if (val) parcalar.push(val);
            }
        });
        document.querySelectorAll('.ekle-ikili-anahtar[data-etiket-not]').forEach(function (anahtar) {
            var acikBtn = anahtar.querySelector('.anahtar-secenek[data-durum="acik"]');
            if (acikBtn && acikBtn.classList.contains('aktif')) {
                parcalar.push(anahtar.getAttribute('data-etiket-not'));
            }
        });
        if (aciklamaInput && parcalar.length > 0) {
            aciklamaInput.value = parcalar.join(' · ').slice(0, 200);
        }
    }

    var otoNotBtn = document.getElementById('otomatikNotOlustur');
    if (otoNotBtn) {
        otoNotBtn.addEventListener('click', secimlerdenNotUret);
    }

    var tumEtiketBtn = document.getElementById('tumEtiketleriAc');
    if (tumEtiketBtn) {
        tumEtiketBtn.addEventListener('click', function () {
            document.querySelectorAll('.ekle-ikili-anahtar[data-etiket-not]').forEach(function (anahtar) {
                var acik = anahtar.querySelector('[data-durum="acik"]');
                var kapali = anahtar.querySelector('[data-durum="kapali"]');
                if (acik && kapali) {
                    acik.classList.add('aktif');
                    kapali.classList.remove('aktif');
                }
            });
            secimlerdenNotUret();
        });
    }

    var tumSaklamaBtn = document.getElementById('tumSaklamaAktif');
    if (tumSaklamaBtn) {
        tumSaklamaBtn.addEventListener('click', function () {
            document.querySelectorAll('.ekle-coklu-hap[data-ozellik]').forEach(function (grup) {
                var butonlar = grup.querySelectorAll('.hap-secenek');
                butonlar.forEach(function (b, i) {
                    b.classList.toggle('aktif', i === 0);
                });
            });
            secimlerdenNotUret();
        });
    }

    var tumTakipBtn = document.getElementById('tumTakipAktif');
    if (tumTakipBtn) {
        tumTakipBtn.addEventListener('click', function () {
            document.querySelectorAll('.ekle-sutun-sag .ekle-coklu-hap').forEach(function (grup) {
                var butonlar = grup.querySelectorAll('.hap-secenek');
                butonlar.forEach(function (b, i) {
                    b.classList.toggle('aktif', i === 0);
                });
            });
        });
    }

    var sifirlaBtn = document.getElementById('formuTemizleButon');
    if (sifirlaBtn) {
        sifirlaBtn.addEventListener('click', function () {
            if (urunInput) urunInput.value = '';
            if (katSelect) katSelect.value = '';
            if (miktarInput) miktarInput.value = '';
            if (aciklamaInput) aciklamaInput.value = '';
            document.querySelectorAll('.ekle-ikili-anahtar[data-sablon]').forEach(function (anahtar) {
                var acik = anahtar.querySelector('[data-durum="acik"]');
                var kapali = anahtar.querySelector('[data-durum="kapali"]');
                if (acik && kapali) {
                    acik.classList.remove('aktif');
                    kapali.classList.add('aktif');
                }
            });
        });
    }
})();
</script>

<?php require 'footer.php'; ?>
