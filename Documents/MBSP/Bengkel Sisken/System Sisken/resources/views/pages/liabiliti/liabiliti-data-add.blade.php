@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Tambah Tuntutan Awam' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Borang pendaftaran tuntutan liabiliti awam.
            </p>
        </div>

        <div>
            <a href="{{ route('liabiliti-kes-list') }}"
                class="inline-flex items-center rounded-lg bg-gray-600 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-700">
                Senarai
            </a>
        </div>
    </div>

    <x-common.component-card title="Maklumat Tuntutan Awam">
        <form method="POST" action="{{ route('liabiliti-store-liabiliti') }}" id="liabilitiAwamForm" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-12 gap-4">

                <div class="col-span-3">
                    <label class="form-label">Tahun <span class="text-red-600">*</span></label>
                    <input type="number"
                        name="tahun"
                        value="{{ old('tahun', date('Y')) }}"
                        class="form-control"
                        placeholder="2026"
                        required>
                    @error('tahun') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-5">
                    <label class="form-label">No Plat / Harta Benda <span class="text-red-600">*</span></label>
                    <input type="text"
                        name="no_plat_harta_benda"
                        value="{{ old('no_plat_harta_benda') }}"
                        class="form-control uppercase"
                        placeholder="Contoh: PLV 6296 / Rumah / Pagar"
                        required>
                    @error('no_plat_harta_benda') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Tarikh Hantar Kes <span class="text-red-600">*</span></label>
                    <input type="text"
                        name="tarikh_hantar_kes"
                        value="{{ old('tarikh_hantar_kes') }}"
                        class="roadtax-date-picker form-control"
                        placeholder="Pilih tarikh"
                        required>
                    @error('tarikh_hantar_kes') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-6">
                    <label class="form-label">Jenis Tuntutan <span class="text-red-600">*</span></label>
                    <select name="tuntutan_awam_id" class="form-control" required>
                        <option value="" selected disabled>-- Sila Pilih --</option>
                        @foreach($jenisTuntutanAwam as $item)
                            @php
                                $id = $item->ID ?? $item->id;
                                $name = $item->TUNTUTAN_NAME ?? $item->tuntutan_name ?? '-';
                            @endphp

                            <option value="{{ $id }}" {{ old('tuntutan_awam_id') == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error('tuntutan_awam_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-6">
                    <label class="form-label">Nama Penuntut <span class="text-red-600">*</span></label>
                    <input type="text"
                        name="nama_penuntut"
                        value="{{ old('nama_penuntut') }}"
                        class="form-control uppercase"
                        placeholder="Nama penuntut"
                        required>
                    @error('nama_penuntut') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Tarikh Kejadian <span class="text-red-600">*</span></label>
                    <input type="text"
                        name="tarikh_kejadian"
                        value="{{ old('tarikh_kejadian') }}"
                        class="roadtax-date-picker form-control"
                        placeholder="Pilih tarikh"
                        required>
                    @error('tarikh_kejadian') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Amaun Tuntutan (RM) <span class="text-red-600">*</span></label>
                    <input type="number"
                        step="0.01"
                        min="0"
                        name="amaun_tuntutan"
                        value="{{ old('amaun_tuntutan') }}"
                        class="form-control"
                        placeholder="0.00"
                        required>
                    @error('amaun_tuntutan') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">No Telefon</label>
                    <input type="text"
                        name="no_telefon"
                        value="{{ old('no_telefon') }}"
                        class="form-control"
                        placeholder="Contoh: 0123456789">
                    @error('no_telefon') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Tarikh Offer Letter</label>
                    <input type="text"
                        name="tarikh_offer_letter"
                        value="{{ old('tarikh_offer_letter') }}"
                        class="roadtax-date-picker form-control"
                        placeholder="Pilih tarikh">
                    @error('tarikh_offer_letter') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-8">
                    <label class="form-label">Lokasi Kejadian / Alamat Rumah <span class="text-red-600">*</span></label>
                    <input type="text"
                        name="lokasi_kejadian"
                        value="{{ old('lokasi_kejadian') }}"
                        class="form-control"
                        placeholder="Masukkan lokasi kejadian atau alamat rumah"
                        required>
                    @error('lokasi_kejadian') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Jabatan --}}
                <div class="col-span-6">
                    <label for="department" class="form-label">
                        Jabatan <span class="text-red-600">*</span>
                    </label>

                    <select name="jabatan_kod"
                        id="department"
                        class="form-control"
                        required>
                        <option value="" selected disabled>-- Sila Pilih Jabatan --</option>

                        @foreach($departments as $dept)
                            <option 
                                value="{{ $dept['CODE'] ?? '' }}"
                                data-description="{{ $dept['DESCRIPTION'] ?? '' }}"
                                {{ old('jabatan_kod') == ($dept['CODE'] ?? '') ? 'selected' : '' }}>
                                {{ $dept['DESCRIPTION'] ?? '' }}
                            </option>
                        @endforeach
                    </select>

                    <input type="hidden"
                        name="jabatan_nama"
                        id="jabatan"
                        value="{{ old('jabatan_nama') }}">

                    @error('jabatan_kod')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-6">
                    <label for="department" class="form-label">
                        Bahagian <span class="text-red-600">*</span>
                    </label>
                
                    <select name="bahagian"
                        id="bahagian"
                        class="form-control"
                        required>
                        <option value="">-- Sila Pilih Bahagian --</option>
                
                        @foreach($lokasiPenempatan as $row)
                            @php
                                $lokasiId = $row->ID ?? $row->id ?? '';
                                $lokasiName = $row->LOKASI_PENEMPATAN_NAME ?? $row->lokasi_penempatan_name ?? '';
                            @endphp
                
                            <option value="{{ $lokasiId }}"
                                {{ old('bahagian') == $lokasiId ? 'selected' : '' }}>
                                {{ $lokasiName }}
                            </option>
                        @endforeach
                    </select>
                
                    @error('bahagian')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Daerah Kejadian <span class="text-red-600">*</span></label>
                    <select name="daerah_kejadian" class="form-control" required>
                        <option value="" selected disabled>-- Sila Pilih --</option>
                        <option value="SPU" {{ old('daerah_kejadian') == 'SPU' ? 'selected' : '' }}>SPU</option>
                        <option value="SPT" {{ old('daerah_kejadian') == 'SPT' ? 'selected' : '' }}>SPT</option>
                        <option value="SPS" {{ old('daerah_kejadian') == 'SPS' ? 'selected' : '' }}>SPS</option>
                    </select>
                    @error('daerah_kejadian') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Status Tuntutan <span class="text-red-600">*</span></label>
                    <select name="status_tuntutan" class="form-control" required>
                        <option value="" selected disabled>-- Sila Pilih --</option>
                        <option value="DALAM PROSES" {{ old('status_tuntutan') == 'DALAM PROSES' ? 'selected' : '' }}>Dalam Proses</option>
                        <option value="TINDAKAN ADJUSTER" {{ old('status_tuntutan') == 'TINDAKAN ADJUSTER' ? 'selected' : '' }}>Tindakan Adjuster</option>
                        <option value="SELESAI TANDATANGAN OFFER LETTER" {{ old('status_tuntutan') == 'SELESAI TANDATANGAN OFFER LETTER' ? 'selected' : '' }}>Selesai Tandatangan Offer Letter</option>
                    </select>
                    @error('status_tuntutan') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Keputusan Kes</label>
                    <select name="keputusan_kes" class="form-control">
                        <option value="" selected disabled>-- Sila Pilih --</option>
                        <option value="LULUS" {{ old('keputusan_kes') == 'LULUS' ? 'selected' : '' }}>Lulus</option>
                        <option value="DITOLAK" {{ old('keputusan_kes') == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    @error('keputusan_kes') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-12">
                    <label class="form-label">Laporan Adjuster / Insurans</label>
                    <textarea name="laporan_adjuster_insurans"
                        rows="5"
                        class="form-control textarea-control"
                        placeholder="Masukkan laporan adjuster / insurans">{{ old('laporan_adjuster_insurans') }}</textarea>
                    @error('laporan_adjuster_insurans') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-6">
                    <label class="form-label">
                        Lampiran Fail
                    </label>
                
                    <input type="file"
                        name="lampiran_file"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="form-control">
                
                    <p class="mt-1 text-xs text-gray-500">
                        Format dibenarkan: PDF, JPG, JPEG, PNG. Maksimum 5MB.
                    </p>
                
                    @error('lampiran_file')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="reset"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                    Reset
                </button>

                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
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

    .textarea-control {
        padding-top: 12px;
        min-height: 130px;
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

    .flatpickr-calendar {
        z-index: 1000001 !important;
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

    const departmentSelect = document.getElementById('department');
    const jabatanInput = document.getElementById('jabatan');

    function setJabatanNama() {
        if (!departmentSelect || !jabatanInput) return;

        const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];

        jabatanInput.value = selectedOption
            ? (selectedOption.dataset.description || '')
            : '';
    }

    if (departmentSelect) {
        departmentSelect.addEventListener('change', setJabatanNama);
        setJabatanNama();
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