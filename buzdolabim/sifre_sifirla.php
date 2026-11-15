<?php

require 'functions.php';

$sayfaBasligi = 'Yeni Şifre Belirle';
$hatalar = [];
$token = $_GET['token'] ?? '';
$kayit = gecerliSifirlamaKaydi($token);

if ($kayit !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $sifre       = $_POST['sifre'] ?? '';
    $sifreTekrar = $_POST['sifre_tekrar'] ?? '';

    if (!mb_check_encoding($sifre, 'UTF-8') || mb_strlen($sifre) < 4) {
        $hatalar[] = 'Şifre en az 4 karakter olmalıdır.';
    } elseif ($sifre !== $sifreTekrar) {
        $hatalar[] = 'Şifre ve şifre tekrarı birbiriyle eşleşmiyor.';
    }

    if (count($hatalar) === 0) {
        sifreyiSifirla((int) $kayit['kullanici_id'], $sifre);
        unset($_SESSION['kullanici_hafta2']);
        session_regenerate_id(true);
        mesajKaydet('basari', 'Şifreniz güncellendi. Yeni şifrenizle giriş yapabilirsiniz.');
        header('Location: giris.php');
        exit;
    }
}

require 'header.php';
?>

<section class="giris-split-sarmal">
    <div class="giris-split-panel sifre-panel">
        <div class="giris-split-sol">
            <?php if ($kayit === null): ?>
                <div class="giris-baslik-kutu">
                    <h1>Bağlantı Geçersiz</h1>
                    <p>Bu şifre sıfırlama bağlantısının süresi dolmuş, daha önce kullanılmış ya da hatalı kopyalanmış olabilir.</p>
                </div>
                <p style="margin-top:22px;text-align:center"><a href="sifremi_unuttum.php" class="giris-hap-buton" style="display:inline-block;text-decoration:none">Yeni bağlantı iste →</a></p>
            <?php else: ?>
                <div class="giris-baslik-kutu">
                    <h1>Yeni Şifre Belirle</h1>
                    <p><strong><?= guvenli($kayit['kullanici_adi']) ?></strong> hesabı için yeni şifrenizi girin.</p>
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

                <form method="post" action="sifre_sifirla.php?token=<?= guvenli($token) ?>" class="giris-modern-form" novalidate>
                    <?= csrfAlani() ?>
                    <label class="giris-hap-etiket">
                        <span>Yeni Şifre</span>
                        <input type="password" name="sifre" class="giris-hap-girdi" placeholder="••••••••" required autofocus autocomplete="new-password">
                    </label>
                    <label class="giris-hap-etiket">
                        <span>Yeni Şifre (Tekrar)</span>
                        <input type="password" name="sifre_tekrar" class="giris-hap-girdi" placeholder="••••••••" required autocomplete="new-password">
                    </label>
                    <button type="submit" class="giris-hap-buton">Şifreyi Güncelle →</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
