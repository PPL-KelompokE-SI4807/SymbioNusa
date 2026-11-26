{{--
    Textarea (DESIGN.md › Forms). Isi awal lewat slot.
--}}
@props(['name' => null, 'rows' => 4])

@php
    $invalid = $name && $errors->has($name);
@endphp

<textarea @if ($name) name="{{ $name }}" @endif rows="{{ $rows }}"
          @if ($invalid) aria-invalid="true" @endif
          {{ $attributes->class([
              'block w-full rounded-sm border bg-surface px-3 py-2 text-body-md text-on-surface placeholder:text-on-surface-muted',
              'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus-ring',
              'disabled:cursor-not-allowed disabled:bg-background disabled:opacity-50',
              $invalid ? 'border-error' : 'border-outline-input',
          ]) }}>{{ $slot }}</textarea>
