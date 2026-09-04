@extends('layouts.app')

@section('content')
@php
    $rowArray = (array) $kes;

    $normalized = [];
    foreach ($rowArray as $key => $value) {
        $normalized[strtoupper($key)] = $value;
    }

    $get = function (...$keys) use ($normalized) {
        foreach ($keys as $key) {
            $upperKey = strtoupper($key);

            if (array_key_exists($upperKey, $normalized)) {
                $value = $normalized[$upperKey];

                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
        }

        return null;
    };

    $id = $get('ID');

    $tarikhHantarKes = $get('TARIKH_HANTAR_KES')
        ? \Carbon\Carbon::parse($get('TARIKH_HANTAR_KES'))->format('Y-m-d')
        : '';

    $tarikhKejadian = $get('TARIKH_KEJADIAN')
        ? \Carbon\Carbon::parse($get('TARIKH_KEJADIAN'))->format('Y-m-d')
        : '';

    $tarikhOfferLetter = $get('TARIKH_OFFER_LETTER')
        ? \Carbon\Carbon::parse($get('TARIKH_OFFER_LETTER'))->format('Y-m-d')
        : '';
@endphp

<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Kemaskini Kes Liabiliti Awam' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Kemaskini semua maklumat tuntutan liabiliti awam.
            </p>
        </div>

        <div>
            <a href="{{ route('liabiliti-kes-list') }}"
                class="inline-flex items-center rounded-lg bg-gray-600 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-700">
                Kembali
            </a>
        </div>
    </div>

    <x-common.component-card title="Kemaskini Maklumat Kes">
        <form method="POST" action="{{ route('liabiliti-kes-update', $id) }}" id="liabilitiAwamEditForm">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-12 gap-4">

                <div class="col-span-3">
                    <label class="form-label">Tahun <span class="text-red-600">*</span></label>
                    <input type="number"
                        name="tahun"
                        value="{{ old('tahun', $get('TAHUN')) }}"
                        class="form-control"
                        placeholder="2026"
                        required>
                    @error('tahun') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-5">
                    <label class="form-label">No Plat / Harta Benda <span class="text-red-600">*</span></label>
                    <input type="text"
                        name="no_plat_harta_benda"
                        value="{{ old('no_plat_harta_benda', $get('NO_PLAT_HARTA_BENDA')) }}"
                        class="form-control uppercase"
                        placeholder="Contoh: PLV 6296 / Rumah / Pagar"
                        required>
                    @error('no_plat_harta_benda') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Tarikh Hantar Kes <span class="text-red-600">*</span></label>
                    <input type="text"
                        name="tarikh_hantar_kes"
                        value="{{ old('tarikh_hantar_kes', $tarikhHantarKes) }}"
                        class="roadtax-date-picker form-control"
                        placeholder="Pilih tarikh"
                        required>
                    @error('tarikh_hantar_kes') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-6">
                    <label class="form-label">Jenis Tuntutan <span class="text-red-600">*</span></label>
                    <select name="tuntutan_awam_id" class="form-control" required>
                        <option value="">-- Sila Pilih --</option>
                        @foreach($jenisTuntutanAwam as $item)
                            @php
                                $jenisId = $item->ID ?? $item->id;
                                $jenisName = $item->TUNTUTAN_NAME ?? $item->tuntutan_name ?? '-';
                                $selectedJenis = old('tuntutan_awam_id', $get('TUNTUTAN_AWAM_ID'));
                            @endphp

                            <option value="{{ $jenisId }}" {{ (string)$selectedJenis === (string)$jenisId ? 'selected' : '' }}>
                                {{ $jenisName }}
                            </option>
                        @endforeach
                    </select>
                    @error('tuntutan_awam_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-6">
                    <label class="form-label">Nama Penuntut <span class="text-red-600">*</span></label>
                    <input type="text"
                        name="nama_penuntut"
                        value="{{ old('nama_penuntut', $get('NAMA_PENUNTUT')) }}"
                        class="form-control uppercase"
                        placeholder="Nama penuntut"
                        required>
                    @error('nama_penuntut') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Tarikh Kejadian <span class="text-red-600">*</span></label>
                    <input type="text"
                        name="tarikh_kejadian"
                        value="{{ old('tarikh_kejadian', $tarikhKejadian) }}"
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
                        value="{{ old('amaun_tuntutan', $get('AMAUN_TUNTUTAN')) }}"
                        class="form-control"
                        placeholder="0.00"
                        required>
                    @error('amaun_tuntutan') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">No Telefon</label>
                    <input type="text"
                        name="no_telefon"
                        value="{{ old('no_telefon', $get('NO_TELEFON')) }}"
                        class="form-control"
                        placeholder="Contoh: 0123456789">
                    @error('no_telefon') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Tarikh Offer Letter</label>
                    <input type="text"
                        name="tarikh_offer_letter"
                        value="{{ old('tarikh_offer_letter', $tarikhOfferLetter) }}"
                        class="roadtax-date-picker form-control"
                        placeholder="Pilih tarikh">
                    @error('tarikh_offer_letter') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-8">
                    <label class="form-label">Lokasi Kejadian / Alamat Rumah <span class="text-red-600">*</span></label>
                    <input type="text"
                        name="lokasi_kejadian"
                        value="{{ old('lokasi_kejadian', $get('LOKASI_KEJADIAN')) }}"
                        class="form-control"
                        placeholder="Masukkan lokasi kejadian atau alamat rumah"
                        required>
                    @error('lokasi_kejadian') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-6">
                    <label for="department" class="form-label">
                        Jabatan <span class="text-red-600">*</span>
                    </label>

                    <select name="jabatan_kod"
                        id="department"
                        class="form-control"
                        required>
                        <option value="" disabled>-- Sila Pilih Jabatan --</option>

                        @foreach($departments as $dept)
                            @php
                                $code = $dept['CODE'] ?? '';
                                $description = $dept['DESCRIPTION'] ?? '';
                                $selectedJabatan = old('jabatan_kod', $get('JABATAN_KOD'));
                            @endphp

                            <option 
                                value="{{ $code }}"
                                data-description="{{ $description }}"
                                {{ (string)$selectedJabatan === (string)$code ? 'selected' : '' }}>
                                {{ $description }}
                            </option>
                        @endforeach
                    </select>

                    <input type="hidden"
                        name="jabatan_nama"
                        id="jabatan"
                        value="{{ old('jabatan_nama', $get('JABATAN_NAMA')) }}">

                    @error('jabatan_kod') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                @php
                    $selectedBahagian = old('bahagian', trim((string) ($get('BAHAGIAN') ?? '')));
                @endphp

                <div class="col-span-6">
                    <label class="form-label">
                        Bahagian <span class="text-red-600">*</span>
                    </label>

                    <select name="bahagian" class="form-control" required>
                        <option value="">-- Sila Pilih --</option>

                        @foreach($lokasiPenempatan as $row)
                            @php
                                $lokasiId = trim((string) ($row->ID ?? $row->id ?? ''));
                                $lokasiName = $row->LOKASI_PENEMPATAN_NAME ?? $row->lokasi_penempatan_name ?? '';
                            @endphp

                            <option value="{{ $lokasiId }}"
                                {{ (string) $selectedBahagian === (string) $lokasiId ? 'selected' : '' }}>
                                {{ $lokasiName }}
                            </option>
                        @endforeach
                    </select>

                    @error('bahagian')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Daerah Kejadian <span class="text-red-600">*</span></label>
                    @php $selectedDaerah = old('daerah_kejadian', $get('DAERAH_KEJADIAN')); @endphp
                    <select name="daerah_kejadian" class="form-control" required>
                        <option value="">-- Sila Pilih --</option>
                        <option value="SPU" {{ $selectedDaerah == 'SPU' ? 'selected' : '' }}>SPU</option>
                        <option value="SPT" {{ $selectedDaerah == 'SPT' ? 'selected' : '' }}>SPT</option>
                        <option value="SPS" {{ $selectedDaerah == 'SPS' ? 'selected' : '' }}>SPS</option>
                    </select>
                    @error('daerah_kejadian') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Status Tuntutan <span class="text-red-600">*</span></label>
                    @php $selectedStatus = old('status_tuntutan', $get('STATUS_TUNTUTAN')); @endphp
                    <select name="status_tuntutan" class="form-control" required>
                        <option value="">-- Sila Pilih --</option>
                        <option value="DALAM PROSES" {{ $selectedStatus == 'DALAM PROSES' ? 'selected' : '' }}>Dalam Proses</option>
                        <option value="TINDAKAN ADJUSTER" {{ $selectedStatus == 'TINDAKAN ADJUSTER' ? 'selected' : '' }}>Tindakan Adjuster</option>
                        <option value="SELESAI TANDATANGAN OFFER LETTER" {{ $selectedStatus == 'SELESAI TANDATANGAN OFFER LETTER' ? 'selected' : '' }}>Selesai Tandatangan Offer Letter</option>
                    </select>
                    @error('status_tuntutan') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-4">
                    <label class="form-label">Keputusan Kes</label>
                    @php $selectedKeputusan = old('keputusan_kes', $get('KEPUTUSAN_KES')); @endphp
                    <select name="keputusan_kes" class="form-control">
                        <option value="">-- Sila Pilih --</option>
                        <option value="LULUS" {{ $selectedKeputusan == 'LULUS' ? 'selected' : '' }}>Lulus</option>
                        <option value="DITOLAK" {{ $selectedKeputusan == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    @error('keputusan_kes') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-12">
                    <label class="form-label">Laporan Adjuster / Insurans</label>
                    <textarea name="laporan_adjuster_insurans"
                        rows="5"
                        class="form-control textarea-control"
                        placeholder="Masukkan laporan adjuster / insurans">{{ old('laporan_adjuster_insurans', $get('LAPORAN_ADJUSTER_INSURANS')) }}</textarea>
                    @error('laporan_adjuster_insurans') <p class="form-error">{{ $message }}</p> @enderror
                </div>

            </div>

            <div class="mt-6 flex justify-end gap-2">
                <a href="{{ route('liabiliti-kes-list') }}"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                    Batal
                </a>

                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                    Kemaskini
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