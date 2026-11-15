import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { RoundedBoxGeometry } from 'three/addons/geometries/RoundedBoxGeometry.js';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';

const V = THREE.Vector3;
const $ = (id) => document.getElementById(id);
const kap = $('simSahne');
function sahneyiBaslat() {
    try {
        baslat();
    } catch (hata) {
        console.error(hata);
        const y = $('simYukleniyor');
        if (y) y.textContent = '3D sahne bu tarayıcıda başlatılamadı';
    }
}
if (kap) sahneyiBaslat();

function baslat() {
    const veri = JSON.parse(($('simVeri') || {}).textContent || '{}');
    const urunler = veri.urunler || [];
    const azHareket = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'low-power' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFShadowMap;
    kap.appendChild(renderer.domElement);

    let baglamKayip = false;
    renderer.domElement.addEventListener('webglcontextlost', (ev) => {
        ev.preventDefault();
        baglamKayip = true;
        const not = document.createElement('div');
        not.className = 'sim-yukleniyor';
        not.textContent = '3D sahne duraklatıldı (ekran kartı sıfırlandı). Yeniden başlatılıyor…';
        kap.appendChild(not);
    });
    renderer.domElement.addEventListener('webglcontextrestored', () => location.reload());
    const yuk = $('simYukleniyor');
    if (yuk) yuk.remove();

    const sahne = new THREE.Scene();
    const pmrem = new THREE.PMREMGenerator(renderer);
    sahne.environment = pmrem.fromScene(new RoomEnvironment(renderer), 0.04).texture;

    const kamera = new THREE.PerspectiveCamera(30, 1, 0.1, 100);
    kamera.position.set(6.4, 3.4, 8.6);
    const kontrol = new OrbitControls(kamera, renderer.domElement);
    const TABAN_HEDEF = new V(0, 1.75, 0);
    kontrol.target.copy(TABAN_HEDEF);
    kontrol.enableDamping = true;
    kontrol.enablePan = false;
    kontrol.minDistance = 5;
    kontrol.maxDistance = 17;
    kontrol.maxPolarAngle = Math.PI * 0.56;
    kontrol.autoRotate = !azHareket;
    kontrol.autoRotateSpeed = 0.55;

    sahne.add(new THREE.HemisphereLight(0xffffff, 0xd8d2c4, 0.55));
    const gunes = new THREE.DirectionalLight(0xffffff, 1.7);
    gunes.position.set(4, 9, 5);
    gunes.castShadow = true;
    gunes.shadow.mapSize.set(1024, 1024);
    Object.assign(gunes.shadow.camera, { left: -5, right: 5, top: 5, bottom: -5, near: 1, far: 25 });
    gunes.shadow.bias = -0.0004;
    sahne.add(gunes);
    const dolgu = new THREE.DirectionalLight(0xffe4d4, 0.5);
    dolgu.position.set(-6, 3, -2);
    sahne.add(dolgu);

    const zemin = new THREE.Mesh(new THREE.PlaneGeometry(40, 40), new THREE.ShadowMaterial({ opacity: 0.13 }));
    zemin.rotation.x = -Math.PI / 2;
    zemin.receiveShadow = true;
    sahne.add(zemin);

    const dekorMat = new THREE.LineBasicMaterial({ color: 0xcbc5b8, transparent: true, opacity: 0.9 });
    const dekorKesik = new THREE.LineDashedMaterial({ color: 0xcbc5b8, dashSize: 0.08, gapSize: 0.08, transparent: true, opacity: 0.9 });
    const turuncuCizgi = new THREE.LineBasicMaterial({ color: 0xff4f17 });
    function cember(r, mat, bas = 0, son = Math.PI * 2, n = 160) {
        const nk = [];
        for (let i = 0; i <= n; i++) {
            const a = bas + (son - bas) * (i / n);
            nk.push(new V(Math.cos(a) * r, 0, Math.sin(a) * r));
        }
        const c = new THREE.Line(new THREE.BufferGeometry().setFromPoints(nk), mat);
        c.computeLineDistances();
        return c;
    }
    const dekor = new THREE.Group();
    dekor.add(cember(1.9, dekorMat));
    dekor.add(cember(2.35, dekorKesik));
    const yay = cember(1.9, turuncuCizgi, -0.5, 0.35, 40);
    yay.position.y = 0.002;
    dekor.add(yay);
    const yorunge1 = cember(2.9, dekorMat);
    yorunge1.rotation.set(Math.PI / 2.2, 0.35, 0);
    yorunge1.position.y = 1.8;
    const yorunge2 = cember(3.4, dekorKesik);
    yorunge2.rotation.set(Math.PI / 1.8, -0.6, 0.2);
    yorunge2.position.y = 1.8;
    dekor.add(yorunge1, yorunge2);
    const artiNk = [];
    for (let x = -3; x <= 3; x += 1) {
        for (let z = -3; z <= 3; z += 1) {
            if (Math.hypot(x, z) < 2.6) continue;
            artiNk.push(new V(x - 0.05, 0, z), new V(x + 0.05, 0, z), new V(x, 0, z - 0.05), new V(x, 0, z + 0.05));
        }
    }
    dekor.add(new THREE.LineSegments(new THREE.BufferGeometry().setFromPoints(artiNk), dekorMat));
    dekor.position.y = 0.003;
    sahne.add(dekor);

    function temaRenkleri() {
        const st = getComputedStyle(document.documentElement);
        const c = st.getPropertyValue('--cizgi-koyu').trim() || '#cbc5b8';
        dekorMat.color.set(c);
        dekorKesik.color.set(c);
        const koyu = document.documentElement.getAttribute('data-tema') === 'koyu';
        zemin.material.opacity = koyu ? 0.35 : 0.13;
        dalgaCizgiRengi = st.getPropertyValue('--cizgi').trim() || '#e1ddd3';
    }
    let dalgaCizgiRengi = '#e1ddd3';
    temaRenkleri();
    document.addEventListener('temaDegisti', temaRenkleri);

    const ekranTuval = document.createElement('canvas');
    ekranTuval.width = 256;
    ekranTuval.height = 160;
    const ekranDoku = new THREE.CanvasTexture(ekranTuval);
    ekranDoku.colorSpace = THREE.SRGBColorSpace;
    function ekranCiz(derece) {
        const c = ekranTuval.getContext('2d');
        c.fillStyle = '#0d0d0f';
        c.fillRect(0, 0, 256, 160);
        c.fillStyle = '#ff4f17';
        c.font = '700 64px "JetBrains Mono", monospace';
        c.fillText(derece, 18, 92);
        c.font = '600 18px "JetBrains Mono", monospace';
        c.fillStyle = '#8c887f';
        c.fillText('FR-7 · ANA BÖLME', 18, 128);
        c.fillStyle = '#ff4f17';
        c.fillRect(18, 22, 10, 10);
        ekranDoku.needsUpdate = true;
    }
    ekranCiz('+3.8°');

    const M = {
        beyaz: new THREE.MeshPhysicalMaterial({ color: 0xf3f1ec, roughness: 0.34, clearcoat: 0.55, clearcoatRoughness: 0.25 }),
        icBeyaz: new THREE.MeshStandardMaterial({ color: 0xe9e5dc, roughness: 0.7 }),
        turuncu: new THREE.MeshStandardMaterial({ color: 0xff4f17, roughness: 0.42 }),
        siyah: new THREE.MeshStandardMaterial({ color: 0x1a1a1c, roughness: 0.5, metalness: 0.25 }),
        metal: new THREE.MeshStandardMaterial({ color: 0xbdbdbd, roughness: 0.25, metalness: 0.95 }),
        lens: new THREE.MeshPhysicalMaterial({ color: 0x08080a, roughness: 0.04, metalness: 0.2, clearcoat: 1 }),
        cam: new THREE.MeshPhysicalMaterial({ color: 0xd8e6ec, roughness: 0.06, transparent: true, opacity: 0.32 }),
        isik: new THREE.MeshBasicMaterial({ color: 0xff6a35 }),
        ekran: new THREE.MeshBasicMaterial({ map: ekranDoku }),
        kirmizi: new THREE.MeshStandardMaterial({ color: 0xd92d20, roughness: 0.45 }),
        yesil: new THREE.MeshStandardMaterial({ color: 0x1f9d5a, roughness: 0.5 }),
    };

    const malzemeKasa = {};
    function mat(asm, anahtar) {
        malzemeKasa[asm] = malzemeKasa[asm] || {};
        if (!malzemeKasa[asm][anahtar]) {
            const m = M[anahtar].clone();
            m.userData.tabanOpaklik = m.opacity;
            m.userData.tabanSeffaf = m.transparent;
            malzemeKasa[asm][anahtar] = m;
        }
        return malzemeKasa[asm][anahtar];
    }

    const tumMeshler = [];
    function mesh(geo, asm, anahtar, konum, donus) {
        const m = new THREE.Mesh(geo, mat(asm, anahtar));
        if (konum) m.position.set(konum[0], konum[1], konum[2]);
        if (donus) m.rotation.set(donus[0], donus[1], donus[2]);
        m.castShadow = true;
        m.receiveShadow = true;
        m.userData.asm = asm;
        tumMeshler.push(m);
        return m;
    }
    function cubuk(a, b, r, asm, anahtar) {
        const d = new V().subVectors(b, a);
        const m = mesh(new THREE.CylinderGeometry(r, r, d.length(), 18), asm, anahtar);
        m.position.copy(a).addScaledVector(d, 0.5);
        m.quaternion.setFromUnitVectors(new V(0, 1, 0), d.clone().normalize());
        return m;
    }
    const rbox = (w, h, d, r = 0.035) => new RoundedBoxGeometry(w, h, d, 4, r);

    const robot = new THREE.Group();
    sahne.add(robot);

    const parcalar = [];
    function parca(asm, konum, yon, gecikme = 0) {
        const g = new THREE.Group();
        g.position.copy(konum);
        robot.add(g);
        parcalar.push({ grup: g, asm, taban: konum.clone(), yon, gecikme });
        return g;
    }
    function capa(grup, x, y, z) {
        const o = new THREE.Object3D();
        o.position.set(x, y, z);
        grup.add(o);
        return o;
    }

    const W = 1.3, H = 1.7, D = 1.0, C = 1.55;
    const capalar = {};
    const altCapalar = {};

    const govde = parca('govde', new V(0, C, 0), new V(0, 0, 0));
    govde.add(mesh(rbox(W, H, 0.08), 'govde', 'beyaz', [0, 0, -D / 2 + 0.04]));
    govde.add(mesh(rbox(W - 0.1, H - 0.1, 0.02, 0.008), 'govde', 'icBeyaz', [0, 0, -D / 2 + 0.09]));
    govde.add(mesh(rbox(W, 0.08, D), 'govde', 'beyaz', [0, H / 2 - 0.04, 0]));
    govde.add(mesh(rbox(W, 0.1, D), 'govde', 'beyaz', [0, -H / 2 + 0.05, 0]));
    govde.add(mesh(new THREE.BoxGeometry(W - 0.2, 0.02, 0.02), 'govde', 'isik', [0, H / 2 - 0.1, -D / 2 + 0.14]));
    govde.add(mesh(new THREE.BoxGeometry(W + 0.01, 0.04, D + 0.01), 'govde', 'turuncu', [0, -H / 2 + 0.12, 0]));
    capalar.govde = capa(govde, -W / 2 + 0.05, -H / 2 + 0.3, -D / 2 + 0.05);

    [-1, 1].forEach((s) => {
        const p = parca('yan', new V(s * (W / 2 - 0.04), C, 0), new V(s * 0.95, 0, -0.1), 0.12);
        p.add(mesh(rbox(0.08, H, D), 'yan', 'beyaz'));
        for (let i = 0; i < 5; i++) {
            p.add(mesh(new THREE.BoxGeometry(0.012, 0.03, 0.42), 'yan', 'siyah', [s * 0.045, 0.45 - i * 0.07, -0.15]));
        }
        p.add(mesh(new THREE.BoxGeometry(0.012, 0.36, 0.05), 'yan', 'turuncu', [s * 0.045, -0.5, 0.3]));
        if (s === 1) capalar.yan = capa(p, 0.05, 0.3, 0.1);
    });

    const ustKapi = parca('kapi', new V(0, C + 0.6, D / 2 + 0.06), new V(0.1, 0.3, 1.25), 0.08);
    ustKapi.add(mesh(rbox(W + 0.04, 0.5, 0.09), 'kapi', 'beyaz'));
    ustKapi.add(mesh(new THREE.CylinderGeometry(0.022, 0.022, 0.5, 14), 'kapi', 'turuncu', [W / 2 - 0.16, 0, 0.08], [0, 0, Math.PI / 2]));
    ustKapi.add(mesh(new THREE.BoxGeometry(0.03, 0.04, 0.06), 'kapi', 'siyah', [W / 2 - 0.4, 0, 0.05]));
    ustKapi.add(mesh(new THREE.BoxGeometry(0.03, 0.04, 0.06), 'kapi', 'siyah', [W / 2 + 0.08, 0, 0.05]));
    const altKapi = parca('kapi', new V(0, C - 0.27, D / 2 + 0.06), new V(-0.1, -0.05, 1.5), 0.1);
    altKapi.add(mesh(rbox(W + 0.04, 1.2, 0.09), 'kapi', 'beyaz'));
    altKapi.add(mesh(rbox(0.4, 0.25, 0.02, 0.01), 'kapi', 'siyah', [-0.32, 0.36, 0.05]));
    altKapi.add(mesh(new THREE.PlaneGeometry(0.36, 0.21), 'kapi', 'ekran', [-0.32, 0.36, 0.062]));
    altKapi.add(mesh(new THREE.CylinderGeometry(0.022, 0.022, 0.8, 14), 'kapi', 'turuncu', [W / 2 - 0.12, 0.05, 0.1]));
    altKapi.add(mesh(new THREE.BoxGeometry(0.04, 0.04, 0.08), 'kapi', 'siyah', [W / 2 - 0.12, 0.42, 0.05]));
    altKapi.add(mesh(new THREE.BoxGeometry(0.04, 0.04, 0.08), 'kapi', 'siyah', [W / 2 - 0.12, -0.32, 0.05]));
    altKapi.add(mesh(new THREE.BoxGeometry(W - 0.1, 0.012, 0.012), 'kapi', 'turuncu', [0, -0.55, 0.05]));
    capalar.kapi = capa(altKapi, -0.32, 0.36, 0.07);

    const rafY = [-0.48, -0.02, 0.42];
    const rafAlt = [];
    rafY.forEach((y, i) => {
        const raf = parca('raflar', new V(0, C + y, 0.02), new V(0, (i - 1) * 0.36, 0.75 + i * 0.22), 0.15 + i * 0.04);
        raf.add(mesh(new THREE.BoxGeometry(W - 0.18, 0.025, D - 0.16), 'raflar', 'cam'));
        raf.add(mesh(new THREE.BoxGeometry(W - 0.18, 0.035, 0.03), 'raflar', 'turuncu', [0, 0, (D - 0.16) / 2]));
        raf.add(mesh(new THREE.BoxGeometry(W - 0.18, 0.035, 0.03), 'raflar', 'siyah', [0, 0, -(D - 0.16) / 2]));
        const buRaf = urunler.slice(i * 4, i * 4 + 4);
        buRaf.forEach((u, j) => {
            const x = -0.42 + j * 0.28;
            const z = ((j * 37) % 5) * 0.05 - 0.1;
            const renk = u.durum === 'Bozuldu' ? 'kirmizi' : (u.durum === 'Yaklaşıyor' ? 'turuncu' : 'beyaz');
            urunMesh(raf, u.kategori, renk, x, z);
        });
        const adlar = buRaf.map((u) => u.ad).join(' · ');
        altCapalar.raflar = altCapalar.raflar || [];
        altCapalar.raflar.push({
            capa: capa(raf, (W - 0.18) / 2, 0.02, (D - 0.16) / 2),
            baslik: 'Raf ' + (i + 1) + ' · ' + buRaf.length + ' ürün',
            alt: adlar ? (adlar.length > 34 ? adlar.slice(0, 33) + '…' : adlar) : 'Boş',
        });
        if (i === 2) capalar.raflar = capa(raf, -(W - 0.18) / 2, 0.02, (D - 0.16) / 2);
        rafAlt.push(raf);
    });

    function urunMesh(raf, kategori, renk, x, z) {
        const asm = 'raflar';
        let m;
        switch (kategori) {
            case 'Süt Ürünü': {
                m = new THREE.Group();
                m.add(mesh(new THREE.BoxGeometry(0.12, 0.22, 0.12), asm, renk, [0, 0.11, 0]));
                m.add(mesh(new THREE.ConeGeometry(0.085, 0.07, 4), asm, renk === 'beyaz' ? 'siyah' : 'beyaz', [0, 0.255, 0], [0, Math.PI / 4, 0]));
                break;
            }
            case 'İçecek': {
                m = new THREE.Group();
                m.add(mesh(new THREE.CylinderGeometry(0.05, 0.055, 0.26, 20), asm, renk, [0, 0.13, 0]));
                m.add(mesh(new THREE.CylinderGeometry(0.022, 0.03, 0.06, 14), asm, 'siyah', [0, 0.29, 0]));
                break;
            }
            case 'Sebze & Meyve':
                m = mesh(new THREE.SphereGeometry(0.07, 24, 16), asm, renk === 'beyaz' ? 'yesil' : renk, [0, 0.07, 0]);
                break;
            case 'Et & Tavuk':
                m = mesh(rbox(0.2, 0.06, 0.14, 0.02), asm, renk, [0, 0.03, 0]);
                break;
            case 'Sos & Konserve': {
                m = new THREE.Group();
                m.add(mesh(new THREE.CylinderGeometry(0.06, 0.06, 0.12, 20), asm, renk, [0, 0.06, 0]));
                m.add(mesh(new THREE.CylinderGeometry(0.062, 0.062, 0.025, 20), asm, 'siyah', [0, 0.13, 0]));
                break;
            }
            default:
                m = mesh(new THREE.CylinderGeometry(0.08, 0.08, 0.07, 24), asm, renk, [0, 0.035, 0]);
        }
        m.position.x += x;
        m.position.z += z;
        m.position.y += 0.013;
        raf.add(m);
    }

    const komp = parca('kompresor', new V(0, 0.48, -0.15), new V(0, -0.25, -1.7), 0.2);
    komp.add(mesh(new THREE.CylinderGeometry(0.17, 0.17, 0.5, 32), 'kompresor', 'siyah', [0, 0, 0], [0, 0, Math.PI / 2]));
    komp.add(mesh(new THREE.CylinderGeometry(0.18, 0.18, 0.04, 32), 'kompresor', 'metal', [0.27, 0, 0], [0, 0, Math.PI / 2]));
    komp.add(mesh(new THREE.CylinderGeometry(0.18, 0.18, 0.04, 32), 'kompresor', 'metal', [-0.27, 0, 0], [0, 0, Math.PI / 2]));
    const helis = [];
    for (let i = 0; i <= 220; i++) {
        const a = (i / 220) * Math.PI * 2 * 7;
        helis.push(new V(-0.24 + (i / 220) * 0.48, Math.cos(a) * 0.215, Math.sin(a) * 0.215));
    }
    komp.add(mesh(new THREE.TubeGeometry(new THREE.CatmullRomCurve3(helis), 400, 0.012, 8), 'kompresor', 'turuncu'));
    const fan = new THREE.Group();
    fan.position.set(0, 0, -0.26);
    for (let i = 0; i < 5; i++) {
        const k = mesh(new THREE.BoxGeometry(0.05, 0.15, 0.01), 'kompresor', 'metal', [0, 0.08, 0], [0, 0.4, 0]);
        const p = new THREE.Group();
        p.rotation.z = (i / 5) * Math.PI * 2;
        p.add(k);
        fan.add(p);
    }
    fan.add(mesh(new THREE.CylinderGeometry(0.035, 0.035, 0.04, 16), 'kompresor', 'turuncu', [0, 0, 0], [Math.PI / 2, 0, 0]));
    fan.add(mesh(new THREE.TorusGeometry(0.19, 0.012, 8, 48), 'kompresor', 'siyah'));
    komp.add(fan);
    capalar.kompresor = capa(komp, 0.27, 0.15, 0);

    [[-1, -1], [1, -1], [-1, 1], [1, 1]].forEach(([sx, sz], i) => {
        const kalca = new V(sx * 0.48, 0.72, sz * 0.3);
        const yon = new V(sx, 0, sz).normalize().multiplyScalar(0.9).add(new V(0, -0.2, 0));
        const b = parca('bacak', kalca, yon, 0.24 + i * 0.015);
        const diz = new V(sx * 0.38, -0.18, sz * 0.3);
        const ayak = new V(sx * 0.52, -0.69, sz * 0.42);
        b.add(mesh(new THREE.SphereGeometry(0.095, 24, 16), 'bacak', 'turuncu'));
        b.add(cubuk(new V(0, 0, 0), diz, 0.045, 'bacak', 'siyah'));
        const dizM = mesh(new THREE.SphereGeometry(0.07, 20, 14), 'bacak', 'beyaz');
        dizM.position.copy(diz);
        b.add(dizM);
        const dizH = mesh(new THREE.TorusGeometry(0.072, 0.014, 8, 32), 'bacak', 'turuncu');
        dizH.position.copy(diz);
        dizH.rotation.y = Math.atan2(sx, sz);
        b.add(dizH);
        b.add(cubuk(diz, ayak, 0.038, 'bacak', 'beyaz'));
        b.add(cubuk(diz.clone().lerp(ayak, 0.25), diz.clone().lerp(ayak, 0.45), 0.042, 'bacak', 'siyah'));
        const ayakM = mesh(new THREE.CylinderGeometry(0.08, 0.1, 0.035, 24), 'bacak', 'siyah');
        ayakM.position.copy(ayak).add(new V(0, -0.005, 0));
        b.add(ayakM);
        if (sx === 1 && sz === 1) capalar.bacak = capa(b, diz.x, diz.y, diz.z);
    });

    const KY = C + H / 2 + 0.46;
    const kafa = parca('optik', new V(0, KY, 0.05), new V(0, 0.8, 0.35), 0);
    const kafaKure = mesh(new THREE.SphereGeometry(0.44, 48, 32), 'optik', 'beyaz');
    kafaKure.scale.set(1, 0.92, 1);
    kafa.add(kafaKure);
    kafa.add(mesh(new THREE.CylinderGeometry(0.15, 0.17, 0.12, 32), 'optik', 'siyah', [0, -0.42, 0]));
    kafa.add(mesh(new THREE.TorusGeometry(0.445, 0.012, 8, 80), 'optik', 'turuncu', [0, -0.02, 0], [Math.PI / 2, 0, 0]));
    [-1, 1].forEach((s) => {
        kafa.add(mesh(new THREE.CylinderGeometry(0.15, 0.15, 0.12, 40), 'optik', 'beyaz', [s * 0.42, 0, 0], [0, 0, Math.PI / 2]));
        kafa.add(mesh(new THREE.TorusGeometry(0.14, 0.022, 12, 40), 'optik', 'turuncu', [s * 0.485, 0, 0], [0, Math.PI / 2, 0]));
        kafa.add(mesh(new THREE.CylinderGeometry(0.1, 0.1, 0.02, 32), 'optik', 'lens', [s * 0.485, 0, 0], [0, 0, Math.PI / 2]));
    });
    const lensParcalari = [
        { m: mesh(new THREE.CylinderGeometry(0.27, 0.29, 0.2, 48), 'optik', 'siyah', [0, 0, 0.4], [Math.PI / 2, 0, 0]), ek: 0 },
        { m: mesh(new THREE.TorusGeometry(0.275, 0.03, 16, 64), 'optik', 'turuncu', [0, 0, 0.5]), ek: 0.3 },
        { m: mesh(new THREE.SphereGeometry(0.21, 32, 24), 'optik', 'lens', [0, 0, 0.5]), ek: 0.55 },
        { m: mesh(new THREE.CircleGeometry(0.055, 32), 'optik', 'isik', [0, 0, 0.6]), ek: 0.78 },
        { m: mesh(new THREE.CylinderGeometry(0.25, 0.25, 0.02, 48), 'optik', 'cam', [0, 0, 0.56], [Math.PI / 2, 0, 0]), ek: 1.0 },
    ];
    lensParcalari[2].m.scale.set(1, 1, 0.45);
    lensParcalari.forEach((l) => {
        l.tabanZ = l.m.position.z;
        kafa.add(l.m);
    });
    capalar.optik = capa(kafa, 0.42, 0.18, 0);
    altCapalar.optik = [
        { capa: capa(lensParcalari[4].m, 0.25, 0, 0), baslik: 'Dış Cam', alt: '24 mm · AR kaplama' },
        { capa: capa(lensParcalari[2].m, -0.2, 0, 0), baslik: 'İris', alt: '12 kanat · f/1.8' },
        { capa: capa(lensParcalari[0].m, 0.28, 0, 0), baslik: 'Lens Gövdesi', alt: 'Odak · 0–120 cm' },
    ];

    const anten = parca('anten', new V(0, KY, 0.05), new V(0, 1.35, 0.05), 0.05);
    const antenUclari = [];
    [-1, 1].forEach((s) => {
        const a = new V(s * 0.14, 0.35, -0.05);
        const b = new V(s * 0.34, 1.08, -0.14);
        const g = new THREE.Group();
        g.add(cubuk(a, b, 0.018, 'anten', 'siyah'));
        [0.62, 0.7, 0.78].forEach((f) => g.add(cubuk(a.clone().lerp(b, f), a.clone().lerp(b, f + 0.04), 0.026, 'anten', 'turuncu')));
        const uc = mesh(new THREE.SphereGeometry(0.035, 16, 12), 'anten', 'turuncu');
        uc.position.copy(b);
        g.add(uc);
        g.add(mesh(new THREE.CylinderGeometry(0.04, 0.05, 0.06, 16), 'anten', 'siyah', [a.x, a.y, a.z]));
        anten.add(g);
        antenUclari.push({ g, s });
    });
    capalar.anten = capa(anten, 0.34, 1.08, -0.14);

    const MONTAJ = [
        { id: 'optik', ad: 'Optik', baslik: 'Optik Sensör', alt: 'Ürün tanıma · 4K' },
        { id: 'anten', ad: 'Anten', baslik: 'Anten Dizisi', alt: '2.4 GHz · bulut senk.' },
        { id: 'kapi', ad: 'Kapı', baslik: 'Ön Kapılar', alt: 'Dokunmatik panel · conta' },
        { id: 'raflar', ad: 'Raflar', baslik: 'Cam Raflar', alt: '3 raf · ' + urunler.length + ' ürün' },
        { id: 'yan', ad: 'Yan Panel', baslik: 'Yan Paneller', alt: 'Poliüretan yalıtım' },
        { id: 'kompresor', ad: 'Kompresör', baslik: 'Kompresör', alt: 'R600a · inverter' },
        { id: 'bacak', ad: 'Bacaklar', baslik: 'Bacak Servoları', alt: '4 bacak · 8 eklem' },
    ];
    const montajAd = Object.fromEntries(MONTAJ.map((m) => [m.id, m]));

    const SURE = 24;
    const SAHNELER = [
        { bas: 0, e: 0, iz: null, mod: 'Montajlı', alt: 'Tüm modüller kilitli', adim: 'Operatör · Montaj kontrolü' },
        { bas: 3, e: 1, iz: null, mod: 'Patlatılmış', alt: 'Modüller ayrılıyor · 7 grup', adim: 'Operatör · Sökümü başlat' },
        { bas: 7, e: 1, iz: 'optik', mod: 'Optik Açık', alt: 'Optik · 5 parça ayrık', adim: 'Operatör · Optiği izole et' },
        { bas: 10.5, e: 1, iz: 'raflar', mod: 'Raf Taraması', alt: urunler.length + ' ürün tarandı', adim: 'Operatör · Rafları tara' },
        { bas: 14, e: 1, iz: 'kompresor', mod: 'Soğutma', alt: 'Kompresör · R600a', adim: 'Operatör · Kompresörü izole et' },
        { bas: 17, e: 1, iz: 'bacak', mod: 'Yürüyüş', alt: '4 bacak · 8 servo', adim: 'Operatör · Bacakları izole et' },
        { bas: 20, e: 0, iz: null, mod: 'Montaj', alt: 'Modüller kilitleniyor', adim: 'Operatör · Yeniden montaj' },
    ];
    const sahneBul = (t) => {
        let s = SAHNELER[0];
        for (const x of SAHNELER) if (t >= x.bas) s = x;
        return s;
    };

    const durum = {
        oto: !azHareket,
        t: 0,
        manuel: { e: 0, iz: null },
        etiket: true,
        e: 0,
        alt: 0,
        opak: Object.fromEntries(MONTAJ.map((m) => [m.id, 1])),
    };
    durum.opak.govde = 1;

    const chipKap = $('simMontajlar');
    MONTAJ.forEach((m) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'sim-chip';
        b.textContent = m.ad;
        b.dataset.asm = m.id;
        b.addEventListener('click', () => izoleEt(m.id));
        chipKap.appendChild(b);
    });
    const altListe = $('simAltSistem');
    MONTAJ.forEach((m, i) => {
        const li = document.createElement('li');
        li.dataset.asm = m.id;
        li.innerHTML = '<span>' + String(i + 1).padStart(2, '0') + '</span><div><b></b><small></small></div><span class="sim-durum"></span>';
        li.querySelector('b').textContent = m.baslik;
        li.querySelector('small').textContent = m.alt;
        altListe.appendChild(li);
    });

    const gorunumDugmeleri = document.querySelectorAll('[data-gorunum]');
    gorunumDugmeleri.forEach((d) => d.addEventListener('click', () => {
        const g = d.dataset.gorunum;
        if (g === 'etiket') durum.etiket = !durum.etiket;
        if (g === 'dondur') kontrol.autoRotate = !kontrol.autoRotate;
        if (g === 'patlat') {
            const simdi = aktifHedef();
            durum.manuel = { e: simdi.e > 0.5 ? 0 : 1, iz: null };
            otoDurdur();
        }
        hudGorunumGuncelle();
    }));

    function izoleEt(id) {
        const simdi = aktifHedef();
        durum.manuel = simdi.iz === id ? { e: 1, iz: null } : { e: 1, iz: id };
        otoDurdur();
    }
    function otoDurdur() {
        durum.oto = false;
        oynatIkon();
    }

    const oynat = $('simOynat');
    oynat.addEventListener('click', () => {
        durum.oto = !durum.oto;
        if (durum.oto) {
            const s = sahneBul(durum.t);
            durum.manuel = { e: s.e, iz: s.iz };
        }
        oynatIkon();
    });
    function oynatIkon() {
        $('simOynatIkon').innerHTML = durum.oto
            ? '<rect x="2" y="1" width="3.5" height="12"/><rect x="8.5" y="1" width="3.5" height="12"/>'
            : '<path d="M3 1.5v11l9.5-5.5z"/>';
        oynat.setAttribute('aria-label', durum.oto ? 'Duraklat' : 'Oynat');
    }
    oynatIkon();

    const ray = $('simRay');
    SAHNELER.forEach((s) => {
        const tik = document.createElement('i');
        tik.className = 'tik';
        tik.style.left = (s.bas / SURE) * 100 + '%';
        ray.appendChild(tik);
    });
    ray.addEventListener('click', (ev) => {
        const r = ray.getBoundingClientRect();
        durum.t = Math.max(0, Math.min(SURE - 0.01, ((ev.clientX - r.left) / r.width) * SURE));
        durum.oto = true;
        oynatIkon();
    });

    function aktifHedef() {
        if (durum.oto) {
            const s = sahneBul(durum.t);
            return { e: s.e, iz: s.iz, mod: s.mod, alt: s.alt, adim: s.adim };
        }
        const iz = durum.manuel.iz;
        if (iz) {
            const m = montajAd[iz];
            return { e: 1, iz, mod: m.ad + ' İzole', alt: m.alt, adim: 'Manuel · ' + m.baslik };
        }
        return durum.manuel.e > 0.5
            ? { e: 1, iz: null, mod: 'Patlatılmış', alt: 'Manuel söküm görünümü', adim: 'Manuel · Patlatılmış görünüm' }
            : { e: 0, iz: null, mod: 'Montajlı', alt: 'Tüm modüller kilitli', adim: 'Manuel · Montajlı görünüm' };
    }

    const etiketKap = $('simEtiketler');
    const svg = $('simEtiketSvg');
    const NS = 'http://www.w3.org/2000/svg';
    const etiketler = [];
    function etiketEkle(capaObj, asm, baslik, alt, tip, sira) {
        const el = document.createElement('div');
        el.className = 'sim-etiket';
        el.style.opacity = 0;
        el.innerHTML = '<b></b><small></small>';
        el.querySelector('b').textContent = baslik;
        el.querySelector('small').textContent = alt;
        etiketKap.appendChild(el);
        const cizgi = document.createElementNS(NS, 'line');
        const nokta = document.createElementNS(NS, 'circle');
        nokta.setAttribute('r', '3.5');
        cizgi.style.display = 'none';
        nokta.style.display = 'none';
        svg.appendChild(cizgi);
        svg.appendChild(nokta);
        etiketler.push({ el, cizgi, nokta, capa: capaObj, asm, tip, sira, gorunur: 0 });
    }
    MONTAJ.forEach((m, i) => etiketEkle(capalar[m.id], m.id, m.baslik, m.alt, 'ana', i));
    Object.entries(altCapalar).forEach(([asm, liste]) => {
        liste.forEach((a, i) => etiketEkle(a.capa, asm, a.baslik, a.alt, 'alt', i));
    });

    const gecici = new V();
    const robotMerkez = new V();
    function etiketleriGuncelle(w, h, hedef) {
        robotMerkez.set(0, 1.6, 0).project(kamera);
        const mx = (robotMerkez.x + 1) / 2 * w;
        etiketler.forEach((e) => {
            let goster = false;
            if (durum.etiket) {
                if (e.tip === 'ana') goster = hedef.iz ? e.asm === hedef.iz && !(altCapalar[e.asm] && durum.alt > 0.5) : durum.e > 0.55;
                else goster = hedef.iz === e.asm && durum.e > 0.6;
            }
            if (goster) {
                e.capa.getWorldPosition(gecici).project(kamera);
                if (gecici.z > 1) goster = false;
                const px = (gecici.x + 1) / 2 * w;
                const py = (1 - gecici.y) / 2 * h;
                const yon = px >= mx ? 1 : -1;
                const L = e.tip === 'ana' ? 92 : 70;
                const dy = e.tip === 'alt' ? (e.sira - 1) * 26 : (e.sira % 2 ? -14 : 14);
                const lx = px + yon * L;
                const ly = py + dy;
                e.el.style.left = lx + 'px';
                e.el.style.top = ly + 'px';
                e.el.classList.toggle('sol', yon < 0);
                e.cizgi.setAttribute('x1', px);
                e.cizgi.setAttribute('y1', py);
                e.cizgi.setAttribute('x2', lx - yon * 4);
                e.cizgi.setAttribute('y2', ly);
                e.nokta.setAttribute('cx', px);
                e.nokta.setAttribute('cy', py);
            }
            if (e.gorunur !== +goster) {
                e.gorunur = +goster;
                e.el.style.opacity = goster ? 1 : 0;
                e.cizgi.style.display = goster ? '' : 'none';
                e.nokta.style.display = goster ? '' : 'none';
            }
        });
    }

    const isin = new THREE.Raycaster();
    const fare = new THREE.Vector2();
    let basla = null;
    function parcaBul(ev) {
        const r = renderer.domElement.getBoundingClientRect();
        fare.set(((ev.clientX - r.left) / r.width) * 2 - 1, -((ev.clientY - r.top) / r.height) * 2 + 1);
        isin.setFromCamera(fare, kamera);
        const vur = isin.intersectObjects(tumMeshler, false).find((v) => v.object.userData.asm !== 'govde' && v.object.material.opacity > 0.3);
        return vur ? vur.object.userData.asm : null;
    }
    renderer.domElement.addEventListener('pointerdown', (ev) => { basla = [ev.clientX, ev.clientY]; });
    renderer.domElement.addEventListener('pointerup', (ev) => {
        if (!basla || Math.hypot(ev.clientX - basla[0], ev.clientY - basla[1]) > 5) return;
        const asm = parcaBul(ev);
        if (asm) izoleEt(asm);
        else if (!durum.oto && durum.manuel.iz) { durum.manuel.iz = null; }
    });
    let sonHover = 0;
    renderer.domElement.addEventListener('pointermove', (ev) => {
        if (ev.buttons || performance.now() - sonHover < 60) return;
        sonHover = performance.now();
        renderer.domElement.style.cursor = parcaBul(ev) ? 'pointer' : '';
    });

    const dalga = $('simDalga');
    const dctx = dalga.getContext('2d');
    function dalgaCiz(zaman, genlik) {
        const dpr = Math.min(window.devicePixelRatio, 2);
        const w = dalga.clientWidth, h = dalga.clientHeight;
        if (dalga.width !== w * dpr) { dalga.width = w * dpr; dalga.height = h * dpr; }
        dctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        dctx.clearRect(0, 0, w, h);
        dctx.strokeStyle = dalgaCizgiRengi;
        dctx.lineWidth = 1;
        dctx.beginPath();
        dctx.moveTo(0, h / 2); dctx.lineTo(w, h / 2);
        for (let x = 0; x <= w; x += w / 8) { dctx.moveTo(x, h / 2 - 3); dctx.lineTo(x, h / 2 + 3); }
        dctx.stroke();
        dctx.strokeStyle = '#ff4f17';
        dctx.lineWidth = 1.6;
        dctx.beginPath();
        let son = [0, 0];
        for (let x = 0; x <= w; x += 2) {
            const y = h / 2 + Math.sin(x * 0.11 - zaman * 5) * (h * 0.38) * genlik;
            x === 0 ? dctx.moveTo(x, y) : dctx.lineTo(x, y);
            son = [x, y];
        }
        dctx.stroke();
        dctx.fillStyle = '#ff4f17';
        dctx.fillRect(son[0] - 3, son[1] - 3, 6, 6);
    }

    let G = 1, Y = 1;
    function boyutla() {
        G = kap.clientWidth || 1;
        Y = kap.clientHeight || 1;
        renderer.setSize(G, Y, false);
        kamera.aspect = G / Y;
        kamera.fov = G / Y < 1.1 ? 40 : 30;
        kamera.updateProjectionMatrix();
    }
    new ResizeObserver(boyutla).observe(kap);
    boyutla();

    let gorunur = true;
    new IntersectionObserver((g) => { gorunur = g[0].isIntersecting; }, { threshold: 0.01 }).observe(kap);

    let sonMod = '', sonIz = undefined, sonDurumKod = '';
    const hudModEl = $('simMod'), hudModAlt = $('simModAlt'), hudAdim = $('simAdim'), hudDurum = $('simDurumYazi'), hudIzole = $('simIzoleNot');
    function hudGorunumGuncelle() {
        gorunumDugmeleri.forEach((d) => {
            const g = d.dataset.gorunum;
            let aktif = false;
            if (g === 'etiket') aktif = durum.etiket;
            if (g === 'dondur') aktif = kontrol.autoRotate;
            if (g === 'patlat') aktif = aktifHedef().e > 0.5;
            d.classList.toggle('aktif', aktif);
        });
    }
    function hudGuncelle(hedef) {
        if (hedef.mod !== sonMod) {
            sonMod = hedef.mod;
            hudModEl.textContent = hedef.mod;
            hudModAlt.textContent = hedef.alt;
            hudAdim.textContent = hedef.adim;
            hudDurum.textContent = hedef.mod.toLocaleUpperCase('tr-TR');
            hudGorunumGuncelle();
        }
        const ayrik = durum.e > 0.5;
        const kod = (hedef.iz || '-') + (ayrik ? '1' : '0');
        if (hedef.iz !== sonIz || kod !== sonDurumKod) {
            sonIz = hedef.iz;
            sonDurumKod = kod;
            hudIzole.textContent = 'İzole · ' + (hedef.iz ? montajAd[hedef.iz].ad : '—');
            chipKap.querySelectorAll('.sim-chip').forEach((c) => c.classList.toggle('aktif', c.dataset.asm === hedef.iz));
            altListe.querySelectorAll('li').forEach((li) => {
                const id = li.dataset.asm;
                const etiket = li.querySelector('.sim-durum');
                li.classList.toggle('secili', id === hedef.iz);
                let metin = ayrik ? 'Ayrık' : 'Kilitli', sinif = ayrik ? 'sim-durum ayrik' : 'sim-durum';
                if (id === 'raflar' && veri.bozuk > 0) { metin = 'Uyarı'; sinif = 'sim-durum kirmizi'; }
                if (id === hedef.iz) { metin = 'İzole'; sinif = 'sim-durum turuncu'; }
                etiket.textContent = metin;
                etiket.className = sinif;
            });
        }
    }

    const saat = new THREE.Clock();
    const acilis = performance.now();
    const hedefMerkez = new V();
    const tmp = new V();
    const kolay = (x) => (x < 0.5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2);
    const sinirla = (x) => Math.max(0, Math.min(1, x));
    const yumusat = (a, b, l, dt) => a + (b - a) * (1 - Math.exp(-l * dt));
    const dunyaKonum = new V();
    let sonSaniye = -1;

    const KARE_ARALIGI = 1000 / 30;
    let sonKare = 0;

    function dongu(simdi) {
        requestAnimationFrame(dongu);
        if (!gorunur || document.hidden || baglamKayip) return;
        if (simdi - sonKare < KARE_ARALIGI - 1) return;
        sonKare = simdi;
        const dt = Math.min(saat.getDelta(), 0.05);
        const zaman = saat.elapsedTime;

        if (durum.oto) durum.t = (durum.t + dt) % SURE;
        const hedef = aktifHedef();

        durum.e = yumusat(durum.e, hedef.e, 2.1, dt);
        durum.alt = yumusat(durum.alt, hedef.iz && durum.e > 0.7 ? 1 : 0, 2.4, dt);

        parcalar.forEach((p) => {
            const k = kolay(sinirla((durum.e - p.gecikme * 0.6) / (1 - p.gecikme * 0.6)));
            p.grup.position.copy(p.taban).addScaledVector(p.yon, k);
        });
        const optikAlt = hedef.iz === 'optik' ? kolay(durum.alt) : 0;
        lensParcalari.forEach((l) => { l.m.position.z = l.tabanZ + l.ek * 0.55 * optikAlt; });
        rafAlt.forEach((r, i) => {
            const a = hedef.iz === 'raflar' ? kolay(durum.alt) : 0;
            r.position.x += (i - 1) * 0.35 * a;
        });

        robot.position.y = Math.sin(zaman * 1.6) * 0.015;
        robot.scale.setScalar(1 - 0.2 * kolay(sinirla(durum.e)));
        lensParcalari[3].m.scale.setScalar(0.85 + Math.sin(zaman * 3) * 0.15);
        fan.rotation.z += dt * 9;
        antenUclari.forEach(({ g, s }) => { g.rotation.z = Math.sin(zaman * 2.2 + s) * 0.04; });

        Object.keys(malzemeKasa).forEach((asm) => {
            const hedefOpak = hedef.iz ? (asm === hedef.iz ? 1 : 0.1) : 1;
            const o = (durum.opak[asm] = yumusat(durum.opak[asm] ?? 1, hedefOpak, 4, dt));
            Object.values(malzemeKasa[asm]).forEach((m) => {
                const seffaf = o < 0.995 || m.userData.tabanSeffaf;
                if (m.transparent !== seffaf) { m.transparent = seffaf; m.needsUpdate = true; }
                m.opacity = m.userData.tabanOpaklik * o;
                m.depthWrite = o > 0.5;
            });
        });

        hedefMerkez.copy(TABAN_HEDEF);
        if (hedef.iz) {
            tmp.set(0, 0, 0);
            let n = 0;
            parcalar.forEach((p) => { if (p.asm === hedef.iz) { tmp.add(p.grup.getWorldPosition(dunyaKonum)); n++; } });
            if (n) hedefMerkez.lerp(tmp.divideScalar(n), 0.5);
        }
        kontrol.target.lerp(hedefMerkez, 1 - Math.exp(-2 * dt));
        kontrol.update(dt);

        renderer.render(sahne, kamera);
        etiketleriGuncelle(G, Y, hedef);
        hudGuncelle(hedef);

        const oran = durum.t / SURE;
        $('simRayDolu').style.width = oran * 100 + '%';
        $('simRayTutamak').style.left = oran * 100 + '%';

        const sicak = 3.8 + Math.sin(zaman * 0.7) * 0.18;
        const genlik = 0.35 + 0.65 * (1 - durum.e) * (0.6 + 0.4 * Math.sin(zaman * 0.5));
        dalgaCiz(zaman, hedef.iz === 'kompresor' ? 1 : genlik);
        const saniye = Math.floor(zaman * 4);
        if (saniye !== sonSaniye) {
            sonSaniye = saniye;
            $('simFrekans').innerHTML = sicak.toFixed(1) + '<sup>°C</sup>';
            $('simGenlik').textContent = '±' + (0.2 + genlik * 0.3).toFixed(1) + '°';
            $('simAnaSicak').textContent = '+' + sicak.toFixed(1) + '°C';
            const gs = Math.floor((performance.now() - acilis) / 1000);
            $('simSure').textContent = [gs / 3600, (gs / 60) % 60, gs % 60].map((x) => String(Math.floor(x)).padStart(2, '0')).join(':');
            const ts = Math.floor(durum.t);
            $('simZaman').textContent = '00:' + String(ts).padStart(2, '0') + ' / 00:' + SURE;
            if (saniye % 8 === 0) ekranCiz('+' + sicak.toFixed(1) + '°');
            const az = kontrol.getAzimuthalAngle();
            const pol = kontrol.getPolarAngle();
            $('simUfuk').style.transform = 'rotate(' + (Math.sin(az) * 14).toFixed(1) + 'deg) translateY(' + ((pol - 1.2) * 30).toFixed(1) + 'px)';
        }
    }
    requestAnimationFrame(dongu);
}
