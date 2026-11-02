<!-- Modal Detail Pengaduan Terpadu (Shared Component) -->
<div id="complaintDetailModal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center p-3 sm:p-5 bg-slate-900/75 transition-opacity duration-150 overscroll-contain">
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95" style="transform: translateZ(0); will-change: transform;">
        
        <!-- Modal Top Header (Selaras 1 Baris: Kerusakan -> Kategori -> Status) -->
        <div class="px-4 sm:px-5 py-3 sm:py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 gap-2">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-nowrap min-w-0 overflow-x-auto no-scrollbar py-0.5">
                <!-- 1. Tingkat Kerusakan -->
                <span id="sharedModalTypeBadge" class="shrink-0 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1 sm:gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                    <span>Kerusakan Ringan</span>
                </span>
                <!-- 2. Kategori Infrastruktur -->
                <span id="sharedModalCategoryBadge" class="shrink-0 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-slate-100 text-slate-700 font-semibold text-[10.5px] sm:text-xs border border-slate-200 inline-flex items-center whitespace-nowrap">
                    Infrastruktur
                </span>
                <!-- 3. Status Pengerjaan -->
                <span id="sharedModalStatusBadge" class="shrink-0 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold whitespace-nowrap border">
                    STATUS
                </span>
            </div>
            <button type="button" onclick="closeSharedDetailModal()" class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-300 text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0 ml-1" title="Tutup Detail">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Scrollable Body -->
        <div class="p-4 sm:p-5 overflow-y-auto space-y-4 flex-1 overscroll-contain" style="-webkit-overflow-scrolling: touch;">
            
            <!-- 1. Foto Bukti Kerusakan Awal (Warga) -->
            <div id="sharedModalPhotoContainer" class="space-y-2">
                <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 relative max-h-64 sm:max-h-72 flex items-center justify-center shadow-xs">
                    <img id="sharedModalPhoto" src="" alt="Foto Kerusakan" class="w-full h-full object-cover max-h-64 sm:max-h-72">
                    <div class="absolute bottom-2.5 left-2.5 bg-black/75 backdrop-blur-xs text-white text-[10.5px] font-medium px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-xs">
                        <i data-lucide="camera" class="w-3.5 h-3.5 text-white/90"></i>
                        <span id="sharedModalPhotoCountLabel">Foto Bukti Kerusakan</span>
                    </div>
                </div>
                <!-- Strip Thumbnail Galeri Warga -->
                <div id="sharedModalCitizenGallery" class="flex items-center gap-2 overflow-x-auto pb-1 hidden"></div>
            </div>

            <!-- 2. Foto Bukti Hasil Pengerjaan Fisik (Petugas Lapangan) -->
            <div id="sharedModalCompletionPhotoContainer" class="space-y-2 hidden">
                <div class="rounded-2xl overflow-hidden border border-emerald-300 bg-emerald-50/50 relative max-h-64 sm:max-h-72 flex items-center justify-center shadow-xs">
                    <img id="sharedModalCompletionPhoto" src="" alt="Foto Hasil Pengerjaan" class="w-full h-full object-cover max-h-64 sm:max-h-72">
                    <div class="absolute bottom-2.5 left-2.5 bg-emerald-950/90 backdrop-blur-xs text-white text-[10.5px] font-bold px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-md">
                        <i data-lucide="badge-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span id="sharedModalCompletionPhotoLabel">Bukti Penyelesaian Lapangan</span>
                    </div>
                </div>
                <!-- Strip Thumbnail Galeri Petugas -->
                <div id="sharedModalOfficerGallery" class="flex items-center gap-2 overflow-x-auto pb-1 hidden"></div>
            </div>

            <!-- 3. Judul & Info Waktu Laporan -->
            <div>
                <span id="sharedModalCode" class="text-[11px] font-mono font-medium text-slate-400 block tracking-wide mb-0.5">#ADU-0000</span>
                <h3 id="sharedModalTitle" class="font-outfit font-extrabold text-lg sm:text-xl text-slate-900 leading-snug">
                    Judul Pengaduan
                </h3>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 pt-0.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span id="sharedModalDate">01 Jan 2026, 00:00 WITA</span>
                </div>
            </div>

            <!-- 4. Identitas Pelapor & Regu Lapangan Pelaksana -->
            <div class="space-y-3">
                <!-- Identitas Pelapor Aduan -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-blue-100 text-[#1E3A8A] flex items-center justify-center font-bold text-xs shrink-0">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-slate-400 block font-bold uppercase tracking-wider">PELAPOR ADUAN</span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span id="sharedModalReporter" class="font-outfit font-extrabold text-xs sm:text-sm text-slate-900 leading-tight truncate">
                                    Warga Terdaftar
                                </span>
                                <span id="sharedModalAnonymousBadge" class="hidden text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-600">Anonim</span>
                            </div>
                        </div>
                    </div>
                    <div id="sharedModalReporterPhoneWrap">
                        <!-- Tampilan Telepon: Terbuka untuk staf internal, Disamarkan/Privasi untuk Publik -->
                        <span id="sharedModalPrivacyBadge" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-slate-200/70 text-slate-500 text-[10.5px] font-semibold shrink-0">
                            <span>Privasi Dilindungi</span>
                        </span>
                        <a id="sharedModalReporterPhoneLink" href="#" class="hidden items-center gap-1.5 text-xs text-[#1E3A8A] font-bold font-mono hover:underline shrink-0">
                            <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                            <span id="sharedModalReporterPhoneText">-</span>
                        </a>
                    </div>
                </div>

                <!-- Kartu Regu Lapangan Pelaksana -->
                <div id="sharedModalOfficerCard" class="hidden p-3.5 rounded-2xl bg-blue-50/50 border border-blue-200/80 space-y-2.5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-xl bg-[#1E3A8A] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs mt-0.5">
                                <i data-lucide="hard-hat" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] text-[#1E3A8A] font-extrabold uppercase tracking-wider block">REGU LAPANGAN PELAKSANA</span>
                                <h4 id="sharedModalOfficerName" class="font-outfit font-extrabold text-xs sm:text-sm text-slate-900 truncate leading-tight">
                                    Regu Aspal 01 - Lasusua
                                </h4>
                                <span id="sharedModalOfficerDept" class="text-[10.5px] font-semibold text-slate-500 block truncate">
                                    Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
                                </span>
                            </div>
                        </div>
                        <div id="sharedModalDeadlineBox" class="text-right shrink-0">
                            <span class="text-[9.5px] text-slate-400 font-bold block">Batas Waktu:</span>
                            <span id="sharedModalOfficerDeadline" class="text-xs font-bold text-[#1E3A8A] bg-white px-2 py-0.5 rounded-lg border border-blue-200 inline-block mt-0.5 shadow-2xs">
                                -
                            </span>
                        </div>
                    </div>

                    <!-- Kontak Ketua Regu (Koordinator Lapangan) -->
                    <div class="p-2.5 rounded-xl bg-white border border-slate-200/80 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-6 h-6 rounded-lg bg-blue-50 text-[#1E3A8A] flex items-center justify-center shrink-0">
                                <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="text-[10px] text-slate-400 font-bold block leading-none">Ketua Regu (Koordinator Lapangan):</span>
                                <span id="sharedModalTeamLeader" class="font-bold text-slate-900 truncate block mt-0.5 text-xs">Petugas Lapangan</span>
                            </div>
                        </div>
                        <a id="sharedModalOfficerPhone" href="#" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#1E3A8A] font-bold font-mono text-xs flex items-center gap-1.5 transition shrink-0">
                            <i data-lucide="phone-call" class="w-3 h-3"></i>
                            <span id="sharedModalOfficerPhoneText">081100000000</span>
                        </a>
                    </div>

                    <!-- Instruksi Teknis Pengerjaan -->
                    <div id="sharedModalInstructionRow" class="pt-1 text-[11px] text-slate-600 flex items-start gap-1.5 border-t border-blue-200/40">
                        <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0 mt-0.5"></i>
                        <span>Instruksi: <em id="sharedModalOfficerInstruction" class="text-slate-800 font-medium">Lakukan penanganan fisik sesuai SOP dinas terkait</em></span>
                    </div>
                </div>
            </div>

            <!-- 5. Lokasi Kerusakan & GIS Spasial -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span>Lokasi Kerusakan</span>
                    </span>
                    <span id="sharedModalCoords" class="font-mono text-[11px] font-bold text-[#1E3A8A] bg-white px-2 py-0.5 rounded-md border border-slate-200 shadow-2xs">
                        -
                    </span>
                </div>
                <p id="sharedModalFullAddress" class="text-xs font-semibold text-slate-800 leading-relaxed">
                    Alamat lokasi kerusakan...
                </p>
            </div>

            <!-- 6. Spesifikasi Ruas Jalan Rusak Panjang (> 500 Meter / Parah) -->
            <div id="sharedModalMajorRoadBox" class="hidden rounded-2xl p-4 bg-[#1E3A8A]/5 border border-[#1E3A8A]/20 text-[#1E3A8A] space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="route" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span class="text-xs font-bold uppercase tracking-wider">Kerusakan Ruas Panjang</span>
                    </div>
                    <span id="sharedModalRoadLengthBadge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#1E3A8A] text-white">Major</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                    <div class="p-2.5 rounded-xl bg-white border border-[#1E3A8A]/15">
                        <span class="text-[10px] text-slate-500 block">Kondisi Kerusakan:</span>
                        <span id="sharedModalRoadCondition" class="font-bold text-slate-800">-</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-white border border-[#1E3A8A]/15">
                        <span class="text-[10px] text-slate-500 block">Estimasi Panjang:</span>
                        <span id="sharedModalRoadLength" class="font-bold text-slate-800">-</span>
                    </div>
                </div>
            </div>

            <!-- 7. Deskripsi Lengkap Kerusakan -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-4 h-4 text-slate-400"></i>
                    <span>Deskripsi Kondisi Kerusakan</span>
                </h4>
                <div id="sharedModalDescription" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-700 leading-relaxed whitespace-pre-line font-sans">
                    Kondisi kerusakan infrastruktur...
                </div>
            </div>

        </div>

        <!-- Modal Footer Actions (Dinamis Berdasarkan Role) -->
        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/80 flex items-center justify-end gap-2">
            @if(($role ?? 'public') === 'petugas')
                <a id="sharedModalNavBtn" href="#" target="_blank" class="px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                    <i data-lucide="navigation" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                    <span>Rute Google Maps</span>
                </a>
            @endif

            <button type="button" onclick="closeSharedDetailModal()" class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-[#172e6e] text-white font-bold text-xs shadow-md shadow-[#1E3A8A]/20 hover:shadow-lg transition flex items-center gap-1.5 cursor-pointer">
                <span>Tutup Rincian</span>
            </button>
        </div>

    </div>
</div>
