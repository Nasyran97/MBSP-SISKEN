@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Tambah Jenis Tuntutan Awam' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Tambah jenis tuntutan awam baharu.
            </p>
        </div>

        <div>
            <a href="{{ route('tuntutan-awam-list') }}"
                class="inline-flex items-center rounded-lg bg-gray-600 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-700">
                Senarai
            </a>
        </div>
    </div>

    <x-common.component-card title="Maklumat Jenis Tuntutan Awam">
        <form method="POST" action="{{ route('tuntutan-awam-store') }}">
            @csrf

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Nama Jenis Tuntutan Awam <span class="text-red-600">*</span>
                    </label>

                    <input type="text"
                        name="tuntutan_name"
                        value="{{ old('tuntutan_name') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm uppercase focus:border-blue-500 focus:outline-none"
                        placeholder="Contoh: Tuntutan Pihak Ketiga"
                        required>

                    @error('tuntutan_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('tuntutan-awam-list') }}"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                    Batal
                </a>

                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                    Simpan
                </button>
            </div>
        </form>
    </x-common.component-card>
</div>

<style>
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