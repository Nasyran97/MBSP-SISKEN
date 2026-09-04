@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai pengawas kenderaan.
            </p>
        </div>

        <a href="{{ route('pengawas-add') }}"
           class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600">
            Tambah Pengawas
        </a>
    </div>

    <x-common.component-card title="Senarai Pengawas">
        <div class="p-1 overflow-x-auto">
            <table id="pengawasTable" class="user-table pengawas-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>No Staff</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>No Plat</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($pengawas as $i => $p)
                        @php
                            $pengawasId = $p->NOSTAFF ?? $p->nostaff ?? null;

                            $status = strtoupper(trim($p->STATUS_DATA ?? $p->status_data ?? '-'));

                            $statusClass = match($status) {
                                'ACTIVE' => 'status-active',
                                'PENDING' => 'status-pending',
                                'DELETE', 'DELETED', 'INACTIVE' => 'status-delete',
                                default => 'status-default',
                            };
                        @endphp

                        <tr>
                            <td>{{ $i + 1 }}</td>

                            <td class="font-semibold">
                                {{ $p->NOSTAFF ?? $p->nostaff ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $p->NAMA ?? $p->nama ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $p->JABATAN ?? $p->jabatan ?? '-' }}
                            </td>

                            <td>
                                @php
                                    $vehicles = $p->VEHICLES ?? collect();
                                @endphp
                            
                                @forelse($vehicles as $vehicle)
                                    @php
                                        $noPlat = $vehicle->NO_PLAT ?? $vehicle->no_plat ?? '-';
                                        $vehicleStatus = strtoupper(trim($vehicle->STATUS_DATA ?? $vehicle->status_data ?? ''));
                                    @endphp
                            
                                    <span class="vehicle-badge {{ $vehicleStatus === 'ACTIVE' ? 'vehicle-active' : 'vehicle-inactive' }}">
                                        {{ $noPlat }}
                                    </span>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td class="text-center">
                                @php
                                    $pengawasId = $p->USER_ID ?? $p->user_id ?? null;
                                @endphp
                            
                                <div class="action-btn-group">
                                    @if($status === 'ACTIVE')
                                        <a href="{{ route('pengawas-edit', $pengawasId) }}"
                                           class="action-btn edit-btn"
                                           title="Edit Kenderaan">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                <path d="M13.93 6.93L17.07 10.07M4 20H7.14L16.3 10.84C16.69 10.45 16.69 9.82 16.3 9.43L14.57 7.7C14.18 7.31 13.55 7.31 13.16 7.7L4 16.86V20Z"
                                                      stroke="currentColor"
                                                      stroke-width="1.8"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round" />
                                            </svg>
                                        </a>
                            
                                        <form method="POST" action="{{ route('pengawas-delete', $pengawasId) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="action-btn delete-btn"
                                                    title="Delete"
                                                    onclick="return confirm('Adakah anda pasti mahu padam pengawas ini?')">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <path d="M10 11V17M14 11V17M5 7H19M17 7L16.47 19.13C16.43 20.03 15.69 20.75 14.79 20.75H9.21C8.31 20.75 7.57 20.03 7.53 19.13L7 7M9 7V4.75C9 4.34 9.34 4 9.75 4H14.25C14.66 4 15 4.34 15 4.75V7"
                                                          stroke="currentColor"
                                                          stroke-width="1.8"
                                                          stroke-linecap="round"
                                                          stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </form>
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

<style>
    .user-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .pengawas-table {
        min-width: 1350px;
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

    .pengawas-table th:nth-child(1),
    .pengawas-table td:nth-child(1) {
        width: 60px !important;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        white-space: nowrap;
    }

    .pengawas-table th:nth-child(2),
    .pengawas-table td:nth-child(2) {
        width: 120px !important;
        min-width: 120px;
        max-width: 120px;
        white-space: nowrap;
    }

    .pengawas-table th:nth-child(3),
    .pengawas-table td:nth-child(3) {
        width: 260px !important;
        min-width: 260px;
        max-width: 260px;
        white-space: normal;
    }

    .pengawas-table th:nth-child(4),
    .pengawas-table td:nth-child(4) {
        width: 320px !important;
        min-width: 320px;
        max-width: 320px;
        white-space: normal;
    }

    .pengawas-table th:nth-child(5),
    .pengawas-table td:nth-child(5) {
        width: 320px !important;
        min-width: 320px;
        max-width: 320px;
        white-space: normal;
    }

    .pengawas-table th:nth-child(6),
    .pengawas-table td:nth-child(6) {
        width: 130px !important;
        min-width: 130px;
        max-width: 130px;
        text-align: center;
        white-space: nowrap;
    }

    .pengawas-table th:nth-child(7),
    .pengawas-table td:nth-child(7) {
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

    .action-btn-group {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .action-btn-group form {
        margin: 0;
        display: inline-flex;
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
        padding: 0;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    .edit-btn {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .delete-btn {
        background: #fef3f2;
        color: #b42318;
        border-color: #fecdca;
    }

    .activate-btn {
        background: #ecfdf3;
        color: #027a48;
        border-color: #abefc6;
    }

    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table {
        width: 100% !important;
    }

    .dataTables_scrollHeadInner table.pengawas-table,
    .dataTables_scrollBody table.pengawas-table {
        min-width: 1350px !important;
    }

    .vehicle-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        margin: 2px 4px 2px 0;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .vehicle-active {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .vehicle-inactive {
        background: #f2f4f7;
        color: #344054;
        border: 1px solid #d0d5dd;
    }

    .text-muted {
        color: #98a2b3;
        font-size: 12px;
        font-weight: 600;
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
<script>
$(document).ready(function () {
    if (!document.querySelector('#pengawasTable')) return;

    const pengawasTable = $('#pengawasTable').DataTable({
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
            { width: '120px' },
            { width: '260px' },
            { width: '320px' },
            { width: '320px' },
            { width: '130px' },
            { width: '140px', orderable: false }
        ],

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            zeroRecords: "Tiada data dijumpai",
            emptyTable: "Tiada data pengawas.",
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
        pengawasTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        pengawasTable.columns.adjust();
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

@endsection