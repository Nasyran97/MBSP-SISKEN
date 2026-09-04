@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai maklumat semua pengguna sistem.
            </p>
        </div>

        <a href="{{ route('user-add') }}"
           class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
            + Tambah Pengguna
        </a>
    </div>

    <x-common.component-card title="Senarai Pengguna">
        <div class="p-1 overflow-x-auto">
            <table id="usersTable" class="user-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>No Staff</th>
                        <th>Nama</th>
                        <th>Jawatan</th>
                        <th>Jabatan</th>
                        <th>Email</th>
                        <th>Modul &amp; Role</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $index => $user)
                        @php
                            $nostaff = $user->NOSTAFF ?? $user->nostaff ?? '-';
                            $nama = $user->NAMA ?? $user->nama ?? '-';
                            $jawatan = $user->JAWATAN ?? $user->jawatan ?? '-';
                            $jabatan = $user->JABATAN ?? $user->jabatan ?? '-';
                            $email = $user->EMAIL ?? $user->email ?? '-';
                            $status = strtoupper(trim($user->STATUS_DATA ?? $user->status_data ?? ''));

                            $statusClass = match($status) {
                                'ACTIVE' => 'status-active',
                                'PENDING' => 'status-pending',
                                'DELETE', 'DELETED' => 'status-delete',
                                default => 'status-default',
                            };

                            $moduleRoleDisplay = $user->module_role_display ?? [];
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td class="font-semibold">
                                {{ $nostaff }}
                            </td>

                            <td class="cell-text">
                                {{ $nama !== '-' ? ucwords(strtolower($nama)) : '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $jawatan !== '-' ? ucwords(strtolower($jawatan)) : '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $jabatan !== '-' ? ucwords(strtolower($jabatan)) : '-' }}
                            </td>

                            <td>
                                @if($email !== '-')
                                    <a href="mailto:{{ $email }}" class="email-link">
                                        {{ strtolower($email) }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                @if(count($moduleRoleDisplay))
                                    <div class="module-role-list">
                                        @foreach($moduleRoleDisplay as $item)
                                            <div class="module-role-row">
                                                <span class="module-badge">
                                                    {{ strtoupper($item['module'] ?? '-') }}
                                                </span>
                                                <span class="role-text">
                                                    {{ $item['role'] ?? '-' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $status ?: '-' }}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                                    <a href="{{ route('user-edit', $user->ID ?? $user->id) }}"
                                       class="action-btn edit-btn"
                                       title="Edit">
                                        ✏️
                                    </a>

                                    @if($status === 'ACTIVE')
                                        <form action="{{ route('user-delete', $user->ID ?? $user->id) }}"
                                              method="POST"
                                              style="display:inline-block;">
                                            @csrf
                                            <button type="submit"
                                                    class="action-btn delete-btn"
                                                    title="Delete"
                                                    onclick="return confirm('Adakah anda pasti mahu padam pengguna ini?')">
                                                🗑️
                                            </button>
                                        </form>
                                    @elseif($status === 'DELETE' || $status === 'DELETED')
                                        <form action="{{ route('user-activate', $user->ID ?? $user->id) }}"
                                              method="POST"
                                              style="display:inline-block;">
                                            @csrf
                                            <button type="submit"
                                                    class="action-btn activate-btn"
                                                    title="Activate"
                                                    onclick="return confirm('Aktifkan semula pengguna ini?')">
                                                ↻
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-gray-500">
                                Tiada data pengguna.
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
        min-width: 1640px;
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
        width: 60px !important;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(2),
    .user-table td:nth-child(2) {
        width: 110px !important;
        min-width: 110px;
        max-width: 110px;
        white-space: nowrap;
    }

    .user-table th:nth-child(3),
    .user-table td:nth-child(3) {
        width: 220px !important;
        min-width: 220px;
        max-width: 220px;
        white-space: normal;
    }

    .user-table th:nth-child(4),
    .user-table td:nth-child(4) {
        width: 220px !important;
        min-width: 220px;
        max-width: 220px;
        white-space: normal;
    }

    .user-table th:nth-child(5),
    .user-table td:nth-child(5) {
        width: 260px !important;
        min-width: 260px;
        max-width: 260px;
        white-space: normal;
    }

    .user-table th:nth-child(6),
    .user-table td:nth-child(6) {
        width: 190px !important;
        min-width: 190px;
        max-width: 190px;
        white-space: nowrap;
    }

    .user-table th:nth-child(7),
    .user-table td:nth-child(7) {
        width: 320px !important;
        min-width: 320px;
        max-width: 320px;
        white-space: nowrap;
    }

    .user-table th:nth-child(8),
    .user-table td:nth-child(8) {
        width: 130px !important;
        min-width: 130px;
        max-width: 130px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(9),
    .user-table td:nth-child(9) {
        width: 130px !important;
        min-width: 130px;
        max-width: 130px;
        text-align: center;
        white-space: nowrap;
    }

    .cell-text {
        line-height: 1.45;
        font-weight: 500;
        overflow-wrap: break-word;
        word-break: normal;
    }

    .email-link {
        color: #155eef;
        font-size: 12px;
        text-decoration: none;
        white-space: nowrap;
    }

    .email-link:hover {
        text-decoration: underline;
    }

    .module-role-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        width: 100%;
    }

    .module-role-row {
        display: grid;
        grid-template-columns: 160px 1fr;
        align-items: center;
        gap: 12px;
        white-space: nowrap;
    }

    .module-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 160px;
        padding: 6px 12px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
    }

    .role-text {
        font-size: 13px;
        font-weight: 500;
        color: #101828;
        white-space: nowrap;
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
    const usersTable = $('#usersTable').DataTable({
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
            { width: '110px' },
            { width: '220px' },
            { width: '220px' },
            { width: '260px' },
            { width: '190px' },
            { width: '320px' },
            { width: '130px' },
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
        usersTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        usersTable.columns.adjust();
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