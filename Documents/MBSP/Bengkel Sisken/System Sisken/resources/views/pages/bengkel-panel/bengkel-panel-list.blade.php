@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title ?? 'Senarai Bengkel Panel' }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai bengkel panel yang berdaftar dalam sistem.
            </p>
        </div>

        <a href="{{ route('bengkel-panel-add') }}"
        class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
            + Tambah Bengkel
        </a>
    </div>

    <x-common.component-card title="Senarai Bengkel Panel">
        <div class="p-1 overflow-x-auto">
            <table id="bengkelPanelTable" class="user-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>Kod</th>
                        <th>Nama Bengkel</th>
                        <th>Kategori</th>
                        <th>Servis</th>
                        <th>No. Tel</th>
                        <th>Bandar</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($panels as $index => $panel)
                        @php
                            $status = strtoupper(trim($panel->status_data ?? $panel->STATUS_DATA ?? ''));
                            $statusClass = match($status) {
                                'ACTIVE' => 'status-active',
                                'INACTIVE' => 'status-delete',
                                default => 'status-default',
                            };
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $panel->kod_panel ?? '-' }}</td>
                            <td class="cell-text">{{ $panel->nama_panel ?? '-' }}</td>
                            <td>{{ $panel->kategori ?? '-' }}</td>
                            <td>{{ $panel->servis ?? '-' }}</td>
                            <td>{{ $panel->no_tel ?? '-' }}</td>
                            <td>{{ $panel->bandar ?? '-' }}</td>
                            <td>
                                <span class="status-badge {{ $statusClass }}">{{ $status ?: '-' }}</span>
                            </td>
                            <td class="text-center">
                                <div class="action-btn-group">
                                    <form action="{{ route('bengkel-panel-delete', $panel->id) }}"
                                          method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="action-btn delete-btn"
                                                title="Buang"
                                                onclick="return confirm('Adakah anda pasti mahu membuang bengkel panel ini?')">
                                            🗑
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>

<style>
    .user-table {
        width: 100% !important;
        min-width: 900px;
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

    .status-active { background: #ecfdf3; color: #027a48; border: 1px solid #abefc6; }
    .status-delete { background: #fef3f2; color: #b42318; border: 1px solid #fecdca; }
    .status-default { background: #f2f4f7; color: #344054; border: 1px solid #d0d5dd; }

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

    .action-btn:hover { transform: translateY(-1px); }

    .delete-btn { background: #fef3f2; color: #b42318; border-color: #fecdca; }
</style>

@push('scripts')
<script>
$(document).ready(function () {
    const bengkelPanelTable = $('#bengkelPanelTable').DataTable({
        responsive: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,
        columnDefs: [{ targets: -1, orderable: false }],

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

    setTimeout(function () {
        bengkelPanelTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        bengkelPanelTable.columns.adjust();
    });

    // Re-align header/body when the sidebar is collapsed/expanded (container width changes)
    const tableWrapper = document.querySelector('#bengkelPanelTable').closest('.p-1');
    if (tableWrapper && window.ResizeObserver) {
        let rafId = null;
        new ResizeObserver(function () {
            cancelAnimationFrame(rafId);
            rafId = requestAnimationFrame(function () {
                bengkelPanelTable.columns.adjust();
            });
        }).observe(tableWrapper);
    }
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
