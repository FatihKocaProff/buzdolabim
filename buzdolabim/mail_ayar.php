<?php

const MAIL_GMAIL_ADRES      = '';
const MAIL_UYGULAMA_SIFRESI = '';
const MAIL_GONDEREN_AD      = 'Buzdolabim';

const SITE_ADRESLERI = [
    'localhost'          => 'http://localhost/buzdolabim',
    '127.0.0.1'          => 'http://127.0.0.1/buzdolabim',
];

define('SITE_ADRESI', SITE_ADRESLERI[strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0])] ?? array_values(SITE_ADRESLERI)[0]);

const SIFIRLAMA_GECERLILIK_DK = 30;
