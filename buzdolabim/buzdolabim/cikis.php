<?php

require 'functions.php';

if (!csrfGecerliMi($_GET['csrf'] ?? null)) {
    header('Location: index.php');
    exit;
}

unset($_SESSION['kullanici_hafta2']);
session_regenerate_id(true);
mesajKaydet('basari', 'Başarıyla çıkış yaptınız.');

header('Location: giris.php');
exit;
