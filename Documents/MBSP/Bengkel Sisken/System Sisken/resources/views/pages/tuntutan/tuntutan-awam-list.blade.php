@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Senarai Jenis Tuntutan Awam' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai jenis tuntutan awam yang digunakan dalam modul insurans.
            </p>
        </div>

        <div>
            <a href="{{ route('tuntutan-awam-add') }}"
                class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                + Tambah Jenis Tuntutan
            </a>
        </div>
    </div>

    <x-common.component-card title="Senarai Jenis Tuntutan Awam">
        <div class="p-1">
            <table id="tuntutanAwamTable" class="user-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>Jenis Tuntutan Awam</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($tuntutanAwam as $index => $row)
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
                            $tuntutanName = $get('TUNTUTAN_NAME') ?? '-';
                            $createdBy = $get('CREATED_BY') ?? '-';
                            $createdAt = $get('CREATED_AT');
                            $statusData = strtoupper($get('STATUS_DATA') ?? 'ACTIVE');

                            $statusClass = match($statusData) {
                                'ACTIVE' => 'status-active',
                                'DELETE' => 'status-delete',
                                default => 'status-default',
                            };
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td class="font-semibold">
                                {{ $tuntutanName }}
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statusData }}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                            
                                    @if($statusData === 'ACTIVE')
                                        <button type="button"
                                            class="action-btn edit-btn"
                                            onclick="openTuntutanEditModal('tuntutan-edit-modal-{{ $id }}')"
                                            title="Edit">
                                            ✏️
                                        </button>
                            
                                        <form method="POST"
                                            action="{{ route('tuntutan-awam-delete', $id) }}"
                                            class="inline delete-form">
                                            @csrf
                                            @method('DELETE')
                            
                                            <button type="button"
                                                class="action-btn delete-btn"
                                                onclick="confirmDelete(this)"
                                                title="Padam">
                                                🗑️
                                            </button>
                                        </form>
                                    @elseif($statusData === 'DELETE')
                                        <form method="POST"
                                            action="{{ route('tuntutan-awam-activate', $id) }}"
                                            class="inline activate-form">
                                            @csrf
                                            @method('PATCH')
                            
                                            <button type="button"
                                                class="action-btn activate-btn"
                                                onclick="confirmActivate(this)"
                                                title="Aktifkan Semula">
                                                ✅
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                            
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-500">
                                Tiada data jenis tuntutan awam.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>

{{-- MODAL EDIT --}}
@foreach($tuntutanAwam as $row)
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
        $tuntutanName = $get('TUNTUTAN_NAME') ?? '';
    @endphp

    <div id="tuntutan-edit-modal-{{ $id }}" class="tuntutan-modal hidden">
        <div class="tuntutan-modal-overlay" onclick="closeTuntutanEditModal('tuntutan-edit-modal-{{ $id }}')"></div>

        <div class="tuntutan-modal-card">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Kemaskini Jenis Tuntutan Awam
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ $tuntutanName }}
                    </p>
                </div>

                <button type="button"
                    class="modal-close-btn"
                    onclick="closeTuntutanEditModal('tuntutan-edit-modal-{{ $id }}')">
                    ×
                </button>
            </div>

            <form method="POST" action="{{ route('tuntutan-awam-update', $id) }}">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Nama Jenis Tuntutan Awam
                    </label>

                    <input type="text"
                        name="tuntutan_name"
                        value="{{ $tuntutanName }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none"
                        required>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium"
                        onclick="closeTuntutanEditModal('tuntutan-edit-modal-{{ $id }}')">
                        Batal
                    </button>

                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<style>
    .user-table {
        width: 100% !important;
        min-width: 100% !important;
        table-layout: fixed;
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

    /* Bil */
    .user-table th:nth-child(1),
    .user-table td:nth-child(1) {
        width: 80px !important;
        text-align: center;
        white-space: nowrap;
    }

    /* Jenis Tuntutan Awam */
    .user-table th:nth-child(2),
    .user-table td:nth-child(2) {
        width: auto !important;
        white-space: normal;
    }

    /* Status */
    .user-table th:nth-child(3),
    .user-table td:nth-child(3) {
        width: 180px !important;
        text-align: center;
        white-space: nowrap;
    }

    /* Action */
    .user-table th:nth-child(4),
    .user-table td:nth-child(4) {
        width: 180px !important;
        text-align: center;
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

    .status-delete {
        background: #fef3f2;
        color: #b42318;
        border: 1px solid #fecdca;
    }

    .activate-btn {
        background: #ecfdf3;
        color: #027a48;
        border-color: #abefc6;
    }

    .text-muted {
        color: #98a2b3;
        font-size: 12px;
        font-weight: 600;
    }

    .tuntutan-modal {
        position: fixed;
        inset: 0;
        z-index: 999999;
    }

    .tuntutan-modal.hidden {
        display: none;
    }

    .tuntutan-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }

    .tuntutan-modal-card {
        position: relative;
        z-index: 1;
        width: min(650px, calc(100% - 40px));
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
function openTuntutanEditModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeTuntutanEditModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function confirmActivate(button) {
    const form = button.closest('form');

    Swal.fire({
        icon: 'question',
        title: 'Aktifkan semula rekod?',
        text: 'Rekod ini akan dipaparkan semula sebagai aktif.',
        showCancelButton: true,
        confirmButtonText: 'Ya, Aktifkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        heightAuto: false,
        backdrop: true,
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.tuntutan-modal:not(.hidden)').forEach(function (modal) {
            modal.classList.add('hidden');
        });

        document.body.classList.remove('overflow-hidden');
    }
});

function confirmDelete(button) {
    const form = button.closest('form');

    Swal.fire({
        icon: 'warning',
        title: 'Padam rekod?',
        text: 'Rekod ini akan dipadam daripada senarai aktif.',
        showCancelButton: true,
        confirmButtonText: 'Ya, Padam',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        heightAuto: false,
        backdrop: true,
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}

$(document).ready(function () {
    $('#tuntutanAwamTable').DataTable({
        responsive: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        autoWidth: false,
        scrollX: false,

        columns: [
            { width: '80px' },
            { width: null },
            { width: '180px' },
            { width: '180px', orderable: false }
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
        }
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