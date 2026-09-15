@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Kemaskini maklumat permohonan penyelenggaraan kenderaan.
            </p>
        </div>

        <a href="{{ route('penyelenggaraan-list') }}"
           class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600">
            Senarai Penyelenggaraan
        </a>
    </div>

    @php
        $selectedVehicleId = old('vehicle_id', $penyelenggaraan->VEHICLE_ID ?? $penyelenggaraan->vehicle_id ?? null);
        $selectedDriverId = old('driver_id', $penyelenggaraan->DRIVER_ID ?? $penyelenggaraan->driver_id ?? null);
        $selectedJkjgId = old('jkjg_id', $penyelenggaraan->JKJG_ID ?? $penyelenggaraan->jkjg_id ?? null);
        $selectedKategoriPenyelenggaraanId = old('kategori_penyelenggaraan_id', $penyelenggaraan->KATEGORI_PENYELENGGARAAN_ID ?? $penyelenggaraan->kategori_penyelenggaraan_id ?? '');
    @endphp

    <x-common.component-card title="Borang Kemaskini Penyelenggaraan">
        <form method="POST" action="{{ route('penyelenggaraan-update', $penyelenggaraan->ID ?? $penyelenggaraan->id) }}">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label>No Plat Kenderaan</label>
                    <select name="vehicle_id" id="vehicle_id" required>
                        <option value="" disabled>-- Sila Pilih No Plat --</option>

                        @foreach($vehicles as $vehicle)
                            @php
                                $vehicleId = $vehicle->ID ?? $vehicle->id ?? null;
                                $noPlat = $vehicle->NO_PLAT ?? $vehicle->no_plat ?? '-';
                                $jenisKenderaan = $vehicle->JENIS_KENDERAAN ?? $vehicle->jenis_kenderaan ?? '';
                                $buatan = $vehicle->BUATAN ?? $vehicle->buatan ?? '';
                                $model = $vehicle->MODEL ?? $vehicle->model ?? '';
                                $lokasiPenempatan = $vehicle->LOKASI_PENEMPATAN ?? $vehicle->lokasi_penempatan ?? '';
                            @endphp

                            <option value="{{ $vehicleId }}"
                                data-jenis-kenderaan="{{ $jenisKenderaan }}"
                                data-buatan="{{ $buatan }}"
                                data-model="{{ $model }}"
                                data-lokasi-penempatan="{{ $lokasiPenempatan }}"
                                {{ (string) $selectedVehicleId === (string) $vehicleId ? 'selected' : '' }}>
                                {{ $noPlat }}
                            </option>
                        @endforeach
                    </select>

                    @error('vehicle_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Jenis Kenderaan</label>
                    <input type="text" id="jenis_kenderaan" readonly>
                </div>

                <div class="form-group">
                    <label>Model Kenderaan</label>
                    <input type="text" id="model_kenderaan" readonly>
                </div>

                <div class="form-group">
                    <label>Lokasi Penempatan</label>
                    <input type="text" id="lokasi_penempatan" readonly>
                </div>

                <div class="form-group">
                    <label>Pemandu</label>
                    <select name="driver_id" id="driver_id" required>
                        <option value="" disabled>-- Sila Pilih Pemandu --</option>

                        @foreach($drivers as $driver)
                            @php
                                $driverId = $driver->ID ?? $driver->id ?? null;
                                $nama = $driver->NAMA ?? $driver->nama ?? '-';
                                $nostaff = $driver->NOSTAFF ?? $driver->nostaff ?? '-';
                                $jabatan = $driver->JABATAN ?? $driver->jabatan ?? '';
                            @endphp

                            <option value="{{ $driverId }}"
                                {{ (string) $selectedDriverId === (string) $driverId ? 'selected' : '' }}>
                                {{ $nama }} - {{ $nostaff }} {{ $jabatan ? '(' . $jabatan . ')' : '' }}
                            </option>
                        @endforeach
                    </select>

                    @error('driver_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>No Telefon Pemandu</label>
                    <input type="text"
                        name="driver_phone_num"
                        value="{{ old('driver_phone_num', $penyelenggaraan->DRIVER_PHONE_NUM ?? $penyelenggaraan->driver_phone_num ?? '') }}"
                        placeholder="Contoh: 0123456789">

                    @error('driver_phone_num')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>JKJG</label>
                    <select name="jkjg_id" id="jkjg_id" required>
                        <option value="" disabled>-- Sila Pilih JKJG --</option>

                        @foreach($jkjgUsers as $jkjg)
                            @php
                                $jkjgId = $jkjg->ID ?? $jkjg->id ?? null;
                                $nama = $jkjg->NAMA ?? $jkjg->nama ?? '-';
                                $nostaff = $jkjg->NOSTAFF ?? $jkjg->nostaff ?? '-';
                                $jabatan = $jkjg->JABATAN ?? $jkjg->jabatan ?? '';
                            @endphp

                            <option value="{{ $jkjgId }}"
                                {{ (string) $selectedJkjgId === (string) $jkjgId ? 'selected' : '' }}>
                                {{ $nama }} - {{ $nostaff }} {{ $jabatan ? '(' . $jabatan . ')' : '' }}
                            </option>
                        @endforeach
                    </select>

                    @error('jkjg_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Odometer (KM)</label>
                    <input type="number"
                        name="odometer"
                        value="{{ old('odometer', $penyelenggaraan->ODOMETER ?? $penyelenggaraan->odometer ?? '') }}"
                        placeholder="Contoh: 125000"
                        min="0"
                        step="1"
                        required>

                    @error('odometer')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Jenis Permohonan</label>
                    <select name="kategori_penyelenggaraan_id" id="repair_category" required>
                        <option value="" disabled>-- Sila Pilih Jenis Permohonan --</option>
                        @foreach($kategoriList as $kategoriOption)
                            @php
                                $kategoriOptionId = $kategoriOption->ID ?? $kategoriOption->id ?? null;
                                $kategoriOptionNama = $kategoriOption->NAMA_KATEGORI ?? $kategoriOption->nama_kategori ?? '';
                            @endphp
                            <option value="{{ $kategoriOptionId }}"
                                data-nama="{{ strtoupper($kategoriOptionNama) }}"
                                {{ $selectedKategoriPenyelenggaraanId == $kategoriOptionId ? 'selected' : '' }}>
                                {{ $kategoriOptionNama }}
                            </option>
                        @endforeach
                    </select>

                    @error('kategori_penyelenggaraan_id')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group form-full">
                    <label>Keterangan Kerosakan</label>
                    <textarea name="repair_remark"
                        rows="5"
                        placeholder="Sila nyatakan keterangan kerosakan atau keperluan penyelenggaraan..."
                        required>{{ old('repair_remark', $penyelenggaraan->REPAIR_REMARK ?? $penyelenggaraan->repair_remark ?? '') }}</textarea>

                    @error('repair_remark')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                @php
                    $existingLatitude = old('location_latitude', $penyelenggaraan->LOCATION_LATITUDE ?? $penyelenggaraan->location_latitude ?? '');
                    $existingLongitude = old('location_longitude', $penyelenggaraan->LOCATION_LONGITUDE ?? $penyelenggaraan->location_longitude ?? '');
                    $existingLocationName = old('location_name', $penyelenggaraan->LOCATION_NAME ?? $penyelenggaraan->location_name ?? '');
                @endphp

                <div id="breakdown_location_section" class="form-group form-full hidden">
                    <label>Lokasi Breakdown / Lokasi Semasa</label>

                    <div class="map-action-row">
                        <button type="button" class="map-btn" onclick="getCurrentLocation()">
                            Guna Lokasi Semasa
                        </button>

                        <span class="map-help">
                            Klik pada peta untuk pilih lokasi atau gunakan lokasi semasa.
                        </span>
                    </div>

                    <div id="breakdownMap"></div>

                    <input type="hidden"
                        name="location_latitude"
                        id="location_latitude"
                        value="{{ $existingLatitude }}">

                    <input type="hidden"
                        name="location_longitude"
                        id="location_longitude"
                        value="{{ $existingLongitude }}">

                    <div class="mt-3">
                        <label>Koordinat Dipilih</label>
                        <input type="text"
                            id="coordinate_display"
                            readonly
                            placeholder="Koordinat akan dipaparkan selepas lokasi dipilih">
                    </div>

                    @error('location_latitude')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                    @error('location_longitude')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                    <div class="mt-3">
                        <label>Catatan Lokasi</label>
                        <input type="text"
                            name="location_name"
                            id="location_name"
                            value="{{ $existingLocationName }}"
                            placeholder="Contoh: Tepi jalan utama, hadapan bangunan, kawasan parkir dan sebagainya">

                        @error('location_name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-footer">
                <a href="{{ route('penyelenggaraan-list') }}" class="btn-secondary">
                    Kembali
                </a>

                <button type="submit" class="btn-primary">
                    Kemaskini Permohonan
                </button>
            </div>
        </form>
    </x-common.component-card>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-full {
        grid-column: span 2 / span 2;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 700;
        color: #344054;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        border: 1px solid #d0d5dd;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 14px;
        color: #101828;
        background: #ffffff;
        outline: none;
        transition: all .2s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }

    .form-group input[readonly] {
        background: #f8fafc;
        color: #475467;
    }

    .form-error {
        color: #dc2626;
        font-size: 12px;
        font-weight: 600;
    }

    .form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #eef1f4;
    }

    .btn-primary,
    .btn-secondary,
    .map-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        padding: 12px 18px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: all .2s ease;
    }

    .btn-primary {
        background: #2563eb;
        color: #ffffff;
    }

    .btn-primary:hover {
        background: #1d4ed8;
    }

    .btn-secondary {
        background: #f2f4f7;
        color: #344054;
    }

    .btn-secondary:hover {
        background: #e4e7ec;
    }

    .map-action-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .map-btn {
        background: #ecfdf3;
        color: #027a48;
        border: 1px solid #abefc6;
    }

    .map-btn:hover {
        background: #d1fadf;
    }

    .map-help {
        color: #667085;
        font-size: 13px;
    }

    #breakdownMap {
        width: 100%;
        height: 420px;
        border-radius: 16px;
        border: 1px solid #d0d5dd;
        overflow: hidden;
        background: #f2f4f7;
    }

    .hidden {
        display: none !important;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-full {
            grid-column: span 1 / span 1;
        }

        #breakdownMap {
            height: 320px;
        }
    }
</style>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const vehicleSelect = document.getElementById('vehicle_id');
    const jenisInput = document.getElementById('jenis_kenderaan');
    const modelInput = document.getElementById('model_kenderaan');
    const lokasiInput = document.getElementById('lokasi_penempatan');

    const repairCategorySelect = document.getElementById('repair_category');
    const breakdownLocationSection = document.getElementById('breakdown_location_section');

    const latitudeInput = document.getElementById('location_latitude');
    const longitudeInput = document.getElementById('location_longitude');
    const coordinateDisplay = document.getElementById('coordinate_display');
    const locationNameInput = document.getElementById('location_name');

    let map = null;
    let marker = null;

    function updateVehicleInfo() {
        if (!vehicleSelect) return;

        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            jenisInput.value = '';
            modelInput.value = '';
            lokasiInput.value = '';
            return;
        }

        const jenis = selectedOption.dataset.jenisKenderaan || '';
        const buatan = selectedOption.dataset.buatan || '';
        const model = selectedOption.dataset.model || '';
        const lokasi = selectedOption.dataset.lokasiPenempatan || '';

        jenisInput.value = jenis;
        modelInput.value = (buatan + ' ' + model).trim();
        lokasiInput.value = lokasi;
    }

    function initMap() {
        if (map) return;

        const defaultLat = 5.3980;
        const defaultLng = 100.4030;

        let startLat = defaultLat;
        let startLng = defaultLng;

        if (latitudeInput.value && longitudeInput.value) {
            startLat = parseFloat(latitudeInput.value) || defaultLat;
            startLng = parseFloat(longitudeInput.value) || defaultLng;
        }

        map = L.map('breakdownMap').setView([startLat, startLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        marker = L.marker([startLat, startLng], {
            draggable: true
        }).addTo(map);

        map.on('click', function (event) {
            setCoordinate(event.latlng.lat, event.latlng.lng);
        });

        marker.on('dragend', function (event) {
            const position = event.target.getLatLng();
            setCoordinate(position.lat, position.lng);
        });

        if (latitudeInput.value && longitudeInput.value && coordinateDisplay) {
            coordinateDisplay.value = '(' + latitudeInput.value + ', ' + longitudeInput.value + ')';
        }
    }

    function setCoordinate(lat, lng) {
        const latValue = Number(lat).toFixed(6);
        const lngValue = Number(lng).toFixed(6);

        latitudeInput.value = latValue;
        longitudeInput.value = lngValue;

        if (coordinateDisplay) {
            coordinateDisplay.value = '(' + latValue + ', ' + lngValue + ')';
        }

        if (marker && map) {
            marker.setLatLng([latValue, lngValue]);
            map.setView([latValue, lngValue], 16);
        }
    }

    function toggleBreakdownLocation(resetOnHide) {
        const selectedOption = repairCategorySelect
            ? repairCategorySelect.options[repairCategorySelect.selectedIndex]
            : null;

        const selectedCategory = selectedOption
            ? (selectedOption.dataset.nama || '').toUpperCase()
            : '';

        const isBreakdown = selectedCategory === 'BREAKDOWN';

        if (isBreakdown) {
            breakdownLocationSection.classList.remove('hidden');

            latitudeInput.setAttribute('required', 'required');
            longitudeInput.setAttribute('required', 'required');
            locationNameInput.setAttribute('required', 'required');

            initMap();

            setTimeout(function () {
                if (map) {
                    map.invalidateSize();
                }
            }, 300);
        } else {
            breakdownLocationSection.classList.add('hidden');

            latitudeInput.removeAttribute('required');
            longitudeInput.removeAttribute('required');
            locationNameInput.removeAttribute('required');

            if (resetOnHide) {
                latitudeInput.value = '';
                longitudeInput.value = '';
                coordinateDisplay.value = '';
                locationNameInput.value = '';
            }
        }
    }

    if (vehicleSelect) {
        vehicleSelect.addEventListener('change', updateVehicleInfo);
        updateVehicleInfo();
    }

    if (repairCategorySelect) {
        repairCategorySelect.addEventListener('change', function () {
            toggleBreakdownLocation(true);
        });
        toggleBreakdownLocation(false);
    }

    if (typeof TomSelect !== 'undefined') {
        new TomSelect('#vehicle_id', {
            create: false,
            allowEmptyOption: true,
            placeholder: 'Cari no plat...',
            maxOptions: 5000,
            searchField: ['text'],
            sortField: { field: 'text', direction: 'asc' },
            onChange: function () {
                updateVehicleInfo();
            }
        });

        new TomSelect('#driver_id', {
            create: false,
            allowEmptyOption: true,
            placeholder: 'Cari pemandu...',
            maxOptions: 5000,
            searchField: ['text'],
            sortField: { field: 'text', direction: 'asc' }
        });

        new TomSelect('#jkjg_id', {
            create: false,
            allowEmptyOption: true,
            placeholder: 'Cari JKJG...',
            maxOptions: 5000,
            searchField: ['text'],
            sortField: { field: 'text', direction: 'asc' }
        });
    }

    window.getCurrentLocation = function () {
        if (!navigator.geolocation) {
            alert('Browser tidak menyokong fungsi lokasi semasa.');
            return;
        }

        initMap();

        navigator.geolocation.getCurrentPosition(
            function (position) {
                setCoordinate(position.coords.latitude, position.coords.longitude);
            },
            function () {
                alert('Tidak dapat mendapatkan lokasi semasa. Sila benarkan akses lokasi atau klik pada peta.');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    };
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
        confirmButtonText: 'OK'
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
        confirmButtonText: 'OK'
    });
});
</script>
@endif

@endsection
