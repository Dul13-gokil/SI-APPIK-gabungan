{{-- Tabel Daftar Pengaduan Terpadu (Seragam Admin: KODE | JUDUL & LOKASI | TIPE | KATEGORI | PELAPOR | STATUS | PETUGAS | AKSI) --}}
@php
    $isPetugas = str_starts_with($mode ?? '', 'petugas-');
    $detailFn = $isPetugas ? 'openPetugasDetailModal' : 'openOperatorModal';
    $hasAction = in_array($mode ?? '', ['operator-verify', 'operator-dispatched', 'operator-validation', 'petugas-assigned', 'petugas-tasks']);
@endphp
<div id="{{ $wrapId }}" class="rounded-xl border border-[#E2E8F0] overflow-hidden md:overflow-x-auto bg-white {{ !empty($hidden) ? 'hidden' : '' }}">
    <table class="block md:table w-full text-left text-xs text-slate-600">
        <thead class="hidden md:table-header-group bg-[#F8FAFC] text-[#475569] font-bold border-b border-[#E2E8F0] uppercase tracking-wider text-[11px]">
            <tr>
                <th class="py-3 px-3 w-28 whitespace-nowrap">Kode</th>
                <th class="py-3 px-3">Judul &amp; Lokasi</th>
                <th class="py-3 px-3 w-24 whitespace-nowrap">Tipe</th>
                <th class="py-3 px-3 w-32 whitespace-nowrap">Kategori</th>
                <th class="py-3 px-3 w-32 whitespace-nowrap">Pelapor</th>
                <th class="py-3 px-3 w-28 text-center whitespace-nowrap">Status</th>
                <th class="py-3 px-3 w-32 whitespace-nowrap">Petugas</th>
                @if($hasAction)
                    <th class="py-3 px-3 text-right whitespace-nowrap">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody class="block md:table-row-group divide-y divide-[#E2E8F0]">
            @forelse($items as $item)
                @php
                    $firstPhoto = $item->photos->first()
                        ? ($item->photos->first()->url ?? asset('storage/' . $item->photos->first()->storage_key))
                        : null;
                    $stVal = $item->status?->value ?? $item->status;
                    $stLabel = match($stVal) {
                        'COMPLETED', 'CLOSED' => 'SELESAI',
                        'WAITING_VERIFICATION' => 'VALIDASI',
                        'IN_PROGRESS' => 'DIKERJAKAN',
                        'DISPATCHED' => 'DITUGASKAN',
                        'REJECTED' => 'DITOLAK',
                        'REVISION_REQUIRED' => 'PERLU REVISI',
                        default => 'VERIFIKASI',
                    };
                    $stClass = match($stVal) {
                        'COMPLETED', 'CLOSED' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                        'WAITING_VERIFICATION' => 'bg-amber-50 text-amber-700 border-amber-300',
                        'IN_PROGRESS', 'REVISION_REQUIRED' => 'bg-amber-50 text-amber-800 border-amber-300',
                        'DISPATCHED' => 'bg-blue-50 text-[#2563EB] border-blue-200',
                        default => 'bg-red-50 text-red-700 border-red-200',
                    };
                    $officerName = $item->latestDisposition?->team?->name
                        ?? $item->latestDisposition?->officer?->name
                        ?? 'Belum diambil';
                    $searchStr = strtolower(($item->public_code ?? '') . ' ' . ($item->title ?? '') . ' ' . ($item->address ?? '') . ' ' . ($item->citizen?->name ?? ''));
                @endphp
                <tr onclick="{{ $detailFn }}({{ $item->id }})" class="{{ $rowClass }} flex flex-row items-center justify-between md:table-row gap-2.5 px-3.5 py-3 md:p-0 w-full hover:bg-slate-50/80 transition cursor-pointer"
                    data-category="{{ $item->category_id ?? '' }}"
                    data-damage-type="{{ $item->damage_type ?? 'minor' }}"
                    data-search="{{ $searchStr }}"
                    data-status="{{ $stVal }}">
                    <td class="hidden md:table-cell py-3 px-3 align-middle whitespace-nowrap">
                        <button type="button" onclick="event.stopPropagation(); {{ $detailFn }}({{ $item->id }})" class="font-mono font-bold text-[#2563EB] hover:underline cursor-pointer">
                            #{{ $item->public_code }}
                        </button>
                        <span class="block text-[10px] text-[#475569]">{{ $item->created_at?->diffForHumans() }}</span>
                    </td>
                    <td class="block md:table-cell p-0 md:py-3 md:px-3 align-middle flex-1 md:w-auto min-w-0">
                        <div class="flex items-center gap-2.5 min-w-0 group">
                            <div class="w-10 h-10 rounded-lg overflow-hidden border border-[#E2E8F0] bg-slate-100 shrink-0">
                                @if($firstPhoto)
                                    <img src="{{ $firstPhoto }}" alt="Foto Aduan" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 text-[9px]">-</div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="md:hidden flex items-center gap-1.5 flex-wrap mb-0.5">
                                    <span class="font-mono font-bold text-[#2563EB] text-[10px]">#{{ $item->public_code }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold border {{ $stClass }}">{{ $stLabel }}</span>
                                </div>
                                <button type="button" onclick="{{ $detailFn }}({{ $item->id }})" class="font-bold text-[#0F172A] hover:text-[#2563EB] truncate block w-full md:max-w-[240px] text-left cursor-pointer" title="{{ $item->title }}">
                                    {{ $item->title }}
                                </button>
                                <span class="text-[11px] text-[#475569] truncate block w-full md:max-w-[240px]" title="{{ $item->address }}">
                                    {{ $item->address }}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="hidden md:table-cell py-3 px-3 align-middle whitespace-nowrap">
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200">
                            {{ ($item->damage_type ?? 'minor') === 'major' ? 'Berat' : 'Ringan' }}
                        </span>
                    </td>
                    <td class="hidden md:table-cell py-3 px-3 align-middle whitespace-nowrap">
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-[#0F172A] border border-[#E2E8F0]">
                            {{ $item->category?->name ?? 'Infrastruktur' }}
                        </span>
                    </td>
                    <td class="hidden md:table-cell py-3 px-3 align-middle font-semibold text-[#0F172A] whitespace-nowrap">
                        {{ $item->citizen?->name ?? 'Warga' }}
                    </td>
                    <td class="hidden md:table-cell py-3 px-3 align-middle text-center whitespace-nowrap">
                        <span class="px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $stClass }}">
                            {{ $stLabel }}
                        </span>
                    </td>
                    <td class="hidden md:table-cell py-3 px-3 align-middle text-[#475569] whitespace-nowrap">
                        <span class="truncate block max-w-[140px]" title="{{ $officerName }}">{{ $officerName }}</span>
                    </td>
                    @if($hasAction)
                        <td onclick="event.stopPropagation();" class="block md:table-cell p-0 md:py-3 md:px-3 align-middle text-right shrink-0 md:w-auto cursor-default">
                            <div class="flex md:inline-flex items-center gap-1.5 justify-end flex-wrap">
                                @if($mode === 'operator-verify')
                                    <button type="button" onclick="openDispatchModal({{ $item->id }})"
                                        class="px-2.5 py-1.5 rounded-lg bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold text-[11px] inline-flex items-center gap-1 transition cursor-pointer">
                                        <i data-lucide="send" class="w-3 h-3"></i>
                                        <span>Verifikasi</span>
                                    </button>
                                @elseif($mode === 'operator-dispatched')
                                    @if(($dispatchMode ?? 'HYBRID') === 'SELF_CLAIM_ONLY')
                                        <span class="text-[10px] text-slate-400 italic whitespace-nowrap">Klaim Mandiri</span>
                                    @else
                                        @php $dispItem = $item->latestDisposition; @endphp
                                        <button type="button" onclick="openAssignTeamModal({{ $item->id }}, '{{ $item->public_code }}', '{{ addslashes($item->title) }}', '{{ $dispItem?->team_id ?? '' }}', '{{ addslashes($dispItem?->instruction ?? '') }}')"
                                            class="px-2.5 py-1.5 rounded-lg bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold text-[11px] inline-flex items-center gap-1 transition cursor-pointer whitespace-nowrap">
                                            <i data-lucide="user-plus" class="w-3 h-3"></i>
                                            <span>{{ $dispItem?->team_id ? 'Ubah Regu' : 'Tempatkan Regu' }}</span>
                                        </button>
                                    @endif
                                @elseif($mode === 'operator-validation')
                                    <button type="button" onclick="openValidationModal({{ $item->id }})"
                                        class="px-2.5 py-1.5 rounded-lg bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold text-[11px] inline-flex items-center gap-1 transition cursor-pointer">
                                        <i data-lucide="check-square" class="w-3 h-3"></i>
                                        <span>Validasi</span>
                                    </button>
                                @elseif($mode === 'petugas-assigned')
                                    <form method="POST" action="{{ route('petugas.complaints.start', $item->id) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="px-2.5 py-1.5 rounded-lg bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold text-[11px] inline-flex items-center gap-1 transition cursor-pointer">
                                            <i data-lucide="play" class="w-3 h-3"></i>
                                            <span>Mulai</span>
                                        </button>
                                    </form>
                                @elseif($mode === 'petugas-tasks')
                                    <button type="button" onclick="openPetugasFinishModal({{ $item->id }}, '{{ $item->public_code }}', '{{ addslashes($item->title) }}')"
                                        class="px-2.5 py-1.5 rounded-lg bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold text-[11px] inline-flex items-center gap-1 transition cursor-pointer">
                                        <i data-lucide="upload-cloud" class="w-3 h-3"></i>
                                        <span>Selesai</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $hasAction ? 8 : 7 }}" class="py-8 text-center text-[#475569] text-xs">
                        Belum ada data pengaduan pada tab ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
