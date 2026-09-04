@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title ?? 'Tambah Panel Insurans' }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Sila masukkan nama panel insurans baharu.
            </p>
        </div>

        <a href="{{ route('insurans-panel-list') }}"
           class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
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

    <x-common.component-card title="Maklumat Panel Insurans">
        <form method="POST" action="{{ route('insurans-panel-store') }}">
            @csrf

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label for="panel_name" class="mb-2 block text-sm font-medium text-gray-700">
                        Nama Panel Insurans <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                        name="panel_name"
                        id="panel_name"
                        value="{{ old('panel_name') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                        placeholder="Contoh: Etiqa General Insurance Berhad"
                        required>

                    @error('panel_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('insurans-panel-list') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </a>

                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </x-common.component-card>
</div>

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Tidak Berjaya',
        text: @json(session('error')),
        confirmButtonText: 'OK'
    });
});
</script>
@endif

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

@endsection