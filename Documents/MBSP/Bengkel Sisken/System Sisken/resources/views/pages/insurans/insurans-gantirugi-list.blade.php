@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Senarai Gantirugi' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai tuntutan gantirugi kenderaan.
            </p>
        </div>

        <div>
            <a href="{{ route('insurans-data-add') }}"
                class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                + Tambah Data
            </a>
        </div>
    </div>

    <x-common.component-card title="Senarai Gantirugi">
        <div class="p-1 overflow-x-auto">
            <table id="gantirugiTable" class="user-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>Tahun</th>
                        <th>Tarikh Hantar Kes</th>
                        <th>Tarikh Kejadian</th>
                        <th>No Plat</th>
                        <th>Jenis Kenderaan</th>
                        <th>Jabatan</th>
                        <th>Bahagian</th>
                        <th>Kes Tuntutan</th>
                        <th>Kos Tuntutan</th>
                        <th>Status Gantirugi</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($gantirugi as $index => $row)
                        @php
                            $rowArray = (array) $row;

                            $normalized = [];
                            foreach ($rowArray as $key => $value) {
                                $normalized[strtoupper($key)] = $value;
                            }

                            $get = function (...$keys) use ($normalized) {
                                foreach ($keys as $key) {
                                    $upperKey = strtoupper($key);

                                    if (array_key_exists($upperKey, $normalized)) {
                                        $value = $normalized[$upperKey];

                                        if ($value !== null && $value !== '') {
                                            return $value;
                                        }
                                    }
                                }

                                return null;
                            };

                            $id = $get('ID');

                            $tarikhKejadian = $get('TARIKH_KEMALANGAN');
                            $tarikhHantarKes = $get('TARIKH_HANTAR_KES');

                            $tahun = !empty($tarikhKejadian)
                                ? \Carbon\Carbon::parse($tarikhKejadian)->format('Y')
                                : '-';

                            $noPlat = $get(
                                'NO_PLAT_KEMALANGAN',
                                'NO_PLAT_VEHICLE',
                                'NO_PLAT'
                            ) ?? '-';

                            $jenisKenderaan = $get('JENIS_KENDERAAN') ?? '-';

                            $jabatan = $get(
                                'JABATAN_KENDERAAN',
                                'JABATAN_VEHICLE',
                                'DESC_JABATAN_STAF',
                                'KOD_JABATAN_STAF'
                            ) ?? '-';

                            $bahagian = $get('BAHAGIAN') ?? '-';

                            $kesTuntutan = $get('KETERANGAN_KEMALANGAN') ?? '-';

                            $kosTuntutan = $get('KOS_TUNTUTAN_PIHAK_KETIGA') ?? null;

                            $statusGantirugi = strtoupper(trim($get('STATUS_GANTIRUGI') ?? 'DALAM PROSES'));

                            $statusClass = match($statusGantirugi) {
                                'SELESAI' => 'status-active',
                                'DALAM PROSES' => 'status-warning',
                                default => 'status-default',
                            };
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td>{{ $tahun }}</td>

                            <td>
                                {{ !empty($tarikhHantarKes)
                                    ? \Carbon\Carbon::parse($tarikhHantarKes)->format('d/m/Y')
                                    : '-'
                                }}
                            </td>

                            <td>
                                {{ !empty($tarikhKejadian)
                                    ? \Carbon\Carbon::parse($tarikhKejadian)->format('d/m/Y')
                                    : '-'
                                }}
                            </td>

                            <td class="font-semibold">
                                {{ $noPlat }}
                            </td>

                            <td class="cell-text">
                                {{ $jenisKenderaan }}
                            </td>

                            <td class="cell-text">
                                {{ $jabatan }}
                            </td>

                            <td class="cell-text">
                                {{ $bahagian }}
                            </td>

                            <td class="cell-text">
                                {{ $kesTuntutan }}
                            </td>

                            <td class="text-right">
                                {{ $kosTuntutan !== null ? 'RM ' . number_format((float)$kosTuntutan, 2) : '-' }}
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statusGantirugi }}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                                    <button type="button"
                                        class="action-btn info-btn"
                                        onclick="openGantirugiInfoModal('gantirugi-info-modal-{{ $id }}')"
                                        title="Maklumat">
                                        ℹ️
                                    </button>

                                    <button type="button"
                                        class="action-btn edit-btn"
                                        onclick="openGantirugiEditModal('gantirugi-edit-modal-{{ $id }}')"
                                        title="Edit Status Gantirugi">
                                        ✏️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-4 text-gray-500">
                                Tiada data gantirugi insurans.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>

{{-- MODAL INFO GANTIRUGI --}}
@foreach($gantirugi as $row)
    @php
        $rowArray = (array) $row;

        $normalized = [];
        foreach ($rowArray as $key => $value) {
            $normalized[strtoupper($key)] = $value;
        }

        $get = function (...$keys) use ($normalized) {
            foreach ($keys as $key) {
                $upperKey = strtoupper($key);

                if (array_key_exists($upperKey, $normalized)) {
                    $value = $normalized[$upperKey];

                    if ($value !== null && $value !== '') {
                        return $value;
                    }
                }
            }

            return null;
        };

        $id = $get('ID');

        $noPlat = $get(
            'NO_PLAT_KEMALANGAN',
            'NO_PLAT_VEHICLE',
            'NO_PLAT'
        ) ?? '-';

        $tarikhKemalangan = $get('TARIKH_KEMALANGAN');
        $tarikhHantarKes = $get('TARIKH_HANTAR_KES');
        $createdAt = $get('CREATED_AT');
        $updatedAt = $get('UPDATED_AT');
    @endphp

    <div id="gantirugi-info-modal-{{ $id }}" class="gantirugi-modal hidden">
        <div class="gantirugi-modal-overlay" onclick="closeGantirugiInfoModal('gantirugi-info-modal-{{ $id }}')"></div>

        <div class="gantirugi-modal-card">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Maklumat Gantirugi
                    </h3>
                    <p class="text-sm text-gray-500">
                        No Plat: {{ $noPlat }}
                    </p>
                </div>

                <button type="button"
                    class="modal-close-btn"
                    onclick="closeGantirugiInfoModal('gantirugi-info-modal-{{ $id }}')">
                    ×
                </button>
            </div>

            <div class="detail-section">
                <h4>Maklumat Utama</h4>
                <div class="detail-grid">
                    <div><span>No Plat</span><strong>{{ $noPlat }}</strong></div>
                    <div><span>No Pekerja</span><strong>{{ $get('NO_PEKERJA') ?? '-' }}</strong></div>
                    <div><span>Nama</span><strong>{{ $get('NAMA') ?? '-' }}</strong></div>
                    <div><span>Jawatan</span><strong>{{ $get('JAWATAN') ?? '-' }}</strong></div>
                    <div><span>Jabatan Staf</span><strong>{{ $get('DESC_JABATAN_STAF', 'KOD_JABATAN_STAF') ?? '-' }}</strong></div>
                    <div><span>Jabatan Kenderaan</span><strong>{{ $get('JABATAN_KENDERAAN', 'JABATAN_VEHICLE') ?? '-' }}</strong></div>
                    <div><span>Jenis Kenderaan</span><strong>{{ $get('JENIS_KENDERAAN') ?? '-' }}</strong></div>
                    <div><span>NCD Semasa</span><strong>{{ $get('NCD_SEMASA') ?? '-' }}</strong></div>
                </div>
            </div>

            <div class="detail-section">
                <h4>Maklumat Insurans / Tuntutan</h4>
                <div class="detail-grid">
                    <div><span>Baik Pulih Bengkel</span><strong>{{ $get('BAIK_PULIH_BENGKEL') ?? '-' }}</strong></div>
                    <div><span>Jenis Tuntutan Insurans</span><strong>{{ $get('JENIS_TUNTUTAN_INSURANS') ?? '-' }}</strong></div>
                    <div><span>Bahagian</span><strong>{{ $get('BAHAGIAN') ?? '-' }}</strong></div>
                    <div><span>Daerah</span><strong>{{ $get('DAERAH') ?? '-' }}</strong></div>

                    <div>
                        <span>Tarikh Kemalangan</span>
                        <strong>{{ !empty($tarikhKemalangan) ? \Carbon\Carbon::parse($tarikhKemalangan)->format('d/m/Y') : '-' }}</strong>
                    </div>

                    <div>
                        <span>Tarikh Hantar Kes</span>
                        <strong>{{ !empty($tarikhHantarKes) ? \Carbon\Carbon::parse($tarikhHantarKes)->format('d/m/Y') : '-' }}</strong>
                    </div>

                    <div><span>Lokasi Kemalangan</span><strong>{{ $get('LOKASI_KEMALANGAN') ?? '-' }}</strong></div>
                    {{-- <div><span>Kos Baik Pulih</span><strong>RM {{ $get('KOS_BAIK_PULIH') ?? '0.00' }}</strong></div> --}}
                    <div><span>Kos Tuntutan Pihak Ketiga</span><strong>RM {{ $get('KOS_TUNTUTAN_PIHAK_KETIGA') ?? '0.00' }}</strong></div>
                    <div class="detail-full"><span>Keterangan Kemalangan</span><strong>{{ $get('KETERANGAN_KEMALANGAN') ?? '-' }}</strong></div>
                </div>
            </div>

            <div class="detail-section">
                <h4>Maklumat Pihak Ketiga</h4>
                <div class="detail-grid">
                    <div><span>Nama Penuntut</span><strong>{{ $get('NAMA_PENUNTUT') ?? '-' }}</strong></div>
                    <div><span>No Plat Pihak Ketiga</span><strong>{{ $get('NO_PLAT_PIHAK_KETIGA') ?? '-' }}</strong></div>
                    <div><span>Jenis Kenderaan Pihak Ketiga</span><strong>{{ $get('JENIS_KENDERAAN_PIHAK_KETIGA') ?? '-' }}</strong></div>
                    <div><span>Jenis Tuntutan Pihak Ketiga</span><strong>{{ $get('JENIS_TUNTUTAN_PIHAK_KETIGA') ?? '-' }}</strong></div>
                    <div><span>No Telefon Pihak Ketiga</span><strong>{{ $get('NO_TELEFON_PIHAK_KETIGA') ?? '-' }}</strong></div>
                    <div class="detail-full"><span>Kerosakan Pihak Ketiga</span><strong>{{ $get('KEROSAKAN_PIHAK_KETIGA') ?? '-' }}</strong></div>
                </div>
            </div>

            <div class="detail-section">
                <h4>Status</h4>
                <div class="detail-grid">
                    <div><span>Status Kes</span><strong>{{ $get('STATUS_KES') ?? '-' }}</strong></div>
                    <div><span>Status Gantirugi</span><strong>{{ $get('STATUS_GANTIRUGI') ?? '-' }}</strong></div>
                    <div><span>Keputusan Kes Trafik</span><strong>{{ $get('KEPUTUSAN_KES_TRAFIK') ?? '-' }}</strong></div>
                    <div class="detail-full"><span>Kemajuan Semasa</span><strong>{{ $get('KEMAJUAN_SEMASA') ?? '-' }}</strong></div>
                    <div class="detail-full"><span>Laporan Adjuster / Insurans</span><strong>{{ $get('LAPORAN_ADJUSTER_INSURANS') ?? '-' }}</strong></div>
                </div>
            </div>

            {{-- <div class="detail-section">
                <h4>Maklumat Sistem</h4>
                <div class="detail-grid">
                    <div><span>Created By</span><strong>{{ $get('CREATED_BY') ?? '-' }}</strong></div>
                    <div>
                        <span>Created At</span>
                        <strong>{{ !empty($createdAt) ? \Carbon\Carbon::parse($createdAt)->format('d/m/Y H:i') : '-' }}</strong>
                    </div>
                    <div><span>Updated By</span><strong>{{ $get('UPDATED_BY') ?? '-' }}</strong></div>
                    <div>
                        <span>Updated At</span>
                        <strong>{{ !empty($updatedAt) ? \Carbon\Carbon::parse($updatedAt)->format('d/m/Y H:i') : '-' }}</strong>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
@endforeach

{{-- MODAL EDIT STATUS GANTIRUGI --}}
@foreach($gantirugi as $row)
    @php
        $rowArray = (array) $row;

        $normalized = [];
        foreach ($rowArray as $key => $value) {
            $normalized[strtoupper($key)] = $value;
        }

        $get = function (...$keys) use ($normalized) {
            foreach ($keys as $key) {
                $upperKey = strtoupper($key);

                if (array_key_exists($upperKey, $normalized)) {
                    $value = $normalized[$upperKey];

                    if ($value !== null && $value !== '') {
                        return $value;
                    }
                }
            }

            return null;
        };

        $id = $get('ID');

        $noPlat = $get(
            'NO_PLAT_KEMALANGAN',
            'NO_PLAT_VEHICLE',
            'NO_PLAT'
        ) ?? '-';

        $statusGantirugi = $get('STATUS_GANTIRUGI') ?? 'DALAM PROSES';
    @endphp

    <div id="gantirugi-edit-modal-{{ $id }}" class="gantirugi-modal hidden">
        <div class="gantirugi-modal-overlay" onclick="closeGantirugiEditModal('gantirugi-edit-modal-{{ $id }}')"></div>

        <div class="gantirugi-modal-card gantirugi-edit-card">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Kemaskini Status Gantirugi
                    </h3>
                    <p class="text-sm text-gray-500">
                        No Plat: {{ $noPlat }}
                    </p>
                </div>

                <button type="button"
                    class="modal-close-btn"
                    onclick="closeGantirugiEditModal('gantirugi-edit-modal-{{ $id }}')">
                    ×
                </button>
            </div>

            <form method="POST" action="{{ route('insurans-gantirugi-update-status', $id) }}">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Status Gantirugi
                    </label>

                    <select name="status_gantirugi"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                        required>
                        <option value="DALAM PROSES" {{ strtoupper($statusGantirugi) == 'DALAM PROSES' ? 'selected' : '' }}>
                            Dalam Proses
                        </option>
                        <option value="SELESAI" {{ strtoupper($statusGantirugi) == 'SELESAI' ? 'selected' : '' }}>
                            Selesai
                        </option>
                    </select>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium"
                        onclick="closeGantirugiEditModal('gantirugi-edit-modal-{{ $id }}')">
                        Batal
                    </button>

                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<style>
    .user-table {
        width: 100% !important;
        min-width: 1900px;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .user-table thead th {
        background: #f8f9fb;
        color: #344054;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .3px;
        padding: 14px 16px;
        border-bottom: 1px solid #e9ecef;
        white-space: nowrap;
        text-align: left;
        vertical-align: middle;
    }

    .user-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #eef1f4;
        vertical-align: middle;
        color: #101828;
    }

    .user-table tbody tr:hover td {
        background: #fafbfc;
    }

    .cell-text {
        line-height: 1.45;
        font-weight: 500;
        overflow-wrap: break-word;
        word-break: normal;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
    }

    .status-active {
        background: #ecfdf3;
        color: #027a48;
        border: 1px solid #abefc6;
    }

    .status-warning {
        background: #fffaeb;
        color: #b54708;
        border: 1px solid #fedf89;
    }

    .status-default {
        background: #f2f4f7;
        color: #344054;
        border: 1px solid #d0d5dd;
    }

    .action-btn-group {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e4e7ec;
        background: #fff;
        color: #344054;
        transition: all .2s ease;
        cursor: pointer;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    .info-btn {
        background: #f0f9ff;
        color: #026aa2;
        border-color: #bae6fd;
    }

    .edit-btn {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .gantirugi-modal {
        position: fixed;
        inset: 0;
        z-index: 999999;
    }

    .gantirugi-modal.hidden {
        display: none;
    }

    .gantirugi-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }

    .gantirugi-modal-card {
        position: relative;
        z-index: 1;
        width: min(1200px, calc(100% - 40px));
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        margin: 24px auto;
        background: #fff;
        border-radius: 18px;
        padding: 32px 36px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
    }

    .gantirugi-edit-card {
        width: min(650px, calc(100% - 40px));
    }

    .modal-close-btn {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        background: #f2f4f7;
        color: #344054;
        font-size: 26px;
        line-height: 1;
    }

    .detail-section {
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid #eef1f4;
    }

    .detail-section:first-of-type {
        margin-top: 0;
        padding-top: 0;
        border-top: none;
    }

    .detail-section h4 {
        font-size: 15px;
        font-weight: 800;
        color: #1d2939;
        margin-bottom: 14px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .detail-grid div {
        background: #f8fafc;
        border: 1px solid #eef1f4;
        border-radius: 12px;
        padding: 12px 14px;
    }

    .detail-grid span {
        display: block;
        color: #667085;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .detail-grid strong {
        display: block;
        color: #101828;
        font-size: 14px;
        font-weight: 700;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .detail-full {
        grid-column: span 2 / span 2;
    }

    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table {
        width: 100% !important;
    }

    .swal2-container {
        z-index: 9999999 !important;
        position: fixed !important;
        inset: 0 !important;
    }

    .swal2-backdrop-show {
        background: rgba(15, 23, 42, 0.65) !important;
    }

    .swal2-popup {
        z-index: 10000000 !important;
    }
</style>

@push('scripts')
<script>
function openGantirugiInfoModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeGantirugiInfoModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openGantirugiEditModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeGantirugiEditModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.gantirugi-modal:not(.hidden)').forEach(function (modal) {
            modal.classList.add('hidden');
        });

        document.body.classList.remove('overflow-hidden');
    }
});

$(document).ready(function () {
    const gantirugiTable = $('#gantirugiTable').DataTable({
        responsive: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,

        columns: [
            { width: '60px' },
            { width: '90px' },
            { width: '150px' },
            { width: '150px' },
            { width: '130px' },
            { width: '180px' },
            { width: '260px' },
            { width: '180px' },
            { width: '350px' },
            { width: '150px' },
            { width: '170px' },
            { width: '130px', orderable: false }
        ],

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            zeroRecords: "Tiada data dijumpai",
            info: "Paparan _START_ hingga _END_ daripada _TOTAL_ rekod",
            infoEmpty: "Tiada rekod",
            infoFiltered: "(ditapis daripada _MAX_ jumlah rekod)",
            paginate: {
                first: "Pertama",
                last: "Akhir",
                next: "Seterusnya",
                previous: "Sebelumnya"
            }
        },

        initComplete: function () {
            this.api().columns.adjust();
        }
    });

    setTimeout(function () {
        gantirugiTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        gantirugiTable.columns.adjust();
    });
});
</script>
@endpush

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Berjaya',
        text: @json(session('success')),
        confirmButtonText: 'OK',
        heightAuto: false,
        backdrop: true,
        allowOutsideClick: false
    });
});
</script>
@endif

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Ralat',
        text: @json(session('error')),
        confirmButtonText: 'OK',
        heightAuto: false,
        backdrop: true,
        allowOutsideClick: false
    });
});
</script>
@endif

@endsection