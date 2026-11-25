// Dashboard Senja di Sawah: solver spring bersama + komponen Alpine (Alpine dibawa Livewire 4).
import { bindPress } from './motion/spring';
import { kacaAdmin, kacaRiver, kacaSegment } from './dashboard/kaca';
import { antreanAdmin, daftarBerjalan, donutMaterial, grafikTahap, hasilAkhir, garisWaktu, ruteKirim, sungaiOrder } from './dashboard/admin';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('kacaAdmin', kacaAdmin);
    window.Alpine.data('kacaRiver', kacaRiver);
    window.Alpine.directive('kaca-seg', (el, _value, { cleanup }) => cleanup(kacaSegment(el)));
    window.Alpine.data('sungaiOrder', sungaiOrder);
    window.Alpine.data('antreanAdmin', antreanAdmin);
    window.Alpine.data('daftarBerjalan', daftarBerjalan);
    window.Alpine.data('grafikTahap', grafikTahap);
    window.Alpine.data('donutMaterial', donutMaterial);
    window.Alpine.data('hasilAkhir', hasilAkhir);
    window.Alpine.data('ruteKirim', ruteKirim);
    window.Alpine.data('garisWaktu', garisWaktu);
});

bindPress(document);
