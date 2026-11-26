/**
 * Komponen Alpine dashboard Admin "Senja di Sawah" (panduan dashboard §6.1, §7, §8).
 * Server (DashboardService) adalah satu-satunya sumber angka; komponen ini hanya menampilkan dan menggerakkan.
 * Integritas interaksi: setiap komponen hanya mengubah panelnya sendiri. Satu-satunya penghubung sungai → antrean
 * adalah tombol eksplisit "Lihat di antrean ↓".
 */
import { spring, springHeight, slideIn, prefersReducedMotion } from '../motion/spring';
import { number, percent, rupiahShort, tonFromKg } from './format';

const rupiahFull = (v) => `Rp ${number(Math.round(Number(v) || 0))}`;

/** Gerakkan thumb segmented control ke tombol aktif (spring Snappy, dapat diinterupsi). */
function moveThumb(key, ctl, from) {
    if (!ctl) return;
    const thumb = ctl.querySelector('.seg-ctl-thumb');
    const active = ctl.querySelector('[aria-selected="true"]');
    if (!thumb || !active) return;
    const x = active.offsetLeft;
    const w = active.offsetWidth;
    const instant = from === 'init';
    spring(`${key}:x`, x, { preset: 'snappy', from: instant ? x : undefined, precision: 0.05, onUpdate: (v) => { thumb.style.transform = `translateX(${v}px)`; } });
    spring(`${key}:w`, w, { preset: 'snappy', from: instant ? w : undefined, precision: 0.05, onUpdate: (v) => { thumb.style.width = `${v}px`; } });
}

/** Pola tablist: panah kiri/kanan, Home/End. */
function tablistKey(event, keys, currentKey, activate) {
    const i = keys.indexOf(currentKey);
    const next = {
        ArrowRight: keys[(i + 1) % keys.length],
        ArrowLeft: keys[(i - 1 + keys.length) % keys.length],
        Home: keys[0],
        End: keys[keys.length - 1],
    }[event.key];
    if (next === undefined) return;
    event.preventDefault();
    activate(next);
    event.currentTarget.querySelector(`[data-tab="${next}"]`)?.focus();
}

/** Tinggi wadah mengikuti perubahan isi dengan spring Gentle (FLIP tinggi). */
function animateHeight(key, el, mutate, nextTick) {
    if (!el || prefersReducedMotion()) { mutate(); return; }
    const from = el.getBoundingClientRect().height;
    mutate();
    nextTick(() => {
        el.style.height = 'auto';
        const to = el.getBoundingClientRect().height;
        el.style.overflow = 'hidden';
        el.style.height = `${from}px`;
        spring(key, to, {
            preset: 'gentle', from, precision: 0.5,
            onUpdate: (v) => { el.style.height = `${v}px`; },
            onRest: () => { el.style.height = ''; el.style.overflow = ''; },
        });
    });
}

function highlight(el) {
    if (!el || prefersReducedMotion()) return;
    el.classList.remove('dash-highlight');
    void el.offsetWidth;
    el.classList.add('dash-highlight');
}

/* ------------------------------------------------------------------------------------------------ */

export function sungaiOrder(flow) {
    return {
        stages: flow.stages,
        exits: flow.exits,
        selected: null,
        paused: false,
        reduced: prefersReducedMotion(),
        particles: [],

        init() {
            const first = this.stages.find((s) => s.count > 0) ?? this.exits.find((s) => s.count > 0) ?? this.stages[0];
            this.selected = first.key;
            this.$nextTick(() => this.startParticles());
        },

        get all() { return [...this.stages, ...this.exits]; },
        get current() { return this.all.find((s) => s.key === this.selected); },
        get maxAge() { return Math.max(1, ...this.current.orders.map((o) => o.age ?? 0)); },
        get title() { return `${this.current.label} · ${number(this.current.count)} order`; },
        get total() { return `Total ${rupiahShort(this.current.nilai)}`; },
        get partial() {
            const c = this.current;
            return c.count > c.orders.length ? `Menampilkan ${number(c.orders.length)} dari ${number(c.count)}` : '';
        },
        get linkedRows() {
            const ids = new Set(this.current.orders.map((o) => String(o.id)));
            return [...document.querySelectorAll('[data-queue-po]')].filter((el) => ids.has(el.dataset.queuePo));
        },

        isOn(key) { return this.selected === key; },
        money: rupiahShort,
        num: number,
        kg: (v) => tonFromKg(v, 1),
        ageWidth(o) { return `${Math.max(6, Math.round(100 * (o.age ?? 0) / this.maxAge))}%`; },

        select(key) {
            if (key === this.selected) return;
            const order = this.all.map((s) => s.key);
            const dir = order.indexOf(key) >= order.indexOf(this.selected) ? 1 : -1;
            this.selected = key;
            this.$nextTick(() => {
                slideIn('flow-panel', this.$refs.panelBody, dir);
                highlight(this.$refs.panel);
                this.repaintParticles();
            });
        },

        /** Satu-satunya tautan lintas: gulir ke antrean dan sorot baris yang cocok. Tidak mengubah tab/filter antrean. */
        toQueue() {
            const rows = this.linkedRows;
            const visible = rows.filter((el) => el.offsetParent !== null);
            const target = visible[0] ?? rows[0]?.closest('[data-queue-group]') ?? document.getElementById('antrean-tindakan');
            target?.scrollIntoView({ behavior: prefersReducedMotion() ? 'auto' : 'smooth', block: 'center' });
            const marks = visible.length ? visible : [target];
            setTimeout(() => marks.forEach(highlight), prefersReducedMotion() ? 0 : 350);
        },

        /* --- Partikel: satu-satunya gerak data kontinu; kepadatan & laju ∝ jumlah order tahap itu. --- */
        startParticles() {
            if (this.reduced) return;
            const canvases = [...this.$root.querySelectorAll('canvas[data-flow-canvas]')];
            const max = Math.max(1, ...canvases.map((c) => Number(c.dataset.count)));
            const gold = getComputedStyle(this.$root).getPropertyValue('--color-category-plastik').trim() || '#d97706';
            this.particles = canvases.map((canvas) => {
                const count = Number(canvas.dataset.count);
                const n = count > 0 ? Math.max(3, Math.round(30 * count / max)) : 0;
                const dots = Array.from({ length: n }, () => ({ x: Math.random(), y: 0.2 + Math.random() * 0.6, r: 1.4 + Math.random() * 1.2, p: Math.random() * Math.PI * 2 }));
                return { canvas, ctx: canvas.getContext('2d'), dots, speed: 0.10 + 0.28 * count / max, gold };
            });
            const resize = () => this.particles.forEach(({ canvas }) => {
                const ratio = window.devicePixelRatio || 1;
                canvas.width = Math.round(canvas.clientWidth * ratio);
                canvas.height = Math.round(canvas.clientHeight * ratio);
            });
            resize();
            new ResizeObserver(resize).observe(this.$root);
            document.addEventListener('visibilitychange', () => this.loop());
            this.loop();
        },

        loop() {
            if (this._raf || this.paused || document.hidden || this.reduced) return;
            let last = performance.now();
            const step = (now) => {
                const dt = Math.min(0.05, (now - last) / 1000);
                last = now;
                this.drawParticles(dt);
                this._raf = (this.paused || document.hidden) ? null : requestAnimationFrame(step);
            };
            this._raf = requestAnimationFrame(step);
        },

        drawParticles(dt) {
            for (const p of this.particles) {
                const { canvas, ctx, dots } = p;
                const on = canvas.parentElement.getAttribute('aria-pressed') === 'true';
                const w = canvas.width;
                const h = canvas.height;
                ctx.clearRect(0, 0, w, h);
                ctx.fillStyle = on ? 'rgba(255,255,255,0.55)' : p.gold;
                ctx.globalAlpha = on ? 1 : 0.5;
                for (const d of dots) {
                    d.x += p.speed * dt * (60 / Math.max(60, canvas.clientWidth)) * 2;
                    if (d.x > 1.02) d.x -= 1.04;
                    d.p += dt * 2;
                    const y = (d.y + Math.sin(d.p) * 0.04) * h;
                    ctx.beginPath();
                    ctx.arc(d.x * w, y, d.r * (window.devicePixelRatio || 1), 0, Math.PI * 2);
                    ctx.fill();
                }
                ctx.globalAlpha = 1;
            }
        },

        repaintParticles() { if (this.paused) this.drawParticles(0); },

        togglePause() {
            this.paused = !this.paused;
            if (!this.paused) this.loop();
        },
    };
}

/* ------------------------------------------------------------------------------------------------ */

export function antreanAdmin(groups) {
    return {
        tab: 'semua',
        expanded: {},
        groups,

        init() { this.$nextTick(() => moveThumb('queue-tab', this.$refs.tabs, 'init')); },

        setTab(tab) {
            if (tab === this.tab) return;
            animateHeight('queue-list', this.$refs.list, () => { this.tab = tab; }, this.$nextTick);
            this.$nextTick(() => moveThumb('queue-tab', this.$refs.tabs));
        },
        keyTabs(e) { tablistKey(e, ['semua', 'mendesak'], this.tab, (t) => this.setTab(t)); },

        groupShown(key) { return this.tab === 'semua' || this.groups[key].urgent > 0; },
        groupCount(key) { return this.tab === 'semua' ? this.groups[key].count : this.groups[key].urgent; },
        rowShown(key, idx, urgentIdx) {
            const i = this.tab === 'semua' ? idx : urgentIdx;
            return i >= 0 && (i < 3 || !!this.expanded[key]);
        },
        hiddenCount(key) {
            const loaded = this.tab === 'semua' ? this.groups[key].loaded : this.groups[key].urgentLoaded;
            return Math.max(0, loaded - 3);
        },
        toggle(key) {
            animateHeight(`queue-${key}`, this.$refs[`group-${key}`], () => { this.expanded[key] = !this.expanded[key]; }, this.$nextTick);
        },
        num: number,
    };
}

/* ------------------------------------------------------------------------------------------------ */

export function daftarBerjalan() {
    return {
        open: false,
        day: null,
        toggle() { animateHeight('running-list', this.$refs.list, () => { this.open = !this.open; }, this.$nextTick); },
        /** Klik hari di jadwal 7 hari: sorot baris hari itu (baris lain meredup), klik lagi untuk kembali. */
        pickDay(date) {
            this.day = this.day === date ? null : date;
            if (this.day && !this.open && this.$refs.list.querySelector(`[data-extra][data-date="${date}"]`)) this.toggle();
        },
    };
}

/* ------------------------------------------------------------------------------------------------ */

export function hasilAkhir(outcomes) {
    return {
        outcomes,
        sel: null,
        get current() { return this.outcomes.find((o) => o.key === this.sel) ?? null; },
        pick(key) {
            const panel = this.$refs.detail;
            const next = this.sel === key ? null : key;
            if (next === null) {
                springHeight('outcome-detail', panel, false, () => { this.sel = null; });
                return;
            }
            const switching = this.sel !== null;
            this.sel = next;
            this.$nextTick(() => { springHeight('outcome-detail', panel, true); if (switching) highlight(panel); });
        },
        money: rupiahShort,
        num: number,
    };
}

/* ------------------------------------------------------------------------------------------------ */

export function grafikTahap(stages) {
    return {
        stages,
        mode: 'ton',
        selected: null,
        hover: null,
        drawn: false,
        horizontal: false,

        init() {
            this.$nextTick(() => moveThumb('chart-mode', this.$refs.modes, 'init'));
            // Arah batang mengikuti lebar kartu (sama dengan container query CSS 780 px).
            const measure = () => {
                const h = this.$refs.bars.clientWidth < 780;
                if (h !== this.horizontal) { this.horizontal = h; if (this.drawn) this.render(); }
            };
            this.horizontal = this.$refs.bars.clientWidth < 780;
            new ResizeObserver(measure).observe(this.$refs.bars);
            // Gambar-masuk sekali saat pertama terlihat (spring Gentle); tidak ada animasi berulang.
            const io = new IntersectionObserver((entries) => {
                if (!entries.some((e) => e.isIntersecting)) return;
                io.disconnect();
                this.render(true);
            }, { threshold: 0.15 });
            io.observe(this.$refs.bars);
            this.render(false, true);
        },

        value(s, mode = this.mode) { return mode === 'ton' ? s.kg / 1000 : s.nilai; },
        get max() { return Math.max(...this.stages.map((s) => this.value(s)), 0) || 1; },
        label(v) { return this.mode === 'ton' ? number(v, 1) : rupiahShort(v).replace('Rp ', ''); },
        exact(s) { return this.mode === 'ton' ? `${number(s.kg / 1000, 2)} ton` : rupiahFull(s.nilai); },

        /** Batang memorf dari nilai yang sedang tampil ke dataset baru — grafik tidak pernah dibongkar. */
        render(grow = false, hidden = false) {
            const cols = this.$refs.bars.querySelectorAll('[data-bar]');
            const max = this.max;
            this.stages.forEach((s, i) => {
                const col = cols[i];
                const bar = col.querySelector('.bar');
                const lbl = col.querySelector('.bar-value');
                // Satu spring per batang: tinggi dan label ikut rasio yang sama, label selalu dalam satuan aktif
                // (rasio × maksimum dataset aktif), diformat Indonesia di setiap frame. Saat diam = nilai persis.
                const apply = (r) => {
                    const rr = Math.max(0, r);
                    if (this.horizontal) {
                        bar.style.transform = `scaleX(${rr})`;
                        lbl.style.bottom = '';
                        lbl.style.left = `calc(${rr} * (100% - 64px) + 6px)`;
                    } else {
                        bar.style.transform = `scaleY(${rr})`;
                        lbl.style.left = '';
                        lbl.style.bottom = `calc(${rr} * (100% - 26px) + 4px)`;
                    }
                    lbl.textContent = this.label(rr * max);
                };
                if (hidden) { bar.style.transform = this.horizontal ? 'scaleX(0)' : 'scaleY(0)'; lbl.textContent = ''; return; }
                spring(`bar-${i}`, this.value(s) / max, { preset: grow ? 'gentle' : 'snappy', from: grow ? 0 : undefined, precision: 0.0005, onUpdate: apply });
            });
            this.drawn = true;
        },

        setMode(mode) {
            if (mode === this.mode) return;
            this.mode = mode;
            this.$nextTick(() => moveThumb('chart-mode', this.$refs.modes));
            this.render();
        },
        keyModes(e) { tablistKey(e, ['ton', 'val'], this.mode, (t) => this.setMode(t)); },

        tip(i) { this.hover = i; },
        get tipStyle() {
            if (this.hover === null) return '';
            const col = this.$refs.bars.querySelectorAll('[data-bar]')[this.hover];
            if (this.horizontal) return `top:${col.offsetTop - 30}px; left:112px`;
            const x = col.offsetLeft + col.offsetWidth / 2;
            const w = this.$refs.bars.clientWidth;
            return `left:${x}px; transform:translateX(${x < 90 ? '0' : (x > w - 90 ? '-100%' : '-50%')})`;
        },
        get crossStyle() {
            if (this.hover === null || this.horizontal) return 'display:none';
            const col = this.$refs.bars.querySelectorAll('[data-bar]')[this.hover];
            return `left:${col.offsetLeft + col.offsetWidth / 2}px`;
        },
        get tipText() { const s = this.stages[this.hover]; return s ? `${s.label} · ${this.exact(s)}` : ''; },

        pick(i) {
            const next = this.selected === i ? null : i;
            animateHeight('chart-list', this.$refs.list, () => { this.selected = next; }, this.$nextTick);
            if (next !== null) this.$nextTick(() => highlight(this.$refs.list));
        },
        reset() { this.pick(this.selected); },
        get sel() { return this.selected === null ? null : this.stages[this.selected]; },
        rowValue(o) { return this.mode === 'ton' ? tonFromKg(o.kg, 1) : rupiahShort(o.nilai); },
        num: number,
    };
}

/* ------------------------------------------------------------------------------------------------ */

export function donutMaterial(data) {
    return {
        rows: data.rows,
        totalTon: data.totalTon,
        hover: null,
        selected: null,

        get focus() { return this.rows.find((r) => r.key === (this.hover ?? this.selected)) ?? null; },
        get centerNum() { return number(this.focus ? this.focus.ton : this.totalTon, 1); },
        get centerLbl() { return this.focus ? `ton · ${this.focus.nama}` : 'ton · Total tercatat'; },
        get centerSub() { return this.focus ? `${percent(this.focus.share)} · ${rupiahShort(this.focus.nilai)}` : ''; },
        dim(key) { const k = this.hover ?? this.selected; return k !== null && k !== key; },

        pick(key) {
            const next = this.selected === key ? null : key;
            const panel = this.$refs.detail;
            if (this.selected !== null && next !== null) {
                this.selected = next;
                this.$nextTick(() => { springHeight('donut-detail', panel, true); highlight(panel); });
                return;
            }
            if (next !== null) {
                this.selected = next;
                this.$nextTick(() => springHeight('donut-detail', panel, true));
            } else {
                springHeight('donut-detail', panel, false, () => { this.selected = null; });
            }
        },
        close() { if (this.selected !== null) this.pick(this.selected); },
        get sel() { return this.rows.find((r) => r.key === this.selected) ?? null; },
        money: rupiahShort,
        num: number,
        ton: (v) => `${number(v, 1)} ton`,
    };
}

/* ------------------------------------------------------------------------------------------------ */

/**
 * Pelacak rute Buyer (panduan §7.7). Posisi penanda = status manifes (0 · ½ · 1), bukan GPS. Penanda bergerak sekali
 * ke posisinya dengan spring Gentle (bukan animasi berulang) dan berbelok dari posisi saat ini bila pengiriman diganti.
 */
export function ruteKirim(items) {
    return {
        items,
        sel: 0,
        ms: null,
        len: 0,

        init() {
            const first = this.items.findIndex((s) => s.stage === 1);
            this.sel = first >= 0 ? first : 0;
            this.$nextTick(() => {
                this.len = this.$refs.path.getTotalLength();
                const io = new IntersectionObserver((entries) => {
                    if (!entries.some((e) => e.isIntersecting)) return;
                    io.disconnect();
                    this.move(true);
                }, { threshold: 0.3 });
                io.observe(this.$refs.box);
                this.place(0);
            });
        },

        get current() { return this.items[this.sel] ?? null; },

        place(t) {
            const p = this.$refs.path.getPointAtLength(Math.max(0, Math.min(1, t)) * this.len);
            this.$refs.marker.setAttribute('transform', `translate(${p.x} ${p.y})`);
            this.$refs.done.style.strokeDashoffset = String(this.len * (1 - Math.max(0, Math.min(1, t))));
        },

        move(first = false) {
            spring('route-t', this.current.progress, {
                preset: 'gentle',
                from: first ? 0 : undefined,
                precision: 0.0005,
                onUpdate: (t) => this.place(t),
            });
        },

        pick(i) {
            if (i === this.sel) return;
            this.sel = i;
            this.ms = null;
            this.$nextTick(() => { this.move(); highlight(this.$refs.box); });
        },

        pickMs(i) { this.ms = this.ms === i ? null : i; },
        msState(i) { return i < this.current.stage ? 'done' : (i === this.current.stage ? 'now' : 'next'); },
    };
}

/* ------------------------------------------------------------------------------------------------ */

/**
 * Garis waktu jam Inspector (panduan §7.15). Jadwal 7 hari adalah satu-satunya pengendali hari yang tampil.
 * Tepat satu penanda SEKARANG, hanya pada hari ini, disisipkan menurut jam saat ini (WIB, dari server).
 * Klik tugas: detail mengembang di tempat (tinggi ber-spring Gentle), klik lagi melipat.
 */
export function garisWaktu(tl) {
    return {
        tl,
        day: null,
        open: null,

        init() {
            const days = Object.keys(this.tl.days);
            this.day = this.tl.days[this.tl.today]?.length ? this.tl.today : (days.find((d) => this.tl.days[d].length) ?? this.tl.today);
        },

        get entries() { return this.tl.days[this.day] ?? []; },
        get isToday() { return this.day === this.tl.today; },
        /** Indeks entri pertama setelah jam sekarang; penanda SEKARANG ditaruh sebelum entri ini. */
        get nowIndex() {
            if (!this.isToday) return -1;
            const i = this.entries.findIndex((e) => e.minutes > this.tl.now_minutes);
            return i === -1 ? this.entries.length : i;
        },
        get nextId() {
            const start = this.isToday ? this.nowIndex : 0;
            return this.entries.slice(Math.max(0, start)).find((e) => !e.done)?.id ?? null;
        },
        get dayLabel() { return this.tl.week.find((d) => d.date === this.day)?.label ?? ''; },

        pickDay(date) {
            if (date === this.day) return;
            this.open = null;
            this.day = date;
            this.$nextTick(() => highlight(this.$refs.rail));
        },
        keyDays(e) { tablistKey(e, this.tl.week.map((d) => d.date), this.day, (d) => this.pickDay(d)); },

        toggle(id) {
            const el = this.$refs.rail.querySelector(`[data-detail="${id}"]`);
            if (this.open === id) {
                springHeight(`tl-${id}`, el, false, () => { if (this.open === id) this.open = null; });
                this.open = -1;
                return;
            }
            const prev = this.open;
            if (prev !== null && prev !== -1) {
                springHeight(`tl-${prev}`, this.$refs.rail.querySelector(`[data-detail="${prev}"]`), false);
            }
            this.open = id;
            this.$nextTick(() => springHeight(`tl-${id}`, el, true));
        },
    };
}
