{{--
    Indikator notifikasi (DESIGN.md › Layout, amandemen FND v8). Jumlah = notifikasi belum dibaca milik pengguna
    (tabel notifications, read_at NULL). Halaman notifikasi belum ada, jadi ini indikator status, bukan tombol.
--}}
@php
    $unread = 0;
    try {
        $userId = auth()->id();
        if ($userId && \Illuminate\Support\Facades\Schema::hasTable('notifications')) {
            $unread = \Illuminate\Support\Facades\DB::table('notifications')
                ->where('users_id_user', $userId)->whereNull('read_at')->count();
        }
    } catch (\Throwable) {
        $unread = 0;
    }
@endphp

<span {{ $attributes->class(['relative flex size-11 shrink-0 items-center justify-center rounded-sm border border-outline bg-surface text-on-surface']) }}
      role="img" aria-label="{{ $unread > 0 ? $unread.' notifikasi belum dibaca' : 'Tidak ada notifikasi baru' }}"
      title="{{ $unread > 0 ? $unread.' notifikasi belum dibaca' : 'Tidak ada notifikasi baru' }}">
    <x-ui.icon name="bell" />
    @if ($unread > 0)
        <span class="absolute -top-1.5 -right-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-error px-1 text-label-sm text-on-primary tabular-nums" aria-hidden="true">
            {{ $unread > 99 ? '99+' : $unread }}
        </span>
    @endif
</span>
