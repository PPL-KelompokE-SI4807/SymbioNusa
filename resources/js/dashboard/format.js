/**
 * Format angka Indonesia untuk dashboard, sama dengan App\Support\Format (PHP): dipakai di setiap frame animasi
 * sehingga koma desimal dan titik ribuan tidak pernah rusak di tengah gerak (panduan §8.6).
 */
const formatters = new Map();

export function number(value, decimals = 0) {
    const key = decimals;
    if (!formatters.has(key)) {
        formatters.set(key, new Intl.NumberFormat('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }));
    }
    return formatters.get(key).format(Number(value) || 0);
}

/** "Rp 47,5 jt", "Rp 18 jt", "Rp 1,2 M" — tanpa ",0" di belakang. */
export function rupiahShort(value) {
    const v = Number(value) || 0;
    const abs = Math.abs(v);
    let scaled = v;
    let unit = '';
    if (abs >= 1e9) { scaled = v / 1e9; unit = 'M'; } else if (abs >= 1e6) { scaled = v / 1e6; unit = 'jt'; } else if (abs >= 1e3) { scaled = v / 1e3; unit = 'rb'; }
    if (unit === '') return `Rp ${number(v)}`;
    let text = number(scaled, 1);
    if (text.endsWith(',0')) text = text.slice(0, -2);
    return `Rp ${text} ${unit}`;
}

/** kg → "12,4 ton". */
export function tonFromKg(kg, decimals = 1) {
    return `${number((Number(kg) || 0) / 1000, decimals)} ton`;
}

export function ton(value, decimals = 1) {
    return `${number(value, decimals)} ton`;
}

/** "41%" dari rasio 0–1; bagian > 0 tetapi < 0,5% ditulis "<1%". */
export function percent(share) {
    const p = Math.round((Number(share) || 0) * 100);
    return p === 0 && share > 0 ? '<1%' : `${p}%`;
}
