@extends('layouts.app')

@section('content')
<div class="container">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Cipta kumpulan mekanik baharu mengikut kategori. Ahli boleh ditambah selepas kumpulan dicipta.
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

    <x-common.component-card title="Tambah Kumpulan Mekanik">
        <form method="POST" action="{{ route('mekanik-group-store') }}">
            @csrf

            <div class="mb-4">
                <label for="category_code" class="mb-2 block text-sm font-medium text-gray-700">
                    Kategori Mekanik
                </label>

                <select name="category_code" id="category_code"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                    required>
                    <option value="" selected disabled>-- Sila Pilih Kategori --</option>

                    @foreach($categories as $c)
                        @php
                            $code = $c->MEKANIK_CATEGORY ?? $c->mekanik_category ?? null;
                            $name = $c->NAMA_KATEGORI ?? $c->nama_kategori ?? $code;
                        @endphp

                        <option value="{{ $code }}" {{ old('category_code') == $code ? 'selected' : '' }}>
                            {{ $name }} ({{ $code }})
                        </option>
                    @endforeach
                </select>

                @error('category_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="group_code" class="mb-2 block text-sm font-medium text-gray-700">
                    Kod Kumpulan
                </label>

                <input type="text" name="group_code" id="group_code"
                    value="{{ old('group_code') }}"
                    placeholder="Cth: SERVICE-A"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                    required>

                @error('group_code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="group_name" class="mb-2 block text-sm font-medium text-gray-700">
                    Nama Kumpulan
                </label>

                <input type="text" name="group_name" id="group_name"
                    value="{{ old('group_name') }}"
                    placeholder="Cth: Team Service A"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                    required>

                @error('group_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="max_member" class="mb-2 block text-sm font-medium text-gray-700">
                    Bilangan Maksimum Ahli
                </label>

                <input type="number" name="max_member" id="max_member"
                    value="{{ old('max_member') }}"
                    min="1"
                    placeholder="Cth: 5"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                    required>

                <p class="mt-1 text-xs text-gray-500">
                    Panduan: Servis = 5 ahli, Breakdown = 2 ahli, Kimpalan = 3 ahli (termasuk ketua).
                </p>

                @error('max_member')
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
            new TomSelect('#category_code', {
                create: false,
                allowEmptyOption: true,
                placeholder: 'Cari kategori...',
                searchField: ['text'],
            });
        }
    });
</script>
@endsection
