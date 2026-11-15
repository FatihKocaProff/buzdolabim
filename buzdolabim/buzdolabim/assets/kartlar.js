(function () {
    var girdi = document.getElementById('urunBolumu') ? document.getElementById('ustAramaInput') : null;
    var kartlar = Array.prototype.slice.call(document.querySelectorAll('.sayfa-acilir-kart'));
    var sayacEl = document.getElementById('gorunenUrunSayisi');

    if (kartlar.length === 0) return;

    function gorunenKartlar() {
        return kartlar.filter(function (k) {
            return k.style.display !== 'none';
        });
    }

    function gorunumdekiKartlariAc() {
        var pencereYukseklik = window.innerHeight || document.documentElement.clientHeight;
        var aktifler = gorunenKartlar();

        aktifler.forEach(function (kart) {
            var rect = kart.getBoundingClientRect();
            if (rect.top < pencereYukseklik * 0.92 && rect.bottom > 40) {
                kart.classList.add('kart-acik');
            } else if (rect.top > pencereYukseklik * 0.98) {
                kart.classList.remove('kart-acik');
            }
        });
    }

    window.requestAnimationFrame(function () {
        setTimeout(gorunumdekiKartlariAc, 80);
    });

    window.addEventListener('scroll', function () {
        window.requestAnimationFrame(gorunumdekiKartlariAc);
    }, { passive: true });

    window.addEventListener('wheel', function () {
        window.requestAnimationFrame(gorunumdekiKartlariAc);
    }, { passive: true });

    if (girdi) {
        girdi.addEventListener('input', function () {
            var aranan = girdi.value.toLocaleLowerCase('tr-TR').trim();
            var gorunen = 0;
            kartlar.forEach(function (kart) {
                var metin = (kart.getAttribute('data-arama') || '').toLocaleLowerCase('tr-TR');
                var eslesti = aranan === '' || metin.indexOf(aranan) !== -1;
                kart.style.display = eslesti ? '' : 'none';
                if (eslesti) {
                    gorunen++;
                    kart.classList.remove('kart-acik');
                }
            });
            if (sayacEl) sayacEl.textContent = gorunen;
            setTimeout(gorunumdekiKartlariAc, 40);
        });
    }
})();
