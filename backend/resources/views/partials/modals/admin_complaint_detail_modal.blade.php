<!-- Modal Detail Dashboard Admin & Operator: Akses data lengkap & kontak pelapor -->
<div id="adminComplaintDetailModal" class="fixed inset-0 z-[99999] hidden flex items-center justify-center p-3 sm:p-5 bg-slate-900/75 transition-opacity duration-150 overscroll-contain">
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95" style="transform: translateZ(0); will-change: transform;">
        
        <!-- Modal Top Header (Selaras 1 Baris: Kerusakan -> Kategori -> Status) -->
        <div class="px-4 sm:px-5 py-3 sm:py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 gap-2">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-nowrap min-w-0 overflow-x-auto no-scrollbar py-0.5">
                <!-- 1. Tingkat Kerusakan -->
                <span id="admModalTypeBadge" class="shrink-0 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold bg-red-50 text-red-700 border border-red-200 inline-flex items-center gap-1 sm:gap-1.5 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                    <span>Kerusakan Ringan</span>
                </span>
                <!-- 2. Kategori Infrastruktur -->
                <span id="admModalCategoryBadge" class="shrink-0 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full bg-slate-100 text-slate-700 font-semibold text-[10.5px] sm:text-xs border border-slate-200 inline-flex items-center whitespace-nowrap">
                    Kategori
                </span>
                <!-- 3. Status Pengerjaan -->
                <span id="admModalStatusBadge" class="shrink-0 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full text-[10.5px] sm:text-xs font-bold border border-blue-200/80 bg-blue-50 text-[#1E3A8A] whitespace-nowrap">
                    STATUS
                </span>
            </div>
            <button type="button" onclick="closeAdminDetailModal()" class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-300 text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0 ml-1" title="Tutup Detail">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Modal Scrollable Body -->
        <div class="p-5 overflow-y-auto space-y-4 flex-1 overscroll-contain" style="transform: translateZ(0); -webkit-overflow-scrolling: touch; will-change: scroll-position;">
            
            <!-- 1. Foto Bukti Kerusakan Warga -->
            <div id="admModalPhotoContainer" class="space-y-2">
                <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 relative max-h-64 sm:max-h-72 flex items-center justify-center">
                    <img id="admModalPhoto" src="" alt="Foto Kerusakan Warga" class="w-full h-full object-cover max-h-64 sm:max-h-72">
                    <div class="absolute bottom-2.5 left-2.5 bg-black/75 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="image" class="w-3.5 h-3.5"></i>
                        <span id="admModalPhotoLabel">Foto Bukti Kerusakan</span>
                    </div>
                </div>
                <!-- Gallery Thumbnails Strip (Khusus Foto Warga) -->
                <div id="admModalPhotoGallery" class="flex items-center gap-2 overflow-x-auto pb-1 hidden"></div>
            </div>

            <!-- 2. Foto Bukti Penanganan Petugas Lapangan -->
            <div id="admModalCompletionPhotoContainer" class="space-y-2 hidden">
                <div class="rounded-2xl overflow-hidden border border-emerald-300 bg-emerald-50/50 relative max-h-64 sm:max-h-72 flex items-center justify-center">
                    <img id="admModalCompletionPhoto" src="" alt="Foto Hasil Pengerjaan Petugas" class="w-full h-full object-cover max-h-64 sm:max-h-72">
                    <div class="absolute bottom-2.5 left-2.5 bg-emerald-950/90 backdrop-blur-xs text-white text-[10px] font-bold px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span id="admModalCompletionPhotoLabel">Bukti Penyelesaian Lapangan</span>
                    </div>
                </div>
                <!-- Gallery Thumbnails Strip (Khusus Foto Petugas) -->
                <div id="admModalOfficerGallery" class="flex items-center gap-2 overflow-x-auto pb-1 hidden"></div>
            </div>

            <!-- Judul & Info Waktu Laporan -->
            <div>
                <span id="admModalCode" class="text-[11px] font-mono font-medium text-slate-400 block tracking-wide mb-0.5">
                    #ADU-0000
                </span>
                <h3 id="admModalTitle" class="font-outfit font-extrabold text-lg sm:text-xl text-slate-900 leading-snug">
                    Judul Laporan
                </h3>
                <div class="flex items-center gap-1.5 text-xs text-slate-500 pt-0.5">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span id="admModalDate">03 Sep 2026</span>
                </div>
            </div>

            <!-- Identitas Pelapor Aduan (Akses Penuh Super Admin: Telepon & WhatsApp) -->
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-slate-200/80 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                        <i data-lucide="user" class="w-4 h-4 text-[#1E3A8A]"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Pelapor Aduan</span>
                        <div class="flex items-center gap-2">
                            <span id="admModalCitizenName" class="font-outfit font-extrabold text-xs sm:text-sm text-slate-900 truncate">
                                Nama Pelapor
                            </span>
                            <span id="admModalAnonymousBadge" class="hidden text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-600">Anonim</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 text-xs text-slate-700 font-bold shrink-0">
                    <i data-lucide="phone" class="w-3.5 h-3.5 text-[#1E3A8A]"></i>
                    <a id="admModalCitizenPhone" href="#" class="font-mono hover:underline text-[#1E3A8A]" title="Hubungi / Kirim WhatsApp">08123456789</a>
                </div>
            </div>

            <!-- Regu Penanganan Lapangan -->
            <div id="admModalOfficerCard" class="hidden p-3.5 rounded-2xl bg-blue-50/60 border border-blue-200/80 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-[#1E3A8A] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                            <i data-lucide="hard-hat" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-[#1E3A8A] font-extrabold uppercase tracking-wider block">Regu Lapangan Pelaksana</span>
                            <h4 id="admModalTeamName" class="font-outfit font-extrabold text-xs sm:text-sm text-slate-900 truncate leading-tight">
                                Regu Aspal 01 - Lasusua
                            </h4>
                            <span id="admModalOfficerDept" class="text-[10.5px] font-semibold text-slate-500 block truncate">
                                Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)
                            </span>
                        </div>
                    </div>
                    <div id="admModalDeadlineBox" class="text-right shrink-0">
                        <span class="text-[10px] text-slate-400 block font-medium">Batas Waktu:</span>
                        <span id="admModalOfficerDeadline" class="text-xs font-bold text-[#1E3A8A] bg-white px-2 py-0.5 rounded border border-blue-200">-</span>
                    </div>
                </div>

                <!-- Baris Kontak Ketua Regu (Koordinator Lapangan) -->
                <div class="p-2.5 rounded-xl bg-white border border-blue-100 flex items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-6 h-6 rounded-lg bg-blue-100 text-[#1E3A8A] flex items-center justify-center shrink-0">
                            <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] text-slate-400 font-bold block leading-none">Ketua Regu (Koordinator Lapangan):</span>
                            <span id="admModalTeamLeader" class="font-bold text-slate-900 truncate block mt-0.5">Nama Ketua Regu</span>
                        </div>
                    </div>
                    <a id="admModalTeamPhone" href="#" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-[#1E3A8A] font-bold font-mono text-xs flex items-center gap-1.5 transition shrink-0" title="Hubungi Petugas">
                        <i data-lucide="phone-call" class="w-3 h-3"></i>
                        <span id="admModalTeamPhoneText">081234567816</span>
                    </a>
                </div>

                <!-- Hidden legacy fields to prevent JS reference errors -->
                <span id="admModalOfficerName" class="hidden"></span>
                <span id="admModalOfficerPhone" class="hidden"></span>

                <!-- Arahan Instruksi Teknis -->
                <div id="admModalInstructionRow" class="hidden text-xs text-slate-600 flex items-start gap-1.5 pt-1.5 border-t border-blue-200/50">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-[#1E3A8A] shrink-0 mt-0.5"></i>
                    <span>Instruksi: <span id="admModalOfficerInstruction" class="text-slate-800 font-medium italic">Lakukan perbaikan</span></span>
                </div>
            </div>

            <!-- Lokasi Kerusakan (Satu Kartu Bersih & Jelas) -->
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span>Lokasi Kerusakan</span>
                    </span>
                    <span id="admModalCoords" class="font-mono text-[11px] font-bold text-[#1E3A8A] bg-white px-2 py-0.5 rounded-md border border-slate-200">
                        -
                    </span>
                </div>
                <p id="admModalAddress" class="text-xs font-semibold text-slate-800 leading-relaxed">
                    Alamat lokasi kerusakan...
                </p>
                <span id="admModalRoad" class="hidden"></span>
                <span id="admModalVillage" class="hidden"></span>
                <span id="admModalDistrict" class="hidden"></span>
                <span id="admModalRegency" class="hidden"></span>
            </div>

            <!-- Detail Kerusakan Ruas Panjang (Tipe Major) -->
            <div id="admModalMajorRoadBox" class="hidden p-3.5 rounded-2xl bg-blue-50/80 border border-blue-200 space-y-2">
                <div class="flex items-center justify-between text-xs font-bold text-[#1E3A8A]">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="route" class="w-4 h-4 text-[#1E3A8A]"></i>
                        <span>Detail Kerusakan Berat Ruas Jalan</span>
                    </span>
                    <span id="admModalRoadLengthBadge" class="text-[10px] text-white bg-[#1E3A8A] px-2.5 py-0.5 rounded-full font-bold">
                        ≥ 500 Meter
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2 rounded-xl bg-white border border-blue-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Estimasi Panjang Ruas:</span>
                        <span id="admModalRoadLength" class="font-bold text-[#1E3A8A]">-</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white border border-blue-100">
                        <span class="text-[10px] text-slate-400 block font-semibold">Kondisi Permukaan:</span>
                        <span id="admModalRoadCondition" class="font-bold text-[#1E3A8A]">-</span>
                    </div>
                    <div class="p-2 rounded-xl bg-white border border-blue-100 col-span-2">
                        <span class="text-[10px] text-slate-400 block font-semibold">Koordinat Titik Awal & Akhir:</span>
                        <div class="font-mono text-[11px] text-slate-700 flex flex-col gap-0.5 mt-1">
                            <div><span class="font-bold text-blue-700">Titik Awal:</span> <span id="admModalStartCoords">-</span></div>
                            <div><span class="font-bold text-slate-700">Titik Akhir:</span> <span id="admModalEndCoords">-</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deskripsi Lengkap -->
            <div class="space-y-2">
                <h4 class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-4 h-4 text-slate-400"></i>
                    <span>Deskripsi Kondisi Kerusakan</span>
                </h4>
                <div id="admModalDescription" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-700 leading-relaxed whitespace-pre-line font-sans">
                    Deskripsi lengkap...
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-end gap-2">
            <button type="button" onclick="closeAdminDetailModal()" class="px-5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition cursor-pointer">
                Tutup Rincian
            </button>
        </div>

    </div>
</div>
