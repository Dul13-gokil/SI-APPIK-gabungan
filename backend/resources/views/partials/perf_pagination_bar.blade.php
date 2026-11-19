{{-- Reusable Pagination Bar (Selaras dengan desain SI-APPIK V2) --}}
@php
    $pagerId = $pagerId ?? 'perfPager';
    $unitLabel = $unitLabel ?? 'data';
@endphp
<div id="{{ $pagerId }}" class="mt-3 pt-3 border-t border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs">
    <div class="flex flex-wrap items-center gap-2 text-[#475569]">
        <span>Tampilkan:</span>
        <select id="{{ $pagerId }}-size"
            class="px-2.5 py-1.5 rounded-xl border border-[#E2E8F0] bg-white text-xs font-bold text-[#0F172A] focus:outline-none focus:border-[#2563EB] cursor-pointer">
            <option value="5">5 {{ $unitLabel }}</option>
            <option value="10" selected>10 {{ $unitLabel }}</option>
            <option value="25">25 {{ $unitLabel }}</option>
            <option value="50">50 {{ $unitLabel }}</option>
        </select>
        <span id="{{ $pagerId }}-info" class="text-xs text-[#64748B]">Menampilkan 0 data</span>
    </div>

    <div id="{{ $pagerId }}-nav" class="flex items-center gap-1.5 flex-wrap justify-end"></div>
</div>
