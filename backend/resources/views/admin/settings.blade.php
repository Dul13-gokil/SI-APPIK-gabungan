@extends('layouts.app')

@section('title', 'Pengaturan Sistem - SI-APPIK')
@section('header_title', 'Pengaturan Sistem')

@section('content')
<div class="py-4 sm:py-6 max-w-2xl space-y-5">
    <div>
        <h2 class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A]">Ambang AI &amp; Radius Foto</h2>
        <p class="text-[13px] text-[#475569] mt-1">
            Perubahan berlaku langsung tanpa deploy. Setiap perubahan tercatat di log aktivitas.
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-[13px] text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-[13px] text-red-700">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if(! empty($settings['ai_last_failure_at']))
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-[13px] text-amber-800">
            Klasifikasi AI terakhir gagal pada {{ $settings['ai_last_failure_at'] }}.
            Laporan pada periode itu masuk antrean operator tanpa penilaian AI.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}"
          class="rounded-2xl border border-[#E2E8F0] bg-white p-5 space-y-5 shadow-sm">
        @csrf

        <div>
            <label class="block text-[12px] font-semibold text-[#0F172A] mb-1">
                Ambang verifikasi otomatis (0,50 - 0,99) *
            </label>
            <input type="number" step="0.01" min="0.50" max="0.99" name="ai_verify_threshold" required
                   value="{{ old('ai_verify_threshold', $settings['ai_verify_threshold']) }}"
                   class="w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]">
            <p class="text-[11px] text-[#64748B] mt-1">
                Laporan dengan keyakinan AI di atas nilai ini langsung berstatus VERIFIED dan masuk tab "Siap Ditugaskan".
            </p>
        </div>

        <div>
            <label class="block text-[12px] font-semibold text-[#0F172A] mb-1">
                Ambang pemeriksaan hasil kerja oleh AI (0,50 - 0,99) *
            </label>
            <input type="number" step="0.01" min="0.50" max="0.99" name="ai_completion_threshold" required
                   value="{{ old('ai_completion_threshold', $settings['ai_completion_threshold']) }}"
                   class="w-full rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]">
            <p class="text-[11px] text-[#64748B] mt-1">
                Bila skor AI memenuhi nilai ini, pekerjaan langsung dinyatakan selesai (COMPLETED).
            </p>
        </div>

        <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-3.5">
            <label class="flex items-start gap-2.5 cursor-pointer">
                <input type="checkbox" name="photo_radius_require_location" value="1"
                       {{ old('photo_radius_require_location', $settings['photo_radius_require_location']) ? 'checked' : '' }}
                       class="w-4 h-4 mt-0.5 rounded border-[#CBD5E1] text-[#2563EB]">
                <span>
                    <span class="block text-[12px] font-semibold text-[#0F172A]">Wajibkan lokasi GPS saat mengirim bukti pengerjaan</span>
                    <span class="block text-[11px] text-[#64748B] mt-0.5">
                        Aktifkan setelah seluruh petugas memakai aplikasi versi terbaru. Jika aktif, bukti tanpa lokasi ditolak.
                    </span>
                </span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-[#E2E8F0]">
            <a href="{{ route('admin.dashboard') }}"
               class="px-4 py-2 rounded-xl bg-[#F1F5F9] hover:bg-slate-200 text-[#334155] text-[13px] font-semibold">
                Batal
            </a>
            <button type="submit"
                    class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-[13px] font-semibold">
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
@endsection
