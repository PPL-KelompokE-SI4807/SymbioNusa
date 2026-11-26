{{-- Kartu "Aturan & Info" di samping form (SCREENS 03). --}}
@use('App\Http\Requests\Inspector\InspectorRules')

<x-ui.card title="Aturan & info">
    <ul class="list-disc space-y-2 pl-5 text-body-sm text-on-surface-muted">
        <li>Status Inspector otomatis menjadi Nonaktif ketika tanggal berlaku sertifikat terlewati.</li>
        <li>Hanya Inspector aktif dengan keahlian sesuai yang muncul pada penugasan inspeksi (PBI-18).</li>
        <li>Wilayah tugas dipakai menyaring penugasan mode Lapangan.</li>
        <li>Berkas maks. {{ (int) (InspectorRules::maxUploadKb() / 1024) }} MB (PDF, JPG, PNG) dan hanya bisa diunduh Admin serta Inspector pemiliknya.</li>
    </ul>
</x-ui.card>
