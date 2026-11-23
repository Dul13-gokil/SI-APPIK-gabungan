@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem - SI-APPIK')
@section('header_title', 'Log Aktivitas Sistem')

@section('content')
<div class="py-4 sm:py-6 space-y-4">
    <div>
        <h2 class="font-outfit font-bold text-lg sm:text-xl text-[#0F172A]">Log Aktivitas</h2>
        <p class="text-[13px] text-[#475569] mt-1">
            Jejak audit seluruh perubahan status, penugasan, akun, dan penolakan. Hanya Super Admin.
        </p>
    </div>

    <form method="GET" action="{{ route('admin.activity') }}"
          class="grid grid-cols-1 sm:grid-cols-5 gap-2 rounded-2xl border border-[#E2E8F0] bg-white p-3">
        <input type="text" name="action" value="{{ request('action') }}" placeholder="Aksi (mis. VERIFY)"
               class="rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]" />
        <input type="text" name="actor" value="{{ request('actor') }}" placeholder="Nama aktor"
               class="rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]" />
        <input type="date" name="from" value="{{ request('from') }}"
               class="rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]" />
        <input type="date" name="to" value="{{ request('to') }}"
               class="rounded-xl border border-[#E2E8F0] px-3 py-2 text-[13px]" />
        <button type="submit"
                class="rounded-xl bg-[#1E3A8A] px-4 py-2 text-[13px] font-semibold text-white hover:bg-[#172554]">
            Saring
        </button>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-[#E2E8F0] bg-white">
        <table class="min-w-full text-[12.5px]">
            <thead class="bg-[#F1F5F9] text-[#475569]">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold">Waktu</th>
                    <th class="px-3 py-2 text-left font-semibold">Aktor</th>
                    <th class="px-3 py-2 text-left font-semibold">Aksi</th>
                    <th class="px-3 py-2 text-left font-semibold">Objek</th>
                    <th class="px-3 py-2 text-left font-semibold">Perubahan</th>
                    <th class="px-3 py-2 text-left font-semibold">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="border-t border-[#E2E8F0]">
                        <td class="px-3 py-2 whitespace-nowrap text-[#475569]">
                            {{ $log->created_at?->timezone('Asia/Makassar')->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-3 py-2">{{ $log->actor?->name ?? 'Sistem' }}</td>
                        <td class="px-3 py-2 font-semibold text-[#0F172A]">{{ $log->action }}</td>
                        <td class="px-3 py-2 text-[#475569]">
                            {{ class_basename($log->entity_type) }} #{{ $log->entity_id }}
                        </td>
                        <td class="px-3 py-2 text-[#475569] max-w-[380px]">
                            <span class="text-[11px]">{{ \Illuminate\Support\Str::limit(json_encode($log->after_json, JSON_UNESCAPED_UNICODE), 160) }}</span>
                        </td>
                        <td class="px-3 py-2 text-[#94A3B8]">{{ $log->ip ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-[#94A3B8]">Belum ada aktivitas tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $logs->links() }}</div>
</div>
@endsection
