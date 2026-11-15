<?php

require 'functions.php';
girisKontrol();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: urunler.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$islem = $_POST['islem'] ?? '';
$donus = in_array($_POST['donus'] ?? '', ['index.php', 'urunler.php'], true) ? $_POST['donus'] : 'urunler.php';
$kullaniciId = aktifKullaniciId();

if ($islem === 'bozuklari_temizle') {
    $tumUrunler = urunleriGetir($kullaniciId);
    $temizlenenSayi = 0;
    $kazanilanXp = 0;
    foreach ($tumUrunler as $u) {
        if (urunDurumu($u['skt']) === 'Bozuldu' && urunSil($kullaniciId, (int) $u['id'])) {
            $temizlenenSayi++;
            $kazanilanXp += xpEkle($kullaniciId, 'bozuk_temizle', XP_BOZUK_TEMIZLE, 'Bozuk ürün temizlendi: ' . $u['urun'], XP_GUNLUK_LIMIT);
        }
    }
    if ($temizlenenSayi > 0) {
        mesajKaydet('basari', $temizlenenSayi . ' adet bozulmuş ürün buzdolabından temizlendi.');
        if ($kazanilanXp > 0) {
            xpBildir('🧹 ' . $temizlenenSayi . ' bozuk ürün temizlendi! +' . $kazanilanXp . ' XP');
        }
    } else {
        mesajKaydet('hata', 'Temizlenecek bozulmuş ürün bulunamadı.');
    }
    header('Location: ' . $donus);
    exit;
}

$urun = urunGetir($kullaniciId, $id);

if ($urun !== null && urunSil($kullaniciId, $id)) {
    if (urunDurumu($urun['skt']) === 'Bozuldu') {
        mesajKaydet('basari', '"' . $urun['urun'] . '" dolaptan temizlendi.');
        if (xpEkle($kullaniciId, 'bozuk_temizle', XP_BOZUK_TEMIZLE, 'Bozuk ürün temizlendi: ' . $urun['urun'], XP_GUNLUK_LIMIT) > 0) {
            xpBildir('🧹 Bozuk ürün temizlendi: ' . $urun['urun'] . ' (+' . XP_BOZUK_TEMIZLE . ' XP)');
        }
    } else {
        mesajKaydet('basari', '"' . $urun['urun'] . '" afiyetle tüketildi ve envanterden düşüldü.');
        if (xpEkle($kullaniciId, 'urun_tuket', XP_URUN_TUKET, 'Ziyan etmeden tüketildi: ' . $urun['urun'], XP_GUNLUK_LIMIT) > 0) {
            xpBildir('😋 Ziyan etmeden tüketildi: ' . $urun['urun'] . ' (+' . XP_URUN_TUKET . ' XP)');
        }
    }
} else {
    mesajKaydet('hata', 'İşlem yapılmak istenen ürün bulunamadı.');
}

header('Location: ' . $donus);
exit;
