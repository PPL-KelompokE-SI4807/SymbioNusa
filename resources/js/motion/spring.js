/**
 * Solver spring tunggal SymbioNusa (DESIGN.md › Motion; panduan dashboard Senja §8.8).
 * - Euler semi-implisit, sub-step 120 Hz, satu loop requestAnimationFrame untuk semua spring aktif.
 * - Spring dikenali lewat kunci stabil per properti. Menargetkan ulang spring yang sedang berjalan melanjutkan dari
 *   nilai dan kecepatan saat ini: tidak mulai ulang, tidak mengantre, tidak berteleportasi.
 * - prefers-reduced-motion: setiap spring langsung selesai di targetnya.
 */
export const SPRING_PRESETS = {
    gentle: { stiffness: 112, damping: 26, mass: 1 },
    snappy: { stiffness: 420, damping: 34, mass: 1 },
    softBounce: { stiffness: 280, damping: 19, mass: 1 },
};

const STEP = 1 / 120;
const springs = new Map();
const reducedQuery = typeof window !== 'undefined' && window.matchMedia
    ? window.matchMedia('(prefers-reduced-motion: reduce)')
    : { matches: false };

let frame = null;
let last = 0;

export const prefersReducedMotion = () => reducedQuery.matches;

function settle(s) {
    s.value = s.target;
    s.velocity = 0;
    s.active = false;
    s.onUpdate?.(s.value);
    const done = s.onRest;
    s.onRest = null;
    done?.();
}

function tick(now) {
    const dt = Math.min(0.064, Math.max(0, (now - last) / 1000));
    last = now;
    const steps = Math.max(1, Math.round(dt / STEP));
    const h = dt / steps;
    let running = false;

    for (const s of springs.values()) {
        if (!s.active) continue;
        const { stiffness, damping, mass } = s.params;
        for (let i = 0; i < steps; i++) {
            const accel = (-stiffness * (s.value - s.target) - damping * s.velocity) / mass;
            s.velocity += accel * h; // semi-implisit: kecepatan dulu, lalu posisi
            s.value += s.velocity * h;
        }
        if (Math.abs(s.value - s.target) < s.precision && Math.abs(s.velocity) < s.precision * 10) {
            settle(s);
        } else {
            s.onUpdate?.(s.value);
            running = true;
        }
    }

    frame = running ? requestAnimationFrame(tick) : null;
}

/**
 * Gerakkan nilai berkunci menuju target.
 * @param {string} key kunci stabil per properti
 * @param {number} target
 * @param {{preset?: 'gentle'|'snappy'|'softBounce', from?: number, precision?: number, onUpdate?: (v:number)=>void, onRest?: ()=>void}} options
 *   `from` hanya dipakai bila spring belum berjalan (spring yang sedang berjalan selalu melanjutkan dari posisinya).
 */
export function spring(key, target, options = {}) {
    let s = springs.get(key);
    if (!s) {
        s = { value: options.from ?? target, velocity: 0, active: false };
        springs.set(key, s);
    } else if (!s.active && options.from !== undefined) {
        s.value = options.from;
        s.velocity = 0;
    }

    s.target = target;
    s.params = SPRING_PRESETS[options.preset ?? 'snappy'];
    s.precision = options.precision ?? 0.001;
    if (options.onUpdate) s.onUpdate = options.onUpdate;
    s.onRest = options.onRest ?? null;

    if (prefersReducedMotion()) {
        settle(s);
        return s;
    }

    s.active = true;
    if (frame === null) {
        last = performance.now();
        frame = requestAnimationFrame(tick);
    }

    return s;
}

/** Nilai yang sedang ditampilkan untuk sebuah kunci (atau fallback bila belum ada). */
export function current(key, fallback = 0) {
    return springs.get(key)?.value ?? fallback;
}

/**
 * Ekspansi/penciutan tinggi dengan spring Gentle (FLIP tinggi): mulai dari tinggi yang sedang tampil.
 */
export function springHeight(key, el, open, onRest) {
    if (!el) return;
    const from = el.getBoundingClientRect().height;
    el.style.overflow = 'hidden';
    el.style.height = 'auto';
    const to = open ? el.scrollHeight : 0;
    el.style.height = `${from}px`;
    spring(key, to, {
        preset: 'gentle',
        from,
        precision: 0.5,
        onUpdate: (v) => { el.style.height = `${Math.max(0, v)}px`; },
        onRest: () => {
            if (open) {
                el.style.height = 'auto';
                el.style.overflow = '';
            } else {
                el.style.height = '0px';
            }
            onRest?.();
        },
    });
}

/**
 * Konten pengganti bergeser masuk searah aliran (dir = 1: dari kiri, -1: dari kanan) dengan spring Gentle.
 */
export function slideIn(key, el, dir = 1) {
    if (!el || prefersReducedMotion()) return;
    spring(`${key}:x`, 0, { preset: 'gentle', from: -24 * dir, precision: 0.05, onUpdate: (v) => { el.style.transform = `translateX(${v}px)`; } });
    spring(`${key}:o`, 1, { preset: 'gentle', from: 0, onUpdate: (v) => { el.style.opacity = String(Math.min(1, Math.max(0, v))); } });
}

let pressId = 0;
const pressKeys = new WeakMap();

/** Tekan ber-spring untuk elemen [data-press]: turun sedikit saat ditekan, memantul Snappy saat dilepas. */
export function bindPress(root = document) {
    const keyOf = (el) => {
        if (!pressKeys.has(el)) pressKeys.set(el, `press-${++pressId}`);
        return pressKeys.get(el);
    };
    const to = (el, scale) => spring(keyOf(el), scale, {
        preset: 'snappy',
        from: 1,
        precision: 0.0005,
        onUpdate: (v) => { el.style.transform = v === 1 ? '' : `scale(${v})`; },
    });

    root.addEventListener('pointerdown', (e) => {
        const el = e.target.closest?.('[data-press]');
        if (el) to(el, 0.96);
    });
    for (const type of ['pointerup', 'pointercancel', 'pointerleave']) {
        root.addEventListener(type, (e) => {
            const el = e.target.closest?.('[data-press]');
            if (el) to(el, 1);
        }, true);
    }
}
