<?php

require 'functions.php';
require_once 'mailer.php';

$sayfaBasligi = 'Şifremi Unuttum';
$hatalar = [];
$gonderildi = false;
$girdi = '';

function sifirlamaMailiGonder($kullanici)
{
    $eposta = $kullanici['eposta'];
    if ($eposta === '' || !filter_var($eposta, FILTER_VALIDATE_EMAIL) || str_ends_with(strtolower($eposta), '@buzdolabim.local')) {
        return;
    }
    if (sonSifirlamaIstekSayisi((int) $kullanici['id'], 15) >= 3) {
        return;
    }

    $token = sifirlamaTokeniOlustur((int) $kullanici['id'], SIFIRLAMA_GECERLILIK_DK);
    $link  = SITE_ADRESI . '/sifre_sifirla.php?token=' . $token;
    $ad    = $kullanici['ad_soyad'];
    $dk    = SIFIRLAMA_GECERLILIK_DK;

    $duz = "Merhaba $ad,\n\n"
        . "Buzdolabım hesabınız ({$kullanici['kullanici_adi']}) için şifre sıfırlama isteği aldık.\n"
        . "Yeni şifre belirlemek için aşağıdaki bağlantıyı açın ($dk dakika geçerlidir):\n\n"
        . "$link\n\n"
        . "Bu isteği siz yapmadıysanız bu e-postayı yok sayabilirsiniz; şifreniz değişmez.\n\n"
        . "— Buzdolabım";

    $h = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $html = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:520px;margin:0 auto;color:#141414">'
        . '<h2 style="color:#ff4f17;margin-bottom:8px">Buzdolabım · Şifre Sıfırlama</h2>'
        . '<p>Merhaba <strong>' . $h($ad) . '</strong>,</p>'
        . '<p><strong>' . $h($kullanici['kullanici_adi']) . '</strong> kullanıcı adlı hesabınız için şifre sıfırlama isteği aldık.</p>'
        . '<p style="margin:28px 0"><a href="' . $h($link) . '" style="background:#ff4f17;color:#fff;padding:12px 22px;border-radius:4px;text-decoration:none;font-weight:bold">Yeni Şifre Belirle</a></p>'
        . '<p style="font-size:13px;color:#4b4944">Bağlantı ' . $dk . ' dakika geçerlidir ve yalnızca bir kez kullanılabilir. Buton çalışmazsa bu adresi tarayıcınıza yapıştırın:<br>'
        . '<a href="' . $h($link) . '" style="color:#ff4f17;word-break:break-all">' . $h($link) . '</a></p>'
        . '<p style="font-size:13px;color:#4b4944">Bu isteği siz yapmadıysanız bu e-postayı yok sayabilirsiniz; şifreniz değişmez.</p>'
        . '</div>';

    if (!epostaGonder($eposta, $ad, 'Buzdolabım şifre sıfırlama bağlantınız', $duz, $html)) {
        throw new RuntimeException('mail');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $girdi = trim($_POST['kimlik'] ?? '');

    if (!mailAyarliMi()) {
        $hatalar[] = 'E-posta gönderimi henüz ayarlanmamış. Yönetici mail_ayar.php dosyasına Gmail adresini ve uygulama şifresini girmeli.';
    } elseif ($girdi === '' || !mb_check_encoding($girdi, 'UTF-8') || mb_strlen($girdi) > 150) {
        $hatalar[] = 'Lütfen kullanıcı adınızı veya e-posta adresinizi girin.';
    } else {
        $hedefler = str_contains($girdi, '@')
            ? kullanicilariEpostaIleBul($girdi)
            : array_filter([kullaniciBul($girdi)]);

        try {
            foreach ($hedefler as $k) {
                sifirlamaMailiGonder($k);
            }
            $gonderildi = true;
        } catch (RuntimeException $e) {
            $hatalar[] = 'E-posta şu anda gönderilemedi. Lütfen biraz sonra tekrar deneyin.';
        }
    }
}

require 'header.php';
?>

<section class="giris-split-sarmal">
    <div class="giris-split-panel sifre-panel">
        <div class="giris-split-sol">
            <div class="giris-baslik-kutu">
                <h1>Şifremi Unuttum</h1>
                <p>Kullanıcı adınızı veya hesabınıza kayıtlı e-posta adresini girin. Size şifre sıfırlama bağlantısı gönderelim.</p>
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

            <?php if ($gonderildi): ?>
                <div class="uyari uyari-basari">
                    Bilgiler bir hesapla eşleşiyorsa, kayıtlı e-posta adresine <?= (int) SIFIRLAMA_GECERLILIK_DK ?> dakika geçerli bir sıfırlama bağlantısı gönderildi. Gelen kutunuzu (ve spam klasörünü) kontrol edin.
                </div>
                <p style="margin-top:22px;text-align:center"><a href="giris.php" class="giris-hap-buton" style="display:inline-block;text-decoration:none">← Giriş sayfasına dön</a></p>
            <?php else: ?>
                <form method="post" action="sifremi_unuttum.php" class="giris-modern-form" novalidate>
                    <?= csrfAlani() ?>
                    <label class="giris-hap-etiket">
                        <span>Kullanıcı Adı veya E-posta</span>
                        <input type="text" name="kimlik" class="giris-hap-girdi" value="<?= guvenli($girdi) ?>" placeholder="örn. fatih veya fatih@gmail.com" required autofocus>
                    </label>
                    <button type="submit" class="giris-hap-buton">Sıfırlama Bağlantısı Gönder →</button>
                </form>
                <p class="giris-alt-gecis" style="margin-top:22px;text-align:center;font-size:.95rem">
                    Şifreni hatırladın mı? <a href="giris.php" style="font-weight:700;color:inherit;text-decoration:underline">Giriş yap</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>
