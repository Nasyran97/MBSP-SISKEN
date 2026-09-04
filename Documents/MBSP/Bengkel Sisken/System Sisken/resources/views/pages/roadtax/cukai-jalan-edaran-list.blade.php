@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">Senarai edaran cukai jalan kenderaan.</p>
        </div>
    </div>

    <x-common.component-card title="Senarai Maklumat Cukai Jalan">

        <form method="GET" action="{{ route('cukai-jalan-edaran-list') }}">
            <div class="grid grid-cols-12 gap-4 mb-6 items-end">
        
                <div class="col-span-12 md:col-span-5">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Jabatan
                    </label>
        
                    <select name="jabatan" class="form-input">
                        <option value="">-- Semua Jabatan --</option>
        
                        @foreach($departments as $dept)
                            <option value="{{ $dept->jabatan }}"
                                {{ request('jabatan') == $dept->jabatan ? 'selected' : '' }}>
                                {{ $dept->jabatan }}
                            </option>
                        @endforeach
                    </select>
                </div>
        
                <div class="col-span-12 md:col-span-5">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Bulan / Tarikh Tamat Sebelum
                    </label>
        
                    <input type="text"
                        name="tarikh_tamat"
                        value="{{ request('tarikh_tamat') }}"
                        class="date-picker form-input"
                        placeholder="Pilih tarikh">
                </div>
        
                <div class="col-span-12 md:col-span-2">
                    <button type="submit"
                        class="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-medium text-white hover:bg-blue-700">
                        Filter
                    </button>
                </div>
        
            </div>
        </form>

        {{-- Selected No Plat --}}
        <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
            <div class="text-sm font-semibold text-gray-700 mb-2">
                No Plat Dipilih:
            </div>
            <div id="selectedPlates" class="flex flex-wrap gap-2 text-sm text-gray-600">
                Tiada no plat dipilih.
            </div>
            <br>
            <form id="printSelectedForm" method="POST" action="{{ route('roadtax-edaran-pdf') }}" target="_blank">
                @csrf
            
                <input type="hidden" name="selected_vehicle_ids" id="selected_vehicle_ids">
                <input type="hidden" name="filter_jabatan" value="{{ request('jabatan') }}">
                <input type="hidden" name="filter_tarikh_tamat" value="{{ request('tarikh_tamat') }}">
            
                <button type="submit"
                    class="mb-2 rounded-lg bg-blue-600 px-5 py-3 text-sm font-medium text-white hover:bg-blue-700">
                    Cetak PDF
                </button>
            </form>
        </div>

        <div class="p-1 overflow-x-auto">
            <table id="cukaiJalanEdaranTable" class="user-table cukai-jalan-edaran-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>No Plat</th>
                        <th>Jenis Kenderaan</th>
                        <th>Jabatan</th>
                        <th>Lokasi Penempatan</th>
                        <th>Pengawas Kenderaan</th>
                        <th>Tarikh Mula Cukai Jalan</th>
                        <th>Tarikh Tamat Cukai Jalan</th>
                        <th>Tarikh Tamat Insurans</th>
                        <th>Status</th>
                        <th class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                <input type="checkbox" id="checkAllVehicles">
                                <span>Pilih</span>
                            </div>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($cars as $index => $car)
                        @php
                            $roadtaxStatus = strtoupper(trim($car->roadtax_status ?? '-'));
                
                            $statusClass = match($roadtaxStatus) {
                                'ACTIVE', 'AKTIF' => 'status-active',
                                'AKAN TAMAT TEMPOH' => 'status-warning',
                                'TAMAT TEMPOH' => 'status-expired',
                                'AKAN AKTIF' => 'status-upcoming',
                                default => 'status-default',
                            };
                
                            $noPlat = $car->no_plat ?? '-';
                        @endphp
                
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="font-semibold">{{ $noPlat }}</td>
                            <td>{{ $car->jenis_kenderaan ?? '-' }}</td>
                            <td>{{ $car->jabatan ?? '-' }}</td>
                            <td>{{ $car->lokasi_penempatan ?? '-' }}</td>
                            <td>{{ $car->pengawas_kenderaan ?? '-' }}</td>
                            <td>{{ !empty($car->tarikh_mula) ? \Carbon\Carbon::parse($car->tarikh_mula)->format('d/m/Y') : '-' }}</td>
                            <td>{{ !empty($car->tarikh_tamat) ? \Carbon\Carbon::parse($car->tarikh_tamat)->format('d/m/Y') : '-' }}</td>
                            <td>{{ !empty($car->tarikh_tamat_insurans) ? \Carbon\Carbon::parse($car->tarikh_tamat_insurans)->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $roadtaxStatus }}
                                </span>
                            </td>
                            <td class="text-center">
                                <input type="checkbox"
                                    class="vehicle-checkbox"
                                    name="vehicle_ids[]"
                                    value="{{ $car->id }}"
                                    data-plate="{{ $car->no_plat }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>

<style>
    .form-input {
        width: 100%;
        border-radius: 8px;
        border: 1px solid #d0d5dd;
        padding: 10px 12px;
        font-size: 13px;
    }

    .user-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .cukai-jalan-edaran-table {
        min-width: 2040px;
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

    .cukai-jalan-edaran-table th:nth-child(1),
    .cukai-jalan-edaran-table td:nth-child(1) {
        width: 60px !important;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        white-space: nowrap;
    }

    .cukai-jalan-edaran-table th:nth-child(2),
    .cukai-jalan-edaran-table td:nth-child(2) {
        width: 130px !important;
        min-width: 130px;
        max-width: 130px;
        white-space: nowrap;
        font-weight: 700;
    }

    .cukai-jalan-edaran-table th:nth-child(3),
    .cukai-jalan-edaran-table td:nth-child(3) {
        width: 190px !important;
        min-width: 190px;
        max-width: 190px;
        white-space: normal;
    }

    .cukai-jalan-edaran-table th:nth-child(4),
    .cukai-jalan-edaran-table td:nth-child(4) {
        width: 300px !important;
        min-width: 300px;
        max-width: 300px;
        white-space: normal;
    }

    .cukai-jalan-edaran-table th:nth-child(5),
    .cukai-jalan-edaran-table td:nth-child(5) {
        width: 300px !important;
        min-width: 300px;
        max-width: 300px;
        white-space: normal;
    }

    .cukai-jalan-edaran-table th:nth-child(6),
    .cukai-jalan-edaran-table td:nth-child(6) {
        width: 260px !important;
        min-width: 260px;
        max-width: 260px;
        white-space: normal;
    }

    .cukai-jalan-edaran-table th:nth-child(7),
    .cukai-jalan-edaran-table td:nth-child(7) {
        width: 190px !important;
        min-width: 190px;
        max-width: 190px;
        text-align: center;
        white-space: nowrap;
    }

    .cukai-jalan-edaran-table th:nth-child(8),
    .cukai-jalan-edaran-table td:nth-child(8) {
        width: 190px !important;
        min-width: 190px;
        max-width: 190px;
        text-align: center;
        white-space: nowrap;
    }

    .cukai-jalan-edaran-table th:nth-child(9),
    .cukai-jalan-edaran-table td:nth-child(9) {
        width: 170px !important;
        min-width: 170px;
        max-width: 170px;
        text-align: center;
        white-space: nowrap;
    }

    .cukai-jalan-edaran-table th:nth-child(10),
    .cukai-jalan-edaran-table td:nth-child(10) {
        width: 110px !important;
        min-width: 110px;
        max-width: 110px;
        text-align: center;
        white-space: nowrap;
    }

    .cukai-jalan-edaran-table th:nth-child(11),
    .cukai-jalan-edaran-table td:nth-child(11) {
        width: 110px !important;
        min-width: 110px;
        max-width: 110px;
        text-align: center;
        white-space: nowrap;
    }

    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table {
        width: 100% !important;
    }

    .dataTables_scrollHeadInner table.cukai-jalan-edaran-table,
    .dataTables_scrollBody table.cukai-jalan-edaran-table {
        min-width: 2040px !important;
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

    .status-expired {
        background: #fef3f2;
        color: #b42318;
        border: 1px solid #fecdca;
    }

    .status-upcoming {
        background: #eff6ff;
        color: #175cd3;
        border: 1px solid #b2ddff;
    }

    .status-default {
        background: #f2f4f7;
        color: #344054;
        border: 1px solid #d0d5dd;
    }

    .selected-plate-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        background: #eef4ff;
        color: #3538cd;
        border: 1px solid #c7d7fe;
        font-weight: 600;
    }
</style>

@push('scripts')
<script>
$(document).ready(function () {
    if (!document.querySelector('#cukaiJalanEdaranTable')) return;

    const cukaiJalanEdaranTable = $('#cukaiJalanEdaranTable').DataTable({
        responsive: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,

        columns: [
            { width: '60px' },                         // Bil
            { width: '130px' },                        // No Plat
            { width: '190px' },                        // Jenis Kenderaan
            { width: '300px' },                        // Jabatan
            { width: '300px' },                        // Lokasi Penempatan
            { width: '260px' },                        // Pengawas Kenderaan
            { width: '190px' },                        // Tarikh Mula
            { width: '190px' },                        // Tarikh Tamat
            { width: '190px' },                        // Tarikh Tamat
            { width: '170px' },                        // Status
            { width: '110px', orderable: false }       // Pilih
        ],

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            zeroRecords: "Tiada data dijumpai",
            emptyTable: "Tiada data cukai jalan.",
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
        cukaiJalanEdaranTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        cukaiJalanEdaranTable.columns.adjust();
    });

    if (typeof flatpickr !== 'undefined') {
        flatpickr(".date-picker", {
            dateFormat: "Y-m-d",
            allowInput: true
        });
    }

    updateSelectedPlates();
});

function updateSelectedPlates() {
    let selected = [];
    let selectedIds = [];

    $('.vehicle-checkbox:checked').each(function () {
        selected.push($(this).data('plate'));
        selectedIds.push($(this).val());
    });

    $('#selected_vehicle_ids').val(selectedIds.join(','));

    if (selected.length === 0) {
        $('#selectedPlates').html('Tiada no plat dipilih.');
        return;
    }

    let html = selected.map(function (plate) {
        return `<span class="selected-plate-badge">${plate}</span>`;
    }).join('');

    $('#selectedPlates').html(html);
}

$('#printSelectedForm').on('submit', function (e) {
    if (!$('#selected_vehicle_ids').val()) {
        e.preventDefault();
        alert('Sila pilih sekurang-kurangnya satu kenderaan.');
    }
});

$('#checkAllVehicles').on('change', function () {
    $('#cukaiJalanEdaranTable tbody .vehicle-checkbox')
        .prop('checked', $(this).is(':checked'));

    updateSelectedPlates();
});

// If user unchecks one row, uncheck Select All
$(document).on('change', '.vehicle-checkbox', function () {

    const total = $('.vehicle-checkbox').length;
    const checked = $('.vehicle-checkbox:checked').length;

    $('#checkAllVehicles').prop('checked', total === checked);

    updateSelectedPlates();
});
</script>
@endpush
@endsection