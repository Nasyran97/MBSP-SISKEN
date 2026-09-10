@extends('layouts.app')

@section('content')
<div class="container">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Sila pilih nama staf untuk didaftarkan sebagai pengawas.
            </p>
        </div>

        <a href="{{ route('pengawas-list') }}"
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

    <x-common.component-card title="Tambah Pengawas">
        <form method="POST" action="{{ route('pengawas-store') }}">
            @csrf
        
            {{-- Step 1: Pengawas --}}
            <div class="mb-4">
                <label for="user_id" class="mb-2 block text-sm font-medium text-gray-700">
                    Nama Pengguna / Pengawas
                </label>

                <select name="user_id" id="user_id"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                    required>
                    <option value="" selected disabled>-- Sila Pilih Pengawas --</option>

                    @foreach($engineeringUsers as $user)
                        @php    
                            $userId = $user->ID ?? $user->id ?? null;
                            $nostaff = $user->NOSTAFF ?? $user->nostaff ?? '-';
                            $name = $user->NAMA ?? $user->nama ?? $user->NAME ?? $user->name ?? '-';
                            $jabatan = $user->JABATAN ?? $user->jabatan ?? '-';
                        @endphp

                        <option value="{{ $userId }}"
                            data-nostaff="{{ $nostaff }}"
                            data-nama="{{ $name }}"
                            data-jabatan="{{ $jabatan }}"
                            {{ old('user_id') == $userId ? 'selected' : '' }}>
                            {{ $nostaff }} - {{ $name }}
                        </option>
                    @endforeach
                </select>

                @error('user_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    No Staff
                </label>

                <input type="text"
                    id="display_nostaff"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-100"
                    readonly>
            </div>

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Nama
                </label>

                <input type="text"
                    id="display_nama"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-100"
                    readonly>
            </div>

            <div class="mb-4">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Jabatan
                </label>

                <input type="text"
                    id="display_jabatan"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-100"
                    readonly>
            </div>
        
            {{-- Step 2: No Plat --}}
            <div class="mb-4">
                <label for="vehicle_id" class="mb-2 block text-sm font-medium text-gray-700">
                    No Plat
                </label>
        
                <select name="vehicle_id[]" id="vehicle_id"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                    multiple
                    required>
        
                    @foreach($vehicles as $v)
                        @php
                            $vehicleId = $v->ID ?? $v->id ?? null;
                            $noPlat = $v->NO_PLAT ?? $v->no_plat ?? '-';
                        @endphp
        
                        <option value="{{ $vehicleId }}"
                            {{ collect(old('vehicle_id', []))->map(fn($id) => (string) $id)->contains((string) $vehicleId) ? 'selected' : '' }}>
                            {{ $noPlat }}
                        </option>
                    @endforeach
                </select>
        
                @error('vehicle_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        
            <div class="mt-6 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </x-common.component-card>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const userSelect = document.getElementById('user_id');
        const nostaffInput = document.getElementById('display_nostaff');
        const namaInput = document.getElementById('display_nama');
        const jabatanInput = document.getElementById('display_jabatan');
    
        function updatePengawasInfo(value) {
            let selectedOption = null;
    
            if (value) {
                selectedOption = userSelect.querySelector('option[value="' + value + '"]');
            } else {
                selectedOption = userSelect.options[userSelect.selectedIndex];
            }
    
            if (!selectedOption || !selectedOption.value) {
                nostaffInput.value = '';
                namaInput.value = '';
                jabatanInput.value = '';
                return;
            }
    
            nostaffInput.value = selectedOption.dataset.nostaff || '';
            namaInput.value = selectedOption.dataset.nama || '';
            jabatanInput.value = selectedOption.dataset.jabatan || '';
        }
    
        if (typeof TomSelect !== 'undefined') {
            const userTom = new TomSelect('#user_id', {
                create: false,
                allowEmptyOption: true,
                placeholder: 'Cari nama pengawas...',
                maxOptions: 5000,
                searchField: ['text'],
                sortField: {
                    field: 'text',
                    direction: 'asc'
                },
                onChange: function (value) {
                    updatePengawasInfo(value);
                }
            });
    
            new TomSelect('#vehicle_id', {
                create: false,
                allowEmptyOption: false,
                placeholder: 'Cari dan pilih no plat...',
                maxOptions: 5000,
                plugins: ['remove_button'],
                searchField: ['text'],
                sortField: {
                    field: 'text',
                    direction: 'asc'
                }
            });
    
            updatePengawasInfo(userTom.getValue());
        } else {
            userSelect.addEventListener('change', function () {
                updatePengawasInfo(this.value);
            });
    
            updatePengawasInfo(userSelect.value);
        }
    });
</script>
@endsection