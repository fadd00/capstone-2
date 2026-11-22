@props([
    'id' => null,
    'align' => 'right',
    'width' => '48',
    'contentClasses' => 'py-1 bg-white',
])

@php
$menuId = $id ?? 'popover-menu-' . \Illuminate\Support\Str::random(6);
$widthClass = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="relative inline-block">
    {{ $trigger }}

    <div id="{{ $menuId }}"
         popover="auto"
         class="popover-menu {{ $widthClass }} {{ $contentClasses }}">
        {{ $content }}
    </div>
</div>
