@extends('layouts.app')

@section('title', 'Tambah Pekerja Lapangan - SI-APPIK')
@section('header_title', 'Tambah Pekerja Lapangan')

@section('content')
<div class="py-4 sm:py-6 max-w-2xl space-y-5">
    <div>
        <h2 class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A]">Akun Pekerja Lapangan Baru</h2>
        <p class="text-[13px] text-[#475569] mt-1">
            Operator boleh menambahkan akun pekerja lapangan. Sandi awal otomatis <b>password</b> dan wajib diganti saat login pertama.
        </p>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-[13px] text-red-700">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('operator.petugas.store') }}"
          class="rounded-2xl border border-[#E2E8F0] bg-white p-5 space-y-4 shadow-sm">
        @csrf
        <div>
            <label class="block text-[12px] font-semibold text-[#0F172A] mb-1">Nama Lengkap *</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]" />
        </div>
        <div>
            <label class="block text-[12px] font-semibold text-[#0F172A] mb-1">Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]" />
        </div>
        <div>
            <label class="block text-[12px] font-semibold text-[#0F172A] mb-1">Nomor HP / WhatsApp</label>
            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx"
                   class="w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]" />
        </div>
        <div>
            <label class="block text-[12px] font-semibold text-[#0F172A] mb-1">Regu (opsional)</label>
            <select name="team_id" class="w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]">
                <option value="">- Belum masuk regu -</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-3 py-2.5 text-[12px] text-[#475569] leading-relaxed">
            Sandi sementara dibuat otomatis dan ditampilkan sekali setelah akun disimpan.
            Catat lalu berikan ke petugas; ia wajib menggantinya saat login pertama.
        </div>
        <div class="flex items-center gap-2 pt-1">
            <button type="submit"
                    class="rounded-xl bg-[#1E3A8A] px-4 py-2 text-[13px] font-semibold text-white hover:bg-[#172554]">
                Simpan Akun
            </button>
            <a href="{{ route('operator.dashboard') }}"
               class="rounded-xl border border-[#E2E8F0] px-4 py-2 text-[13px] font-semibold text-[#475569]">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
