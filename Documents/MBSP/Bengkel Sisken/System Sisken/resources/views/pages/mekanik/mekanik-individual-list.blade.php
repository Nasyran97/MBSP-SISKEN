@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai mekanik untuk kategori
                {{ $categoryNames->isNotEmpty() ? $categoryNames->implode(', ') : 'Maintenance' }}.
                Tugasan bagi kategori ini diberikan terus kepada seorang mekanik, bukan kumpulan.
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6">
        <x-common.component-card title="Tambah Mekanik Individu">
            <form method="POST" action="{{ route('mekanik-individual-add') }}">
                @csrf

                <div class="mb-4">
                    <label for="user_id" class="mb-2 block text-sm font-medium text-gray-700">
                        Pilih Pengguna
                    </label>

                    <select name="user_id[]" id="user_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                        multiple
                        required>

                        @foreach($availableUsers as $u)
                            @php
                                $userId = $u->ID ?? $u->id ?? null;
                                $nostaff = $u->NOSTAFF ?? $u->nostaff ?? '-';
                                $nama = $u->NAMA ?? $u->nama ?? '-';
                            @endphp

                            <option value="{{ $userId }}"
                                {{ collect(old('user_id', []))->map(fn($id) => (string) $id)->contains((string) $userId) ? 'selected' : '' }}>
                                {{ $nostaff }} - {{ $nama }}
                            </option>
                        @endforeach
                    </select>

                    <p class="mt-1 text-xs text-gray-500">
                        Hanya pengguna yang belum menjadi ahli kumpulan atau mekanik individu lain dipaparkan.
                    </p>

                    @error('user_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700">
                        Tambah Mekanik
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>

    <x-common.component-card title="Senarai Mekanik Individu">
        <div class="p-1 overflow-x-auto">
            <table id="mekanikIndividualTable" class="user-table mekanik-individual-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>No Staff</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($mekaniks as $i => $m)
                        @php
                            $poolId = $m->ID ?? $m->id ?? null;
                        @endphp

                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="font-semibold">{{ $m->NOSTAFF ?? $m->nostaff ?? '-' }}</td>
                            <td class="cell-text">{{ $m->NAMA ?? $m->nama ?? '-' }}</td>
                            <td class="cell-text">{{ $m->JABATAN ?? $m->jabatan ?? '-' }}</td>
                            <td class="text-center">
                                <div class="action-btn-group">
                                    <form method="POST" action="{{ route('mekanik-individual-remove', $poolId) }}">
                                        @csrf
                                        <button type="submit"
                                                class="action-btn delete-btn"
                                                title="Keluarkan Mekanik"
                                                onclick="return confirm('Adakah anda pasti mahu mengeluarkan mekanik ini?')">
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
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Tiada mekanik individu dijumpai.</td>
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
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .mekanik-individual-table {
        min-width: 800px;
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
        word-break: normal;
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

    .delete-btn {
        background: #fef3f2;
        color: #b42318;
        border-color: #fecdca;
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof TomSelect !== 'undefined') {
            const userSelectEl = document.getElementById('user_id');

            if (userSelectEl) {
                new TomSelect('#user_id', {
                    create: false,
                    allowEmptyOption: false,
                    placeholder: 'Cari dan pilih mekanik...',
                    maxOptions: 5000,
                    plugins: ['remove_button'],
                    searchField: ['text'],
                    sortField: {
                        field: 'text',
                        direction: 'asc'
                    }
                });
            }
        }
    });
</script>

@push('scripts')
<script>
$(document).ready(function () {
    if (!document.querySelector('#mekanikIndividualTable')) return;

    const mekanikIndividualTable = $('#mekanikIndividualTable').DataTable({
        responsive: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,

        columns: [
            { width: '60px', orderable: false, searchable: false },
            { width: '150px' },
            { width: '260px' },
            { width: '190px' },
            { width: '120px', orderable: false, searchable: false }
        ],

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            zeroRecords: "Tiada data dijumpai",
            emptyTable: "Tiada data mekanik individu.",
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
        mekanikIndividualTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        mekanikIndividualTable.columns.adjust();
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
