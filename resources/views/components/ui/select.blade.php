{{--
    Select (DESIGN.md › input-field). Isi <option> lewat slot.
--}}
@props(['name' => null])

@php
    $invalid = $name && $errors->has($name);
@endphp

<select @if ($name) name="{{ $name }}" @endif
        @if ($invalid) aria-invalid="true" @endif
        {{ $attributes->class([
            'block h-10 w-full rounded-sm border bg-surface px-3 text-body-md text-on-surface',
            'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring',
            'disabled:cursor-not-allowed disabled:bg-background disabled:opacity-50',
            $invalid ? 'border-error' : 'border-outline-input',
        ]) }}>
    {{ $slot }}
</select>
