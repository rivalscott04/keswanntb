@props(['url', 'label'])

<a href="{{ $url }}" target="_blank" class="text-primary-600 hover:text-primary-500">
    {{ $label }}
    <x-heroicon-s-arrow-top-right-on-square class="w-4 h-4 inline-block ml-1" />
</a>