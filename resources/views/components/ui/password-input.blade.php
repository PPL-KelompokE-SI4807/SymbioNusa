{{--
    Input kata sandi dengan tombol "Tampilkan kata sandi" (DESIGN.md › Forms, amandemen FND v8).
    Atribut lain diteruskan ke <input>. Contoh:
    <x-ui.password-input id="password" name="password" autocomplete="current-password" required />
--}}
@props(['name' => null, 'invalid' => null])

@php
    $invalid = $invalid ?? ($name && $errors->has($name));
@endphp

<div class="relative" x-data="{ show: false }">
    <x-ui.icon name="lock" class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-on-surface-subtle" />
    <input :type="show ? 'text' : 'password'" type="password" @if ($name) name="{{ $name }}" @endif
           @if ($invalid) aria-invalid="true" @endif
           {{ $attributes->class([
               'block h-11 w-full rounded-sm border bg-surface pr-12 pl-11 text-body-md text-on-surface placeholder:text-on-surface-subtle',
               'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring',
               $invalid ? 'border-error' : 'border-outline-input',
           ]) }}>
    <button type="button" @click="show = ! show"
            class="absolute top-1/2 right-1.5 flex size-9 -translate-y-1/2 items-center justify-center rounded-sm text-on-surface-subtle hover:bg-surface-muted focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring"
            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'" aria-label="Tampilkan kata sandi"
            :aria-pressed="show.toString()">
        <x-ui.icon name="eye" x-show="! show" />
        <x-ui.icon name="eye-off" x-show="show" x-cloak />
    </button>
</div>
