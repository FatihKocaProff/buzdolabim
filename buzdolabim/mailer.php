<?php

require_once __DIR__ . '/mail_ayar.php';

function mailAyarliMi()
{
    return MAIL_GMAIL_ADRES !== '' && MAIL_UYGULAMA_SIFRESI !== '';
}

function smtpYanitOku($baglanti)
{
    $yanit = '';
    while (($satir = fgets($baglanti, 515)) !== false) {
        $yanit .= $satir;
        if (strlen($satir) < 4 || $satir[3] === ' ') {
            break;
        }
    }
    return $yanit;
}

function smtpKomut($baglanti, $komut, $beklenenKod)
{
    if ($komut !== null) {
        fwrite($baglanti, $komut . "\r\n");
    }
    $yanit = smtpYanitOku($baglanti);
    if ((int) substr($yanit, 0, 3) !== $beklenenKod) {
        throw new RuntimeException('SMTP beklenmeyen yanıt (' . $beklenenKod . ' bekleniyordu): ' . trim($yanit));
    }
    return $yanit;
}

function mimeBaslik($metin)
{
    return '=?UTF-8?B?' . base64_encode($metin) . '?=';
}

function epostaGonder($aliciAdres, $aliciAd, $konu, $duzMetin, $html)
{
    if (!mailAyarliMi()) {
        error_log('[buzdolabim] E-posta gönderilemedi: mail_ayar.php doldurulmamış.');
        return false;
    }

    $kullanici = MAIL_GMAIL_ADRES;
    $sifre     = str_replace(' ', '', MAIL_UYGULAMA_SIFRESI);

    $baglam = stream_context_create(['ssl' => [
        'verify_peer'      => true,
        'verify_peer_name' => true,
        'peer_name'        => 'smtp.gmail.com',
    ]]);

    $baglanti = @stream_socket_client('ssl://smtp.gmail.com:465', $hataNo, $hataMetni, 15, STREAM_CLIENT_CONNECT, $baglam);
    if ($baglanti === false) {
        error_log('[buzdolabim] SMTP bağlantısı kurulamadı: ' . $hataNo . ' ' . $hataMetni);
        return false;
    }
    stream_set_timeout($baglanti, 15);

    try {
        smtpKomut($baglanti, null, 220);
        smtpKomut($baglanti, 'EHLO buzdolabim.local', 250);
        smtpKomut($baglanti, 'AUTH LOGIN', 334);
        smtpKomut($baglanti, base64_encode($kullanici), 334);
        smtpKomut($baglanti, base64_encode($sifre), 235);
        smtpKomut($baglanti, 'MAIL FROM:<' . $kullanici . '>', 250);
        smtpKomut($baglanti, 'RCPT TO:<' . $aliciAdres . '>', 250);
        smtpKomut($baglanti, 'DATA', 354);

        $sinir = 'bz_' . bin2hex(random_bytes(12));
        $alanAdi = substr(strrchr($kullanici, '@'), 1) ?: 'gmail.com';

        $basliklar = [
            'Date: ' . date('r'),
            'From: ' . mimeBaslik(MAIL_GONDEREN_AD) . ' <' . $kullanici . '>',
            'To: ' . ($aliciAd !== '' ? mimeBaslik($aliciAd) . ' ' : '') . '<' . $aliciAdres . '>',
            'Subject: ' . mimeBaslik($konu),
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . $alanAdi . '>',
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $sinir . '"',
        ];

        $govde = '--' . $sinir . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($duzMetin))
            . '--' . $sinir . "\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html))
            . '--' . $sinir . "--\r\n";

        fwrite($baglanti, implode("\r\n", $basliklar) . "\r\n\r\n" . $govde . "\r\n.\r\n");
        smtpKomut($baglanti, null, 250);
        smtpKomut($baglanti, 'QUIT', 221);
        fclose($baglanti);
        return true;
    } catch (RuntimeException $e) {
        error_log('[buzdolabim] E-posta gönderilemedi: ' . $e->getMessage());
        @fclose($baglanti);
        return false;
    }
}
