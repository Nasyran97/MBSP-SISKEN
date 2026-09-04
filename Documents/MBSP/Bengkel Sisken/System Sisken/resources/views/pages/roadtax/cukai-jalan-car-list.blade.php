@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">Senarai maklumat cukai jalan kenderaan.</p>
        </div>
    </div>

    <x-common.component-card title="Senarai Cukai Jalan">
        <div class="p-1 overflow-x-auto">
            <table id="cukaiCarTable" class="user-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>No Plat</th>
                        <th>Jabatan</th>
                        <th>Jenis Kenderaan</th>
                        <th>Lokasi Penempatan</th>
                        <th>Pengawas Kenderaan</th>
                        <th>Mula Cukai Jalan</th>
                        <th>Tamat Cukai Jalan</th>
                        <th>Status Pembaharuan Cukai Jalan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($cars as $index => $car)
                        @php
                            $status = strtoupper(trim($car->STATUS_PEMBAHARUAN_CUKAI_JALAN ?? $car->status_Pembaharuan_cukai_jalan ?? '-'));

                            $statusClass = match($status) {
                                'SELESAI', 'ACTIVE', 'AKTIF' => 'status-active',
                                'DALAM PROSES', 'PENDING' => 'status-pending',
                                'TIDAK AKTIF', 'DELETE', 'DELETED' => 'status-delete',
                                default => 'status-default',
                            };
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td class="font-semibold">
                                {{ $car->NO_PLAT ?? $car->no_plat ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $car->JABATAN ?? $car->jabatan ?? '-' }}
                            </td>

                            <td>
                                {{ $car->JENIS_KENDERAAN ?? $car->jenis_kenderaan ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $car->LOKASI_PENEMPATAN ?? $car->lokasi_penempatan ?? '-' }}
                            </td>

                            <td>
                                {{ $car->PENGAWAS_KENDERAAN ?? $car->pengawas_kenderaan ?? '-' }}
                            </td>

                            <td>
                                {{ !empty($car->tarikh_tamat) 
                                    ? \Carbon\Carbon::parse($car->tarikh_tamat)->format('d/m/Y') 
                                    : '-' 
                                }}
                            </td>

                            <td>
                                {{ !empty($car->tarikh_mula) 
                                    ? \Carbon\Carbon::parse($car->tarikh_mula)->format('d/m/Y') 
                                    : '-' 
                                }}
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-gray-500">
                                Tiada data cukai jalan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>

<style>
    .user-table {
        width: 100%;
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
        max-width: 260px;
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

    .status-pending {
        background: #fffaeb;
        color: #b54708;
        border: 1px solid #fedf89;
    }

    .status-delete {
        background: #fef3f2;
        color: #b42318;
        border: 1px solid #fecdca;
    }

    .status-default {
        background: #f2f4f7;
        color: #344054;
        border: 1px solid #d0d5dd;
    }
</style>

@push('scripts')
<script>
$(document).ready(function () {
    $('#cukaiCarTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        autoWidth: false,
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
        }
    });
});
</script>
@endpush
@endsection