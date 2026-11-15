(function () {
    var kok = document.documentElement;
    var dugme = document.getElementById('temaDugme');
    if (!dugme) return;

    function dugmeyiGuncelle() {
        var acik = kok.getAttribute('data-tema') === 'acik';
        dugme.textContent = acik ? '🌙' : '☀️';
        var metin = acik ? 'Karanlık temaya geç' : 'Aydınlık temaya geç';
        dugme.setAttribute('aria-label', metin);
        dugme.setAttribute('title', metin);
    }

    dugme.addEventListener('click', function () {
        var yeni = kok.getAttribute('data-tema') === 'acik' ? 'koyu' : 'acik';
        kok.setAttribute('data-tema', yeni);
        document.dispatchEvent(new CustomEvent('temaDegisti', { detail: yeni }));
        localStorage.setItem('tema_hud', yeni);
        dugmeyiGuncelle();
    });

    dugmeyiGuncelle();
})();
