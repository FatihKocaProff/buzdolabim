<?php

require_once __DIR__ . '/db_ayar.php';

function db()
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $secenekler = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 5,
    ];

    try {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_ADI . ';charset=utf8mb4', DB_KULLANICI, DB_SIFRE, $secenekler);
        } catch (PDOException $ilk) {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4', DB_KULLANICI, DB_SIFRE, $secenekler);
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_ADI . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo->exec('USE `' . DB_ADI . '`');
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS kullanicilar (
            id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            kullanici_adi VARCHAR(50)  NOT NULL UNIQUE,
            sifre_hash    VARCHAR(255) NOT NULL,
            ad_soyad      VARCHAR(100) NOT NULL,
            eposta        VARCHAR(150) NOT NULL DEFAULT '',
            mutfak_adi    VARCHAR(100) NOT NULL DEFAULT 'Evimdeki Buzdolabı',
            avatar_renk   VARCHAR(7)   NOT NULL DEFAULT '#141414',
            xp            INT          NOT NULL DEFAULT 0,
            seri          INT          NOT NULL DEFAULT 0,
            en_uzun_seri  INT          NOT NULL DEFAULT 0,
            son_aktif_gun DATE         NULL,
            olusturma     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS urunler (
            id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            kullanici_id INT UNSIGNED NOT NULL,
            urun         VARCHAR(60)  NOT NULL,
            kategori     VARCHAR(50)  NOT NULL,
            miktar       VARCHAR(50)  NOT NULL,
            skt          DATE         NOT NULL,
            aciklama     VARCHAR(200) NOT NULL DEFAULT '',
            olusturma    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS xp_gecmisi (
            id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            kullanici_id INT UNSIGNED NOT NULL,
            tur          VARCHAR(30)  NOT NULL,
            puan         INT          NOT NULL,
            aciklama     VARCHAR(150) NOT NULL DEFAULT '',
            tarih        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS sifre_sifirlama (
            id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            kullanici_id   INT UNSIGNED NOT NULL,
            token_hash     CHAR(64)     NOT NULL UNIQUE,
            son_gecerlilik DATETIME     NOT NULL,
            kullanildi     TINYINT(1)   NOT NULL DEFAULT 0,
            olusturma      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        if ((int) $pdo->query('SELECT COUNT(*) FROM kullanicilar')->fetchColumn() === 0) {
            kullaniciEkle('admin', '1234', 'Yönetici', 'admin@buzdolabim.local', 'Merkez Mutfak', '#141414');
            kullaniciEkle('ogrenci', '1234', 'Öğrenci Kullanıcı', 'ogrenci@kampus.edu.tr', 'Öğrenci Evi Mutfağı', '#16a34a');
        }
    } catch (PDOException $e) {
        $pdo = null;
        throw new RuntimeException("MySQL bağlantısı kurulamadı. XAMPP Control Panel'den MySQL'in çalıştığını ve db_ayar.php içindeki bilgileri kontrol edin.", 0, $e);
    }

    return $pdo;
}

function kullaniciBul($kullaniciAdi)
{
    $sorgu = db()->prepare('SELECT * FROM kullanicilar WHERE LOWER(kullanici_adi) = LOWER(?) LIMIT 1');
    $sorgu->execute([$kullaniciAdi]);
    $satir = $sorgu->fetch();
    return $satir ?: null;
}

function kullaniciEkle($kullaniciAdi, $sifre, $adSoyad, $eposta, $mutfakAdi = 'Evimdeki Buzdolabı', $avatarRenk = '#141414')
{
    $pdo = db();
    $pdo->prepare('INSERT INTO kullanicilar (kullanici_adi, sifre_hash, ad_soyad, eposta, mutfak_adi, avatar_renk) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute([$kullaniciAdi, password_hash($sifre, PASSWORD_DEFAULT), $adSoyad, $eposta, $mutfakAdi, $avatarRenk]);
    ornekUrunleriYukle((int) $pdo->lastInsertId());
}

function kullaniciDogrula($kimlik, $sifre)
{
    $adaylar = str_contains($kimlik, '@')
        ? kullanicilariEpostaIleBul($kimlik)
        : array_filter([kullaniciBul($kimlik)]);
    foreach ($adaylar as $k) {
        if (password_verify($sifre, $k['sifre_hash'])) {
            return $k;
        }
    }
    return null;
}

function kullaniciGuncelle($id, $kullaniciAdi, $adSoyad, $eposta, $mutfakAdi, $avatarRenk, $yeniSifre = '')
{
    $sql = 'UPDATE kullanicilar SET kullanici_adi = ?, ad_soyad = ?, eposta = ?, mutfak_adi = ?, avatar_renk = ?';
    $deger = [$kullaniciAdi, $adSoyad, $eposta, $mutfakAdi, $avatarRenk];
    if ($yeniSifre !== '') {
        $sql .= ', sifre_hash = ?';
        $deger[] = password_hash($yeniSifre, PASSWORD_DEFAULT);
    }
    $sql .= ' WHERE id = ?';
    $deger[] = $id;
    db()->prepare($sql)->execute($deger);
}

function kullanicilariEpostaIleBul($eposta)
{
    $sorgu = db()->prepare('SELECT * FROM kullanicilar WHERE LOWER(eposta) = LOWER(?)');
    $sorgu->execute([$eposta]);
    return $sorgu->fetchAll();
}

function sifirlamaTokeniOlustur($kullaniciId, $dakika)
{
    $token = bin2hex(random_bytes(32));
    db()->prepare('INSERT INTO sifre_sifirlama (kullanici_id, token_hash, son_gecerlilik) VALUES (?, ?, ?)')
        ->execute([$kullaniciId, hash('sha256', $token), date('Y-m-d H:i:s', time() + $dakika * 60)]);
    return $token;
}

function sonSifirlamaIstekSayisi($kullaniciId, $dakika)
{
    $sorgu = db()->prepare('SELECT COUNT(*) FROM sifre_sifirlama WHERE kullanici_id = ? AND olusturma >= ?');
    $sorgu->execute([$kullaniciId, date('Y-m-d H:i:s', time() - $dakika * 60)]);
    return (int) $sorgu->fetchColumn();
}

function gecerliSifirlamaKaydi($token)
{
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }
    $sorgu = db()->prepare('SELECT s.id, s.kullanici_id, k.kullanici_adi, k.ad_soyad
        FROM sifre_sifirlama s JOIN kullanicilar k ON k.id = s.kullanici_id
        WHERE s.token_hash = ? AND s.kullanildi = 0 AND s.son_gecerlilik >= ? LIMIT 1');
    $sorgu->execute([hash('sha256', $token), date('Y-m-d H:i:s')]);
    return $sorgu->fetch() ?: null;
}

function sifreyiSifirla($kullaniciId, $yeniSifre)
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('UPDATE kullanicilar SET sifre_hash = ? WHERE id = ?')
            ->execute([password_hash($yeniSifre, PASSWORD_DEFAULT), $kullaniciId]);
        $pdo->prepare('UPDATE sifre_sifirlama SET kullanildi = 1 WHERE kullanici_id = ?')->execute([$kullaniciId]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function oturumKullanicisi($k)
{
    return [
        'id'            => (int) $k['id'],
        'kullanici_adi' => $k['kullanici_adi'],
        'ad_soyad'      => $k['ad_soyad'],
        'eposta'        => $k['eposta'] !== '' ? $k['eposta'] : $k['kullanici_adi'] . '@buzdolabim.local',
        'mutfak_adi'    => $k['mutfak_adi'],
        'avatar_renk'   => $k['avatar_renk'],
    ];
}

function aktifKullaniciId()
{
    static $dogrulandi = null;
    $k = $_SESSION['kullanici_hafta2'] ?? null;
    if ($k === null) {
        return null;
    }
    if ($dogrulandi === null) {
        $kayit = kullaniciBul($k['kullanici_adi'] ?? '');
        if ($kayit === null || (isset($k['id']) && (int) $k['id'] !== (int) $kayit['id'])) {
            unset($_SESSION['kullanici_hafta2']);
            return null;
        }
        $_SESSION['kullanici_hafta2']['id'] = (int) $kayit['id'];
        $dogrulandi = true;
    }
    return (int) $_SESSION['kullanici_hafta2']['id'];
}

function urunSatiri($satir)
{
    return [
        'id'       => (int) $satir['id'],
        'urun'     => $satir['urun'],
        'kategori' => $satir['kategori'],
        'miktar'   => $satir['miktar'],
        'skt'      => $satir['skt'],
        'aciklama' => $satir['aciklama'],
    ];
}

const URUN_SECIM = 'SELECT id, urun, kategori, miktar, skt, aciklama FROM urunler';

function urunleriGetir($kullaniciId)
{
    $sorgu = db()->prepare(URUN_SECIM . ' WHERE kullanici_id = ? ORDER BY id');
    $sorgu->execute([$kullaniciId]);
    return array_map('urunSatiri', $sorgu->fetchAll());
}

function urunGetir($kullaniciId, $id)
{
    $sorgu = db()->prepare(URUN_SECIM . ' WHERE kullanici_id = ? AND id = ?');
    $sorgu->execute([$kullaniciId, $id]);
    $satir = $sorgu->fetch();
    return $satir ? urunSatiri($satir) : null;
}

function urunEkle($kullaniciId, $veri)
{
    db()->prepare('INSERT INTO urunler (kullanici_id, urun, kategori, miktar, skt, aciklama) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute([$kullaniciId, $veri['urun'], $veri['kategori'], $veri['miktar'], $veri['skt'], $veri['aciklama']]);
}

function urunGuncelle($kullaniciId, $id, $veri)
{
    db()->prepare('UPDATE urunler SET urun = ?, kategori = ?, miktar = ?, skt = ?, aciklama = ? WHERE id = ? AND kullanici_id = ?')
        ->execute([$veri['urun'], $veri['kategori'], $veri['miktar'], $veri['skt'], $veri['aciklama'], $id, $kullaniciId]);
}

function urunSil($kullaniciId, $id)
{
    $sorgu = db()->prepare('DELETE FROM urunler WHERE id = ? AND kullanici_id = ?');
    $sorgu->execute([$id, $kullaniciId]);
    return $sorgu->rowCount() > 0;
}

function ornekUrunleriYukle($kullaniciId)
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM urunler WHERE kullanici_id = ?')->execute([$kullaniciId]);
        foreach (ornekUrunler() as $urun) {
            urunEkle($kullaniciId, $urun);
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

const XP_GUNLUK_TEMEL   = 10;
const XP_SERI_BONUSU    = 5;
const XP_SERI_BONUS_MAX = 40;
const XP_URUN_EKLE      = 15;
const XP_URUN_TUKET     = 20;
const XP_BOZUK_TEMIZLE  = 5;
const XP_GUNLUK_LIMIT   = 10;

function xpEkle($kullaniciId, $tur, $puan, $aciklama = '', $gunlukLimit = null)
{
    $pdo = db();
    if ($gunlukLimit !== null) {
        $sorgu = $pdo->prepare('SELECT COUNT(*) FROM xp_gecmisi WHERE kullanici_id = ? AND tur = ? AND tarih >= ?');
        $sorgu->execute([$kullaniciId, $tur, date('Y-m-d')]);
        if ((int) $sorgu->fetchColumn() >= $gunlukLimit) {
            return 0;
        }
    }
    $pdo->prepare('INSERT INTO xp_gecmisi (kullanici_id, tur, puan, aciklama) VALUES (?, ?, ?, ?)')
        ->execute([$kullaniciId, $tur, $puan, mb_substr($aciklama, 0, 150)]);
    $pdo->prepare('UPDATE kullanicilar SET xp = xp + ? WHERE id = ?')->execute([$puan, $kullaniciId]);
    return $puan;
}

function gunlukSeriKontrol($kullaniciId)
{
    if ($kullaniciId === null) {
        return;
    }
    $bugun = date('Y-m-d');
    $dun   = date('Y-m-d', strtotime('-1 day'));

    $sorgu = db()->prepare('UPDATE kullanicilar SET
            seri          = IF(son_aktif_gun = ?, seri + 1, 1),
            en_uzun_seri  = GREATEST(en_uzun_seri, seri),
            son_aktif_gun = ?
        WHERE id = ? AND (son_aktif_gun IS NULL OR son_aktif_gun < ?)');
    $sorgu->execute([$dun, $bugun, $kullaniciId, $bugun]);
    if ($sorgu->rowCount() === 0) {
        return;
    }

    $oku = db()->prepare('SELECT seri FROM kullanicilar WHERE id = ?');
    $oku->execute([$kullaniciId]);
    $seri = (int) $oku->fetchColumn();

    $puan = XP_GUNLUK_TEMEL + min(($seri - 1) * XP_SERI_BONUSU, XP_SERI_BONUS_MAX);
    xpEkle($kullaniciId, 'gunluk_seri', $puan, $seri . '. gün serisi');
    xpBildir($seri === 1
        ? '🔥 Günlük seri başladı! +' . $puan . ' XP'
        : '🔥 ' . $seri . ' günlük seri! +' . $puan . ' XP');
}

function oyunDurumu($kullaniciId)
{
    $sorgu = db()->prepare('SELECT xp, seri, en_uzun_seri, son_aktif_gun FROM kullanicilar WHERE id = ?');
    $sorgu->execute([$kullaniciId]);
    $s = $sorgu->fetch() ?: ['xp' => 0, 'seri' => 0, 'en_uzun_seri' => 0, 'son_aktif_gun' => null];

    $seri = (int) $s['seri'];
    if ($s['son_aktif_gun'] === null || $s['son_aktif_gun'] < date('Y-m-d', strtotime('-1 day'))) {
        $seri = 0;
    }
    return ['xp' => (int) $s['xp'], 'seri' => $seri, 'en_uzun_seri' => (int) $s['en_uzun_seri']];
}

function seviyeBilgisi($xp)
{
    $seviye = 1; $gereken = 100; $kalan = $xp;
    while ($kalan >= $gereken) { $kalan -= $gereken; $seviye++; $gereken += 50; }
    $unvanlar = [1 => 'Çaylak Aşçı', 3 => 'Mutfak Çırağı', 5 => 'Kiler Ustası', 8 => 'Şef Yardımcısı', 12 => 'Baş Şef', 16 => 'Mutfak Efsanesi'];
    $unvan = 'Çaylak Aşçı';
    foreach ($unvanlar as $sv => $ad) { if ($seviye >= $sv) { $unvan = $ad; } }
    return ['seviye' => $seviye, 'unvan' => $unvan, 'ilerleme' => $kalan, 'gereken' => $gereken,
            'yuzde' => (int) floor($kalan * 100 / $gereken)];
}

function rozetListesi($kullaniciId, $oyun)
{
    $sorgu = db()->prepare('SELECT tur, COUNT(*) FROM xp_gecmisi WHERE kullanici_id = ? GROUP BY tur');
    $sorgu->execute([$kullaniciId]);
    $say = $sorgu->fetchAll(PDO::FETCH_KEY_PAIR);
    $tuket = (int) ($say['urun_tuket'] ?? 0);
    $ekle  = (int) ($say['urun_ekle'] ?? 0);
    $temiz = (int) ($say['bozuk_temizle'] ?? 0);
    $seri  = $oyun['en_uzun_seri'];
    $xp    = $oyun['xp'];

    $tanim = [
        ['🔥', 'İlk Kıvılcım',     'Günlük seriyi başlat',            $seri, 1],
        ['📅', 'Haftalık Disiplin', '7 günlük seriye ulaş',            $seri, 7],
        ['🏆', 'Aylık Efsane',      '30 günlük seriye ulaş',           $seri, 30],
        ['🛒', 'Kiler Dolduran',    '10 ürün ekle',                    $ekle, 10],
        ['🥗', 'Sıfır İsraf',       '10 ürünü bozulmadan tüket',       $tuket, 10],
        ['🧹', 'Temizlik Görevlisi','5 bozuk ürünü temizle',           $temiz, 5],
        ['⭐', 'XP Avcısı',         '500 XP topla',                    $xp, 500],
        ['💎', 'XP Ustası',         '2000 XP topla',                   $xp, 2000],
    ];
    $liste = [];
    foreach ($tanim as [$ikon, $ad, $aciklama, $deger, $hedef]) {
        $liste[] = ['ikon' => $ikon, 'ad' => $ad, 'aciklama' => $aciklama, 'deger' => min($deger, $hedef),
                    'hedef' => $hedef, 'acik' => $deger >= $hedef];
    }
    return $liste;
}

function xpBildir($metin)
{
    $_SESSION['xp_bildirimleri'][] = $metin;
}

function xpBildirimleriAl()
{
    $liste = $_SESSION['xp_bildirimleri'] ?? [];
    unset($_SESSION['xp_bildirimleri']);
    return $liste;
}

function bugunXpSayilari($kullaniciId)
{
    $sonuc = [
        'urun_tuket'    => 0,
        'bozuk_temizle' => 0,
        'urun_ekle'     => 0,
        'gunluk_seri'   => 0,
        'bugun_xp'      => 0,
    ];
    if ($kullaniciId === null) {
        return $sonuc;
    }
    $sorgu = db()->prepare('SELECT tur, COUNT(*) AS adet, COALESCE(SUM(puan), 0) AS toplam_puan FROM xp_gecmisi WHERE kullanici_id = ? AND tarih >= ? GROUP BY tur');
    $sorgu->execute([$kullaniciId, date('Y-m-d')]);
    foreach ($sorgu->fetchAll() as $satir) {
        $tur = $satir['tur'];
        if (isset($sonuc[$tur])) {
            $sonuc[$tur] = (int) $satir['adet'];
        }
        $sonuc['bugun_xp'] += (int) $satir['toplam_puan'];
    }
    return $sonuc;
}
