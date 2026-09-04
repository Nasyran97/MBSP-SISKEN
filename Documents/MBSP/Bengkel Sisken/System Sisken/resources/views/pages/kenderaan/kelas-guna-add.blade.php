@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Tambah Kelas Kegunaan' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Borang pendaftaran kelas kegunaan kenderaan.
            </p>
        </div>

        <div>
            <a href="{{ route('kelas-guna-list') }}"
                class="inline-flex items-center rounded-lg bg-gray-600 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-700">
                Senarai Kelas Kegunaan
            </a>
        </div>
    </div>

    <x-common.component-card title="Maklumat Kelas Kegunaan">
        <form method="POST" action="{{ route('kelas-guna-store') }}" id="kelasGunaForm">
            @csrf

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label class="form-label">
                        Kelas Kegunaan <span class="text-red-600">*</span>
                    </label>

                    <input type="text"
                        name="kelas_guna_name"
                        value="{{ old('kelas_guna_name') }}"
                        class="form-control uppercase"
                        placeholder="Contoh: BARANGAN - KUASA TEMPATAN"
                        required>

                    @error('kelas_guna_name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-gray-200 pt-6">
                <button type="reset"
                    class="px-6 py-3 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold">
                    Reset
                </button>

                <button type="submit"
                    class="px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                    Simpan
                </button>
            </div>
        </form>
    </x-common.component-card>
</div>

<style>
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