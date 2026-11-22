@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidthClass = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth] ?? 'sm:max-w-2xl';
@endphp

<dialog
    data-modal-name="{{ $name }}"
    data-initial-show="{{ $show ? 'true' : 'false' }}"
    closedby="any"
    {{ $attributes->merge(['class' => 'app-dialog w-full ' . $maxWidthClass]) }}
>
    {{ $slot }}
</dialog>
