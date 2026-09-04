@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Tambah Lupus Kenderaan' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Borang pendaftaran maklumat pelupusan kenderaan.
            </p>
        </div>

        <div>
            <a href="{{ route('lupus-car-list') }}"
                class="inline-flex items-center rounded-lg bg-gray-600 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-700">
                Senarai Lupus
            </a>
        </div>
    </div>

    <x-common.component-card title="Maklumat Lupus Kenderaan">
        <form method="POST" action="{{ route('lupus-car-store') }}" id="lupusCarForm">
            @csrf

            <div class="form-section">
                <h4 class="section-title">Maklumat Kenderaan</h4>

                <div class="grid grid-cols-12 gap-4">

                    @php
                        $vehicleId = $vehicle->ID ?? $vehicle->id ?? null;
                        $noPlat = $vehicle->NO_PLAT ?? $vehicle->no_plat ?? '-';
                        $buatan = $vehicle->BUATAN ?? $vehicle->buatan ?? '-';
                        $model = $vehicle->MODEL ?? $vehicle->model ?? '-';
                    @endphp

                    <input type="hidden" name="vehicle_id" value="{{ $vehicleId }}">

                    <div class="col-span-6">
                        <label class="form-label">No Plat</label>
                        <input type="text"
                            value="{{ $noPlat }}"
                            class="form-control uppercase bg-gray-100"
                            readonly>
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Pembeli <span class="text-red-600">*</span></label>
                        <input type="text"
                            name="pembeli"
                            value="{{ old('pembeli') }}"
                            class="form-control uppercase"
                            placeholder="Nama pembeli"
                            required>
                        @error('pembeli') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Buatan</label>
                        <input type="text"
                            value="{{ $buatan }}"
                            class="form-control uppercase bg-gray-100"
                            readonly>
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Model</label>
                        <input type="text"
                            value="{{ $model }}"
                            class="form-control uppercase bg-gray-100"
                            readonly>
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Harga Jualan (RM) <span class="text-red-600">*</span></label>
                        <input type="number"
                            step="0.01"
                            min="0"
                            name="harga_jualan"
                            value="{{ old('harga_jualan') }}"
                            class="form-control"
                            placeholder="0.00"
                            required>
                        @error('harga_jualan') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Status <span class="text-red-600">*</span></label>
                        <select name="status_lupus" class="form-control" required>
                            <option value="" selected disabled>-- Sila Pilih --</option>
                            <option value="AKTIF" {{ old('status_lupus') == 'AKTIF' ? 'selected' : '' }}>Aktif</option>
                            <option value="TAK AKTIF" {{ old('status_lupus') == 'TAK AKTIF' ? 'selected' : '' }}>Tak Aktif</option>
                        </select>
                        @error('status_lupus') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                </div>
            </div>

            <div class="form-section mt-6">
                <h4 class="section-title">Maklumat Pelupusan</h4>

                <div class="grid grid-cols-12 gap-4">

                    <div class="col-span-6">
                        <label class="form-label">Tarikh Lupus</label>
                        <input type="text"
                            name="tarikh_lupus"
                            value="{{ old('tarikh_lupus') }}"
                            class="roadtax-date-picker form-control"
                            placeholder="Pilih tarikh">
                        @error('tarikh_lupus') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Kaedah Jualan <span class="text-red-600">*</span></label>
                        <select name="kaedah_jualan" class="form-control" required>
                            <option value="" selected disabled>-- Sila Pilih --</option>
                            <option value="TUKAR NAMA" {{ old('kaedah_jualan') == 'TUKAR NAMA' ? 'selected' : '' }}>Tukar Nama</option>
                            <option value="SCRAP" {{ old('kaedah_jualan') == 'SCRAP' ? 'selected' : '' }}>Scrap</option>
                        </select>
                        @error('kaedah_jualan') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Cukai Kastam <span class="text-red-600">*</span></label>
                        <select name="cukai_kastam" class="form-control" required>
                            <option value="" selected disabled>-- Sila Pilih --</option>
                            <option value="YA" {{ old('cukai_kastam') == 'YA' ? 'selected' : '' }}>Ya</option>
                            <option value="TIDAK" {{ old('cukai_kastam') == 'TIDAK' ? 'selected' : '' }}>Tidak</option>
                        </select>
                        @error('cukai_kastam') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Tarikh Lelong</label>
                        <input type="text"
                            name="tarikh_lelong"
                            value="{{ old('tarikh_lelong') }}"
                            class="roadtax-date-picker form-control"
                            placeholder="Pilih tarikh">
                        @error('tarikh_lelong') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Tarikh Tukar Milik</label>
                        <input type="text"
                            name="tarikh_tukar_milik"
                            value="{{ old('tarikh_tukar_milik') }}"
                            class="roadtax-date-picker form-control"
                            placeholder="Pilih tarikh">
                        @error('tarikh_tukar_milik') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Tarikh Puspakom</label>
                        <input type="text"
                            name="tarikh_puspakom"
                            value="{{ old('tarikh_puspakom') }}"
                            class="roadtax-date-picker form-control"
                            placeholder="Pilih tarikh">
                        @error('tarikh_puspakom') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

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
    .form-section {
        border: 1px solid #e4e7ec;
        border-radius: 16px;
        padding: 20px;
        background: #fff;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #101828;
        margin-bottom: 16px;
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

    .flatpickr-calendar {
        z-index: 1000001 !important;
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

    @media (max-width: 768px) {
        .grid.grid-cols-12 > div {
            grid-column: span 12 / span 12 !important;
        }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof flatpickr !== 'undefined') {
        flatpickr(".roadtax-date-picker", {
            dateFormat: "Y-m-d",
            allowInput: true,
            position: "above",
            appendTo: document.body
        });
    }
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