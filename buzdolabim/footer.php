<?php
?>
</main>
<footer class="alt">
    <div class="kapsayici"></div>
</footer>
<script>
(function () {
    var ANAHTAR = 'buzdolabim_kaydirma';
    try {
        var kayit = JSON.parse(sessionStorage.getItem(ANAHTAR) || 'null');
        sessionStorage.removeItem(ANAHTAR);
        if (kayit && kayit.sayfa === location.pathname && Date.now() - kayit.zaman < 15000) {
            if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
            var git = function () { window.scrollTo(0, kayit.y); };
            git();
            window.addEventListener('load', git);
            setTimeout(git, 150);
        }
    } catch (e) {}
    document.addEventListener('submit', function (olay) {
        var form = olay.target;
        if (!form || (form.method || '').toLowerCase() !== 'post') return;
        try {
            sessionStorage.setItem(ANAHTAR, JSON.stringify({
                sayfa: location.pathname, y: window.scrollY, zaman: Date.now()
            }));
        } catch (e) {}
    }, true);
})();
</script>
</body>
</html>
