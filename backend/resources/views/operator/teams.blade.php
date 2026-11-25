@extends('layouts.app')

@section('title', 'Regu & Anggota — Operator PUPR')

@section('content')
<div class="max-w-6xl mx-auto space-y-5">

    <div>
        <h1 class="font-outfit text-2xl font-bold text-[#0F172A]">Regu & Anggota</h1>
        <p class="text-[13px] text-[#475569] mt-1">
            Bentuk regu dan isi identitas personilnya di sini. Akun personil dibuat otomatis
            dengan sandi sementara yang ditampilkan sekali.
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[13px] text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Bentuk regu baru --}}
    <form method="POST" action="{{ route('operator.teams.store') }}"
          class="bg-white border border-[#E2E8F0] rounded-xl p-4 space-y-3">
        @csrf
        <h2 class="font-outfit text-sm font-bold text-[#0F172A]">Bentuk Regu Baru</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[12px] font-semibold text-[#0F172A] mb-1">Nama Regu *</label>
                <input type="text" name="name" required placeholder="Regu Aspal 03 - Lasusua"
                       class="w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]">
            </div>
            <div>
                <label class="block text-[12px] font-semibold text-[#0F172A] mb-1">Kode</label>
                <input type="text" name="code" placeholder="otomatis bila kosong"
                       class="w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]">
            </div>
            <div>
                <label class="block text-[12px] font-semibold text-[#0F172A] mb-1">Wilayah Kerja</label>
                <input type="text" name="district" placeholder="Lasusua"
                       class="w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]">
            </div>
        </div>
        <button type="submit" class="rounded-xl bg-[#1E3A8A] px-4 py-2 text-[13px] font-semibold text-white hover:bg-[#172554]">
            Simpan Regu
        </button>
    </form>

    {{-- Daftar regu --}}
    @forelse($teams as $team)
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-4 space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b border-[#E2E8F0]">
                <div>
                    <div class="font-outfit text-base font-bold text-[#0F172A]">
                        {{ $team->name }}
                        <span class="ml-2 px-2 py-0.5 rounded-md bg-[#F8FAFC] border border-[#E2E8F0] text-[11px] font-mono text-[#475569]">{{ $team->code }}</span>
                    </div>
                    <div class="text-[12px] text-[#475569] mt-1">
                        Ketua: {{ $team->leader?->name ?? 'belum ditunjuk' }} &middot;
                        {{ $team->members_count }} / 15 personil &middot;
                        Wilayah: {{ $team->district ?? 'semua wilayah' }}
                    </div>
                </div>
            </div>

            {{-- Anggota --}}
            <div class="space-y-2">
                @forelse($team->members as $anggota)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-[#E2E8F0] px-3 py-2">
                        <div class="min-w-0">
                            <div class="text-[13px] font-semibold text-[#0F172A] truncate">
                                {{ $anggota->user?->name ?? 'Akun terhapus' }}
                                @if($anggota->role === 'LEADER')
                                    <span class="ml-1.5 px-1.5 py-0.5 rounded bg-[#EFF6FF] text-[#2563EB] text-[10px] font-bold">KETUA</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-[#64748B] truncate">{{ $anggota->user?->email ?? '-' }}</div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($anggota->role !== 'LEADER' && $anggota->user)
                                <form method="POST" action="{{ route('operator.teams.leader', [$team->id, $anggota->user_id]) }}">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-semibold text-[#2563EB] hover:underline">Jadikan Ketua</button>
                                </form>
                            @endif
                            @if($anggota->user)
                                <form method="POST" action="{{ route('operator.teams.members.remove', [$team->id, $anggota->user_id]) }}"
                                      onsubmit="return confirm('Keluarkan {{ $anggota->user->name }} dari regu ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-[11px] font-semibold text-red-600 hover:underline">Keluarkan</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-[12px] text-[#64748B]">Belum ada personil di regu ini.</p>
                @endforelse
            </div>

            {{-- Tambah personil dengan identitas --}}
            @if($team->members_count < 15)
                <form method="POST" action="{{ route('operator.teams.members.create', $team->id) }}"
                      class="rounded-lg border border-dashed border-[#CBD5E1] p-3 space-y-2">
                    @csrf
                    <div class="text-[12px] font-semibold text-[#0F172A]">Tambah Personil (identitas baru)</div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                        <input type="text" name="name" required placeholder="Nama lengkap"
                               class="rounded-lg border border-[#E2E8F0] px-2.5 py-1.5 text-[12px]">
                        <input type="email" name="email" required placeholder="Email akun"
                               class="rounded-lg border border-[#E2E8F0] px-2.5 py-1.5 text-[12px]">
                        <input type="text" name="phone" placeholder="08xxxxxxxxxx"
                               class="rounded-lg border border-[#E2E8F0] px-2.5 py-1.5 text-[12px]">
                        <select name="role" class="rounded-lg border border-[#E2E8F0] px-2.5 py-1.5 text-[12px]">
                            <option value="MEMBER">Anggota</option>
                            <option value="LEADER">Ketua Regu</option>
                        </select>
                    </div>
                    <button type="submit" class="rounded-lg bg-[#2563EB] px-3 py-1.5 text-[12px] font-semibold text-white hover:bg-[#1D4ED8]">
                        Tambah Personil
                    </button>
                </form>
            @else
                <p class="text-[12px] text-amber-700">Regu penuh (15 personil).</p>
            @endif
        </div>
    @empty
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-8 text-center text-[13px] text-[#64748B]">
            Belum ada regu. Bentuk regu pertama di form di atas.
        </div>
    @endforelse
</div>
@endsection