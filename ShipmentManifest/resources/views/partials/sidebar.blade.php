@php
    /*
     | Menu otomatis aktif bila rute modul lain sudah terdaftar (Route::has).
     | INTEGRASI: cukup definisikan rute bernama di bawah, tautan langsung hidup.
     */
    $nav = [
        ['label' => 'Dasbor', 'route' => 'dashboard', 'match' => 'dashboard'],
        ['label' => 'Purchase Order', 'route' => 'purchase-orders.index', 'match' => 'purchase-orders.*'],
        ['label' => 'Invoice & Pembayaran', 'route' => 'invoices.index', 'match' => 'invoices.*'],
        ['label' => 'Pengiriman & Manifes', 'route' => 'shipments.index', 'match' => 'shipments.*'],
        ['label' => 'Penerimaan Barang', 'route' => 'goods-receipts.index', 'match' => 'goods-receipts.*'],
        ['label' => 'Sengketa & Klaim', 'route' => 'disputes.index', 'match' => 'disputes.*'],
    ];
    $me = auth()->user();
@endphp

<div id="sidebar-backdrop" data-sidebar-backdrop class="fixed inset-0 z-30 hidden bg-black/30 md:hidden"></div>

<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-60 -translate-x-full flex-col border-r border-zinc-200 bg-white transition-transform md:translate-x-0">
    <div class="flex h-16 items-center gap-2.5 border-b border-zinc-200 px-5">
        <span class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-600 text-sm font-bold text-white">S</span>
        <div class="leading-tight">
            <p class="text-sm font-semibold">SymbioNusa</p>
            <p class="text-xs text-zinc-500">Ekonomi Sirkular B2B</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Navigasi utama">
        @foreach ($nav as $item)
            @if (Route::has($item['route']))
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium {{ $active ? 'bg-emerald-50 text-emerald-700' : 'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $active ? 'bg-emerald-600' : 'bg-zinc-300' }}"></span>
                    {{ $item['label'] }}
                </a>
            @else
                <span class="flex cursor-not-allowed items-center gap-3 rounded-md px-3 py-2 text-sm text-zinc-400" title="Modul lain (belum terintegrasi)">
                    <span class="h-1.5 w-1.5 rounded-full bg-zinc-200"></span>
                    {{ $item['label'] }}
                </span>
            @endif
        @endforeach
    </nav>

    <div class="border-t border-zinc-200 p-4">
        @if (config('symbionusa.standalone'))
            <form method="POST" action="{{ route('demo.actor') }}">
                @csrf
                <label for="demo-user" class="mb-1 block text-xs font-medium text-zinc-500">Masuk sebagai (demo)</label>
                <select id="demo-user" name="user_id" onchange="this.form.submit()" class="input py-1.5 text-xs">
                    @foreach (\App\Models\User::orderBy('role')->orderBy('name')->get() as $u)
                        <option value="{{ $u->id }}" @selected($me && $me->id === $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </form>
        @endif
        @if ($me)
            <p class="mt-3 text-xs text-zinc-500">Peran: <span class="font-medium text-zinc-700">{{ $me->roleLabel() }}</span></p>
        @endif
    </div>
</aside>
