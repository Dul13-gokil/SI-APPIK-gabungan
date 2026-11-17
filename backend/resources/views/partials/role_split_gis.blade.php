@php
    $activeDistrictCount = collect($districtStats)->where('total', '>', 0)->count();
    $safeDistrictCount = max(0, count($districtStats) - $activeDistrictCount);
@endphp

<!-- Split GIS Workspace (Left: Peta Spasial, Right: Direktori 15 Kecamatan) -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-stretch">

    <!-- Kolom Kiri: Peta Spasial -->
    <div class="xl:col-span-7 rounded-[20px] p-5 bg-white border border-[#E2E8F0] flex flex-col gap-3.5 h-full">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <h3 class="font-outfit font-bold text-[15px] text-[#0F172A] flex items-center gap-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-[#2563EB] shrink-0"></i>
                <span>{{ $mapTitle ?? 'Peta Spasial 15 Kecamatan' }}</span>
            </h3>
            <div class="flex items-center gap-2.5 text-[11px] font-semibold text-[#475569] overflow-x-auto whitespace-nowrap pb-0.5 sm:pb-0">
                <span class="inline-flex items-center gap-1 shrink-0"><span class="w-2 h-2 rounded-full bg-[#EF4444]"></span> Verifikasi</span>
                <span class="inline-flex items-center gap-1 shrink-0"><span class="w-2 h-2 rounded-full bg-[#2563EB]"></span> Ditugaskan</span>
                <span class="inline-flex items-center gap-1 shrink-0"><span class="w-2 h-2 rounded-full bg-[#F59E0B]"></span> Dikerjakan / Validasi</span>
                <span class="inline-flex items-center gap-1 shrink-0"><span class="w-2 h-2 rounded-full bg-[#10B981]"></span> Selesai</span>
                <span class="inline-flex items-center gap-1 shrink-0"><span class="w-2 h-2 rounded-full bg-[#DC2626]"></span> Ditolak</span>
            </div>
        </div>

        <div class="relative w-full flex-1 min-h-[460px] xl:min-h-[580px] rounded-2xl overflow-hidden border border-[#CBD5E1] bg-slate-100">
            <div id="{{ $mapId }}" class="w-full h-full z-0"></div>
        </div>
    </div>

    <!-- Kolom Kanan: Direktori 15 Kecamatan -->
    <div class="xl:col-span-5 rounded-[20px] p-5 bg-white border border-[#E2E8F0] flex flex-col gap-3.5 h-full">
        <div class="flex items-center justify-between gap-2">
            <h3 class="font-outfit font-bold text-[15px] text-[#0F172A]">
                Direktori 15 Kecamatan
            </h3>
            <span class="text-[11px] font-semibold text-[#64748B]">
                {{ count($districtStats) }} Wilayah
            </span>
        </div>

        <!-- Search & Filter Chips -->
        <div class="space-y-2.5">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#94A3B8]">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </div>
                <input type="text" id="{{ $prefix }}RegionSearch" oninput="filterRoleRegions('{{ $prefix }}')"
                    placeholder="Cari nama kecamatan atau kelurahan/desa..."
                    class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] text-[#0F172A] placeholder:text-[#94A3B8] focus:bg-white focus:border-[#2563EB] focus:ring-2 focus:ring-[#2563EB]/15 focus:outline-none transition">
            </div>

            <div class="flex items-center gap-1.5 flex-wrap">
                <button type="button" onclick="setRoleRegionFilter('{{ $prefix }}', 'all', this)" class="{{ $prefix }}-region-filter-btn px-3 py-1 rounded-full text-[11px] font-bold bg-[#2563EB] text-white transition cursor-pointer">
                    Semua ({{ count($districtStats) }})
                </button>
                <button type="button" onclick="setRoleRegionFilter('{{ $prefix }}', 'active', this)" class="{{ $prefix }}-region-filter-btn px-3 py-1 rounded-full text-[11px] font-semibold bg-[#F8FAFC] text-[#475569] border border-[#E2E8F0] hover:bg-slate-100 transition cursor-pointer">
                    Ada Aduan ({{ $activeDistrictCount }})
                </button>
                <button type="button" onclick="setRoleRegionFilter('{{ $prefix }}', 'safe', this)" class="{{ $prefix }}-region-filter-btn px-3 py-1 rounded-full text-[11px] font-semibold bg-[#F8FAFC] text-[#475569] border border-[#E2E8F0] hover:bg-slate-100 transition cursor-pointer">
                    Aman ({{ $safeDistrictCount }})
                </button>
            </div>
        </div>

        <!-- Scrollable District List -->
        <div class="flex-1 max-h-[490px] overflow-y-auto pr-1 space-y-2.5">
            @foreach($districtStats as $ds)
                @php $isLasusua = str_contains(strtolower($ds['district']), 'lasusua'); @endphp
                <div data-name="{{ strtolower($ds['district'] . ' ' . $ds['village']) }}"
                     data-total="{{ $ds['total'] }}"
                     class="{{ $prefix }}-region-item p-3.5 rounded-[14px] {{ $isLasusua ? 'bg-[#EFF6FF]/60 border-[#BFDBFE]' : 'bg-white border-[#E2E8F0]' }} border hover:border-[#2563EB] transition space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="font-outfit font-bold text-[13px] text-[#0F172A] truncate">{{ $ds['district'] }}</span>
                                @if($isLasusua)
                                    <span class="px-1.5 py-0.5 rounded bg-white border border-blue-200 text-[#2563EB] text-[9px] font-bold uppercase">Ibukota</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-[#64748B] flex items-center gap-1.5 mt-0.5 flex-wrap">
                                <span>Pusat: {{ $ds['village'] }}</span>
                                <span>&bull;</span>
                                <span class="font-mono text-[10px] font-semibold text-[#475569]">
                                    {{ number_format($ds['latitude'], 4) }}, {{ number_format($ds['longitude'], 4) }}
                                </span>
                            </div>
                        </div>
                        @if($ds['total'] > 0)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#2563EB] text-white shrink-0 whitespace-nowrap">
                                {{ $ds['total'] }} Aduan
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#F1F5F9] text-[#64748B] shrink-0 whitespace-nowrap">
                                0 Aduan
                            </span>
                        @endif
                    </div>

                    @if($ds['total'] > 0)
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @if(($ds['pending'] ?? 0) > 0)
                                <span class="px-2 py-0.5 rounded-md bg-red-50 text-red-700 border border-red-200 text-[10px] font-bold">{{ $ds['pending'] }} Verifikasi</span>
                            @endif
                            @if(($ds['dispatched'] ?? 0) > 0)
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-[#2563EB] border border-blue-200 text-[10px] font-bold">{{ $ds['dispatched'] }} Ditugaskan</span>
                            @endif
                            @if(($ds['in_progress'] ?? 0) > 0)
                                <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">{{ $ds['in_progress'] }} Dikerjakan</span>
                            @endif
                            @if(($ds['waiting_verification'] ?? 0) > 0)
                                <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">{{ $ds['waiting_verification'] }} Validasi</span>
                            @endif
                            @if(($ds['completed'] ?? 0) > 0)
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">{{ $ds['completed'] }} Selesai</span>
                            @endif
                            @if(($ds['rejected'] ?? 0) > 0)
                                <span class="px-2 py-0.5 rounded-md bg-red-50 text-red-700 border border-red-200 text-[10px] font-bold">{{ $ds['rejected'] }} Ditolak</span>
                            @endif
                        </div>
                    @endif

                    <div class="pt-1 flex items-center gap-3 text-[11px] font-bold">
                        <button type="button" onclick="{{ $focusFn }}({{ $ds['latitude'] }}, {{ $ds['longitude'] }}, '{{ $ds['district'] }}')"
                            class="text-[#2563EB] hover:underline inline-flex items-center gap-1 cursor-pointer">
                            <i data-lucide="crosshair" class="w-3 h-3"></i>
                            <span>Fokus Peta</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-2 border-t border-[#E2E8F0] text-[11px] font-medium text-[#64748B]">
            Menampilkan <span id="{{ $prefix }}RegionVisibleCount" class="font-bold text-[#0F172A]">{{ count($districtStats) }}</span> dari {{ count($districtStats) }} kecamatan &bull; Gulir untuk melihat lainnya
        </div>
    </div>

</div>

<script>
    window.__roleRegionFilters = window.__roleRegionFilters || {};
    function setRoleRegionFilter(prefix, mode, btn) {
        window.__roleRegionFilters[prefix] = mode;
        document.querySelectorAll(`.${prefix}-region-filter-btn`).forEach(b => {
            b.className = `${prefix}-region-filter-btn px-3 py-1 rounded-full text-[11px] font-semibold bg-[#F8FAFC] text-[#475569] border border-[#E2E8F0] hover:bg-slate-100 transition cursor-pointer`;
        });
        if (btn) {
            btn.className = `${prefix}-region-filter-btn px-3 py-1 rounded-full text-[11px] font-bold bg-[#2563EB] text-white transition cursor-pointer`;
        }
        filterRoleRegions(prefix);
    }
    function filterRoleRegions(prefix) {
        const mode = window.__roleRegionFilters[prefix] || 'all';
        const q = (document.getElementById(`${prefix}RegionSearch`)?.value || '').toLowerCase().trim();
        let visible = 0;
        document.querySelectorAll(`.${prefix}-region-item`).forEach(el => {
            const name = el.getAttribute('data-name') || '';
            const total = parseInt(el.getAttribute('data-total') || '0', 10);
            const matchQuery = !q || name.includes(q);
            const matchFilter = mode === 'all'
                || (mode === 'active' && total > 0)
                || (mode === 'safe' && total === 0);
            if (matchQuery && matchFilter) {
                el.classList.remove('hidden');
                visible++;
            } else {
                el.classList.add('hidden');
            }
        });
        const countEl = document.getElementById(`${prefix}RegionVisibleCount`);
        if (countEl) countEl.textContent = visible;
    }
</script>
