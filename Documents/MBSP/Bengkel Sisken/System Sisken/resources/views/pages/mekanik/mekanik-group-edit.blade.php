@extends('layouts.app')

@section('content')
<div class="container">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Kemaskini maklumat kumpulan dan uruskan ahli kumpulan mekanik.
            </p>
        </div>

        <a href="{{ route('mekanik-group-list') }}"
           class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600">
            Kembali
        </a>
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

    @php
        $groupId = $group->ID ?? null;
        $memberCount = $members->count();
        $maxMember = $group->MAX_MEMBER ?? 0;
        $slotsLeft = max($maxMember - $memberCount, 0);
    @endphp

    <div class="mb-6">
        <x-common.component-card title="Maklumat Kumpulan">
            <form method="POST" action="{{ route('mekanik-group-update', $groupId) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Kategori
                        </label>

                        <input type="text"
                               value="{{ $group->CATEGORY_NAME ?? '-' }}"
                               class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-100"
                               disabled>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">
                            Kod Kumpulan
                        </label>

                        <input type="text"
                               value="{{ $group->GROUP_CODE ?? '-' }}"
                               class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-100"
                               disabled>
                    </div>

                    <div>
                        <label for="group_name" class="mb-2 block text-sm font-medium text-gray-700">
                            Nama Kumpulan
                        </label>

                        <input type="text" name="group_name" id="group_name"
                            value="{{ old('group_name', $group->GROUP_NAME) }}"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                            required>

                        @error('group_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="max_member" class="mb-2 block text-sm font-medium text-gray-700">
                            Bilangan Maksimum Ahli
                        </label>

                        <input type="number" name="max_member" id="max_member"
                            value="{{ old('max_member', $group->MAX_MEMBER) }}"
                            min="1"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                            required>

                        @error('max_member')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700">
                        Simpan Maklumat Kumpulan
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>

    <div class="mb-6">
        <x-common.component-card title="Tambah Ahli">
            <form method="POST" action="{{ route('mekanik-group-member-add', $groupId) }}">
                @csrf

                <div class="mb-4">
                    <label for="user_id" class="mb-2 block text-sm font-medium text-gray-700">
                        Pilih Pengguna
                    </label>

                    <select name="user_id[]" id="user_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                        multiple
                        {{ $slotsLeft <= 0 ? 'disabled' : '' }}
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
                        Baki tempat kosong: {{ $slotsLeft }} daripada {{ $maxMember }}.
                    </p>

                    @error('user_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        {{ $slotsLeft <= 0 ? 'disabled' : '' }}>
                        Tambah Ahli
                    </button>
                </div>

                @if($slotsLeft <= 0)
                    <p class="mt-2 text-sm text-amber-600">
                        Kumpulan ini telah mencapai bilangan maksimum ahli. Naikkan had maksimum atau keluarkan ahli sedia ada untuk menambah ahli baharu.
                    </p>
                @endif
            </form>
        </x-common.component-card>
    </div>

    <x-common.component-card title="Senarai Ahli Kumpulan">
        <div class="p-1 overflow-x-auto">
            <table class="user-table member-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>No Staff</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Ketua</th>
                        <th>Tarikh Sertai</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($members as $i => $m)
                        @php
                            $memberId = $m->ID ?? $m->id ?? null;
                            $isLeader = strtoupper(trim($m->IS_LEADER ?? $m->is_leader ?? '')) === 'Y';
                            $joinedAt = $m->JOINED_AT ?? $m->joined_at ?? null;
                        @endphp

                        <tr>
                            <td>{{ $i + 1 }}</td>

                            <td class="font-semibold">
                                {{ $m->NOSTAFF ?? $m->nostaff ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $m->NAMA ?? $m->nama ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $m->JABATAN ?? $m->jabatan ?? '-' }}
                            </td>

                            <td>
                                @if($isLeader)
                                    <span class="status-badge status-active">KETUA</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="cell-text">
                                {{ $joinedAt ? \Illuminate\Support\Carbon::parse($joinedAt)->format('d/m/Y') : '-' }}
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                                    @if(!$isLeader)
                                        <form method="POST" action="{{ route('mekanik-group-member-set-leader', [$groupId, $memberId]) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="action-btn activate-btn"
                                                    title="Jadikan Ketua"
                                                    onclick="return confirm('Jadikan ahli ini sebagai ketua kumpulan?')">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <path d="M12 2L14.5 8.5L21 9L16 13.5L17.5 20L12 16.5L6.5 20L8 13.5L3 9L9.5 8.5L12 2Z"
                                                          stroke="currentColor"
                                                          stroke-width="1.6"
                                                          stroke-linecap="round"
                                                          stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('mekanik-group-member-remove', [$groupId, $memberId]) }}">
                                        @csrf
                                        <button type="submit"
                                                class="action-btn delete-btn"
                                                title="Keluarkan Ahli"
                                                onclick="return confirm('Adakah anda pasti mahu mengeluarkan ahli ini daripada kumpulan?')">
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
                            <td colspan="7" class="text-center text-muted">Tiada ahli dalam kumpulan ini.</td>
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

    .member-table {
        min-width: 900px;
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

    .activate-btn {
        background: #fffaeb;
        color: #b54708;
        border-color: #fedf89;
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof TomSelect !== 'undefined') {
            const userSelectEl = document.getElementById('user_id');

            if (userSelectEl && !userSelectEl.disabled) {
                new TomSelect('#user_id', {
                    create: false,
                    allowEmptyOption: false,
                    placeholder: 'Cari dan pilih ahli...',
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
