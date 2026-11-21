<!-- Dependensi Chart.js CDN (Dipastikan Selalu Termuat) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@php
    $totalOnTime = $departments->sum('completed_tasks');
    $totalLate = $departments->sum(fn($d) => max(0, ($d->total_tasks ?? 0) - ($d->completed_tasks ?? 0)));
    $totalAssigned = $departments->sum('total_tasks');
    $fieldCount = max(0, ($summary['in_progress'] ?? 0) + ($summary['completed'] ?? 0));
    $operatorCount = max(0, ($summary['pending'] ?? 0) + ($summary['waiting_verification'] ?? 0));
    $deptCount = max(0, $totalOnTime);
    $totalWorkload = max(1, $fieldCount + $operatorCount + $deptCount);
    $fieldPercent = round(($fieldCount / $totalWorkload) * 100);
    $operatorPercent = round(($operatorCount / $totalWorkload) * 100);
    $deptPercent = max(0, 100 - $fieldPercent - $operatorPercent);
@endphp

<script>
// Monitoring Eksekutif, Grafik Kinerja & Pelaporan Resmi

let opdPerformanceChartInstance = null;
let categoryDistributionChartInstance = null;
let complaintStatusChartInstance = null;
let stakeholderPerformanceChartInstance = null;

function initExecutiveMonitoringCharts() {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js sedang dimuat...');
        setTimeout(initExecutiveMonitoringCharts, 150);
        return;
    }

    // 1. GRAFIK KINERJA OPD (Bar Chart: Tepat Waktu vs Terlambat)
    const opdCanvas = document.getElementById('opdPerformanceChart');
    if (opdCanvas) {
        if (opdPerformanceChartInstance) {
            opdPerformanceChartInstance.destroy();
        }
        const ctxOpd = opdCanvas.getContext('2d');
        opdPerformanceChartInstance = new Chart(ctxOpd, {
            type: 'bar',
            data: {
                labels: ['Dinas PUPR'],
                datasets: [
                    {
                        label: 'Tepat Waktu',
                        data: [{{ $departments->sum('completed_tasks') }}],
                        backgroundColor: '#10B981',
                        borderRadius: 8,
                        barPercentage: 0.35,
                        categoryPercentage: 0.5
                    },
                    {
                        label: 'Terlambat (Lewat SLA)',
                        data: [{{ $departments->sum(fn($d) => max(0, ($d->total_tasks ?? 0) - ($d->completed_tasks ?? 0))) }}],
                        backgroundColor: '#EF4444',
                        borderRadius: 8,
                        barPercentage: 0.35,
                        categoryPercentage: 0.5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Outfit', size: 12, weight: '600' },
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16
                        }
                    },
                    tooltip: {
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.dataset.label}: ${context.raw} Laporan`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { color: '#F1F5F9' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Outfit', weight: 'bold', size: 12 } }
                    }
                }
            }
        });
    }

    // 2. GRAFIK SEBARAN KATEGORI (Doughnut Chart - Palette All-Blue)
    const catCanvas = document.getElementById('categoryDistributionChart');
    if (catCanvas) {
        if (categoryDistributionChartInstance) {
            categoryDistributionChartInstance.destroy();
        }
        const ctxCat = catCanvas.getContext('2d');
        const blueTones = ['#2563EB', '#2563EB', '#3B82F6', '#60A5FA', '#93C5FD', '#BFDBFE'];
        const catLabels = @json($categories->pluck('name'));
        const catData = @json($categories->pluck('complaints_count'));

        categoryDistributionChartInstance = new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: blueTones.slice(0, catLabels.length),
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw} Laporan`;
                            }
                        }
                    }
                }
            }
        });
    }

    // 3. GRAFIK STATUS SIKLUS PENANGANAN ("Total Laporan", Palette Baku Aturan 5)
    const statusCanvas = document.getElementById('complaintStatusChart');
    if (statusCanvas) {
        if (complaintStatusChartInstance) {
            complaintStatusChartInstance.destroy();
        }
        const ctxStatus = statusCanvas.getContext('2d');
        complaintStatusChartInstance = new Chart(ctxStatus, {
            type: 'bar',
            data: {
                labels: ['Total Laporan', 'Verifikasi', 'Ditugaskan', 'Dikerjakan', 'Selesai', 'Ditolak'],
                datasets: [{
                    data: [
                        {{ $summary['total'] }},
                        {{ $summary['pending'] }},
                        {{ $summary['dispatched'] }},
                        {{ $summary['in_progress'] }},
                        {{ $summary['completed'] }},
                        {{ $summary['rejected'] ?? 0 }}
                    ],
                    backgroundColor: [
                        '#2563EB', // Total Laporan
                        '#EF4444', // Verifikasi (Rule 5: SUBMITTED = Merah)
                        '#2563EB', // Ditugaskan (Rule 5: DISPATCHED = Biru)
                        '#F59E0B', // Dikerjakan (Rule 5: IN_PROGRESS = Kuning)
                        '#10B981', // Selesai (Rule 5: COMPLETED = Hijau)
                        '#DC2626'  // Ditolak (Rule 5: REJECTED = Merah gelap)
                    ],
                    borderRadius: 8,
                    barPercentage: 0.55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw} Laporan`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { color: '#F1F5F9' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Outfit', size: 11, weight: '600' } }
                    }
                }
            }
        });
    }

    // 4. GRAFIK PERSENTASE KINERJA PENANGANAN TUGAS (Doughnut Chart: Petugas, Operator, Dinas)
    const stakeholderCanvas = document.getElementById('stakeholderPerformanceChart');
    if (stakeholderCanvas) {
        if (stakeholderPerformanceChartInstance) {
            stakeholderPerformanceChartInstance.destroy();
        }
        const ctxStakeholder = stakeholderCanvas.getContext('2d');
        stakeholderPerformanceChartInstance = new Chart(ctxStakeholder, {
            type: 'doughnut',
            data: {
                labels: ['Pekerja Lapangan', 'Operator', 'Dinas PUPR'],
                datasets: [{
                    data: [{{ $fieldPercent }}, {{ $operatorPercent }}, {{ $deptPercent }}],
                    backgroundColor: [
                        '#3B82F6', // Pekerja Lapangan
                        '#2563EB', // Operator Verifikasi
                        '#10B981'  // Dinas PUPR
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ` ${context.label}: ${context.raw}% Kontribusi`;
                            }
                        }
                    }
                }
            }
        });
    }

    if (window.lucide) {
        lucide.createIcons();
    }
}

// Inisialisasi otomatis jika elemen chart terlihat
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(initExecutiveMonitoringCharts, 200);
});

// Format Laporan Resmi ('professional' | 'ats')
let activeReportFormat = 'professional';

function setReportFormat(format) {
    activeReportFormat = format === 'ats' ? 'ats' : 'professional';

    const profEl = document.getElementById('reportFormatProfessional');
    const atsEl = document.getElementById('reportFormatAts');
    const subtitleEl = document.getElementById('modalReportFormatSubtitle');

    if (profEl && atsEl) {
        if (activeReportFormat === 'ats') {
            profEl.classList.add('hidden');
            atsEl.classList.remove('hidden');
        } else {
            atsEl.classList.add('hidden');
            profEl.classList.remove('hidden');
        }
    }

    if (subtitleEl) {
        subtitleEl.textContent = activeReportFormat === 'ats'
            ? 'Format ATS (Teks Terstruktur & Ramah Arsip) • Kabupaten'
            : 'Format Profesional Kedinasan • Kabupaten';
    }

    document.querySelectorAll('[data-format-btn]').forEach((btn) => {
        const isSelected = btn.getAttribute('data-format-btn') === activeReportFormat;
        if (isSelected) {
            btn.classList.add('bg-white', 'text-[#2563EB]', 'border-slate-200', 'shadow-2xs');
            btn.classList.remove('text-slate-600', 'border-transparent');
        } else {
            btn.classList.remove('bg-white', 'text-[#2563EB]', 'border-slate-200', 'shadow-2xs');
            btn.classList.add('text-slate-600', 'border-transparent');
        }
    });
}

// Modal Pratinjau Dokumen & Cetak Laporan

function openReportPreviewModal() {
    const modal = document.getElementById('officialReportModal');
    if (modal) {
        // Pindahkan elemen modal langsung ke document.body agar terbebas dari padding/margin/transform kontainer
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        const scrollArea = document.getElementById('reportModalScrollArea');
        if (scrollArea) {
            scrollArea.scrollTop = 0;
            requestAnimationFrame(() => {
                scrollArea.scrollTop = 0;
            });
        }
        if (window.lucide) {
            lucide.createIcons({ root: modal });
        }
    }
}

function closeReportPreviewModal() {
    const modal = document.getElementById('officialReportModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Menutup modal dengan klik area gelap (backdrop) atau tombol Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeReportPreviewModal();
    }
});
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('officialReportModal');
    if (modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeReportPreviewModal();
            }
        });
    }
});

// Cetak Dokumen PDF Resmi
function printOfficialReportPdf() {
    const modal = document.getElementById('officialReportModal');
    if (modal && modal.classList.contains('hidden')) {
        openReportPreviewModal();
    }
    setTimeout(() => {
        window.print();
    }, 250);
}
</script>
