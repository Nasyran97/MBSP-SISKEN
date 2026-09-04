@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Tambah Pembekal' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Borang pendaftaran pembekal.
            </p>
        </div>

        <a href="{{ route('pembekal-list') }}"
            class="inline-flex items-center rounded-lg bg-gray-600 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-700">
            Senarai Pembekal
        </a>
    </div>

    <x-common.component-card title="Maklumat Pembekal">
        <form method="POST" action="{{ route('pembekal-store') }}">
            @csrf

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label class="form-label">
                        Nama Pembekal <span class="text-red-600">*</span>
                    </label>

                    <input type="text"
                        name="nama_pembekal"
                        value="{{ old('nama_pembekal') }}"
                        class="form-control uppercase"
                        placeholder="Contoh: RTG INTECH CORPORATION (M) SDN. BHD."
                        required>

                    @error('nama_pembekal')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-12">
                    <label class="form-label">
                        Alamat Pembekal
                    </label>

                    <textarea name="alamat_pembekal"
                        rows="4"
                        class="form-control textarea-control uppercase"
                        placeholder="Contoh: LOT 123, JALAN UTAMA, 14000 BUKIT MERTAJAM">{{ old('alamat_pembekal') }}</textarea>

                    @error('alamat_pembekal')
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

    .swal2-container {
        z-index: 9999999 !important;
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