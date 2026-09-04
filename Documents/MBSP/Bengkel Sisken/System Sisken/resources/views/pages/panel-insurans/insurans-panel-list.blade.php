@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title ?? 'Senarai Panel Insurans' }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai panel insurans yang berdaftar dalam sistem.
            </p>
        </div>

        <a href="{{ route('insurans-panel-add') }}"
        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
            + Tambah Panel
        </a>
       
    </div>

    <x-common.component-card title="Senarai Panel Insurans">
        <div class="p-1 overflow-x-auto">
            <table id="insuransPanelTable" class="user-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>Nama Panel</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($panels as $index => $panel)
                        @php
                            $id = $panel->ID ?? $panel->id;
                            $panelName = $panel->PANEL_NAME ?? $panel->panel_name ?? '-';
                            $status = strtoupper(trim($panel->STATUS_DATA ?? $panel->status_data ?? ''));

                            $statusClass = match($status) {
                                'ACTIVE' => 'status-active',
                                'INACTIVE' => 'status-delete',
                                'DELETE', 'DELETED' => 'status-delete',
                                default => 'status-default',
                            };
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td class="cell-text">
                                {{ $panelName }}
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $status ?: '-' }}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                                    @if($status === 'ACTIVE')
                                        <form action="{{ route('insurans-panel-deactivate', $id) }}"
                                              method="POST"
                                              style="display:inline-block;">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="action-btn delete-btn"
                                                    title="Deactivate"
                                                    onclick="return confirm('Adakah anda pasti mahu nyahaktifkan panel ini?')">
                                                ⛔
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('insurans-panel-activate', $id) }}"
                                              method="POST"
                                              style="display:inline-block;">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="action-btn activate-btn"
                                                    title="Activate"
                                                    onclick="return confirm('Aktifkan semula panel ini?')">
                                                ↻
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-500">
                                Tiada data panel insurans.
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
        width: 100% !important;
        min-width: 650px;
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

    .user-table th:nth-child(1),
    .user-table td:nth-child(1) {
        width: 70px !important;
        min-width: 70px;
        max-width: 70px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(2),
    .user-table td:nth-child(2) {
        width: 330px !important;
        min-width: 330px;
        max-width: 330px;
        white-space: normal;
    }

    .user-table th:nth-child(3),
    .user-table td:nth-child(3) {
        width: 130px !important;
        min-width: 130px;
        max-width: 130px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(4),
    .user-table td:nth-child(4) {
        width: 120px !important;
        min-width: 120px;
        max-width: 120px;
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
</style>

@push('scripts')
<script>
$(document).ready(function () {
    const insuransPanelTable = $('#insuransPanelTable').DataTable({
        responsive: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,

        columns: [
            { width: '70px' },
            { width: '330px' },
            { width: '130px' },
            { width: '120px', orderable: false }
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
        insuransPanelTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        insuransPanelTable.columns.adjust();
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