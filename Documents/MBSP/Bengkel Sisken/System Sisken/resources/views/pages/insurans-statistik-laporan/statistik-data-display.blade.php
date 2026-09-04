@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Statistik' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Paparan statistik mengikut kategori data kenderaan.
            </p>
        </div>
    </div>

    <x-common.component-card title="Carian Statistik">
        <form method="GET" action="{{ route('statistik-data-display') }}" id="statistikSearchForm">
            <div class="grid grid-cols-12 gap-4 items-end">
                <div class="col-span-8">
                    <label class="form-label">
                        Jenis Statistik
                    </label>

                    <select name="jenis_statistik" class="form-control" required>
                        <option value="" {{ empty($jenisStatistik) ? 'selected' : '' }} disabled>
                            -- Sila Pilih Statistik --
                        </option>

                        <option value="maklumat_kenderaan"
                            {{ $jenisStatistik == 'maklumat_kenderaan' ? 'selected' : '' }}>
                            Maklumat Kenderaan
                        </option>

                        <option value="cukai_jalan"
                            {{ $jenisStatistik == 'cukai_jalan' ? 'selected' : '' }}>
                            Cukai Jalan
                        </option>

                        <option value="tukar_milik_kenderaan"
                            {{ $jenisStatistik == 'tukar_milik_kenderaan' ? 'selected' : '' }}>
                            Tukar Milik Kenderaan
                        </option>

                        <option value="kemalangan_kenderaan_mbsp"
                            {{ $jenisStatistik == 'kemalangan_kenderaan_mbsp' ? 'selected' : '' }}>
                            Kemalangan Kenderaan MBSP
                        </option>

                        <option value="tuntutan_cermin_pecah"
                            {{ $jenisStatistik == 'tuntutan_cermin_pecah' ? 'selected' : '' }}>
                            Tuntutan Cermin Pecah
                        </option>

                        <option value="tuntutan_pokok_tumbang"
                            {{ $jenisStatistik == 'tuntutan_pokok_tumbang' ? 'selected' : '' }}>
                            Tuntutan Pokok Tumbang
                        </option>

                        <option value="tuntutan_jalan_raya_berlubang"
                            {{ $jenisStatistik == 'tuntutan_jalan_raya_berlubang' ? 'selected' : '' }}>
                            Tuntutan Jalan Raya Berlubang
                        </option>

                        <option value="laporan_prosiding_kes_mahkamah"
                            {{ $jenisStatistik == 'laporan_prosiding_kes_mahkamah' ? 'selected' : '' }}>
                            Laporan Prosiding Kes Mahkamah
                        </option>
                    </select>
                </div>

                <div class="col-span-4">
                    <button type="submit"
                        id="statistikSearchButton"
                        class="w-full rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                        Cari
                    </button>
                </div>
            </div>
        </form>
    </x-common.component-card>

    @if($jenisStatistik && !in_array($jenisStatistik, ['maklumat_kenderaan', 'kemalangan_kenderaan_mbsp']))
        <div class="mt-6">
            <x-common.component-card title="Statistik">
                <div class="empty-state">
                    Statistik ini masih dalam pembangunan.
                </div>
            </x-common.component-card>
        </div>
    @endif

    @if($jenisStatistik === 'maklumat_kenderaan')
        <div class="mt-6 grid grid-cols-12 gap-6">

            <div class="col-span-6">
                <x-common.component-card title="Jumlah Kenderaan Mengikut Jenis Bahan Bakar">
                    @if(count($jenisBahanBakarLabels) > 0)
                        <div class="chart-box">
                            <canvas id="jenisBahanBakarChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">
                            No data to display
                        </div>
                    @endif
                </x-common.component-card>
            </div>

            <div class="col-span-6">
                <x-common.component-card title="Jumlah Kenderaan Mengikut Tahun Pendaftaran">
                    @if(count($tahunPendaftaranLabels) > 0)
                        <div class="chart-box">
                            <canvas id="tahunPendaftaranChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">
                            No data to display
                        </div>
                    @endif
                </x-common.component-card>
            </div>

            <div class="col-span-12">
                <x-common.component-card title="Jumlah Kenderaan Mengikut Jenis Badan">
                    @if(count($jenisBadanLabels) > 0)
                        <div class="chart-box chart-box-large">
                            <canvas id="jenisBadanChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">
                            No data to display
                        </div>
                    @endif
                </x-common.component-card>
            </div>

        </div>
    @endif

    @if($jenisStatistik === 'kemalangan_kenderaan_mbsp')
        <div class="mt-6 grid grid-cols-12 gap-6">

            <div class="col-span-12">
                <x-common.component-card title="Jumlah Kemalangan Mengikut Tahun Kejadian">
                    @if(count($kemalanganTahunanLabels) > 0)
                        <div class="chart-box">
                            <canvas id="kemalanganTahunanChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">No data to display</div>
                    @endif
                </x-common.component-card>
            </div>

            <div class="col-span-12">
                <x-common.component-card title="Jumlah Kemalangan Mengikut Daerah">
                    @if(count($daerahKemalanganLabels) > 0)
                        <div class="chart-box">
                            <canvas id="daerahKemalanganChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">No data to display</div>
                    @endif
                </x-common.component-card>
            </div>

            <div class="col-span-12">
                <x-common.component-card title="Jumlah Kemalangan Mengikut Jenis Kenderaan Mengikut Tahun">
                    @if(count($jenisKenderaanYearLabels) > 0)
                        <div class="chart-box chart-box-large">
                            <canvas id="jenisKenderaanYearChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">No data to display</div>
                    @endif
                </x-common.component-card>
            </div>

            <div class="col-span-12">
                <x-common.component-card title="Jumlah Kemalangan Mengikut Jabatan Kenderaan Mengikut Tahun">
                    @if(count($jabatanKenderaanYearLabels) > 0)
                        <div class="chart-box chart-box-large">
                            <canvas id="jabatanKenderaanYearChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">No data to display</div>
                    @endif
                </x-common.component-card>
            </div>

            <div class="col-span-12">
                <x-common.component-card title="Jumlah Kemalangan Mengikut Lokasi Penempatan Mengikut Tahun">
                    @if(count($lokasiPenempatanYearLabels) > 0)
                        <div class="chart-box chart-box-large">
                            <canvas id="lokasiPenempatanYearChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">No data to display</div>
                    @endif
                </x-common.component-card>
            </div>

            <div class="col-span-12">
                <x-common.component-card title="Jumlah Kemalangan Mengikut Jenis Tuntutan Pihak Ketiga Mengikut Tahun">
                    @if(count($jenisTuntutanPihakKetigaYearLabels) > 0)
                        <div class="chart-box chart-box-large">
                            <canvas id="jenisTuntutanPihakKetigaYearChart"></canvas>
                        </div>
                    @else
                        <div class="empty-state">No data to display</div>
                    @endif
                </x-common.component-card>
            </div>

        </div>
    @endif
</div>

<div id="statistikLoadingOverlay" class="statistik-loading-overlay hidden">
    <div class="statistik-loading-card">
        <div class="statistik-spinner"></div>
        <div class="mt-4 font-semibold text-gray-700">
            Sedang menjana statistik...
        </div>
        <div class="mt-1 text-sm text-gray-500">
            Sila tunggu sebentar.
        </div>
    </div>
</div>

<style>
    .form-label {
        margin-bottom: 8px;
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #344054;
    }

    .form-control {
        width: 100%;
        min-height: 48px;
        border: 1px solid #d0d5dd;
        border-radius: 10px;
        padding: 0 14px;
        font-size: 14px;
        background: #fff;
        color: #101828;
        outline: none;
    }

    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .chart-box {
        position: relative;
        width: 100%;
        height: 420px;
    }

    .chart-box-large {
        height: 700px;
    }

    .empty-state {
        padding: 42px 20px;
        text-align: center;
        color: #667085;
        background: #f8fafc;
        border: 1px dashed #d0d5dd;
        border-radius: 14px;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .grid.grid-cols-12 > div {
            grid-column: span 12 / span 12 !important;
        }

        .chart-box,
        .chart-box-large {
            height: 360px;
        }
    }

    .statistik-loading-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999999;
        background: rgba(15, 23, 42, 0.45);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .statistik-loading-overlay.hidden {
        display: none;
    }

    .statistik-loading-card {
        width: 320px;
        min-height: 180px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.25);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .statistik-spinner {
        width: 54px;
        height: 54px;
        border: 5px solid #e5e7eb;
        border-top-color: #2563eb;
        border-radius: 999px;
        animation: statistikSpin 0.8s linear infinite;
    }

    @keyframes statistikSpin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if($jenisStatistik === 'maklumat_kenderaan')
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('Jenis bahan bakar labels:', @json($jenisBahanBakarLabels));
    console.log('Jenis bahan bakar data:', @json($jenisBahanBakarData));
    console.log('Tahun pendaftaran labels:', @json($tahunPendaftaranLabels));
    console.log('Tahun pendaftaran data:', @json($tahunPendaftaranData));
    console.log('Jenis badan labels:', @json($jenisBadanLabels));
    console.log('Jenis badan data:', @json($jenisBadanData));

    if (typeof Chart === 'undefined') {
        alert('Chart.js tidak berjaya dimuatkan. Sila semak internet/CDN atau letak Chart.js secara local.');
        return;
    }

    const defaultChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Jumlah: ' + context.raw;
                    }
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    autoSkip: false,
                    maxRotation: 45,
                    minRotation: 0
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    };

    const jenisBahanBakarCanvas = document.getElementById('jenisBahanBakarChart');
    const tahunPendaftaranCanvas = document.getElementById('tahunPendaftaranChart');
    const jenisBadanCanvas = document.getElementById('jenisBadanChart');

    if (jenisBahanBakarCanvas) {
        new Chart(jenisBahanBakarCanvas, {
            type: 'bar',
            data: {
                labels: @json($jenisBahanBakarLabels),
                datasets: [{
                    label: 'Jumlah Kenderaan',
                    data: @json($jenisBahanBakarData),
                    borderWidth: 1
                }]
            },
            options: defaultChartOptions
        });
    }

    if (tahunPendaftaranCanvas) {
        new Chart(tahunPendaftaranCanvas, {
            type: 'bar',
            data: {
                labels: @json($tahunPendaftaranLabels),
                datasets: [{
                    label: 'Jumlah Kenderaan',
                    data: @json($tahunPendaftaranData),
                    borderWidth: 1
                }]
            },
            options: defaultChartOptions
        });
    }

    if (jenisBadanCanvas) {
        new Chart(jenisBadanCanvas, {
            type: 'bar',
            data: {
                labels: @json($jenisBadanLabels),
                datasets: [{
                    label: 'Jumlah Kenderaan',
                    data: @json($jenisBadanData),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Jumlah: ' + context.raw;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    },
                    y: {
                        ticks: {
                            autoSkip: false
                        }
                    }
                }
            }
        });
    }
});
</script>
@endif

@if($jenisStatistik === 'kemalangan_kenderaan_mbsp')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        alert('Chart.js tidak berjaya dimuatkan. Sila semak internet/CDN atau letak Chart.js secara local.');
        return;
    }

    const pieOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.raw;
                    }
                }
            }
        }
    };

    const barOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Jumlah: ' + context.raw;
                    }
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    autoSkip: false,
                    maxRotation: 45,
                    minRotation: 0
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    };

    const stackedBarOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw;
                    }
                }
            }
        },
        scales: {
            x: {
                stacked: true,
                ticks: {
                    autoSkip: false,
                    maxRotation: 45,
                    minRotation: 0
                }
            },
            y: {
                stacked: true,
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    };

    const kemalanganTahunanCanvas = document.getElementById('kemalanganTahunanChart');
    const daerahKemalanganCanvas = document.getElementById('daerahKemalanganChart');

    const jenisKenderaanYearCanvas = document.getElementById('jenisKenderaanYearChart');
    const jabatanKenderaanYearCanvas = document.getElementById('jabatanKenderaanYearChart');
    const lokasiPenempatanYearCanvas = document.getElementById('lokasiPenempatanYearChart');
    const jenisTuntutanPihakKetigaYearCanvas = document.getElementById('jenisTuntutanPihakKetigaYearChart');

    if (kemalanganTahunanCanvas) {
        new Chart(kemalanganTahunanCanvas, {
            type: 'pie',
            data: {
                labels: @json($kemalanganTahunanLabels),
                datasets: [{
                    data: @json($kemalanganTahunanData),
                    borderWidth: 1
                }]
            },
            options: pieOptions
        });
    }

    if (daerahKemalanganCanvas) {
        new Chart(daerahKemalanganCanvas, {
            type: 'bar',
            data: {
                labels: @json($daerahKemalanganLabels),
                datasets: [{
                    label: 'Jumlah Kemalangan',
                    data: @json($daerahKemalanganData),
                    borderWidth: 1
                }]
            },
            options: barOptions
        });
    }

    function buildStackedChart(canvas, labels, datasets) {
        if (!canvas) {
            return;
        }

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: stackedBarOptions
        });
    }

    buildStackedChart(
        jenisKenderaanYearCanvas,
        @json($jenisKenderaanYearLabels),
        @json($jenisKenderaanYearDatasets)
    );

    buildStackedChart(
        jabatanKenderaanYearCanvas,
        @json($jabatanKenderaanYearLabels),
        @json($jabatanKenderaanYearDatasets)
    );

    buildStackedChart(
        lokasiPenempatanYearCanvas,
        @json($lokasiPenempatanYearLabels),
        @json($lokasiPenempatanYearDatasets)
    );

    buildStackedChart(
        jenisTuntutanPihakKetigaYearCanvas,
        @json($jenisTuntutanPihakKetigaYearLabels),
        @json($jenisTuntutanPihakKetigaYearDatasets)
    );
});
</script>
@endif

@endpush

@endsection