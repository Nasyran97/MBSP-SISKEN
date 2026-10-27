@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Tambah Tuntutan Awam' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Borang pendaftaran kes TUNTUTAN SECARA PERUNDANGAN.
            </p>
        </div>

        <div>
            <a href="{{ route('mahkamah-kes-list') }}"
                class="inline-flex items-center rounded-lg bg-gray-600 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-700">
                Senarai
            </a>
        </div>
    </div>

    <x-common.component-card title="Maklumat Kes Mahkamah">
        <form method="POST" action="{{ route('mahkamah-data-store') }}" id="mahkamahForm" enctype="multipart/form-data">
            @csrf

            <div class="form-section">
                <h4 class="section-title">Maklumat Kes</h4>

                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-6">
                        <label class="form-label">Tahun <span class="text-red-600">*</span></label>
                        <input type="number"
                            name="tahun"
                            value="{{ old('tahun', date('Y')) }}"
                            class="form-control"
                            required>
                        @error('tahun') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Daerah Kejadian <span class="text-red-600">*</span></label>
                        <select name="daerah_kejadian" class="form-control" required>
                            <option value="" disabled selected>-- Sila Pilih --</option>
                            <option value="SPU" {{ old('daerah_kejadian') == 'SPU' ? 'selected' : '' }}>SPU</option>
                            <option value="SPT" {{ old('daerah_kejadian') == 'SPT' ? 'selected' : '' }}>SPT</option>
                            <option value="SPS" {{ old('daerah_kejadian') == 'SPS' ? 'selected' : '' }}>SPS</option>
                        </select>
                        @error('daerah_kejadian') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-12">
                        <label class="form-label">No Kes Mahkamah <span class="text-red-600">*</span></label>
                    
                        <textarea name="no_kes_mahkamah"
                            rows="3"
                            class="form-control textarea-control uppercase"
                            placeholder="Contoh: PA-XX-XXXX"
                            required>{{ old('no_kes_mahkamah') }}</textarea>
                    
                        @error('no_kes_mahkamah')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Peguam Plaintif</label>
                        <input type="text"
                            name="peguam_plaintif"
                            value="{{ old('peguam_plaintif') }}"
                            class="form-control uppercase"
                            placeholder="Nama peguam plaintif">
                        @error('peguam_plaintif') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Peguam Mewakili MBSP</label>
                        <input type="text"
                            name="peguam_mbsp"
                            value="{{ old('peguam_mbsp') }}"
                            class="form-control uppercase"
                            placeholder="Nama peguam MBSP">
                        @error('peguam_mbsp') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Nama Penuntut <span class="text-red-600">*</span></label>
                        <input type="text"
                            name="nama_penuntut"
                            value="{{ old('nama_penuntut') }}"
                            class="form-control uppercase"
                            required>
                        @error('nama_penuntut') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">No Kenderaan / Jenis</label>
                        <input type="text"
                            name="no_kenderaan_jenis"
                            value="{{ old('no_kenderaan_jenis') }}"
                            class="form-control uppercase"
                            placeholder="Contoh: PXX1234 / Kereta">
                        @error('no_kenderaan_jenis') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Jenis Tuntutan <span class="text-red-600">*</span></label>
                        <select name="tuntutan_mahkamah_id" class="form-control" required>
                            <option value="" disabled selected>-- Sila Pilih --</option>
                            @foreach($jenisTuntutanMahkamah as $item)
                                @php
                                    $jenisId = $item->ID ?? $item->id;
                                    $jenisName = $item->TUNTUTAN_NAME ?? $item->tuntutan_name ?? '-';
                                @endphp

                                <option value="{{ $jenisId }}" {{ old('tuntutan_mahkamah_id') == $jenisId ? 'selected' : '' }}>
                                    {{ $jenisName }}
                                </option>
                            @endforeach
                        </select>
                        @error('tuntutan_mahkamah_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Tarikh Bicara Kes Seterusnya</label>
                        <input type="text"
                            name="tarikh_bicara_seterusnya"
                            value="{{ old('tarikh_bicara_seterusnya') }}"
                            class="roadtax-date-picker form-control"
                            placeholder="Pilih tarikh">
                        @error('tarikh_bicara_seterusnya') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Status Perbicaraan</label>
                        <input type="text"
                            name="status_perbicaraan"
                            value="{{ old('status_perbicaraan') }}"
                            class="form-control"
                            placeholder="Masukkan status perbicaraan">
                        @error('status_perbicaraan') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-6">
                        <label class="form-label">Keputusan Kes</label>
                        <input type="text"
                            name="keputusan_kes"
                            value="{{ old('keputusan_kes') }}"
                            class="form-control"
                            placeholder="Masukkan keputusan kes">
                        @error('keputusan_kes') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-12">
                        <label class="form-label">Penyata Tuntutan</label>
                        <textarea name="penyata_tuntutan"
                            rows="5"
                            class="form-control textarea-control"
                            placeholder="Masukkan penyata tuntutan">{{ old('penyata_tuntutan') }}</textarea>
                        @error('penyata_tuntutan') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="form-section mt-6">
                <h4 class="section-title">Maklumat Kenderaan / Insurans</h4>
            
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-6">
                        <label class="form-label">
                            No Plat Kenderaan <span class="text-red-600"></span>
                        </label>
            
                        <select name="vehicle_id"
                            id="vehicle_id"
                            class="vehicle-select">
                            <option value="" selected>-- Tiada Kenderaan / Sila Pilih No Plat --</option>
            
                            @foreach($vehicles as $v)
                                @php
                                    $vehicleId = $v->VEHICLE_ID ?? $v->vehicle_id ?? null;
                                    $noPlat = $v->NO_PLAT ?? $v->no_plat ?? '';
                                    $jenisKenderaan = $v->JENIS_KENDERAAN ?? $v->jenis_kenderaan ?? '';
                                    $panelId = $v->CURRENT_PANEL_ID ?? '';
                                    $panelName = $v->CURRENT_PANEL_NAME ?? '';
                                @endphp
            
                                <option value="{{ $vehicleId }}"
                                    data-no-plat="{{ $noPlat }}"
                                    data-jenis-kenderaan="{{ $jenisKenderaan }}"
                                    data-panel-id="{{ $panelId }}"
                                    data-panel-name="{{ $panelName }}"
                                    {{ old('vehicle_id') == $vehicleId ? 'selected' : '' }}>
                                    {{ $noPlat }}
                                </option>
                            @endforeach
                        </select>
            
                        <input type="hidden" name="no_plat" id="no_plat" value="{{ old('no_plat') }}">
            
                        @error('vehicle_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
            
                    <div class="col-span-6">
                        <label class="form-label">
                            Syarikat Insurans
                        </label>
            
                        <input type="text"
                            name="syarikat_insurans"
                            id="syarikat_insurans"
                            value="{{ old('syarikat_insurans') }}"
                            class="form-control readonly-field"
                            readonly
                            placeholder="Syarikat insurans semasa akan dipaparkan">
            
                        <input type="hidden"
                            name="syarikat_insurans_id"
                            id="syarikat_insurans_id"
                            value="{{ old('syarikat_insurans_id') }}">
            
                        <p id="insurance_helper" class="helper-text hidden"></p>
                    </div>
                </div>
            </div>

            <div class="form-section mt-6">
                <h4 class="section-title">Maklumat Jabatan / Pegawai</h4>
            
                <div class="grid grid-cols-12 gap-4">
                    {{-- Step 1: Department --}}
                    <div class="col-span-6">
                        <label for="department" class="form-label">
                            Jabatan <span class="text-red-600">*</span>
                        </label>
            
                        <select name="jabatan_kod"
                            id="department"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
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
            
                    {{-- Step 2: Staff --}}
                    <div class="col-span-6">
                        <label for="nostaff" class="form-label">
                            Nama Staf <span class="text-red-600">*</span>
                        </label>
                    
                        <select name="no_pekerja"
                            id="nostaff"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-gray-50"
                            disabled
                            required>
                            <option value="" selected disabled>-- Sila Pilih Jabatan Dahulu --</option>
                        </select>
                    
                        <input type="hidden" name="nama_staf" id="nama_staf" value="{{ old('nama_staf') }}">
                        <input type="hidden" name="jawatan_staf" id="jawatan_staf" value="{{ old('jawatan_staf') }}">
                    
                        @error('no_pekerja')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-section mt-6">
                <h4 class="section-title">Lampiran Fail</h4>

                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12">
                        <label class="form-label">Muat Naik Fail</label>

                        <input type="file"
                            name="file_upload"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="form-control file-control">

                        <p class="mt-1 text-xs text-gray-500">
                            Format dibenarkan: PDF, JPG, JPEG, PNG. Maksimum 5MB.
                        </p>

                        @error('file_upload')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
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

    .textarea-control {
        padding-top: 12px;
        min-height: 130px;
    }

    .file-control {
        padding: 10px 14px;
    }

    .form-error {
        margin-top: 6px;
        color: #dc2626;
        font-size: 13px;
        font-weight: 600;
    }

    .readonly-field {
        background: #f9fafb;
        color: #475467;
    }

    .uppercase {
        text-transform: uppercase;
    }

    .flatpickr-calendar {
        z-index: 1000001 !important;
    }

    @media (max-width: 768px) {
        .grid.grid-cols-12 > div {
            grid-column: span 12 / span 12 !important;
        }
    }

    .vehicle-select {
        width: 100%;
    }

    .ts-wrapper.single .ts-control {
        min-height: 48px;
        border: 1px solid #d0d5dd;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 14px;
        background: #fff;
        color: #101828;
        box-shadow: none;
    }

    .ts-wrapper.single.focus .ts-control {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .ts-dropdown {
        z-index: 1000002 !important;
        border-radius: 10px;
        border: 1px solid #d0d5dd;
        font-size: 14px;
    }

    .ts-dropdown .option {
        padding: 10px 14px;
    }

    .readonly-field.no-insurance {
        color: #b42318;
        background: #fef3f2;
        border-color: #fecdca;
        font-weight: 700;
    }

    .readonly-field.has-insurance {
        color: #027a48;
        background: #ecfdf3;
        border-color: #abefc6;
        font-weight: 700;
    }

    .helper-text {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .helper-error {
        color: #b42318;
    }

    .helper-success {
        color: #027a48;
    }

    .hidden {
        display: none;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const departmentSelect = document.getElementById('department');
        const staffSelect = document.getElementById('nostaff');
        const jabatanInput = document.getElementById('jabatan');
    
        const namaStafInput = document.getElementById('nama_staf');
        const jawatanStafInput = document.getElementById('jawatan_staf');
    
        const vehicleSelect = document.getElementById('vehicle_id');
        const noPlatInput = document.getElementById('no_plat');
        const syarikatInsuransInput = document.getElementById('syarikat_insurans');
        const syarikatInsuransIdInput = document.getElementById('syarikat_insurans_id');

        let vehicleTom = null;
    
        const oldNoStaff = @json(old('no_pekerja'));
    
        if (typeof flatpickr !== 'undefined') {
            flatpickr(".roadtax-date-picker", {
                dateFormat: "Y-m-d",
                allowInput: true,
                position: "above",
                appendTo: document.body
            });
        }
    
        function initVehicleSearch() {
            if (vehicleTom) {
                vehicleTom.destroy();
                vehicleTom = null;
            }
    
            if (typeof TomSelect !== 'undefined' && vehicleSelect) {
                vehicleTom = new TomSelect('#vehicle_id', {
                    create: false,
                    allowEmptyOption: true,
                    placeholder: 'Cari no plat...',
                    maxOptions: 5000,
                    searchField: ['text'],
                    sortField: {
                        field: 'text',
                        direction: 'asc'
                    },
                    onChange: function(value) {
                        const selectedOption = vehicleSelect.querySelector(`option[value="${value}"]`);
                        const helper = document.getElementById('insurance_helper');
    
                        const noPlat = selectedOption ? (selectedOption.dataset.noPlat || '') : '';
                        const panelName = selectedOption ? (selectedOption.dataset.panelName || '') : '';
                        const panelId = selectedOption ? (selectedOption.dataset.panelId || '') : '';
    
                        if (noPlatInput) {
                            noPlatInput.value = noPlat;
                        }
    
                        if (syarikatInsuransInput) {
                            syarikatInsuransInput.classList.remove('no-insurance', 'has-insurance');
                        }
    
                        if (!value) {
                            if (syarikatInsuransInput) {
                                syarikatInsuransInput.value = '';
                            }
    
                            if (syarikatInsuransIdInput) {
                                syarikatInsuransIdInput.value = '';
                            }
    
                            if (helper) {
                                helper.classList.add('hidden');
                                helper.textContent = '';
                            }
    
                            return;
                        }
    
                        if (panelName) {
                            if (syarikatInsuransInput) {
                                syarikatInsuransInput.value = panelName;
                                syarikatInsuransInput.classList.add('has-insurance');
                            }
    
                            if (syarikatInsuransIdInput) {
                                syarikatInsuransIdInput.value = panelId;
                            }
    
                            if (helper) {
                                helper.classList.remove('hidden', 'helper-error');
                                helper.classList.add('helper-success');
                                helper.textContent = 'Insurans aktif dijumpai untuk kenderaan ini.';
                            }
                        } else {
                            if (syarikatInsuransInput) {
                                syarikatInsuransInput.value = 'Kenderaan ini tiada insurans';
                                syarikatInsuransInput.classList.add('no-insurance');
                            }
    
                            if (syarikatInsuransIdInput) {
                                syarikatInsuransIdInput.value = '';
                            }
    
                            if (helper) {
                                helper.classList.remove('hidden', 'helper-success');
                                helper.classList.add('helper-error');
                                helper.textContent = 'Tiada polisi insurans aktif dijumpai untuk kenderaan ini.';
                            }
                        }
                    }
                });
            }
        }

    
        function clearStaffFields() {
            if (namaStafInput) {
                namaStafInput.value = '';
            }
    
            if (jawatanStafInput) {
                jawatanStafInput.value = '';
            }
        }

        let staffTom = null;

        function initStaffSearch() {
            if (staffTom) {
                staffTom.destroy();
                staffTom = null;
            }

            if (typeof TomSelect !== 'undefined' && staffSelect) {
                staffTom = new TomSelect('#nostaff', {
                    create: false,
                    maxOptions: 5000,
                    placeholder: 'Cari nama / no staf...',
                    searchField: ['text'],
                    sortField: {
                        field: 'text',
                        direction: 'asc'
                    }
                });
            }
        }

        function loadStaffByDepartment(kodJabatanStaff, selectedNoStaff = '') {
            if (!staffSelect) {
                return;
            }

            if (staffTom) {
                staffTom.destroy();
                staffTom = null;
            }

            staffSelect.innerHTML = '<option value="">-- Loading Nama Staf --</option>';
            staffSelect.disabled = true;
            staffSelect.classList.add('bg-gray-50');
            initStaffSearch();

            clearStaffFields();

            if (!kodJabatanStaff) {
                if (staffTom) {
                    staffTom.destroy();
                    staffTom = null;
                }

                staffSelect.innerHTML = '<option value="" selected disabled>-- Sila Pilih Jabatan Dahulu --</option>';
                staffSelect.disabled = true;
                staffSelect.classList.add('bg-gray-50');
                initStaffSearch();
                return;
            }

            fetch("{{ route('mahkamah.staff-by-department') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    kodjabatanstaff: kodJabatanStaff
                })
            })
            .then(response => response.json())
            .then(data => {
                if (staffTom) {
                    staffTom.destroy();
                    staffTom = null;
                }

                staffSelect.innerHTML = '<option value="" selected disabled>-- Sila Pilih Nama Staf --</option>';

                const staffs = data.data ?? data.staffs ?? [];

                if (Array.isArray(staffs) && staffs.length > 0) {
                    staffs.forEach(staff => {
                        const noStaff = staff.NOSTAFF ?? staff.nostaff ?? staff.NO_STAFF ?? staff.no_staff ?? '';
                        const namaStaff = staff.NAMASTAFF ?? staff.namastaff ?? staff.NAMA ?? staff.nama ?? '';
                        const jawatanStaff = staff.JAWATANSTAFF ?? staff.jawatanstaff ?? staff.JAWATAN ?? staff.jawatan ?? '';

                        if (!noStaff) {
                            return;
                        }

                        const option = document.createElement('option');

                        option.value = noStaff;
                        option.textContent = noStaff + ' - ' + namaStaff;

                        option.dataset.nama = namaStaff;
                        option.dataset.jawatan = jawatanStaff;

                        if (String(noStaff) === String(selectedNoStaff)) {
                            option.selected = true;
                        }

                        staffSelect.appendChild(option);
                    });

                    staffSelect.disabled = false;
                    staffSelect.classList.remove('bg-gray-50');
                    initStaffSearch();

                    setSelectedStaffHiddenValue();
                } else {
                    staffSelect.innerHTML = '<option value="">-- Tiada Staf Dijumpai --</option>';
                    staffSelect.disabled = true;
                    staffSelect.classList.add('bg-gray-50');
                    initStaffSearch();

                    clearStaffFields();
                }
            })
            .catch(error => {
                console.error('Staff API error:', error);

                if (staffTom) {
                    staffTom.destroy();
                    staffTom = null;
                }

                staffSelect.innerHTML = '<option value="">-- Ralat Semasa Ambil Data Staf --</option>';
                staffSelect.disabled = true;
                staffSelect.classList.add('bg-gray-50');
                initStaffSearch();

                clearStaffFields();
            });
        }

        function setSelectedStaffHiddenValue() {
            if (!staffSelect) {
                return;
            }

            const selectedOption = staffSelect.options[staffSelect.selectedIndex];

            if (namaStafInput) {
                namaStafInput.value = selectedOption ? (selectedOption.dataset.nama || '') : '';
            }

            if (jawatanStafInput) {
                jawatanStafInput.value = selectedOption ? (selectedOption.dataset.jawatan || '') : '';
            }
        }
    
        function setJabatanNama() {
            if (!departmentSelect || !jabatanInput) {
                return;
            }
    
            const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
    
            jabatanInput.value = selectedOption
                ? (selectedOption.dataset.description || '')
                : '';
        }
    
        if (departmentSelect) {
            departmentSelect.addEventListener('change', function () {
                const departmentCode = this.value;
    
                setJabatanNama();
                loadStaffByDepartment(departmentCode);
            });
    
            if (departmentSelect.value) {
                setJabatanNama();
                loadStaffByDepartment(departmentSelect.value, oldNoStaff);
            }
        }

        if (staffSelect) {
            staffSelect.addEventListener('change', function () {
                setSelectedStaffHiddenValue();
            });
        }
    
        if (typeof TomSelect !== 'undefined' && departmentSelect) {
            new TomSelect('#department', {
                create: false,
                maxOptions: 5000,
                placeholder: 'Cari jabatan...',
                searchField: ['text'],
                sortField: {
                    field: 'text',
                    direction: 'asc'
                }
            });
        }

        initVehicleSearch();
        initStaffSearch();

        if (vehicleTom && vehicleSelect && vehicleSelect.value) {
            vehicleTom.trigger('change', vehicleSelect.value);
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