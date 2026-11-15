<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Europe/Istanbul');

const UYARI_GUN = 3;

$kategoriler = ['Süt Ürünü', 'Sebze & Meyve', 'Et & Tavuk', 'İçecek', 'Sos & Konserve', 'Tatlı'];
$durumlar    = ['Taze', 'Yaklaşıyor', 'Bozuldu'];

function gunSonra($gun)
{
    return date('Y-m-d', strtotime("$gun days"));
}

function ornekUrunler()
{
    return [
        ['id' => 1,  'urun' => 'Günlük Süt',        'kategori' => 'Süt Ürünü',      'miktar' => '1 litre',   'skt' => gunSonra(2),   'aciklama' => 'Kahvaltıda bitirilmeli.'],
        ['id' => 2,  'urun' => 'Salkım Domates',    'kategori' => 'Sebze & Meyve',  'miktar' => '1 kg',      'skt' => gunSonra(6),   'aciklama' => 'Salata ve menemen için alındı.'],
        ['id' => 3,  'urun' => 'Tavuk Göğsü',       'kategori' => 'Et & Tavuk',     'miktar' => '500 g',     'skt' => gunSonra(-1),  'aciklama' => 'Dondurucuya koymayı unuttum.'],
        ['id' => 4,  'urun' => 'Süzme Yoğurt',      'kategori' => 'Süt Ürünü',      'miktar' => '750 g',     'skt' => gunSonra(12),  'aciklama' => 'Meze ve akşam yemeği için.'],
        ['id' => 5,  'urun' => 'Portakal Suyu',     'kategori' => 'İçecek',         'miktar' => '1 litre',   'skt' => gunSonra(0),   'aciklama' => 'Açıldı, bugün içilmeli.'],
        ['id' => 6,  'urun' => 'Acılı Ketçap',      'kategori' => 'Sos & Konserve', 'miktar' => '1 şişe',    'skt' => gunSonra(90),  'aciklama' => 'Kapakta duruyor.'],
        ['id' => 7,  'urun' => 'Ezine Beyaz Peynir','kategori' => 'Süt Ürünü',      'miktar' => '400 g',     'skt' => gunSonra(18),  'aciklama' => 'Salamura suyunda saklanıyor.'],
        ['id' => 8,  'urun' => 'Dana Kıyma',        'kategori' => 'Et & Tavuk',     'miktar' => '600 g',     'skt' => gunSonra(1),   'aciklama' => 'Akşama köfte yapılacak.'],
        ['id' => 9,  'urun' => 'Taze Ispanak',      'kategori' => 'Sebze & Meyve',  'miktar' => '1 bağ',     'skt' => gunSonra(3),   'aciklama' => 'Yıkanıp kurutuldu.'],
        ['id' => 10, 'urun' => 'Çilek',             'kategori' => 'Sebze & Meyve',  'miktar' => '500 g',     'skt' => gunSonra(-2),  'aciklama' => 'Alt kısımları yumuşamış.'],
        ['id' => 11, 'urun' => 'Fırın Sütlaç',      'kategori' => 'Tatlı',          'miktar' => '2 kase',    'skt' => gunSonra(2),   'aciklama' => 'Pastaneden taze alındı.'],
        ['id' => 12, 'urun' => 'Soğuk Kahve',       'kategori' => 'İçecek',         'miktar' => '3 kutu',    'skt' => gunSonra(25),  'aciklama' => 'Üst rafta soğuyor.'],
        ['id' => 13, 'urun' => 'Kornişon Turşu',    'kategori' => 'Sos & Konserve', 'miktar' => '1 kavanoz', 'skt' => gunSonra(120), 'aciklama' => 'Serin rafta muhafaza ediliyor.'],
        ['id' => 14, 'urun' => 'Kazandibi',         'kategori' => 'Tatlı',          'miktar' => '1 porsiyon','skt' => gunSonra(-3),  'aciklama' => 'Tarihi geçmiş, atılacak.'],
        ['id' => 15, 'urun' => 'Somon Fileto',      'kategori' => 'Et & Tavuk',     'miktar' => '2 dilim',   'skt' => gunSonra(4),   'aciklama' => 'Fırında limonlu pişirilecek.'],
        ['id' => 16, 'urun' => 'Kefir',             'kategori' => 'İçecek',         'miktar' => '1 şişe',    'skt' => gunSonra(7),   'aciklama' => 'Sade probiyotik kefir.'],
    ];
}

require_once __DIR__ . '/db.php';

ini_set('display_errors', HATA_DETAY_GOSTER ? '1' : '0');
ini_set('log_errors', '1');

set_exception_handler(function (Throwable $e) {
    $kok = $e->getPrevious() ?? $e;
    error_log('[buzdolabim] ' . $e->getMessage() . ' | ' . get_class($kok) . ': ' . $kok->getMessage() . ' @ ' . $kok->getFile() . ':' . $kok->getLine());

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }
    $mesaj = $e instanceof RuntimeException && $e->getPrevious() instanceof PDOException
        ? $e->getMessage()
        : 'Beklenmeyen bir hata oluştu. Lütfen biraz sonra tekrar deneyin.';
    if (HATA_DETAY_GOSTER) {
        $mesaj .= ' [' . get_class($kok) . ': ' . $kok->getMessage() . ']';
    }
    echo '<!DOCTYPE html><html lang="tr"><head><meta charset="UTF-8"><title>Hata — Buzdolabım</title></head>'
        . '<body style="font-family:system-ui,sans-serif;background:#0e0e10;color:#eee;display:grid;place-items:center;min-height:100vh;margin:0">'
        . '<div style="max-width:480px;padding:32px;border:1px solid #ff4f17;border-radius:8px">'
        . '<h1 style="margin-top:0;color:#ff4f17;font-size:20px">Bir sorun oluştu</h1>'
        . '<p>' . htmlspecialchars($mesaj, ENT_QUOTES, 'UTF-8') . '</p>'
        . '<p><a href="index.php" style="color:#ff4f17">Ana sayfaya dön</a></p></div></body></html>';
});

function aktifKullanici()
{
    if (!isset($_SESSION['kullanici_hafta2'])) {
        return null;
    }
    $k = $_SESSION['kullanici_hafta2'];
    return [
        'kullanici_adi' => $k['kullanici_adi'] ?? 'kullanici',
        'ad_soyad'      => $k['ad_soyad'] ?? 'Kullanıcı',
        'eposta'        => $k['eposta'] ?? ($k['kullanici_adi'] . '@buzdolabim.local'),
        'mutfak_adi'    => $k['mutfak_adi'] ?? 'Evimdeki Buzdolabı',
        'avatar_renk'   => $k['avatar_renk'] ?? '#141414',
    ];
}

function girisKontrol()
{
    $kullaniciId = aktifKullaniciId();
    if ($kullaniciId === null) {
        header('Location: giris.php');
        exit;
    }
    gunlukSeriKontrol($kullaniciId);
}

function guvenli($metin)
{
    return htmlspecialchars((string) $metin, ENT_QUOTES, 'UTF-8');
}

function kalanGun($skt)
{
    $bugun = new DateTime('today');
    $tarih = new DateTime($skt);
    $fark  = $bugun->diff($tarih);
    return $fark->invert ? -$fark->days : $fark->days;
}

function urunDurumu($skt)
{
    $kalan = kalanGun($skt);
    if ($kalan < 0) {
        return 'Bozuldu';
    } elseif ($kalan <= UYARI_GUN) {
        return 'Yaklaşıyor';
    } else {
        return 'Taze';
    }
}

function kalanGunMetni($skt)
{
    $kalan = kalanGun($skt);
    if ($kalan < 0) {
        return abs($kalan) . ' gün geçti';
    } elseif ($kalan === 0) {
        return 'Bugün son gün!';
    } else {
        return $kalan . ' gün kaldı';
    }
}

function tarihGoster($skt)
{
    return date('d.m.Y', strtotime($skt));
}

function urunDogrula($veri, $kategoriler)
{
    $hatalar = [];

    if ($veri['urun'] === '') {
        $hatalar[] = 'Ürün adı boş bırakılamaz.';
    } elseif (mb_strlen($veri['urun']) < 2) {
        $hatalar[] = 'Ürün adı en az 2 karakter olmalıdır.';
    } elseif (mb_strlen($veri['urun']) > 60) {
        $hatalar[] = 'Ürün adı en fazla 60 karakter olabilir.';
    }

    if (!in_array($veri['kategori'], $kategoriler)) {
        $hatalar[] = 'Lütfen listeden geçerli bir kategori seçin.';
    }

    if ($veri['miktar'] === '') {
        $hatalar[] = 'Miktar boş bırakılamaz (örn. 1 litre, 500 g, 3 adet).';
    }

    if ($veri['skt'] === '') {
        $hatalar[] = 'Son kullanma tarihi boş bırakılamaz.';
    } else {
        $parca = explode('-', $veri['skt']);
        if (count($parca) !== 3 || !checkdate((int) $parca[1], (int) $parca[2], (int) $parca[0])) {
            $hatalar[] = 'Geçerli bir son kullanma tarihi girin.';
        } elseif (kalanGun($veri['skt']) < -30) {
            $hatalar[] = 'Son kullanma tarihi 30 günden fazla geçmiş bir ürün eklenemez, onu çöpe atın :)';
        }
    }

    if (mb_strlen($veri['aciklama']) > 200) {
        $hatalar[] = 'Not en fazla 200 karakter olabilir.';
    }

    return $hatalar;
}

function urunleriFiltrele($urunler, $durum, $kategori, $arama = '')
{
    $sonuc = [];
    $arama = trim((string) $arama);
    foreach ($urunler as $urun) {
        if ($durum !== '' && urunDurumu($urun['skt']) !== $durum) {
            continue;
        }
        if ($kategori !== '' && $urun['kategori'] !== $kategori) {
            continue;
        }
        if ($arama !== '') {
            $birlestir = $urun['urun'] . ' ' . $urun['kategori'] . ' ' . $urun['miktar'] . ' ' . $urun['aciklama'];
            if (mb_stripos($birlestir, $arama, 0, 'UTF-8') === false) {
                continue;
            }
        }
        $sonuc[] = $urun;
    }
    return $sonuc;
}

function tariheGoreSirala($urunler)
{
    usort($urunler, function ($a, $b) {
        return strcmp($a['skt'], $b['skt']);
    });
    return $urunler;
}

function durumaGoreSay($urunler, $durum)
{
    $sayac = 0;
    foreach ($urunler as $urun) {
        if (urunDurumu($urun['skt']) === $durum) {
            $sayac++;
        }
    }
    return $sayac;
}

function durumSinifi($durum)
{
    switch ($durum) {
        case 'Taze':
            return 'rozet-yesil';
        case 'Yaklaşıyor':
            return 'rozet-turuncu';
        default:
            return 'rozet-kirmizi';
    }
}

function kategoriIkonu($kategori)
{
    switch ($kategori) {
        case 'Süt Ürünü':
            return '🥛';
        case 'Sebze & Meyve':
            return '🥬';
        case 'Et & Tavuk':
            return '🍗';
        case 'İçecek':
            return '🧃';
        case 'Sos & Konserve':
            return '🫙';
        case 'Tatlı':
            return '🍮';
        default:
            return '📦';
    }
}

function kategoriSinifi($kategori)
{
    switch ($kategori) {
        case 'Süt Ürünü':
            return 'kat-sut';
        case 'Sebze & Meyve':
            return 'kat-sebze';
        case 'Et & Tavuk':
            return 'kat-et';
        case 'İçecek':
            return 'kat-icecek';
        case 'Sos & Konserve':
            return 'kat-sos';
        case 'Tatlı':
            return 'kat-tatli';
        default:
            return 'kat-genel';
    }
}

function tazelikYuzdesi($skt)
{
    $kalan = kalanGun($skt);
    if ($kalan < 0) {
        return 100;
    }
    if ($kalan === 0) {
        return 15;
    }
    if ($kalan >= 14) {
        return 100;
    }
    return max(18, min(100, (int) round(($kalan / 14) * 100)));
}

// Raf ömrü çubuğu için artan yüzde: SKT yaklaştıkça dolar, bozulunca %100 olur.
function rafOmruDolulukYuzdesi($skt)
{
    $kalan = kalanGun($skt);
    if ($kalan < 0) {
        return 100;
    }
    if ($kalan >= 14) {
        return 6;
    }
    return max(6, min(95, (int) round(((14 - $kalan) / 14) * 100)));
}

function mesajKaydet($tur, $metin)
{
    $_SESSION['mesaj'] = ['tur' => $tur, 'metin' => $metin];
}

function mesajAl()
{
    if (!isset($_SESSION['mesaj'])) {
        return null;
    }
    $mesaj = $_SESSION['mesaj'];
    unset($_SESSION['mesaj']);
    return $mesaj;
}

function filtreLinki($durum, $kategori, $ara = '')
{
    $parametreler = [];
    if ($durum !== '') {
        $parametreler['durum'] = $durum;
    }
    if ($kategori !== '') {
        $parametreler['kategori'] = $kategori;
    }
    if ($ara !== '') {
        $parametreler['ara'] = $ara;
    }
    return count($parametreler) > 0 ? 'urunler.php?' . http_build_query($parametreler) : 'urunler.php';
}

function acilUrunler($urunler, $adet = 8)
{
    $aday = array_filter($urunler, function ($u) {
        return urunDurumu($u['skt']) !== 'Bozuldu';
    });
    return array_slice(tariheGoreSirala($aday), 0, $adet);
}

function bozukUrunler($urunler)
{
    $aday = array_filter($urunler, function ($u) {
        return urunDurumu($u['skt']) === 'Bozuldu';
    });
    return tariheGoreSirala($aday);
}

function akilliSefTarifleri($urunler)
{
    $saglamlar = tariheGoreSirala(array_filter($urunler, function ($u) {
        return urunDurumu($u['skt']) !== 'Bozuldu';
    }));

    if (count($saglamlar) === 0) {
        return [];
    }

    $bul = function (array $anahtarlar) use ($saglamlar) {
        foreach ($saglamlar as $u) {
            $metin = mb_strtolower($u['urun'] . ' ' . $u['kategori'], 'UTF-8');
            foreach ($anahtarlar as $k) {
                if (mb_stripos($metin, $k, 0, 'UTF-8') !== false) {
                    return $u;
                }
            }
        }
        return null;
    };

    $katalog = [
        [
            'baslik'     => 'Kıymalı Menemen / Izgara Köfte',
            'ikon'       => '🍳',
            'sure'       => '20 dk',
            'etiket'     => 'Sıfır İsraf Şef Önerisi',
            'grup1'      => ['kıyma', 'dana', 'köfte', 'et'],
            'grup2'      => ['domates', 'biber', 'soğan', 'yoğurt', 'turşu', 'ketçap'],
            'tarif_notu' => 'Tavada sulu domateslerle kıymalı menemen veya yanında yoğurtla hızlı ızgara köfte hazırlayabilirsin.',
        ],
        [
            'baslik'     => 'Kıymalı & Yoğurtlu Taze Ispanak Yemeği',
            'ikon'       => '🥬',
            'sure'       => '25 dk',
            'etiket'     => 'Yüksek Protein & Demir',
            'grup1'      => ['ıspanak', 'yeşillik', 'pazı'],
            'grup2'      => ['kıyma', 'yoğurt', 'süt', 'peynir'],
            'tarif_notu' => 'Ispanakları bozulmadan soteleyip üzerine sarımsaklı süzme yoğurt veya kıyma sosu ekleyebilirsin.',
        ],
        [
            'baslik'     => 'Fırında Limonlu Somon & Akdeniz Salatası',
            'ikon'       => '🐟',
            'sure'       => '22 dk',
            'etiket'     => 'Omega-3 Menüsü',
            'grup1'      => ['somon', 'balık', 'fileto'],
            'grup2'      => ['domates', 'ıspanak', 'salata', 'turşu', 'sebze'],
            'tarif_notu' => 'Somon filetoları fırınlayıp yanına taze domates ve yeşilliklerle ferah bir salata kurabilirsin.',
        ],
        [
            'baslik'     => 'Sütlü & Peynirli Hızlı Kahvaltı / Omlet',
            'ikon'       => '🥛',
            'sure'       => '10 dk',
            'etiket'     => 'Hızlı Kahvaltı Kurtarıcısı',
            'grup1'      => ['süt', 'portakal suyu', 'kefir'],
            'grup2'      => ['peynir', 'domates', 'yoğurt', 'sütlaç'],
            'tarif_notu' => 'Tarihi yaklaşan süt ve içecekleri peynirli omlet veya kahvaltı tabağıyla bugün değerlendirebilirsin.',
        ],
        [
            'baslik'     => 'Sebzeli Tavuk Sote & Yoğurt Eşliği',
            'ikon'       => '🍗',
            'sure'       => '20 dk',
            'etiket'     => 'Pratik Akşam Yemeği',
            'grup1'      => ['tavuk', 'bonfile', 'göğsü'],
            'grup2'      => ['domates', 'yoğurt', 'sos', 'ketçap', 'sebze'],
            'tarif_notu' => 'Tavukları küp doğrayıp domates ve baharatlarla yüksek ateşte soteleyebilirsin.',
        ],
    ];

    $oneriler = [];
    foreach ($katalog as $kural) {
        $m1 = $bul($kural['grup1']);
        $m2 = $bul($kural['grup2']);
        if ($m1 !== null && $m2 !== null && $m1['id'] !== $m2['id']) {
            if (kalanGun($m2['skt']) < kalanGun($m1['skt'])) {
                $gecici = $m1;
                $m1 = $m2;
                $m2 = $gecici;
            }
            $oneriler[] = [
                'baslik'          => $kural['baslik'],
                'ikon'            => $kural['ikon'],
                'sure'            => $kural['sure'],
                'etiket'          => $kural['etiket'],
                'aciklama'        => 'Dolapta ' . $m1['urun'] . ' (' . kalanGunMetni($m1['skt']) . ') ve ' . $m2['urun'] . ' var → Bugün ' . $kural['baslik'] . ' yapabilirsin!',
                'detay'           => $kural['tarif_notu'],
                'malzemeler'      => [$m1, $m2],
                'oncelik_urun_id' => (int) $m1['id'],
                'oncelik_urun_ad' => $m1['urun'],
                'acil_skor'       => min(kalanGun($m1['skt']), kalanGun($m2['skt'])),
            ];
        }
    }

    usort($oneriler, function ($a, $b) {
        return $a['acil_skor'] <=> $b['acil_skor'];
    });

    $ilk = $saglamlar[0];
    $ikinci = $saglamlar[1] ?? null;
    $dinamikBaslik = $ikinci
        ? $ilk['urun'] . ' & ' . $ikinci['urun'] . ' Mutfak Tabağı'
        : $ilk['urun'] . ' ile Pratik Öğün';
    $dinamikAciklama = $ikinci
        ? 'Dolapta ' . $ilk['urun'] . ' (' . kalanGunMetni($ilk['skt']) . ') ve ' . $ikinci['urun'] . ' (' . kalanGunMetni($ikinci['skt']) . ') bulunuyor → Öncelikli olarak bu ikiliyi değerlendirebilirsin!'
        : 'Dolapta en acil ürün ' . $ilk['urun'] . ' (' . kalanGunMetni($ilk['skt']) . ') → Ziyan olmaması için bugün tüketebilirsin!';

    $oneriler[] = [
        'baslik'          => $dinamikBaslik,
        'ikon'            => '👨‍🍳',
        'sure'            => '15 dk',
        'etiket'          => 'Acil SKT Önceliği',
        'aciklama'        => $dinamikAciklama,
        'detay'           => 'Son kullanma tarihi en yakın ürünleri ilk sırada tüketerek hem +20 XP kazanır hem de mutfak sağlık skorunu yükseltirsin.',
        'malzemeler'      => $ikinci ? [$ilk, $ikinci] : [$ilk],
        'oncelik_urun_id' => (int) $ilk['id'],
        'oncelik_urun_ad' => $ilk['urun'],
        'acil_skor'       => kalanGun($ilk['skt']),
    ];

    return $oneriler;
}

function csrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfAlani()
{
    return '<input type="hidden" name="csrf" value="' . csrfToken() . '">';
}

function csrfGecerliMi($gelen)
{
    return is_string($gelen) && hash_equals(csrfToken(), $gelen);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !csrfGecerliMi($_POST['csrf'] ?? null)) {
    mesajKaydet('hata', 'Güvenlik doğrulaması başarısız oldu (sayfa çok uzun süre açık kalmış olabilir). Lütfen işlemi tekrar deneyin.');
    header('Location: ' . basename($_SERVER['SCRIPT_NAME']));
    exit;
}
