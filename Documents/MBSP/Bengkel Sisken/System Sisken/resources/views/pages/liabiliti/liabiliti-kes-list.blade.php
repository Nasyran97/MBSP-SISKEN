@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Senarai Kes Liabiliti Awam' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai rekod tuntutan liabiliti awam.
            </p>
        </div>

        <div>
            <a href="{{ route('liabiliti-data-add') }}"
                class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                + Tambah Data
            </a>
        </div>
    </div>

    <x-common.component-card title="Senarai Kes Liabiliti Awam">
        <div class="p-1 overflow-x-auto">
            <table id="liabilitiKesTable" class="user-table liabiliti-kes-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>Tahun</th>
                        <th>Tarikh Hantar Kes</th>
                        <th>No Plat / Harta Benda</th>
                        <th>Tuntutan</th>
                        <th>Tarikh Kejadian</th>
                        <th>Penuntut</th>
                        <th>Amaun Tuntutan</th>
                        <th>No Telefon</th>
                        <th>Offer Letter</th>
                        <th>Keputusan Kes</th>
                        <th>Fail</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($liabiliti as $index => $row)
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
                            $tahun = $get('TAHUN') ?? '-';
                            $tarikhHantarKes = $get('TARIKH_HANTAR_KES');
                            $noPlat = $get('NO_PLAT_HARTA_BENDA') ?? '-';
                            $tuntutan = $get('JENIS_TUNTUTAN') ?? '-';
                            $tarikhKejadian = $get('TARIKH_KEJADIAN');
                            $penuntut = $get('NAMA_PENUNTUT') ?? '-';
                            $amaunTuntutan = $get('AMAUN_TUNTUTAN');
                            $noTelefon = $get('NO_TELEFON') ?? '-';
                            $offerLetter = $get('TARIKH_OFFER_LETTER');
                            $keputusanKes = strtoupper(trim($get('KEPUTUSAN_KES') ?? ''));

                            $keputusanClass = match($keputusanKes) {
                                'LULUS' => 'status-active',
                                'DITOLAK' => 'status-expired',
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

                            <td class="font-semibold">
                                {{ $noPlat }}
                            </td>

                            <td class="cell-text">
                                {{ $tuntutan }}
                            </td>

                            <td>
                                {{ !empty($tarikhKejadian)
                                    ? \Carbon\Carbon::parse($tarikhKejadian)->format('d/m/Y')
                                    : '-'
                                }}
                            </td>

                            <td class="cell-text">
                                {{ $penuntut }}
                            </td>

                            <td class="text-right">
                                {{ $amaunTuntutan !== null ? 'RM ' . number_format((float)$amaunTuntutan, 2) : '-' }}
                            </td>

                            <td>
                                {{ $noTelefon }}
                            </td>

                            <td>
                                {{ !empty($offerLetter)
                                    ? \Carbon\Carbon::parse($offerLetter)->format('d/m/Y')
                                    : '-'
                                }}
                            </td>

                            <td>
                                @if($keputusanKes)
                                    <span class="status-badge {{ $keputusanClass }}">
                                        {{ $keputusanKes }}
                                    </span>
                                @else
                                    <span class="status-badge status-default">
                                        TIADA
                                    </span>
                                @endif
                            </td>

                            @php
                                $lampiranFile = $row->LAMPIRAN_FILE ?? $row->lampiran_file ?? null;
                            @endphp

                            <td class="text-center">
                                @if($lampiranFile)
                                    <a href="{{ route('liabiliti-kes-file', $id) }}"
                                        target="_blank"
                                        class="file-btn">
                                        Buka Fail
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                                    <button type="button"
                                        class="action-btn info-btn"
                                        onclick="openLiabilitiInfoModal('liabiliti-info-modal-{{ $id }}')"
                                        title="Maklumat">
                                        ℹ️
                                    </button>
                            
                                    <a href="{{ route('liabiliti-kes-edit', $id) }}"
                                        class="action-btn edit-btn"
                                        title="Edit">
                                        ✏️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center py-4 text-gray-500">
                                Tiada data kes liabiliti awam.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>

{{-- MODAL INFO LIABILITI --}}
@foreach($liabiliti as $row)
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
        $noPlat = $get('NO_PLAT_HARTA_BENDA') ?? '-';
        $tarikhHantarKes = $get('TARIKH_HANTAR_KES');
        $tarikhKejadian = $get('TARIKH_KEJADIAN');
        $tarikhOfferLetter = $get('TARIKH_OFFER_LETTER');
        $createdAt = $get('CREATED_AT');
        $updatedAt = $get('UPDATED_AT');
    @endphp

    <div id="liabiliti-info-modal-{{ $id }}" class="liabiliti-modal hidden">
        <div class="liabiliti-modal-overlay" onclick="closeLiabilitiInfoModal('liabiliti-info-modal-{{ $id }}')"></div>

        <div class="liabiliti-modal-card">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Maklumat Kes Liabiliti Awam
                    </h3>
                    <p class="text-sm text-gray-500">
                        No Plat / Harta Benda: {{ $noPlat }}
                    </p>
                </div>
            
                <div class="flex items-center gap-2">
                    <a href="{{ route('liabiliti-kes-print', $id) }}"
                        target="_blank"
                        class="print-btn"
                        title="Cetak PDF">
                        🖨️ Cetak
                    </a>
            
                    <button type="button"
                        class="modal-close-btn"
                        onclick="closeLiabilitiInfoModal('liabiliti-info-modal-{{ $id }}')">
                        ×
                    </button>
                </div>
            </div>

            <div class="detail-section">
                <h4>Maklumat Tuntutan</h4>
                <div class="detail-grid">
                    <div><span>Tahun</span><strong>{{ $get('TAHUN') ?? '-' }}</strong></div>
                    <div><span>No Plat / Harta Benda</span><strong>{{ $noPlat }}</strong></div>
                    <div><span>Tarikh Hantar Kes</span><strong>{{ !empty($tarikhHantarKes) ? \Carbon\Carbon::parse($tarikhHantarKes)->format('d/m/Y') : '-' }}</strong></div>
                    <div><span>Jenis Tuntutan</span><strong>{{ $get('JENIS_TUNTUTAN') ?? '-' }}</strong></div>
                    <div><span>Nama Penuntut</span><strong>{{ $get('NAMA_PENUNTUT') ?? '-' }}</strong></div>
                    <div><span>Tarikh Kejadian</span><strong>{{ !empty($tarikhKejadian) ? \Carbon\Carbon::parse($tarikhKejadian)->format('d/m/Y') : '-' }}</strong></div>
                    <div><span>Amaun Tuntutan</span><strong>RM {{ $get('AMAUN_TUNTUTAN') !== null ? number_format((float)$get('AMAUN_TUNTUTAN'), 2) : '0.00' }}</strong></div>
                    <div><span>No Telefon</span><strong>{{ $get('NO_TELEFON') ?? '-' }}</strong></div>
                    <div><span>Tarikh Offer Letter</span><strong>{{ !empty($tarikhOfferLetter) ? \Carbon\Carbon::parse($tarikhOfferLetter)->format('d/m/Y') : '-' }}</strong></div>
                    <div><span>Lokasi Kejadian / Alamat Rumah</span><strong>{{ $get('LOKASI_KEJADIAN') ?? '-' }}</strong></div>
                </div>
            </div>

            <div class="detail-section">
                <h4>Maklumat Jabatan</h4>
                <div class="detail-grid">
                    <div><span>Jabatan Kod</span><strong>{{ $get('JABATAN_KOD') ?? '-' }}</strong></div>
                    <div><span>Jabatan</span><strong>{{ $get('JABATAN_NAMA') ?? '-' }}</strong></div>
                    <div><span>Bahagian</span><strong>{{ $row->BAHAGIAN_NAME ?? $row->bahagian_name ?? $row->BAHAGIAN ?? $row->bahagian ?? '-' }}</strong></div>
                    <div><span>Daerah Kejadian</span><strong>{{ $get('DAERAH_KEJADIAN') ?? '-' }}</strong></div>
                </div>
            </div>

            <div class="detail-section">
                <h4>Status Kes</h4>
                <div class="detail-grid">
                    <div><span>Status Tuntutan</span><strong>{{ $get('STATUS_TUNTUTAN') ?? '-' }}</strong></div>
                    <div><span>Keputusan Kes</span><strong>{{ $get('KEPUTUSAN_KES') ?? '-' }}</strong></div>
                    <div class="detail-full"><span>Laporan Adjuster / Insurans</span><strong>{{ $get('LAPORAN_ADJUSTER_INSURANS') ?? '-' }}</strong></div>
                </div>
            </div>

            {{-- <div class="detail-section">
                <h4>Maklumat Sistem</h4>
                <div class="detail-grid">
                    <div><span>Created By</span><strong>{{ $get('CREATED_BY') ?? '-' }}</strong></div>
                    <div><span>Created At</span><strong>{{ !empty($createdAt) ? \Carbon\Carbon::parse($createdAt)->format('d/m/Y H:i') : '-' }}</strong></div>
                    <div><span>Updated By</span><strong>{{ $get('UPDATED_BY') ?? '-' }}</strong></div>
                    <div><span>Updated At</span><strong>{{ !empty($updatedAt) ? \Carbon\Carbon::parse($updatedAt)->format('d/m/Y H:i') : '-' }}</strong></div>
                </div>
            </div> --}}
        </div>
    </div>
@endforeach

<style>
    .user-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .liabiliti-kes-table {
        min-width: 2050px;
        table-layout: fixed;
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

    .liabiliti-kes-table th:nth-child(1),
    .liabiliti-kes-table td:nth-child(1) {
        width: 60px !important;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        white-space: nowrap;
    }

    .liabiliti-kes-table th:nth-child(2),
    .liabiliti-kes-table td:nth-child(2) {
        width: 90px !important;
        min-width: 90px;
        max-width: 90px;
        text-align: center;
        white-space: nowrap;
    }

    .liabiliti-kes-table th:nth-child(3),
    .liabiliti-kes-table td:nth-child(3) {
        width: 150px !important;
        min-width: 150px;
        max-width: 150px;
        white-space: nowrap;
    }

    .liabiliti-kes-table th:nth-child(4),
    .liabiliti-kes-table td:nth-child(4) {
        width: 200px !important;
        min-width: 200px;
        max-width: 200px;
        white-space: normal;
    }

    .liabiliti-kes-table th:nth-child(5),
    .liabiliti-kes-table td:nth-child(5) {
        width: 240px !important;
        min-width: 240px;
        max-width: 240px;
        white-space: normal;
    }

    .liabiliti-kes-table th:nth-child(6),
    .liabiliti-kes-table td:nth-child(6) {
        width: 150px !important;
        min-width: 150px;
        max-width: 150px;
        white-space: nowrap;
    }

    .liabiliti-kes-table th:nth-child(7),
    .liabiliti-kes-table td:nth-child(7) {
        width: 260px !important;
        min-width: 260px;
        max-width: 260px;
        white-space: normal;
    }

    .liabiliti-kes-table th:nth-child(8),
    .liabiliti-kes-table td:nth-child(8) {
        width: 170px !important;
        min-width: 170px;
        max-width: 170px;
        text-align: right;
        white-space: nowrap;
    }

    .liabiliti-kes-table th:nth-child(9),
    .liabiliti-kes-table td:nth-child(9) {
        width: 150px !important;
        min-width: 150px;
        max-width: 150px;
        white-space: nowrap;
    }

    .liabiliti-kes-table th:nth-child(10),
    .liabiliti-kes-table td:nth-child(10) {
        width: 150px !important;
        min-width: 150px;
        max-width: 150px;
        white-space: nowrap;
    }

    .liabiliti-kes-table th:nth-child(11),
    .liabiliti-kes-table td:nth-child(11) {
        width: 170px !important;
        min-width: 170px;
        max-width: 170px;
        text-align: center;
        white-space: nowrap;
    }

    .liabiliti-kes-table th:nth-child(12),
    .liabiliti-kes-table td:nth-child(12) {
        width: 120px !important;
        min-width: 120px;
        max-width: 120px;
        text-align: center;
        white-space: nowrap;
    }

    .liabiliti-kes-table th:nth-child(13),
    .liabiliti-kes-table td:nth-child(13) {
        width: 140px !important;
        min-width: 140px;
        max-width: 140px;
        text-align: center;
        white-space: nowrap;
    }

    .cell-text {
        line-height: 1.45;
        font-weight: 500;
        overflow-wrap: break-word;
        word-break: normal;
    }

    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table {
        width: 100% !important;
    }

    .dataTables_scrollHeadInner table.liabiliti-kes-table,
    .dataTables_scrollBody table.liabiliti-kes-table {
        min-width: 2050px !important;
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

    .status-expired {
        background: #fef3f2;
        color: #b42318;
        border: 1px solid #fecdca;
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
        text-decoration: none;
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

    .liabiliti-modal {
        position: fixed;
        inset: 0;
        z-index: 999999;
    }

    .liabiliti-modal.hidden {
        display: none;
    }

    .liabiliti-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }

    .liabiliti-modal-card {
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
        width: 100vw !important;
        height: 100vh !important;
    }

    .swal2-backdrop-show {
        background: rgba(15, 23, 42, 0.65) !important;
    }

    .swal2-popup {
        z-index: 10000000 !important;
    }

    .print-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        height: 38px;
        padding: 0 14px;
        border-radius: 10px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: all .2s ease;
    }

    .print-btn:hover {
        background: #dbeafe;
        color: #1e40af;
        transform: translateY(-1px);
    }

    .edit-btn {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .file-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 12px;
        border-radius: 8px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .file-btn:hover {
        background: #dbeafe;
    }
</style>

@push('scripts')
<script>
function openLiabilitiInfoModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeLiabilitiInfoModal(id) {
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
        document.querySelectorAll('.liabiliti-modal:not(.hidden)').forEach(function (modal) {
            modal.classList.add('hidden');
        });

        document.body.classList.remove('overflow-hidden');
    }
});

$(document).ready(function () {
    if (!document.querySelector('#liabilitiKesTable')) return;

    const liabilitiKesTable = $('#liabilitiKesTable').DataTable({
        responsive: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,

        columns: [
            { width: '60px' },                       // Bil
            { width: '90px' },                       // Tahun
            { width: '150px' },                      // Tarikh Hantar Kes
            { width: '200px' },                      // No Plat / Harta Benda
            { width: '240px' },                      // Tuntutan
            { width: '150px' },                      // Tarikh Kejadian
            { width: '260px' },                      // Penuntut
            { width: '170px' },                      // Amaun Tuntutan
            { width: '150px' },                      // No Telefon
            { width: '150px' },                      // Offer Letter
            { width: '170px' },                      // Keputusan Kes
            { width: '120px', orderable: false },    // Fail
            { width: '140px', orderable: false }     // Action
        ],

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            zeroRecords: "Tiada data dijumpai",
            emptyTable: "Tiada data liabiliti awam.",
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
        liabilitiKesTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        liabilitiKesTable.columns.adjust();
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