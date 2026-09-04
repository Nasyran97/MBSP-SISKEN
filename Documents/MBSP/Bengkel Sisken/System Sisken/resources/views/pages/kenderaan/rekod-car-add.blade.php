@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">Sila lengkapkan maklumat kenderaan.</p>
        </div>

        <a href="{{ route('rekod-car-list') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
            Kembali
        </a>
    </div>

    <x-common.component-card title="Maklumat Kenderaan">
        <form method="POST" action="{{ route('rekod-car-store') }}">
            @csrf

            <div class="grid grid-cols-12 gap-4">

                {{-- No Plat --}}
                <div class="col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">No Plat</label>
                    <input type="text" name="no_plat"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none" placeholder="eg: PPP1111">
                </div>

                {{-- Buatan --}}
                <div class="col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Buatan</label>
                    <input type="text" name="buatan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none" placeholder="eg: HINO">
                </div>

                {{-- Model --}}
                <div class="col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Model</label>
                    <input type="text" name="model"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none" placeholder="eg: FG1JPPB (UBS)">
                </div>

                {{-- Jabatan --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Jabatan</label>

                    <select name="kod_jabatan" id="kod_jabatan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- Sila Pilih Jabatan --</option>
                        @foreach($departments as $dept)
                            <option 
                                value="{{ $dept['CODE'] ?? '' }}"
                                data-description="{{ $dept['DESCRIPTION'] ?? '' }}">
                                {{ $dept['DESCRIPTION'] ?? '' }}
                            </option>
                        @endforeach
                    </select>

                    <input type="hidden" name="jabatan" id="jabatan">
                </div>

                {{-- Bahan Bakar --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Bahan Bakar</label>
                    <select name="jenis_bahan_bakar"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="Diesel">Diesel</option>
                        <option value="Diesel Hijau">Diesel Hijau</option>
                        <option value="Petrol">Petrol</option>
                        <option value="Elektrik EV">Elektrik EV</option>
                    </select>
                </div>

                {{-- Tahun --}}
                <div class="col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tahun Dibuat</label>
                    <select name="tahun_dibuat"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- Pilih Tahun --</option>
                        @for($year = date('Y') + 1; $year >= 1980; $year--)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>

                {{-- Jenis Badan --}}
                <div class="col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Jenis Badan
                    </label>

                    <select name="jenis_badan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- Sila Pilih Jenis Badan --</option>

                        @foreach($jenisBadan as $row)
                            @php
                                $jenisBadanName = $row->JENIS_BADAN_NAME ?? $row->jenis_badan_name ?? '';
                            @endphp

                            <option value="{{ $jenisBadanName }}"
                                {{ old('jenis_badan') == $jenisBadanName ? 'selected' : '' }}>
                                {{ $jenisBadanName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Keupayaan Enjin --}}
                <div class="col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Keupayaan Enjin</label>
                    <input type="text" name="keupayaan_enjin"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none" placeholder="eg: 7961">
                </div>

                {{-- No Chasis --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">No Chasis</label>
                    <input type="text" name="no_chasis"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none" placeholder="eg: FG1JPP-23834">
                </div>

                {{-- No Enjin --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">No Enjin</label>
                    <input type="text" name="no_enjin"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none" placeholder="J08CTT50539">
                </div>

                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Jenis Kenderaan</label>
                    <input type="text" name="jenis_kenderaan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                        placeholder="eg: Lori">
                </div>

                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tarikh Pendaftaran</label>
                    <input type="text" name="tarikh_pendaftaran" id="tarikh_pendaftaran"
                        class="date-picker w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                        placeholder="Pilih tarikh">
                </div>

                {{-- Kelas Kegunaan --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Kelas Kegunaan
                    </label>

                    <select name="kelas_kegunaan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- Sila Pilih Kelas Kegunaan --</option>

                        @foreach($kelasGuna as $row)
                            @php
                                $kelasGunaName = $row->KELAS_GUNA_NAME ?? $row->kelas_guna_name ?? '';
                            @endphp

                            <option value="{{ $kelasGunaName }}"
                                {{ old('kelas_kegunaan') == $kelasGunaName ? 'selected' : '' }}>
                                {{ $kelasGunaName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Lokasi --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Lokasi Penempatan
                    </label>

                    <select name="lokasi_penempatan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- Sila Pilih Lokasi Penempatan --</option>

                        @foreach($lokasiPenempatan as $row)
                            @php
                                $lokasiName = $row->LOKASI_PENEMPATAN_NAME ?? $row->lokasi_penempatan_name ?? '';
                            @endphp

                            <option value="{{ $lokasiName }}"
                                {{ old('lokasi_penempatan') == $lokasiName ? 'selected' : '' }}>
                                {{ $lokasiName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Kelas --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Kelas Kenderaan</label>
                    <select name="kelas_kenderaan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option>Commercial</option>
                        <option>Special Type</option>
                        <option>A Type</option>
                        <option>Private Car</option>
                        <option>Private Motorcycle</option>
                    </select>
                </div>

                {{-- Nama Pembekal --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Nama Pembekal
                    </label>

                    <select name="pembekal_id"
                        id="pembekal_id"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- Sila Pilih Pembekal --</option>

                        @foreach($pembekal as $row)
                            @php
                                $pembekalId = $row->ID ?? $row->id ?? '';
                                $namaPembekal = $row->NAMA_PEMBEKAL ?? $row->nama_pembekal ?? '';
                                $alamatPembekal = $row->ALAMAT_PEMBEKAL ?? $row->alamat_pembekal ?? '';
                            @endphp

                            <option value="{{ $pembekalId }}"
                                data-alamat="{{ $alamatPembekal }}"
                                {{ old('pembekal_id') == $pembekalId ? 'selected' : '' }}>
                                {{ $namaPembekal }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Alamat Pembekal --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Alamat Pembekal
                    </label>

                    <textarea id="alamat_pembekal_display"
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                        placeholder="Alamat akan dipaparkan selepas pilih pembekal"
                        readonly></textarea>
                </div>

                {{-- Tarikh Beli --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Tarikh Beli
                    </label>
                    <input type="text" name="tarikh_beli" id="tarikh_beli"
                    class="date-picker w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                    placeholder="Pilih tarikh">
                </div>

                {{-- Harga Beli --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Harga Beli
                    </label>

                    <input type="number"
                        step="0.01"
                        min="0"
                        name="harga_beli"
                        value="{{ old('harga_beli') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                        placeholder="Contoh: 280830.00">
                </div>

                {{-- Status --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Status</label>
                    <select name="status_data"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="ACTIVE">Aktif</option>
                        <option value="INACTIVE">Tidak Aktif</option>
                    </select>
                </div>

                {{-- Tamat Cukai Jalan --}}
                {{-- <div class="col-span-3">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tamat Cukai Jalan</label>
                    <input type="text" name="tamat_cukai_jalan" class="date-picker w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none" placeholder="Pilih tarikh">
                </div> --}}

                {{-- Tamat Insurans --}}
                {{-- <div class="col-span-3">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tamat Insurans</label>
                    <input type="text" name="tamat_insurans" class="date-picker w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none" placeholder="Pilih tarikh">
                </div> --}}

                {{-- NCD --}}
                {{-- <div class="col-span-3">
                    <label class="block mb-1 text-sm font-medium text-gray-700">NCD</label>
                    <input type="number" step="0.01" name="ncd"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                        placeholder="eg: 55">
                </div> --}}

                {{-- Rekod Kemalangan --}}
                {{-- <div class="col-span-3">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Rekod Kemalangan</label>
                    <input type="number" name="rekod_kemalangan" min="0"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                        placeholder="eg: 0">
                </div> --}}

                {{-- Syarikat Insurans --}}
                {{-- <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Syarikat Insurans</label>
                    <input type="text" name="syarikat_insurans"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                        placeholder="eg: Etiqa Takaful">
                </div> --}}

                {{-- Polisi --}}
                {{-- <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Jenis Polisi</label>
                    <select name="jenis_polisi"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="Comprehensive">Comprehensive</option>
                        <option value="Third Party">Third Party</option>
                    </select>
                </div> --}}

            </div>

            {{-- Submit --}}
            <div class="mt-6 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700 transition">
                    Simpan
                </button>
            </div>

        </form>
    </x-common.component-card>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const kodJabatanSelect = document.getElementById('kod_jabatan');
        const jabatanInput = document.getElementById('jabatan');

        kodJabatanSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            jabatanInput.value = selectedOption.dataset.description || '';
        });

        flatpickr(".date-picker", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        const pembekalSelect = document.getElementById('pembekal_id');
        const alamatDisplay = document.getElementById('alamat_pembekal_display');

        function updateAlamatPembekal() {
            if (!pembekalSelect || !alamatDisplay) {
                return;
            }

            const selectedOption = pembekalSelect.options[pembekalSelect.selectedIndex];

            if (!selectedOption || !selectedOption.value) {
                alamatDisplay.value = '';
                return;
            }

            alamatDisplay.value = selectedOption.dataset.alamat || '';
        }

        if (pembekalSelect) {
            pembekalSelect.addEventListener('change', updateAlamatPembekal);
            updateAlamatPembekal();
        }
    });
</script>
@endsection