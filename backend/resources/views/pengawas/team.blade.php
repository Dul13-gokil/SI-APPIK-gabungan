@extends('layouts.app')

@section('title', 'Kelola Anggota Regu - SI-APPIK')
@section('header_title', 'Kelola Anggota Regu')

@section('content')
<div class="py-4 sm:py-6 space-y-5 max-w-4xl">
    <div>
        <h2 class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A]">Anggota Regu</h2>
        <p class="text-[13px] text-[#475569] mt-1">
            Pengawas hanya dapat mengelola anggota regu tempat ia terdaftar.
            @if($team)
                Regu Anda: <b>{{ $team->name }}</b> ({{ $members->count() }}/15 personil).
            @endif
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-[13px] text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-[13px] text-red-700">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-[13px] text-red-700">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if(! $team)
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-8 text-center text-[13px] text-[#475569]">
            Akun Anda belum terdaftar pada regu mana pun. Hubungi Operator untuk penempatan regu.
        </div>
    @else
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 space-y-4 shadow-sm">
            <h3 class="font-outfit font-bold text-sm text-[#0F172A]">Daftar Anggota</h3>

            @if($members->isEmpty())
                <p class="text-[13px] text-[#475569]">Belum ada anggota pada regu ini.</p>
            @else
                <div class="divide-y divide-[#E2E8F0]">
                    @foreach($members as $member)
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <div class="min-w-0">
                                <span class="block text-[13px] font-semibold text-[#0F172A] truncate">{{ $member->name }}</span>
                                <span class="block text-[11px] text-[#475569] truncate">
                                    {{ $member->email }}
                                    @if((int) $team->leader_id === (int) $member->id)
                                        &middot; Ketua Regu
                                    @endif
                                </span>
                            </div>
                            @if((int) $member->id !== (int) auth()->id())
                                <form method="POST" action="{{ route('pengawas.team.members.remove', $member->id) }}"
                                      onsubmit="return confirm('Keluarkan {{ addslashes($member->name) }} dari regu ini?')">
                                    @csrf
                                    <button type="submit"
                                            class="px-3 py-1.5 rounded-lg bg-white hover:bg-red-50 text-red-600 border border-red-200 text-[11px] font-semibold">
                                        Keluarkan
                                    </button>
                                </form>
                            @else
                                <span class="text-[11px] text-[#64748B] italic">Anda</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 space-y-4 shadow-sm">
            <h3 class="font-outfit font-bold text-sm text-[#0F172A]">Tambah Anggota</h3>
            <p class="text-[12px] text-[#475569]">
                Hanya pekerja lapangan yang belum tergabung di regu mana pun yang dapat ditambahkan.
                Akun pekerja lapangan baru dibuat oleh Operator.
            </p>

            @if($candidates->isEmpty())
                <p class="text-[13px] text-[#475569]">Tidak ada pekerja lapangan yang belum masuk regu.</p>
            @else
                <form method="POST" action="{{ route('pengawas.team.members.add') }}" class="flex flex-col sm:flex-row gap-2">
                    @csrf
                    <select name="user_id" required class="flex-1 rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]">
                        <option value="">Pilih pekerja lapangan</option>
                        @foreach($candidates as $candidate)
                            <option value="{{ $candidate->id }}">{{ $candidate->name }} &mdash; {{ $candidate->email }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-[13px] font-semibold whitespace-nowrap">
                        Tambahkan
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
@endsection
