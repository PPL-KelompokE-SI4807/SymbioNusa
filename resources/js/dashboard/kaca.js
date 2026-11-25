/**
 * Komponen Alpine dasbor Admin varian Kaca (DESIGN.md, amandemen FND v12). Padanan SymbioNusa Lab:
 * strip ringkasan (periode), saringan antrean, laci keputusan, dialog "Semua antrean", palet perintah (Ctrl K).
 * Tidak ada keputusan yang dijalankan dari dasbor: laci hanya membuka halaman keputusan (item.url) bila ada.
 */
import { number } from './format';

const FILTERS = {
    semua: () => true,
    sengketa: (kind) => kind === 'sengketa',
    verifikasi: (kind) => kind === 'verifikasi',
    inspeksi: (kind) => kind === 'penugasan' || kind === 'sertifikat',
};

/** Kata kunci palet → saringan (adaptasi Lab › CommandPalette). */
const WORDS = [
    { re: /sengketa|klaim/, chip: ['jenis', 'Sengketa'], test: (i) => i.kind === 'sengketa' },
    { re: /verifikasi|mitra/, chip: ['jenis', 'Verifikasi Mitra'], test: (i) => i.kind === 'verifikasi' },
    { re: /penugasan|inspector|tugas|sertifikat/, chip: ['jenis', 'Penugasan / Inspector'], test: (i) => i.kind === 'penugasan' || i.kind === 'sertifikat' },
    { re: /plastik/, chip: ['material', 'Plastik'], order: /plastik/i },
    { re: /kain|tekstil|perca/, chip: ['material', 'Tekstil'], order: /kain|tekstil|perca/i },
    { re: /kayu|serbuk/, chip: ['material', 'Kayu'], order: /kayu|serbuk/i },
    { re: /tahu|pangan|organik|ampas/, chip: ['material', 'Pangan'], order: /tahu|pangan|organik|ampas/i },
    { re: /kertas|kardus|karton/, chip: ['material', 'Kertas'], order: /kertas|kardus|karton/i },
];

const reduced = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

/**
 * Kurva spring sebagai fungsi CSS linear(), disimulasikan dengan fisika yang sama dengan solver bersama
 * (resources/js/motion/spring.js, Euler semi-implisit 240 Hz). Lab memakai spring { stiffness 420, damping 38, mass 0.9 }.
 * Hasilnya dipakai oleh transisi CSS (--kaca-spring) dan Web Animations (FLIP), jadi gerak tetap spring tanpa pustaka tambahan.
 */
export function springCurve({ stiffness = 420, damping = 38, mass = 0.9 } = {}) {
    const h = 1 / 240;
    let x = 0;
    let v = 0;
    let t = 0;
    const pts = [0];
    while (t < 2) {
        for (let i = 0; i < 4; i++) {
            const a = (-stiffness * (x - 1) - damping * v) / mass;
            v += a * h;
            x += v * h;
            t += h;
        }
        pts.push(Math.round(x * 1000) / 1000);
        if (Math.abs(1 - x) < 0.001 && Math.abs(v) < 0.01) break;
    }
    pts[pts.length - 1] = 1;
    return { easing: `linear(${pts.join(', ')})`, duration: Math.round(t * 1000) };
}

const SPRING = typeof window !== 'undefined' ? springCurve() : { easing: 'ease-out', duration: 400 };
if (typeof document !== 'undefined') {
    document.documentElement.style.setProperty('--kaca-spring', SPRING.easing);
    document.documentElement.style.setProperty('--kaca-spring-ms', `${SPRING.duration}ms`);
}

const visible = (el) => el.offsetParent !== null || getComputedStyle(el).position === 'fixed';

/**
 * FLIP: ukur posisi elemen sebelum perubahan, jalankan perubahan, lalu animasikan dari posisi lama ke posisi baru
 * dengan kurva spring. Elemen baru masuk dengan fade + geser kecil; tinggi wadah ikut beranimasi.
 * Gerak tereduksi (setelan OS): seperti Lab (reducedMotion "user"), hanya fade, tanpa gerak posisi.
 */
export async function flip(container, items, mutate, nextTick) {
    const before = new Map(items().filter(visible).map((el) => [el, el.getBoundingClientRect()]));
    const h0 = container.getBoundingClientRect().height;
    mutate();
    await nextTick();
    const h1 = container.getBoundingClientRect().height;
    const calm = reduced();
    if (!calm && Math.abs(h1 - h0) > 1) {
        container.animate([{ height: `${h0}px` }, { height: `${h1}px` }], { duration: SPRING.duration, easing: SPRING.easing });
    }
    let n = 0;
    for (const el of items().filter(visible)) {
        const old = before.get(el);
        if (old) {
            const now = el.getBoundingClientRect();
            const dx = old.left - now.left;
            const dy = old.top - now.top;
            if (!calm && (Math.abs(dx) > 0.5 || Math.abs(dy) > 0.5)) {
                el.animate([{ transform: `translate(${dx}px, ${dy}px)` }, { transform: 'none' }], { duration: SPRING.duration, easing: SPRING.easing });
            }
        } else {
            const delay = 40 * n++;
            el.animate(calm
                ? [{ opacity: 0 }, { opacity: 1 }]
                : [{ opacity: 0, transform: 'translateY(16px) scale(0.98)' }, { opacity: 1, transform: 'none' }],
            { duration: calm ? 200 : SPRING.duration, easing: calm ? 'ease-out' : SPRING.easing, delay, fill: 'backwards' });
        }
    }
}

/** Tunggu animasi selesai, paling lama `ms` (tab di latar belakang bisa menahan linimasa animasi). */
const settled = (anims, ms) => Promise.race([
    Promise.all(anims.map((a) => a.finished.catch(() => {}))),
    new Promise((resolve) => { setTimeout(resolve, ms); }),
]);

/** Keluarkan elemen dengan cepat (Lab: exit x 60, scale .96, 0,26 s) sebelum tata letak berubah. */
export function exit(els, { x = 40, y = 0 } = {}) {
    const calm = reduced();
    const duration = calm ? 120 : 180;
    return settled(els.map((el) => el.animate(calm
        ? [{ opacity: 1 }, { opacity: 0 }]
        : [{ opacity: 1, transform: 'none' }, { opacity: 0, transform: `translate(${x}px, ${y}px) scale(0.96)` }],
    { duration, easing: 'cubic-bezier(.4, 0, 1, 1)', fill: 'forwards' })), duration + 150);
}

/**
 * Sungai Order: pilih tahap. Panel lama keluar (fade + naik sedikit), tinggi panel beranimasi spring ke tinggi baru,
 * panel baru masuk dari bawah (Lab: AnimatePresence mode "wait").
 */
export function kacaRiver(initial) {
    return {
        selected: initial,
        openOrder: null,
        busy: false,
        async select(key) {
            if (key === this.selected || this.busy) { this.openOrder = null; return; }
            this.busy = true;
            const box = this.$refs.stageBox;
            const panes = () => [...box.querySelectorAll(':scope > [data-stage]')];
            const old = panes().filter(visible);
            await settled(old.map((el) => el.animate(reduced()
                ? [{ opacity: 1 }, { opacity: 0 }]
                : [{ opacity: 1, transform: 'none' }, { opacity: 0, transform: 'translateY(-6px)' }],
            { duration: 150, easing: 'ease-in', fill: 'forwards' })), 300);
            const anims = old.flatMap((el) => el.getAnimations());
            await flip(box, panes, () => { this.selected = key; this.openOrder = null; }, () => this.$nextTick());
            anims.forEach((a) => a.cancel());
            this.busy = false;
        },
    };
}

/**
 * x-kaca-seg: menaruh .kaca-thumb di bawah tombol [aria-pressed="true"] dalam grup. Perubahan aria-pressed (diikat Alpine)
 * memicu MutationObserver, sehingga thumb meluncur lewat transisi CSS. Penempatan pertama tanpa transisi.
 */
export function kacaSegment(el) {
    const thumb = el.querySelector('.kaca-thumb');
    if (!thumb) return () => {};
    const place = () => {
        const on = el.querySelector('button[aria-pressed="true"]');
        if (!on || on.offsetWidth === 0) return;
        thumb.style.width = on.offsetWidth + 'px';
        thumb.style.height = on.offsetHeight + 'px';
        thumb.style.transform = `translate(${on.offsetLeft}px, ${on.offsetTop}px)`;
        if (!thumb.classList.contains('is-ready')) requestAnimationFrame(() => requestAnimationFrame(() => thumb.classList.add('is-ready')));
    };
    const mo = new MutationObserver(place);
    mo.observe(el, { subtree: true, attributes: true, attributeFilter: ['aria-pressed'] });
    window.addEventListener('resize', place);
    requestAnimationFrame(place);
    document.fonts?.ready.then(place);
    return () => { mo.disconnect(); window.removeEventListener('resize', place); };
}

export function kacaAdmin(cfg) {
    return {
        queue: cfg.queue,
        periods: cfg.periods,
        orders: cfg.orders,
        commands: cfg.commands,

        period: 'Hari ini',
        loading: false,
        filter: 'semua',
        focusKey: cfg.queue[0]?.key ?? null,
        drawerIndex: null,
        fullOpen: false,
        paletteOpen: false,
        q: '',
        active: 0,

        num: (v, d = 0) => number(v, d),

        // ---- strip ringkasan
        better(m) {
            const change = (m.value - m.prev) / m.prev;
            return m.lowerBetter ? change < 0 : change > 0;
        },
        shown: cfg.periods['Hari ini'].metrics.map((m) => m.value),
        setPeriod(p) {
            if (p === this.period) return;
            this.loading = true;
            this.period = p;
            setTimeout(() => { this.loading = false; }, 480);
            this.countTo(this.periods[p].metrics.map((m) => m.value));
        },
        /** Angka bergerak dari nilai tampil ke nilai periode baru (Lab › useCountUp); hanya saat periode diganti, bukan saat dimuat. */
        countTo(target) {
            const from = this.shown.slice();
            if (reduced()) { this.shown = target; return; }
            const t0 = performance.now();
            const step = (t) => {
                const k = Math.min(1, (t - t0) / 850);
                const e = 1 - Math.pow(1 - k, 3);
                this.shown = target.map((v, i) => from[i] + (v - from[i]) * e);
                if (k < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        },

        // ---- antrean
        /** Ganti saringan dengan FLIP: baris yang tersaring keluar, sisanya meluncur ke posisi baru, yang baru masuk. */
        async setFilter(key) {
            if (key === this.filter || this.filtering) return;
            this.filtering = true;
            const list = this.$refs.queueList;
            const rows = () => [...list.querySelectorAll(':scope > li[data-kind]')];
            const leaving = rows().filter((li) => visible(li) && !FILTERS[key](li.dataset.kind));
            await exit(leaving);
            await flip(list, rows, () => { this.filter = key; }, () => this.$nextTick());
            leaving.forEach((li) => li.getAnimations().forEach((a) => a.cancel()));
            this.filtering = false;
        },
        filtering: false,
        matches(kind) {
            return FILTERS[this.filter](kind);
        },
        visibleCount() {
            return this.queue.filter((i) => this.matches(i.kind)).length;
        },

        // ---- laci keputusan
        get drawerOpen() { return this.drawerIndex !== null; },
        get item() { return this.drawerIndex === null ? null : this.queue[this.drawerIndex] ?? null; },
        get index() { return this.drawerIndex ?? -1; },
        review(i) { this.drawerIndex = i; },
        closeDrawer() { this.drawerIndex = null; },
        nav(delta) {
            const next = this.drawerIndex + delta;
            if (next >= 0 && next < this.queue.length) this.drawerIndex = next;
        },

        // ---- palet perintah
        openPalette() {
            this.q = '';
            this.active = 0;
            this.paletteOpen = true;
            this.$nextTick(() => this.$refs.paletteInput?.focus());
        },
        get parsed() {
            const text = this.q.trim().toLowerCase();
            const chips = [];
            const tests = [];
            let orderRe = null;
            WORDS.forEach((w) => {
                if (w.re.test(text)) {
                    chips.push(w.chip);
                    if (w.test) tests.push(w.test);
                    if (w.order) orderRe = w.order;
                }
            });
            const items = text === ''
                ? []
                : this.queue.filter((i) => (tests.length ? tests.some((t) => t(i)) : (i.title + ' ' + i.meta + ' ' + i.id).toLowerCase().includes(text)));
            let orders = [];
            if (text !== '') {
                orders = this.orders.filter((o) => (orderRe ? orderRe.test(o.material) : (o.code + ' ' + o.material + ' ' + o.seller + ' ' + o.stage).toLowerCase().includes(text))).slice(0, 4);
            }
            return { chips, items, orders };
        },
        get chips() { return this.parsed.chips; },
        get foundItems() { return this.parsed.items; },
        get rows() {
            const { items, orders } = this.parsed;
            const text = this.q.trim().toLowerCase();
            const cmds = this.commands.filter((c) => text === '' || c.label.toLowerCase().includes(text) || this.parsed.chips.length === 0);
            const rows = [];
            items.forEach((i, n) => rows.push({
                key: 'i' + i.key, head: n === 0 ? 'Antrean keputusan' : null, label: i.title, hint: i.id,
                run: () => { this.paletteOpen = false; this.review(this.queue.indexOf(i)); },
            }));
            orders.forEach((o, n) => rows.push({
                key: 'o' + o.code, head: n === 0 ? 'Order' : null, label: o.code + ' · ' + o.material + ' · ' + o.seller, hint: o.stage,
                run: () => { this.paletteOpen = false; },
            }));
            cmds.forEach((c, n) => rows.push({
                key: 'c' + c.url, head: n === 0 ? 'Perintah' : null, label: c.label, hint: '↵',
                run: () => { window.location.href = c.url; },
            }));
            return rows;
        },
        runRow() {
            this.rows[this.active]?.run();
        },

        // ---- papan ketik global
        onKey(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                if (this.paletteOpen) this.paletteOpen = false; else this.openPalette();
                return;
            }
            if (this.paletteOpen) return;
            if (this.fullOpen && e.key === 'Escape') { this.fullOpen = false; return; }
            if (!this.drawerOpen) return;
            const t = e.target;
            const typing = t.tagName === 'TEXTAREA' || (t.tagName === 'INPUT' && !['radio', 'checkbox'].includes(t.type));
            if (e.key === 'Escape') {
                this.closeDrawer();
            } else if (!typing && (e.key === 'j' || e.key === 'ArrowDown')) {
                e.preventDefault();
                this.nav(1);
            } else if (!typing && (e.key === 'k' || e.key === 'ArrowUp')) {
                e.preventDefault();
                this.nav(-1);
            }
        },
    };
}
