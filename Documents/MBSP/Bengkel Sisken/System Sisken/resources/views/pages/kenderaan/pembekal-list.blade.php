@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Senarai Pembekal' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai nama dan alamat pembekal.
            </p>
        </div>

        <a href="{{ route('pembekal-add') }}"
            class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
            + Tambah Pembekal
        </a>
    </div>

    <x-common.component-card title="Senarai Pembekal">
        <div class="p-1 overflow-x-auto">
            <table id="pembekalTable" class="user-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>Nama Pembekal</th>
                        <th>Alamat Pembekal</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($pembekal as $index => $row)
                        @php
                            $id = $row->ID ?? $row->id ?? null;
                            $namaPembekal = $row->NAMA_PEMBEKAL ?? $row->nama_pembekal ?? '-';
                            $alamatPembekal = $row->ALAMAT_PEMBEKAL ?? $row->alamat_pembekal ?? '-';
                            $status = $row->STATUS_DATA ?? $row->status_data ?? 'ACTIVE';
                        @endphp

                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>

                            <td class="font-semibold">
                                {{ $namaPembekal }}
                            </td>

                            <td class="cell-text">
                                {{ $alamatPembekal ?: '-' }}
                            </td>

                            <td class="text-center">
                                <span class="status-badge {{ strtoupper($status) === 'ACTIVE' ? 'status-active' : 'status-default' }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                                    <button type="button"
                                        class="action-btn edit-btn"
                                        onclick="openEditPembekalModal('edit-pembekal-modal-{{ $id }}')"
                                        title="Edit">
                                        ✏️
                                    </button>

                                    <form method="POST"
                                        action="{{ route('pembekal-delete', $id) }}"
                                        class="delete-form"
                                        style="display:inline;">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="action-btn delete-btn"
                                            title="Padam">
                                            🗑️
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

@foreach($pembekal as $row)
    @php
        $id = $row->ID ?? $row->id ?? null;
        $namaPembekal = $row->NAMA_PEMBEKAL ?? $row->nama_pembekal ?? '-';
        $alamatPembekal = $row->ALAMAT_PEMBEKAL ?? $row->alamat_pembekal ?? '';
    @endphp

    <div id="edit-pembekal-modal-{{ $id }}" class="master-modal hidden">
        <div class="master-modal-overlay" onclick="closeEditPembekalModal('edit-pembekal-modal-{{ $id }}')"></div>

        <div class="master-modal-card">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Kemaskini Pembekal
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ $namaPembekal }}
                    </p>
                </div>

                <button type="button"
                    class="modal-close-btn"
                    onclick="closeEditPembekalModal('edit-pembekal-modal-{{ $id }}')">
                    ×
                </button>
            </div>

            <form method="POST" action="{{ route('pembekal-update', $id) }}">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label class="form-label">
                        Nama Pembekal <span class="text-red-600">*</span>
                    </label>

                    <input type="text"
                        name="nama_pembekal"
                        value="{{ old('nama_pembekal', $namaPembekal) }}"
                        class="form-control uppercase"
                        required>

                    @error('nama_pembekal')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">
                        Alamat Pembekal
                    </label>

                    <textarea name="alamat_pembekal"
                        rows="4"
                        class="form-control textarea-control uppercase">{{ old('alamat_pembekal', $alamatPembekal) }}</textarea>

                    @error('alamat_pembekal')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-5">
                    <button type="button"
                        class="px-6 py-3 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold"
                        onclick="closeEditPembekalModal('edit-pembekal-modal-{{ $id }}')">
                        Batal
                    </button>

                    <button type="submit"
                        class="px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                        Kemaskini
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<style>
    .user-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .pembekal-table {
        min-width: 1200px;
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
        width: 80px !important;
        text-align: center;
    }

    .user-table th:nth-child(2),
    .user-table td:nth-child(2) {
        width: 320px !important;
    }

    .user-table th:nth-child(4),
    .user-table td:nth-child(4) {
        width: 150px !important;
        text-align: center;
    }

    .user-table th:nth-child(5),
    .user-table td:nth-child(5) {
        width: 180px !important;
        text-align: center;
    }

    .cell-text {
        white-space: normal;
        overflow-wrap: break-word;
        line-height: 1.45;
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
        text-decoration: none;
        cursor: pointer;
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

    .master-modal {
        position: fixed;
        inset: 0;
        z-index: 999999;
    }

    .master-modal.hidden {
        display: none;
    }

    .master-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }

    .master-modal-card {
        position: relative;
        z-index: 1;
        width: min(780px, calc(100% - 40px));
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

    .form-label {
        margin-bottom: 8px;
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #344054;
    }

    .form-control {
        width: 100%;
        min-height: 48px;
        border: 1px solid #d0d5dd;
        border-radius: 10px;
        padding: 0 14px;
        font-size: 14px;
        background: #fff;
        color: #101828;
        outline: none;
    }

    .textarea-control {
        padding-top: 12px;
        resize: vertical;
    }

    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .form-error {
        margin-top: 6px;
        color: #dc2626;
        font-size: 13px;
        font-weight: 600;
    }

    .uppercase {
        text-transform: uppercase;
    }
</style>

@push('scripts')
<script>
function openEditPembekalModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeEditPembekalModal(id) {
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
        document.querySelectorAll('.master-modal:not(.hidden)').forEach(function (modal) {
            modal.classList.add('hidden');
        });

        document.body.classList.remove('overflow-hidden');
    }
});

document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('#pembekalTable').DataTable({
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            ordering: true,
            searching: true,
            autoWidth: false,
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
            }
        });
    }

    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Padam rekod?',
                text: 'Rekod pembekal ini akan dipadam.',
                showCancelButton: true,
                confirmButtonText: 'Ya, padam',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                heightAuto: false
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
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
        heightAuto: false
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
        heightAuto: false
    });
});
</script>
@endif

@endsection