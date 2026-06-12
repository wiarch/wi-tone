@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

@php
$alignmentClasses = $align === 'left' ? 'origin-top-left left-0' : 'origin-top-right right-0';
$widthClass = match ($width) {
    '48' => 'w-48',
    default => 'w-48',
};
@endphp

<details class="relative inline-block">
    <summary class="cursor-pointer list-none [&::-webkit-details-marker]:hidden">
        {{ $trigger }}
    </summary>
    <div class="absolute z-50 mt-2 {{ $widthClass }} rounded-md shadow-lg ring-1 ring-black ring-opacity-5 {{ $alignmentClasses }}">
        <div class="{{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</details>
