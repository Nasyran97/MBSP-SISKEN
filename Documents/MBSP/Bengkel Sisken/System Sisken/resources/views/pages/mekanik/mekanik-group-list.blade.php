@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai kumpulan mekanik mengikut kategori (Servis, Breakdown, Kimpalan).
            </p>
        </div>

        <a href="{{ route('mekanik-group-add') }}"
           class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600">
            Tambah Kumpulan
        </a>
    </div>

    <x-common.component-card title="Senarai Kumpulan Mekanik">
        <div class="p-1 overflow-x-auto">
            <table id="mekanikGroupTable" class="user-table mekanik-group-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>Kod Kumpulan</th>
                        <th>Nama Kumpulan</th>
                        <th>Kategori</th>
                        <th>Ketua</th>
                        <th>Bil Ahli</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($groups as $i => $g)
                        @php
                            $groupId = $g->ID ?? $g->id ?? null;
                            $status = strtoupper(trim($g->STATUS_DATA ?? $g->status_data ?? '-'));

                            $statusClass = match($status) {
                                'ACTIVE' => 'status-active',
                                'PENDING' => 'status-pending',
                                'DELETE', 'DELETED', 'INACTIVE' => 'status-delete',
                                default => 'status-default',
                            };

                            $memberCount = $g->MEMBER_COUNT ?? 0;
                            $maxMember = $g->MAX_MEMBER ?? 0;
                        @endphp

                        <tr>
                            <td>{{ $i + 1 }}</td>

                            <td class="font-semibold">
                                {{ $g->GROUP_CODE ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $g->GROUP_NAME ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $g->CATEGORY_NAME ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $g->LEADER_NAME ?? '-' }}
                            </td>

                            <td>
                                <span class="vehicle-badge {{ $memberCount >= $maxMember ? 'vehicle-inactive' : 'vehicle-active' }}">
                                    {{ $memberCount }} / {{ $maxMember }}
                                </span>
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                                    <a href="{{ route('mekanik-group-edit', $groupId) }}"
                                       class="action-btn edit-btn"
                                       title="Uruskan Ahli">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <path d="M13.93 6.93L17.07 10.07M4 20H7.14L16.3 10.84C16.69 10.45 16.69 9.82 16.3 9.43L14.57 7.7C14.18 7.31 13.55 7.31 13.16 7.7L4 16.86V20Z"
                                                  stroke="currentColor"
                                                  stroke-width="1.8"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round" />
                                        </svg>
                                    </a>

                                    <form method="POST" action="{{ route('mekanik-group-delete', $groupId) }}">
                                        @csrf
                                        <button type="submit"
                                                class="action-btn delete-btn"
                                                title="Delete"
                                                onclick="return confirm('Adakah anda pasti mahu padam kumpulan ini?')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                <path d="M10 11V17M14 11V17M5 7H19M17 7L16.47 19.13C16.43 20.03 15.69 20.75 14.79 20.75H9.21C8.31 20.75 7.57 20.03 7.53 19.13L7 7M9 7V4.75C9 4.34 9.34 4 9.75 4H14.25C14.66 4 15 4.34 15 4.75V7"
                                                      stroke="currentColor"
                                                      stroke-width="1.8"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </form>
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

    .mekanik-group-table {
        min-width: 1150px;
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

    .mekanik-group-table th:nth-child(1),
    .mekanik-group-table td:nth-child(1) {
        width: 60px !important;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        white-space: nowrap;
    }

    .mekanik-group-table th:nth-child(2),
    .mekanik-group-table td:nth-child(2) {
        width: 150px !important;
        min-width: 150px;
        max-width: 150px;
        white-space: nowrap;
    }

    .mekanik-group-table th:nth-child(3),
    .mekanik-group-table td:nth-child(3) {
        width: 240px !important;
        min-width: 240px;
        max-width: 240px;
        white-space: normal;
    }

    .mekanik-group-table th:nth-child(4),
    .mekanik-group-table td:nth-child(4) {
        width: 160px !important;
        min-width: 160px;
        max-width: 160px;
        white-space: normal;
    }

    .mekanik-group-table th:nth-child(5),
    .mekanik-group-table td:nth-child(5) {
        width: 200px !important;
        min-width: 200px;
        max-width: 200px;
        white-space: normal;
    }

    .mekanik-group-table th:nth-child(6),
    .mekanik-group-table td:nth-child(6) {
        width: 110px !important;
        min-width: 110px;
        max-width: 110px;
        text-align: center;
        white-space: nowrap;
    }

    .mekanik-group-table th:nth-child(7),
    .mekanik-group-table td:nth-child(7) {
        width: 110px !important;
        min-width: 110px;
        max-width: 110px;
        text-align: center;
        white-space: nowrap;
    }

    .mekanik-group-table th:nth-child(8),
    .mekanik-group-table td:nth-child(8) {
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

    .vehicle-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
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
    if (!document.querySelector('#mekanikGroupTable')) return;

    const mekanikGroupTable = $('#mekanikGroupTable').DataTable({
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
            { width: '150px' },
            { width: '240px' },
            { width: '160px' },
            { width: '200px' },
            { width: '110px' },
            { width: '110px' },
            { width: '140px', orderable: false }
        ],

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            zeroRecords: "Tiada data dijumpai",
            emptyTable: "Tiada data kumpulan mekanik.",
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
        mekanikGroupTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        mekanikGroupTable.columns.adjust();
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
