@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Senarai Lupus Kenderaan' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai rekod pelupusan kenderaan.
            </p>
        </div>

        <div>
            <a href="{{ route('lupus-car-export') }}"
                class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-emerald-700">
                Export Excel
            </a>
        </div>

        {{-- <div>
            <a href="{{ route('lupus-data-add') }}"
                class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                + Tambah Data
            </a>
        </div> --}}
    </div>

    <x-common.component-card title="Senarai Lupus Kenderaan">
        <div class="p-1 overflow-x-auto">
            <table id="lupusCarTable" class="user-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>No Plat</th>
                        <th>Buatan</th>
                        <th>Model</th>
                        <th>Tarikh Lelong</th>
                        <th>Tarikh Puspakom</th>
                        <th>Cukai Kastam</th>
                        <th>Tarikh Tukar Milik</th>
                        <th>Pembeli</th>
                        <th>Kaedah Jualan</th>
                        <th>Harga</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($lupusCars as $index => $row)
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
                            $noPlat = $get('NO_PLAT') ?? '-';
                            $buatan = $get('BUATAN') ?? '-';
                            $model = $get('MODEL') ?? '-';
                            $pembeli = $get('PEMBELI') ?? '-';
                            $kaedahJualan = $get('KAEDAH_JUALAN') ?? '-';
                            $cukaiKastam = $get('CUKAI_KASTAM') ?? '-';
                            $harga = $get('HARGA_JUALAN');
                
                            $tarikhLelongRaw = $get('TARIKH_LELONG');
                            $tarikhLelong = !empty($tarikhLelongRaw)
                                ? \Carbon\Carbon::parse($tarikhLelongRaw)->format('d/m/Y')
                                : '-';
                
                            $tarikhPuspakomRaw = $get('TARIKH_PUSPAKOM');
                            $tarikhPuspakom = !empty($tarikhPuspakomRaw)
                                ? \Carbon\Carbon::parse($tarikhPuspakomRaw)->format('d/m/Y')
                                : '-';
                
                            $tarikhTukarMilikRaw = $get('TARIKH_TUKAR_MILIK');
                            $tarikhTukarMilik = !empty($tarikhTukarMilikRaw)
                                ? \Carbon\Carbon::parse($tarikhTukarMilikRaw)->format('d/m/Y')
                                : '-';
                        @endphp
                
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                
                            <td class="font-semibold">
                                {{ $noPlat }}
                            </td>
                
                            <td>
                                {{ $buatan }}
                            </td>
                
                            <td>
                                {{ $model }}
                            </td>
                
                            <td>
                                {{ $tarikhLelong }}
                            </td>
                
                            <td>
                                {{ $tarikhPuspakom }}
                            </td>
                
                            <td>
                                <span class="status-badge {{ $cukaiKastam === 'YA' ? 'status-active' : 'status-default' }}">
                                    {{ $cukaiKastam }}
                                </span>
                            </td>
                
                            <td>
                                {{ $tarikhTukarMilik }}
                            </td>
                
                            <td class="cell-text">
                                {{ $pembeli }}
                            </td>
                
                            <td>
                                <span class="status-badge {{ $kaedahJualan === 'SCRAP' ? 'status-warning' : 'status-active' }}">
                                    {{ $kaedahJualan }}
                                </span>
                            </td>
                
                            <td class="text-right">
                                {{ $harga !== null ? 'RM ' . number_format((float)$harga, 2) : '-' }}
                            </td>
                
                            <td class="text-center">
                                <div class="action-btn-group">
                                    <button type="button"
                                        class="action-btn info-btn"
                                        onclick="openLupusInfoModal('lupus-info-modal-{{ $id }}')"
                                        title="Maklumat">
                                        ℹ️
                                    </button>
                
                                    @if($canEdit ?? false)
                                        <a href="{{ route('lupus-car-edit', $id) }}"
                                            class="action-btn edit-btn"
                                            title="Edit">
                                            ✏️
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>

{{-- MODAL INFO LUPUS --}}
@foreach($lupusCars as $row)
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
        $noPlat = $get('NO_PLAT') ?? '-';

        $formatDate = function ($value) {
            if (!$value) {
                return '-';
            }

            try {
                return \Carbon\Carbon::parse($value)->format('d/m/Y');
            } catch (\Exception $e) {
                return '-';
            }
        };
    @endphp

    <div id="lupus-info-modal-{{ $id }}" class="lupus-modal hidden">
        <div class="lupus-modal-overlay" onclick="closeLupusInfoModal('lupus-info-modal-{{ $id }}')"></div>

        <div class="lupus-modal-card">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Maklumat Lupus Kenderaan
                    </h3>
                    <p class="text-sm text-gray-500">
                        No Plat: {{ $noPlat }}
                    </p>
                </div>

                <button type="button"
                    class="modal-close-btn"
                    onclick="closeLupusInfoModal('lupus-info-modal-{{ $id }}')">
                    ×
                </button>
            </div>

            <div class="detail-section">
                <h4>Maklumat Kenderaan</h4>

                <div class="detail-grid">
                    <div><span>No Plat</span><strong>{{ $get('NO_PLAT') ?? '-' }}</strong></div>
                    <div><span>Pembeli</span><strong>{{ $get('PEMBELI') ?? '-' }}</strong></div>
                    <div><span>Buatan</span><strong>{{ $get('BUATAN') ?? '-' }}</strong></div>
                    <div><span>Model</span><strong>{{ $get('MODEL') ?? '-' }}</strong></div>
                    <div><span>Harga Jualan</span><strong>{{ $get('HARGA_JUALAN') !== null ? 'RM ' . number_format((float)$get('HARGA_JUALAN'), 2) : '-' }}</strong></div>
                    <div><span>Status</span><strong>{{ $get('STATUS_LUPUS') ?? '-' }}</strong></div>
                </div>
            </div>

            <div class="detail-section">
                <h4>Maklumat Pelupusan</h4>

                <div class="detail-grid">
                    <div><span>Tarikh Lupus</span><strong>{{ $formatDate($get('TARIKH_LUPUS')) }}</strong></div>
                    <div><span>Kaedah Jualan</span><strong>{{ $get('KAEDAH_JUALAN') ?? '-' }}</strong></div>
                    <div><span>Cukai Kastam</span><strong>{{ $get('CUKAI_KASTAM') ?? '-' }}</strong></div>
                    <div><span>Tarikh Lelong</span><strong>{{ $formatDate($get('TARIKH_LELONG')) }}</strong></div>
                    <div><span>Tarikh Tukar Milik</span><strong>{{ $formatDate($get('TARIKH_TUKAR_MILIK')) }}</strong></div>
                    <div><span>Tarikh Puspakom</span><strong>{{ $formatDate($get('TARIKH_PUSPAKOM')) }}</strong></div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<style>
    .user-table {
        width: 100% !important;
        min-width: 1800px;
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
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #fed7aa;
    }

    .status-default {
        background: #f2f4f7;
        color: #344054;
        border: 1px solid #d0d5dd;
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

    .lupus-modal {
        position: fixed;
        inset: 0;
        z-index: 999999;
    }

    .lupus-modal.hidden {
        display: none;
    }

    .lupus-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }

    .lupus-modal-card {
        position: relative;
        z-index: 1;
        width: min(1000px, calc(100% - 40px));
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        margin: 24px auto;
        background: #ffffff;
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
        border: none;
        cursor: pointer;
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

    .action-btn-group {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .edit-btn {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }
</style>

@push('scripts')
<script>
function openLupusInfoModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeLupusInfoModal(id) {
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
        document.querySelectorAll('.lupus-modal:not(.hidden)').forEach(function (modal) {
            modal.classList.add('hidden');
        });

        document.body.classList.remove('overflow-hidden');
    }
});

$(document).ready(function () {
    const lupusCarTable = $('#lupusCarTable').DataTable({
        responsive: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            emptyTable: "No data to display",
            zeroRecords: "No data to display",
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
        lupusCarTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        lupusCarTable.columns.adjust();
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