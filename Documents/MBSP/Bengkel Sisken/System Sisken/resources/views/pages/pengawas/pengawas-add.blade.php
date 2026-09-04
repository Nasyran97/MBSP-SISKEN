@extends('layouts.app')

@section('content')
<div class="container">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Sila pilih jabatan dan nama staf untuk didaftarkan sebagai pengawas.
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
                            $name = $user->NAME ?? $user->name ?? '-';
                            $jabatan = $user->JABATAN ?? $user->jabatan ?? '-';
                        @endphp
        
                        <option value="{{ $userId }}"
                            {{ old('user_id') == $userId ? 'selected' : '' }}>
                            {{ $name }} - {{ $jabatan }}
                        </option>
                    @endforeach
                </select>
        
                @error('user_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
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
        if (typeof TomSelect !== 'undefined') {
            new TomSelect('#user_id', {
                create: false,
                allowEmptyOption: true,
                placeholder: 'Cari nama pengawas...',
                maxOptions: 5000,
                searchField: ['text'],
                sortField: {
                    field: 'text',
                    direction: 'asc'
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
        }
    });
    </script>
@endsection