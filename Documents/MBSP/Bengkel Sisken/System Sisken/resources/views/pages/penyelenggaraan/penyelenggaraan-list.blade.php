@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai rekod permohonan penyelenggaraan kenderaan.
            </p>
        </div>
        
        <a href="{{ route('penyelenggaraan-add') }}"
           class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600">
            + Permohonan Penyelenggaraan
        </a>
    </div>

    <x-common.component-card title="Senarai Penyelenggaraan">
        <div class="p-1 overflow-x-auto">
            <table id="penyelenggaraanTable" class="user-table penyelenggaraan-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>No Plat</th>
                        <th>PIC</th>
                        <th>No Kerja</th>
                        <th>Model Kenderaan</th>
                        <th>Jenis Kenderaan</th>
                        <th>Odometer</th>
                        <th>Kategori</th>
                        <th>Remark</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($penyelenggaraan as $i => $row)
                        @php
                            $id = $row->ID ?? $row->id ?? null;

                            $pic = $row->PIC ?? $row->pic ?? '-';
                            $picNoStaff = $row->PIC_NOSTAFF ?? $row->pic_nostaff ?? null;

                            $noPlat = $row->NO_PLAT ?? $row->no_plat ?? '-';
                            $noKerja = $row->NO_KERJA ?? $row->no_kerja ?? '-';

                            $buatan = $row->BUATAN ?? $row->buatan ?? '';
                            $model = $row->MODEL ?? $row->model ?? '-';
                            $modelKenderaan = trim($buatan . ' ' . $model);

                            $jenisKenderaan = $row->JENIS_KENDERAAN ?? $row->jenis_kenderaan ?? '-';
                            $odometer = $row->ODOMETER ?? $row->odometer ?? null;
                            $kategori = $row->REPAIR_CATEGORY ?? $row->repair_category ?? '-';
                            $remark = $row->REPAIR_REMARK ?? $row->repair_remark ?? '-';

                            $status = strtoupper(trim($row->STATUS ?? $row->status ?? 'PENDING'));

                            $statusClass = match($status) {
                                'PENDING' => 'status-pending',
                                'IN PROGRESS', 'IN_PROGRESS', 'DALAM PROSES' => 'status-progress',
                                'COMPLETED', 'COMPLETE', 'SELESAI' => 'status-completed',
                                'CANCELLED', 'CANCELED', 'BATAL' => 'status-cancelled',
                                default => 'status-default',
                            };
                        @endphp

                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>

                            <td class="font-semibold">
                                {{ $noPlat }}
                            </td>

                            <td class="cell-text">
                                <div class="font-semibold">
                                    {{ $pic }}
                                </div>
                            
                                @if($picNoStaff)
                                    <div class="text-xs text-gray-500">
                                        {{ $picNoStaff }}
                                    </div>
                                @endif
                            </td>

                            <td class="font-semibold">
                                {{ $noKerja }}
                            </td>

                            <td class="cell-text">
                                {{ $modelKenderaan ?: '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $jenisKenderaan }}
                            </td>

                            <td class="text-right">
                                {{ $odometer !== null ? number_format((float) $odometer, 0) . ' KM' : '-' }}
                            </td>

                            <td>
                                <span class="category-badge">
                                    {{ $kategori }}
                                </span>
                            </td>

                            <td class="cell-text">
                                {{ \Illuminate\Support\Str::limit($remark, 80) }}
                            </td>

                            <td class="text-center">
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                                    <button type="button"
                                            class="action-btn info-btn"
                                            title="Maklumat"
                                            onclick="openPenyelenggaraanModal('penyelenggaraan-modal-{{ $id }}')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 17V11M12 7H12.01M21 12A9 9 0 1 1 3 12A9 9 0 0 1 21 12Z"
                                                  stroke="currentColor"
                                                  stroke-width="1.8"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round" />
                                        </svg>
                                    </button>

                                    {{-- nanti aktifkan bila edit page siap --}}
                                    {{--
                                    <a href="{{ route('penyelenggaraan-edit', $id) }}"
                                       class="action-btn edit-btn"
                                       title="Edit">
                                        Edit
                                    </a>
                                    --}}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-common.component-card>

    @foreach($penyelenggaraan as $row)
        @php
            $id = $row->ID ?? $row->id ?? null;

            $pic = $row->PIC ?? $row->pic ?? '-';
            $picNoStaff = $row->PIC_NOSTAFF ?? $row->pic_nostaff ?? null;

            $driverName = $row->DRIVER_NAME ?? $row->driver_name ?? '-';
            $driverNoStaff = $row->DRIVER_NOSTAFF ?? $row->driver_nostaff ?? null;
            $driverPhoneNum = $row->DRIVER_PHONE_NUM ?? $row->driver_phone_num ?? '-';

            $noPlat = $row->NO_PLAT ?? $row->no_plat ?? '-';
            $noKerja = $row->NO_KERJA ?? $row->no_kerja ?? '-';

            $lokasiPenempatan = $row->LOKASI_PENEMPATAN ?? $row->lokasi_penempatan ?? '-';
            $buatan = $row->BUATAN ?? $row->buatan ?? '';
            $model = $row->MODEL ?? $row->model ?? '-';
            $modelKenderaan = trim($buatan . ' ' . $model);

            $jenisKenderaan = $row->JENIS_KENDERAAN ?? $row->jenis_kenderaan ?? '-';
            $odometer = $row->ODOMETER ?? $row->odometer ?? null;

            $jkjgName = $row->JKJG_NAME ?? $row->jkjg_name ?? '-';
            $jkjgNoStaff = $row->JKJG_NOSTAFF ?? $row->jkjg_nostaff ?? null;

            $kategori = $row->REPAIR_CATEGORY ?? $row->repair_category ?? '-';
            $repairCategory = strtoupper(trim($kategori));

            $remark = $row->REPAIR_REMARK ?? $row->repair_remark ?? '-';
            $status = strtoupper(trim($row->STATUS ?? $row->status ?? 'PENDING'));

            $locationLatitude = $row->LOCATION_LATITUDE ?? $row->location_latitude ?? null;
            $locationLongitude = $row->LOCATION_LONGITUDE ?? $row->location_longitude ?? null;

            $locationCoordinate = (
                $locationLatitude !== null && $locationLatitude !== '' &&
                $locationLongitude !== null && $locationLongitude !== ''
            )
                ? '(' . $locationLatitude . ', ' . $locationLongitude . ')'
                : '-';

            $locationName = $row->LOCATION_NAME ?? $row->location_name ?? '-';
            $createdAt = $row->CREATED_AT ?? $row->created_at ?? null;

            $hasBreakdownLocation = (
                $repairCategory === 'BREAKDOWN' &&
                $locationLatitude !== null && $locationLatitude !== '' &&
                $locationLongitude !== null && $locationLongitude !== ''
            );

            $formatDate = function ($date) {
                if (!$date) return '-';

                try {
                    return \Carbon\Carbon::parse($date)->format('d/m/Y H:i');
                } catch (\Exception $e) {
                    return '-';
                }
            };
        @endphp

        <div id="penyelenggaraan-modal-{{ $id }}" class="penyelenggaraan-modal hidden">
            <div class="penyelenggaraan-modal-overlay" onclick="closePenyelenggaraanModal('penyelenggaraan-modal-{{ $id }}')"></div>

            <div class="penyelenggaraan-modal-card">
                <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            Maklumat Penyelenggaraan
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ $noPlat }} / {{ $noKerja }}
                        </p>
                    </div>

                    <button type="button"
                            class="modal-close-btn"
                            onclick="closePenyelenggaraanModal('penyelenggaraan-modal-{{ $id }}')">
                        ×
                    </button>
                </div>

                <div class="detail-grid">
                    <div>
                        <span>No Plat</span>
                        <strong>{{ $noPlat }}</strong>
                    </div>

                    <div>
                        <span>Model Kenderaan</span>
                        <strong>{{ $modelKenderaan ?: '-' }}</strong>
                    </div>

                    <div>
                        <span>Jenis Kenderaan</span>
                        <strong>{{ $jenisKenderaan }}</strong>
                    </div>

                    <div>
                        <span>Lokasi Penempatan</span>
                        <strong>{{ $lokasiPenempatan ?: '-' }}</strong>
                    </div>

                    <div>
                        <span>Nama Pemandu</span>
                        <strong>{{ $driverNoStaff }} - {{ $driverName }}</strong>
                    </div>

                    <div>
                        <span>No Telefon Pemandu</span>
                        <strong>{{ $driverPhoneNum ?: '-' }}</strong>
                    </div>

                    <div>
                        <span>No Kerja</span>
                        <strong>{{ $noKerja }}</strong>
                    </div>

                    <div>
                        <span>PIC</span>
                        <strong>{{ $picNoStaff }} - {{ $pic }}</strong>
                    </div>

                    <div>
                        <span>JKJG</span>
                        <strong>{{ $jkjgNoStaff }} - {{ $jkjgName }}</strong>
                    </div>

                    <div>
                        <span>Odometer</span>
                        <strong>{{ $odometer !== null ? number_format((float) $odometer, 0) . ' KM' : '-' }}</strong>
                    </div>

                    <div>
                        <span>Kategori</span>
                        <strong>{{ $kategori }}</strong>
                    </div>

                    <div>
                        <span>Status</span>
                        <strong>{{ $status }}</strong>
                    </div>

                    <div>
                        <span>Tarikh Daftar</span>
                        <strong>{{ $formatDate($createdAt) }}</strong>
                    </div>

                    <div>
                        <span>Koordinat Lokasi</span>
                        <strong>{{ $locationCoordinate ?: '-' }}</strong>
                    </div>

                    <div>
                        <span>Nama Lokasi</span>
                        <strong>{{ $locationName ?: '-' }}</strong>
                    </div>

                    @if($hasBreakdownLocation)
                        <div class="detail-full">
                            <span>Peta Lokasi Breakdown</span>

                            <div
                                id="breakdown-map-{{ $id }}"
                                class="breakdown-modal-map"
                                data-lat="{{ $locationLatitude }}"
                                data-lng="{{ $locationLongitude }}"
                                data-title="{{ $locationName ?: 'Lokasi Breakdown' }}">
                            </div>
                        </div>
                    @endif

                    <div class="detail-full">
                        <span>Catatan / Remark</span>
                        <strong>{{ $remark }}</strong>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<style>
    .user-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .penyelenggaraan-table {
        min-width: 1750px;
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

    .penyelenggaraan-table th:nth-child(1),
    .penyelenggaraan-table td:nth-child(1) {
        width: 60px !important;
        text-align: center;
    }

    .penyelenggaraan-table th:nth-child(2),
    .penyelenggaraan-table td:nth-child(2) {
        width: 130px !important;
    }

    .penyelenggaraan-table th:nth-child(3),
    .penyelenggaraan-table td:nth-child(3) {
        width: 140px !important;
    }

    .penyelenggaraan-table th:nth-child(4),
    .penyelenggaraan-table td:nth-child(4) {
        width: 220px !important;
    }

    .penyelenggaraan-table th:nth-child(5),
    .penyelenggaraan-table td:nth-child(5) {
        width: 200px !important;
    }

    .penyelenggaraan-table th:nth-child(6),
    .penyelenggaraan-table td:nth-child(6) {
        width: 130px !important;
    }

    .penyelenggaraan-table th:nth-child(7),
    .penyelenggaraan-table td:nth-child(7) {
        width: 180px !important;
    }

    .penyelenggaraan-table th:nth-child(8),
    .penyelenggaraan-table td:nth-child(8) {
        width: 300px !important;
        white-space: normal;
    }

    .penyelenggaraan-table th:nth-child(9),
    .penyelenggaraan-table td:nth-child(9) {
        width: 150px !important;
        text-align: center;
    }

    .penyelenggaraan-table th:nth-child(10),
    .penyelenggaraan-table td:nth-child(10) {
        width: 120px !important;
        text-align: center;
    }

    .penyelenggaraan-table th:nth-child(11),
    .penyelenggaraan-table td:nth-child(11) {
        width: 120px !important;
        text-align: center;
    }

    .cell-text {
        line-height: 1.45;
        font-weight: 500;
        overflow-wrap: break-word;
        word-break: normal;
    }

    .status-badge,
    .category-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
    }

    .category-badge {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .status-pending {
        background: #fffaeb;
        color: #b54708;
        border: 1px solid #fedf89;
    }

    .status-progress {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .status-completed {
        background: #ecfdf3;
        color: #027a48;
        border: 1px solid #abefc6;
    }

    .status-cancelled {
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
        border: 1px solid transparent;
        transition: all .2s ease;
        cursor: pointer;
        text-decoration: none;
        padding: 0;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    .info-btn {
        background: #f0f9ff;
        color: #026aa2;
        border-color: #b9e6fe;
    }

    .edit-btn {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .penyelenggaraan-modal {
        position: fixed;
        inset: 0;
        z-index: 999999;
    }

    .penyelenggaraan-modal.hidden {
        display: none;
    }

    .penyelenggaraan-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }

    .penyelenggaraan-modal-card {
        position: relative;
        z-index: 1;
        width: min(1000px, calc(100% - 32px));
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        margin: 24px auto;
        background: #fff;
        border-radius: 18px;
        padding: 24px;
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

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .detail-grid > div {
        background: #f8fafc;
        border: 1px solid #eef1f4;
        border-radius: 12px;
        padding: 12px 14px;
    }

    .detail-grid > div > span {
        display: block;
        color: #667085;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .detail-grid > div > strong {
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

    .dataTables_scrollHeadInner table.penyelenggaraan-table,
    .dataTables_scrollBody table.penyelenggaraan-table {
        min-width: 1750px !important;
    }

    .breakdown-modal-map {
        width: 100%;
        height: 360px;
        border-radius: 16px;
        border: 1px solid #d0d5dd;
        overflow: hidden;
        background: #f2f4f7;
        margin-top: 8px;
    }

    .detail-grid > div.breakdown-modal-map,
    .breakdown-modal-map {
        background: #f2f4f7 !important;
        padding: 0 !important;
    }

    .breakdown-modal-map .leaflet-pane,
    .breakdown-modal-map .leaflet-tile,
    .breakdown-modal-map .leaflet-marker-icon,
    .breakdown-modal-map .leaflet-marker-shadow,
    .breakdown-modal-map .leaflet-popup,
    .breakdown-modal-map .leaflet-control {
        padding: 0 !important;
        border: 0 !important;
        background: transparent;
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
</style>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const breakdownModalMaps = {};

function openPenyelenggaraanModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(function () {
        initBreakdownModalMap(modal);
    }, 250);
}

function initBreakdownModalMap(modal) {
    const mapEl = modal.querySelector('.breakdown-modal-map');

    if (!mapEl) {
        return;
    }

    const mapId = mapEl.id;
    const lat = parseFloat(mapEl.dataset.lat);
    const lng = parseFloat(mapEl.dataset.lng);
    const title = mapEl.dataset.title || 'Lokasi Breakdown';

    if (Number.isNaN(lat) || Number.isNaN(lng)) {
        console.error('Invalid breakdown coordinate:', mapEl.dataset.lat, mapEl.dataset.lng);
        return;
    }

    if (breakdownModalMaps[mapId]) {
        breakdownModalMaps[mapId].invalidateSize();
        breakdownModalMaps[mapId].setView([lat, lng], 16);
        return;
    }

    const map = L.map(mapId).setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    L.marker([lat, lng])
        .addTo(map)
        .bindPopup(title)
        .openPopup();

    breakdownModalMaps[mapId] = map;

    setTimeout(function () {
        map.invalidateSize();
    }, 300);
}

function closePenyelenggaraanModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');

    const openedModal = document.querySelector('.penyelenggaraan-modal:not(.hidden)');

    if (!openedModal) {
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.penyelenggaraan-modal').forEach(function (modal) {
            modal.classList.add('hidden');
        });

        document.body.style.overflow = '';
    }
});

$(document).ready(function () {
    if (!document.querySelector('#penyelenggaraanTable')) return;

    const penyelenggaraanTable = $('#penyelenggaraanTable').DataTable({
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
            { width: '130px' },
            { width: '240px' },
            { width: '140px' },
            { width: '220px' },
            { width: '200px' },
            { width: '130px' },
            { width: '180px' },
            { width: '300px' },
            { width: '150px' },
            { width: '120px', orderable: false }
        ],

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            zeroRecords: "Tiada data dijumpai",
            emptyTable: "Tiada data penyelenggaraan.",
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
        penyelenggaraanTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        penyelenggaraanTable.columns.adjust();
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
        confirmButtonText: 'OK'
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
        confirmButtonText: 'OK'
    });
});
</script>
@endif

@endsection