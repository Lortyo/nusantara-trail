@php
    $map = [
        'open' => ['Registration open', 'bg-[#e3f0e8] text-forest-700'],
        'draft' => ['Draft', 'bg-[#fdf0d5] text-[#a8670f]'],
        'closed' => ['Registration closed', 'bg-[#fde8e8] text-[#b42318]'],
        'finished' => ['Completed', 'bg-[#efeee9] text-gray-500'],
    ];
    [$label, $cls] = $map[$status ?? 'draft'] ?? $map['draft'];
@endphp
<span class="chip {{ $cls }}">{{ $label }}</span>