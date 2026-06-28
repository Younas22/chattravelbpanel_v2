@props(['name', 'image' => null, 'sizeClass' => 'w-9 h-9', 'colorClass' => 'bg-blue-100 text-blue-700'])
@if($image)
    <div class="relative shrink-0 {{ $sizeClass }}">
        <img src="{{ $image }}" alt="{{ $name }}"
             class="{{ $sizeClass }} rounded-full object-cover"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="{{ $sizeClass }} {{ $colorClass }} rounded-full items-center justify-center font-semibold text-sm absolute inset-0" style="display:none">
            {{ strtoupper(substr($name, 0, 1)) }}
        </div>
    </div>
@else
    <div class="{{ $sizeClass }} {{ $colorClass }} rounded-full flex items-center justify-center font-semibold text-sm shrink-0">
        {{ strtoupper(substr($name, 0, 1)) }}
    </div>
@endif
