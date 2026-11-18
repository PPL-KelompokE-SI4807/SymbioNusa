{{--
    Input teks (DESIGN.md › input-field): tinggi 40 px, radius 6 px, border outline-input.
    Otomatis ditandai tidak valid bila ada galat validasi untuk name-nya.
--}}
@props(['name' => null, 'type' => 'text'])

@php
    $invalid = $name && $errors->has($name);
@endphp

<input type="{{ $type }}" @if ($name) name="{{ $name }}" @endif
       @if ($invalid) aria-invalid="true" @endif
       {{ $attributes->class([
           'block h-10 w-full rounded-sm border bg-surface px-3 text-body-md text-on-surface placeholder:text-on-surface-muted',
           'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring',
           'disabled:cursor-not-allowed disabled:bg-background disabled:opacity-50',
           $invalid ? 'border-error' : 'border-outline-input',
       ]) }}>
