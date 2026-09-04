@extends('layouts.app')

@section('content')
@php
    $id = $car->ID ?? $car->id;
    $kodJabatan = old('kod_jabatan', $car->KOD_JABATAN ?? $car->kod_jabatan ?? '');
    $jabatan = old('jabatan', $car->JABATAN ?? $car->jabatan ?? '');
    $pengawasSelected = old('pengawas_kenderaan', $car->PENGAWAS_KENDERAAN ?? $car->pengawas_kenderaan ?? '');
@endphp

<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">Kemaskini maklumat kenderaan.</p>
        </div>

        <a href="{{ route('rekod-car-list') }}"
           class="inline-flex items-center justify-center rounded-lg bg-gray-500 px-5 py-3 text-sm font-medium text-white hover:bg-gray-600">
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

    <x-common.component-card title="Edit Maklumat Kenderaan">
        <form method="POST" action="{{ route('rekod-car-update', $id) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">No Plat</label>
                    <input type="text" name="no_plat" value="{{ old('no_plat', $car->NO_PLAT ?? $car->no_plat ?? '') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none" readonly>
                </div>

                <div class="col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Buatan</label>
                    <input type="text" name="buatan" value="{{ old('buatan', $car->BUATAN ?? $car->buatan ?? '') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Model</label>
                    <input type="text" name="model" value="{{ old('model', $car->MODEL ?? $car->model ?? '') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Jabatan</label>
                    <select name="kod_jabatan" id="kod_jabatan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- Sila Pilih Jabatan --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept['CODE'] ?? '' }}"
                                data-description="{{ $dept['DESCRIPTION'] ?? '' }}"
                                {{ $kodJabatan == ($dept['CODE'] ?? '') ? 'selected' : '' }}>
                                {{ $dept['DESCRIPTION'] ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="jabatan" id="jabatan" value="{{ $jabatan }}">
                </div>

                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Bahan Bakar</label>
                    @php $bahan = old('jenis_bahan_bakar', $car->JENIS_BAHAN_BAKAR ?? $car->jenis_bahan_bakar ?? ''); @endphp
                    <select name="jenis_bahan_bakar"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        @foreach(['Diesel', 'Diesel Hijau', 'Petrol', 'Elektrik EV'] as $option)
                            <option value="{{ $option }}" {{ $bahan == $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tahun Dibuat</label>
                    @php $tahun = old('tahun_dibuat', $car->TAHUN_DIBUAT ?? $car->tahun_dibuat ?? ''); @endphp
                    <select name="tahun_dibuat"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- Pilih Tahun --</option>
                        @for($year = date('Y') + 1; $year >= 1980; $year--)
                            <option value="{{ $year }}" {{ (string)$tahun === (string)$year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>

                {{-- Jenis Badan --}}
                <div class="col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Jenis Badan
                    </label>

                    @php
                        $selectedJenisBadan = old(
                            'jenis_badan',
                            $car->JENIS_BADAN ?? $car->jenis_badan ?? ''
                        );
                    @endphp

                    <select name="jenis_badan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- Sila Pilih Jenis Badan --</option>

                        @foreach($jenisBadan as $row)
                            @php
                                $jenisBadanName = $row->JENIS_BADAN_NAME ?? $row->jenis_badan_name ?? '';
                            @endphp

                            <option value="{{ $jenisBadanName }}"
                                {{ trim(strtoupper($selectedJenisBadan)) == trim(strtoupper($jenisBadanName)) ? 'selected' : '' }}>
                                {{ $jenisBadanName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Keupayaan Enjin</label>
                    <input type="text" name="keupayaan_enjin" value="{{ old('keupayaan_enjin', $car->KEUPAYAAN_ENJIN ?? $car->keupayaan_enjin ?? '') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">No Chasis</label>
                    <input type="text" name="no_chasis" value="{{ old('no_chasis', $car->NO_CHASIS ?? $car->no_chasis ?? '') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">No Enjin</label>
                    <input type="text" name="no_enjin" value="{{ old('no_enjin', $car->NO_ENJIN ?? $car->no_enjin ?? '') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Jenis Kenderaan</label>
                    <input type="text" name="jenis_kenderaan" value="{{ old('jenis_kenderaan', $car->JENIS_KENDERAAN ?? $car->jenis_kenderaan ?? '') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tarikh Pendaftaran</label>
                    <input type="text" name="tarikh_pendaftaran" value="{{ old('tarikh_pendaftaran', $car->TARIKH_PENDAFTARAN ?? $car->tarikh_pendaftaran ?? '') }}"
                        class="date-picker w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                {{-- Kelas Kegunaan --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Kelas Kegunaan
                    </label>

                    @php
                        $selectedKelasGuna = old(
                            'kelas_kegunaan',
                            $car->KELAS_KEGUNAAN ?? $car->kelas_kegunaan ?? ''
                        );
                    @endphp

                    <select name="kelas_kegunaan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- Sila Pilih Kelas Kegunaan --</option>

                        @foreach($kelasGuna as $row)
                            @php
                                $kelasGunaName = $row->KELAS_GUNA_NAME ?? $row->kelas_guna_name ?? '';
                            @endphp

                            <option value="{{ $kelasGunaName }}"
                                {{ trim(strtoupper($selectedKelasGuna)) == trim(strtoupper($kelasGunaName)) ? 'selected' : '' }}>
                                {{ $kelasGunaName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Pengawas Kenderaan</label>
                    <select name="pengawas_kenderaan" id="pengawas_kenderaan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- Sila Pilih Pengawas --</option>
                        @foreach($pengawas as $p)
                            @php
                                $nostaff = $p->NOSTAFF ?? $p->nostaff ?? '';
                                $nama = $p->NAMA ?? $p->nama ?? '';
                            @endphp
                            <option value="{{ $nostaff }}" {{ (string)$pengawasSelected === (string)$nostaff ? 'selected' : '' }}>
                                {{ $nostaff }} - {{ $nama }}
                            </option>
                        @endforeach
                    </select>
                </div> --}}

                {{-- Lokasi Penempatan --}}
                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Lokasi Penempatan
                    </label>

                    @php
                        $selectedLokasiPenempatan = old(
                            'lokasi_penempatan',
                            $car->LOKASI_PENEMPATAN ?? $car->lokasi_penempatan ?? ''
                        );
                    @endphp

                    <select name="lokasi_penempatan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- Sila Pilih Lokasi Penempatan --</option>

                        @foreach($lokasiPenempatan as $row)
                            @php
                                $lokasiName = $row->LOKASI_PENEMPATAN_NAME ?? $row->lokasi_penempatan_name ?? '';
                            @endphp

                            <option value="{{ $lokasiName }}"
                                {{ trim(strtoupper($selectedLokasiPenempatan)) == trim(strtoupper($lokasiName)) ? 'selected' : '' }}>
                                {{ $lokasiName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Kelas Kenderaan</label>
                    @php $kelas = old('kelas_kenderaan', $car->KELAS_KENDERAAN ?? $car->kelas_kenderaan ?? ''); @endphp
                    <select name="kelas_kenderaan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        @foreach(['Commercial', 'Special Type', 'A Type', 'Private Car', 'Private Motorcycle'] as $option)
                            <option value="{{ $option }}" {{ $kelas == $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                @php
                    $selectedPembekalId = old('pembekal_id', $car->PEMBEKAL_ID ?? $car->pembekal_id ?? '');

                    $formatDateForInput = function ($date) {
                        if (!$date) {
                            return '';
                        }

                        try {
                            return \Carbon\Carbon::parse($date)->format('Y-m-d');
                        } catch (\Exception $e) {
                            return '';
                        }
                    };
                @endphp

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
                                {{ (string) $selectedPembekalId === (string) $pembekalId ? 'selected' : '' }}>
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

                    <input type="date"
                        name="tarikh_beli"
                        value="{{ old('tarikh_beli', $formatDateForInput($car->TARIKH_BELI ?? $car->tarikh_beli ?? null)) }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
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
                        value="{{ old('harga_beli', $car->HARGA_BELI ?? $car->harga_beli ?? '') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                        placeholder="Contoh: 280830.00">
                </div>

                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Status</label>
                    @php $status = old('status_data', $car->STATUS_DATA ?? $car->status_data ?? 'ACTIVE'); @endphp
                    <select name="status_data"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="ACTIVE" {{ $status == 'ACTIVE' ? 'selected' : '' }}>Aktif</option>
                        <option value="INACTIVE" {{ $status == 'INACTIVE' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                {{-- <div class="col-span-3">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tamat Cukai Jalan</label>
                    <input type="text" name="tamat_cukai_jalan" value="{{ old('tamat_cukai_jalan', $car->TAMAT_CUKAI_JALAN ?? $car->tamat_cukai_jalan ?? '') }}"
                        class="date-picker w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="col-span-3">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tamat Insurans</label>
                    <input type="text" name="tamat_insurans" value="{{ old('tamat_insurans', $car->TAMAT_INSURANS ?? $car->tamat_insurans ?? '') }}"
                        class="date-picker w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="col-span-3">
                    <label class="block mb-1 text-sm font-medium text-gray-700">NCD</label>
                    <input type="number" step="0.01" name="ncd" value="{{ old('ncd', $car->NCD ?? $car->ncd ?? '') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="col-span-3">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Rekod Kemalangan</label>
                    <input type="number" min="0" name="rekod_kemalangan" value="{{ old('rekod_kemalangan', $car->REKOD_KEMALANGAN ?? $car->rekod_kemalangan ?? '') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Syarikat Insurans</label>
                    <input type="text" name="syarikat_insurans" value="{{ old('syarikat_insurans', $car->SYARIKAT_INSURANS ?? $car->syarikat_insurans ?? '') }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                </div>

                <div class="col-span-6">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Jenis Polisi</label>
                    @php $polisi = old('jenis_polisi', $car->JENIS_POLISI ?? $car->jenis_polisi ?? ''); @endphp
                    <select name="jenis_polisi"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
                        @foreach(['Comprehensive', 'Third Party'] as $option)
                            <option value="{{ $option }}" {{ $polisi == $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>--}}
            </div> 

            <div class="mt-6 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-medium text-white hover:bg-blue-700">
                    Simpan Perubahan
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