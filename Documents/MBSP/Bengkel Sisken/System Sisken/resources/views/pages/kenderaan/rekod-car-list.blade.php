@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">Senarai maklumat kenderaan.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('rekod-car-export') }}"
               class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-emerald-700">
                Export Excel
            </a>

            <a href="{{ route('rekod-car-add') }}"
               class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                + Tambah Kenderaan
            </a>
        </div>
    </div>

    <x-common.component-card title="Senarai Kenderaan">
        <div class="p-1 overflow-x-auto">
            <table id="rekodCarTable" class="user-table rekod-car-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>No Plat</th>
                        <th>Buatan / Model</th>
                        <th>Jabatan</th>
                        <th>Bahan Bakar</th>
                        <th>Tahun</th>
                        <th>Jenis Badan</th>
                        <th>Geran</th>
                        <th>CN</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($cars as $index => $car)
                        @php
                            $vehicleId = $car->ID ?? $car->id ?? $index;

                            $status = strtoupper(trim($car->STATUS_DATA ?? $car->status_data ?? '-'));

                            $statusClass = match($status) {
                                'ACTIVE' => 'status-active',
                                'PENDING' => 'status-pending',
                                'INACTIVE' => 'status-pending',
                                'DELETE', 'DELETED' => 'status-delete',
                                default => 'status-default',
                            };

                            $geranFile = $car->GERAN_FILE ?? $car->geran_file ?? null;
                            $cnFile = $car->CN_FILE ?? $car->cn_file ?? null;
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td class="font-semibold">
                                {{ $car->NO_PLAT ?? $car->no_plat ?? '-' }}
                            </td>

                            <td class="cell-text">
                                <div class="font-semibold">
                                    {{ $car->BUATAN ?? $car->buatan ?? '-' }}
                                </div>
                                <div class="text-gray-500 text-xs">
                                    {{ $car->MODEL ?? $car->model ?? '-' }}
                                </div>
                            </td>

                            <td class="cell-text">
                                {{ $car->JABATAN ?? $car->jabatan ?? '-' }}
                            </td>

                            <td>
                                {{ $car->JENIS_BAHAN_BAKAR ?? $car->jenis_bahan_bakar ?? '-' }}
                            </td>

                            <td>
                                {{ $car->TAHUN_DIBUAT ?? $car->tahun_dibuat ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $car->JENIS_BADAN ?? $car->jenis_badan ?? '-' }}
                            </td>

                            <td>
                                @if($geranFile)
                                    <button type="button"
                                            class="file-link"
                                            onclick="openVehicleModal('file-geran-modal-{{ $vehicleId }}')">
                                        Lihat
                                    </button>
                                @else
                                    <button type="button"
                                            class="upload-btn"
                                            onclick="openVehicleModal('file-geran-modal-{{ $vehicleId }}')">
                                        Upload
                                    </button>
                                @endif
                            </td>

                            <td>
                                @if($cnFile)
                                    <button type="button"
                                            class="file-link"
                                            onclick="openVehicleModal('file-cn-modal-{{ $vehicleId }}')">
                                        Lihat
                                    </button>
                                @else
                                    <button type="button"
                                            class="upload-btn"
                                            onclick="openVehicleModal('file-cn-modal-{{ $vehicleId }}')">
                                        Upload
                                    </button>
                                @endif
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $status === 'INACTIVE' ? 'TAK AKTIF' : $status }}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                                    <button type="button"
                                            class="action-btn info-btn"
                                            title="Info"
                                            onclick="openVehicleModal('vehicle-modal-{{ $vehicleId }}')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 17V11M12 7H12.01M21 12A9 9 0 1 1 3 12A9 9 0 0 1 21 12Z"
                                                  stroke="currentColor"
                                                  stroke-width="1.8"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round" />
                                        </svg>
                                    </button>

                                    <a href="{{ route('rekod-car-edit', $vehicleId) }}"
                                       class="action-btn edit-btn"
                                       title="Edit">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <path d="M13.93 6.93L17.07 10.07M4 20H7.14L16.3 10.84C16.69 10.45 16.69 9.82 16.3 9.43L14.57 7.7C14.18 7.31 13.55 7.31 13.16 7.7L4 16.86V20Z"
                                                  stroke="currentColor"
                                                  stroke-width="1.8"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('lupus-car-add', $vehicleId) }}"
                                        class="action-btn lupus-btn"
                                        title="Lupus Kenderaan"
                                        onclick="return confirm('Adakah anda pasti mahu lupus kenderaan ini?')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                <path d="M10 11V17M14 11V17M5 7H19M17 7L16.47 19.13C16.43 20.03 15.69 20.75 14.79 20.75H9.21C8.31 20.75 7.57 20.03 7.53 19.13L7 7M9 7V4.75C9 4.34 9.34 4 9.75 4H14.25C14.66 4 15 4.34 15 4.75V7"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round" />
                                            </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-common.component-card>

    {{-- MODALS MUST BE OUTSIDE THE TABLE --}}
    @foreach($cars as $index => $car)
        @php
            $vehicleId = $car->ID ?? $car->id ?? $index;
            $geranFile = $car->GERAN_FILE ?? $car->geran_file ?? null;
            $cnFile = $car->CN_FILE ?? $car->cn_file ?? null;

            $formatDate = function ($date) {
                if (!$date || $date === '-') {
                    return '-';
                }

                try {
                    return \Carbon\Carbon::parse($date)->format('d/m/Y');
                } catch (\Exception $e) {
                    return '-';
                }
            };

            $details = [
                'No Plat' => $car->NO_PLAT ?? $car->no_plat ?? '-',
                'Buatan' => $car->BUATAN ?? $car->buatan ?? '-',
                'Model' => $car->MODEL ?? $car->model ?? '-',
                'Jabatan' => $car->JABATAN ?? $car->jabatan ?? '-',
                'Kod Jabatan' => $car->KOD_JABATAN ?? $car->kod_jabatan ?? '-',
                'Jenis Bahan Bakar' => $car->JENIS_BAHAN_BAKAR ?? $car->jenis_bahan_bakar ?? '-',
                'Tahun Dibuat' => $car->TAHUN_DIBUAT ?? $car->tahun_dibuat ?? '-',
                'Jenis Badan' => $car->JENIS_BADAN ?? $car->jenis_badan ?? '-',
                'Keupayaan Enjin' => $car->KEUPAYAAN_ENJIN ?? $car->keupayaan_enjin ?? '-',
                'No Chasis' => $car->NO_CHASIS ?? $car->no_chasis ?? '-',
                'No Enjin' => $car->NO_ENJIN ?? $car->no_enjin ?? '-',
                'Kelas Kegunaan' => $car->KELAS_KEGUNAAN ?? $car->kelas_kegunaan ?? '-',
                'Jenis Kenderaan' => $car->JENIS_KENDERAAN ?? $car->jenis_kenderaan ?? '-',
                'Lokasi Penempatan' => $car->LOKASI_PENEMPATAN ?? $car->lokasi_penempatan ?? '-',
                'Pengawas Kenderaan' => $car->PENGAWAS_KENDERAAN ?? $car->pengawas_kenderaan ?? '-',
                'Tarikh Pendaftaran' => $formatDate($car->TARIKH_PENDAFTARAN ?? $car->tarikh_pendaftaran ?? null),
                'Kelas Kenderaan' => $car->KELAS_KENDERAAN ?? $car->kelas_kenderaan ?? '-',
                'Nama Pembekal' => $car->NAMA_PEMBEKAL ?? $car->nama_pembekal ?? '-',
                'Alamat Pembekal' => $car->ALAMAT_PEMBEKAL ?? $car->alamat_pembekal ?? '-',
                'Tarikh Beli' => $formatDate($car->TARIKH_BELI ?? $car->tarikh_beli ?? null),
                'Harga Beli' => $car->HARGA_BELI ?? $car->harga_beli ?? '-',
                'Status' => $status === 'INACTIVE' ? 'TAK AKTIF' : ($car->STATUS_DATA ?? $car->status_data ?? '-'),
            ];
        @endphp

        {{-- MODAL INFO --}}
        <div id="vehicle-modal-{{ $vehicleId }}" class="vehicle-modal hidden">
            <div class="vehicle-modal-overlay" onclick="closeVehicleModal('vehicle-modal-{{ $vehicleId }}')"></div>

            <div class="vehicle-modal-card">
                <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            Maklumat Kenderaan
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ $car->NO_PLAT ?? $car->no_plat ?? '-' }}
                        </p>
                    </div>

                    <button type="button"
                            class="modal-close-btn"
                            onclick="closeVehicleModal('vehicle-modal-{{ $vehicleId }}')">
                        ×
                    </button>
                </div>

                <div class="grid grid-cols-12 gap-4 text-sm">
                    @foreach($details as $label => $value)
                        <div class="col-span-6">
                            <div class="detail-box">
                                <div class="detail-label">
                                    {{ $label }}
                                </div>
                                <div class="detail-value">
                                    {{ $value ?: '-' }}
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="col-span-6">
                        <div class="detail-box">
                            <div class="detail-label">Geran</div>
                            <div class="detail-value">
                                @if($geranFile)
                                    <button type="button"
                                            class="file-link"
                                            onclick="openVehicleModal('file-geran-modal-{{ $vehicleId }}')">
                                        Lihat
                                    </button>
                                @else
                                    <button type="button"
                                            class="upload-btn"
                                            onclick="openVehicleModal('file-geran-modal-{{ $vehicleId }}')">
                                        Upload
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-span-6">
                        <div class="detail-box">
                            <div class="detail-label">CN</div>
                            <div class="detail-value">
                                @if($cnFile)
                                    <button type="button"
                                            class="file-link"
                                            onclick="openVehicleModal('file-cn-modal-{{ $vehicleId }}')">
                                        Lihat
                                    </button>
                                @else
                                    <button type="button"
                                            class="upload-btn"
                                            onclick="openVehicleModal('file-cn-modal-{{ $vehicleId }}')">
                                        Upload
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- GERAN MODAL --}}
        <div id="file-geran-modal-{{ $vehicleId }}" class="vehicle-modal hidden">
            <div class="vehicle-modal-overlay" onclick="closeVehicleModal('file-geran-modal-{{ $vehicleId }}')"></div>

            <div class="vehicle-modal-card upload-modal-card">
                <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Geran</h3>
                        <p class="text-sm text-gray-500">
                            {{ $car->NO_PLAT ?? $car->no_plat ?? '-' }}
                        </p>
                    </div>

                    <button type="button"
                            class="modal-close-btn"
                            onclick="closeVehicleModal('file-geran-modal-{{ $vehicleId }}')">
                        ×
                    </button>
                </div>

                @if($geranFile)
                    <div class="file-info-box">
                        <div class="detail-label">Nama Fail</div>
                        <div class="detail-value">
                            {{ basename($geranFile) }}
                        </div>

                        <a href="{{ route('rekod-car-view-file', ['path' => $geranFile]) }}"
                           target="_blank"
                           class="submit-btn mt-4">
                            Buka Fail
                        </a>
                    </div>

                    <hr class="my-4">

                    <form action="{{ route('rekod-car-upload-file', $vehicleId) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="file_type" value="geran">

                        <label class="block text-sm font-semibold mb-2">
                            Ganti Fail Geran
                        </label>

                        <input type="file"
                               name="upload_file"
                               class="file-input"
                               accept=".pdf,.jpg,.jpeg,.png"
                               required>

                        <div class="flex justify-end gap-2 mt-4">
                            <button type="submit" class="submit-btn">
                                Ganti Fail
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('rekod-car-delete-file', $vehicleId) }}"
                          method="POST"
                          class="mt-3"
                          onsubmit="return confirm('Padam fail Geran ini?')">
                        @csrf
                        @method('DELETE')

                        <input type="hidden" name="file_type" value="geran">

                        <button type="submit" class="file-delete-btn">
                            Delete
                        </button>
                    </form>
                @else
                    <form action="{{ route('rekod-car-upload-file', $vehicleId) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="file_type" value="geran">

                        <label class="block text-sm font-semibold mb-2">
                            Upload Fail Geran
                        </label>

                        <input type="file"
                               name="upload_file"
                               class="file-input"
                               accept=".pdf,.jpg,.jpeg,.png"
                               required>

                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button"
                                    class="cancel-btn"
                                    onclick="closeVehicleModal('file-geran-modal-{{ $vehicleId }}')">
                                Batal
                            </button>

                            <button type="submit" class="submit-btn">
                                Upload
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        {{-- CN MODAL --}}
        <div id="file-cn-modal-{{ $vehicleId }}" class="vehicle-modal hidden">
            <div class="vehicle-modal-overlay" onclick="closeVehicleModal('file-cn-modal-{{ $vehicleId }}')"></div>

            <div class="vehicle-modal-card upload-modal-card">
                <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">CN</h3>
                        <p class="text-sm text-gray-500">
                            {{ $car->NO_PLAT ?? $car->no_plat ?? '-' }}
                        </p>
                    </div>

                    <button type="button"
                            class="modal-close-btn"
                            onclick="closeVehicleModal('file-cn-modal-{{ $vehicleId }}')">
                        ×
                    </button>
                </div>

                @if($cnFile)
                    <div class="file-info-box">
                        <div class="detail-label">Nama Fail</div>
                        <div class="detail-value">
                            {{ basename($cnFile) }}
                        </div>

                        <a href="{{ route('rekod-car-view-file', ['path' => $cnFile]) }}"
                           target="_blank"
                           class="submit-btn mt-4">
                            Buka Fail
                        </a>
                    </div>

                    <hr class="my-4">

                    <form action="{{ route('rekod-car-upload-file', $vehicleId) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="file_type" value="cn">

                        <label class="block text-sm font-semibold mb-2">
                            Ganti Fail CN
                        </label>

                        <input type="file"
                               name="upload_file"
                               class="file-input"
                               accept=".pdf,.jpg,.jpeg,.png"
                               required>

                        <div class="flex justify-end gap-2 mt-4">
                            <button type="submit" class="submit-btn">
                                Edit / Replace
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('rekod-car-delete-file', $vehicleId) }}"
                          method="POST"
                          class="mt-3"
                          onsubmit="return confirm('Padam fail CN ini?')">
                        @csrf
                        @method('DELETE')

                        <input type="hidden" name="file_type" value="cn">

                        <button type="submit" class="file-delete-btn">
                            Delete
                        </button>
                    </form>
                @else
                    <form action="{{ route('rekod-car-upload-file', $vehicleId) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="file_type" value="cn">

                        <label class="block text-sm font-semibold mb-2">
                            Upload Fail CN
                        </label>

                        <input type="file"
                               name="upload_file"
                               class="file-input"
                               accept=".pdf,.jpg,.jpeg,.png"
                               required>

                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button"
                                    class="cancel-btn"
                                    onclick="closeVehicleModal('file-cn-modal-{{ $vehicleId }}')">
                                Batal
                            </button>

                            <button type="submit" class="submit-btn">
                                Upload
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>

<style>
    .user-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .rekod-car-table {
        min-width: 1750px;
        table-layout: fixed;
    }

    .user-table thead th {
        background: #f8f9fb;
        color: #344054;
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .3px;
        padding: 14px 16px;
        border-bottom: 1px solid #e9ecef;
        white-space: nowrap;
        text-align: left;
        vertical-align: middle;
    }

    .user-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #eef1f4;
        vertical-align: middle;
        color: #101828;
    }

    .user-table tbody tr:hover td {
        background: #fafbfc;
    }

    .rekod-car-table th:nth-child(1),
    .rekod-car-table td:nth-child(1) {
        width: 60px !important;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        white-space: nowrap;
    }

    .rekod-car-table th:nth-child(2),
    .rekod-car-table td:nth-child(2) {
        width: 130px !important;
        min-width: 130px;
        max-width: 130px;
        white-space: nowrap;
    }

    .rekod-car-table th:nth-child(3),
    .rekod-car-table td:nth-child(3) {
        width: 260px !important;
        min-width: 260px;
        max-width: 260px;
        white-space: normal;
    }

    .rekod-car-table th:nth-child(4),
    .rekod-car-table td:nth-child(4) {
        width: 300px !important;
        min-width: 300px;
        max-width: 300px;
        white-space: normal;
    }

    .rekod-car-table th:nth-child(5),
    .rekod-car-table td:nth-child(5) {
        width: 180px !important;
        min-width: 180px;
        max-width: 180px;
        white-space: nowrap;
    }

    .rekod-car-table th:nth-child(6),
    .rekod-car-table td:nth-child(6) {
        width: 100px !important;
        min-width: 100px;
        max-width: 100px;
        text-align: center;
        white-space: nowrap;
    }

    .rekod-car-table th:nth-child(7),
    .rekod-car-table td:nth-child(7) {
        width: 220px !important;
        min-width: 220px;
        max-width: 220px;
        white-space: normal;
    }

    .rekod-car-table th:nth-child(8),
    .rekod-car-table td:nth-child(8) {
        width: 110px !important;
        min-width: 110px;
        max-width: 110px;
        text-align: center;
        white-space: nowrap;
    }

    .rekod-car-table th:nth-child(9),
    .rekod-car-table td:nth-child(9) {
        width: 100px !important;
        min-width: 100px;
        max-width: 100px;
        text-align: center;
        white-space: nowrap;
    }

    .rekod-car-table th:nth-child(10),
    .rekod-car-table td:nth-child(10) {
        width: 150px !important;
        min-width: 150px;
        max-width: 150px;
        text-align: center;
        white-space: nowrap;
    }

    .rekod-car-table th:nth-child(11),
    .rekod-car-table td:nth-child(11) {
        width: 140px !important;
        min-width: 140px;
        max-width: 140px;
        text-align: center;
        white-space: nowrap;
    }

    .cell-text {
        line-height: 1.45;
        font-weight: 500;
        overflow-wrap: break-word;
        word-break: normal;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
    }

    .status-active {
        background: #ecfdf3;
        color: #027a48;
        border: 1px solid #abefc6;
    }

    .status-pending {
        background: #fffaeb;
        color: #b54708;
        border: 1px solid #fedf89;
    }

    .status-delete {
        background: #fef3f2;
        color: #b42318;
        border: 1px solid #fecdca;
    }

    .status-default {
        background: #f2f4f7;
        color: #344054;
        border: 1px solid #d0d5dd;
    }

    .file-link,
    .upload-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .file-link {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .upload-btn {
        background: #ecfdf3;
        color: #027a48;
        border: 1px solid #abefc6;
    }

    .action-btn-group {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        transition: all .2s ease;
        cursor: pointer;
        text-decoration: none;
        padding: 0;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    .info-btn {
        background: #f0f9ff;
        color: #026aa2;
        border-color: #b9e6fe;
    }

    .edit-btn {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .vehicle-modal {
        position: fixed;
        inset: 0;
        z-index: 999999;
    }

    .vehicle-modal.hidden {
        display: none;
    }

    .vehicle-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }

    .vehicle-modal-card {
        position: relative;
        z-index: 1;
        width: min(1000px, calc(100% - 32px));
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        margin: 24px auto;
        background: #fff;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
    }

    .upload-modal-card {
        max-width: 520px;
    }

    .modal-close-btn {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        background: #f2f4f7;
        color: #344054;
        font-size: 26px;
        line-height: 1;
    }

    .detail-box {
        border: 1px solid #e4e7ec;
        border-radius: 12px;
        padding: 12px 14px;
        background: #fcfcfd;
        min-height: 72px;
    }

    .detail-label {
        font-size: 11px;
        font-weight: 700;
        color: #667085;
        text-transform: uppercase;
        letter-spacing: .3px;
        margin-bottom: 6px;
    }

    .detail-value {
        font-size: 14px;
        font-weight: 600;
        color: #101828;
        line-height: 1.4;
        overflow-wrap: break-word;
    }

    .file-input {
        width: 100%;
        border: 1px solid #d0d5dd;
        border-radius: 10px;
        padding: 10px;
        font-size: 13px;
    }

    .cancel-btn,
    .submit-btn,
    .file-delete-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 9px 16px;
        border-radius: 10px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        border: 0;
    }

    .cancel-btn {
        background: #f2f4f7;
        color: #344054;
    }

    .submit-btn {
        background: #2563eb;
        color: #fff;
    }

    .file-info-box {
        border: 1px solid #e4e7ec;
        border-radius: 12px;
        padding: 14px;
        background: #fcfcfd;
    }

    .file-delete-btn {
        width: 100%;
        background: #dc2626;
        color: #fff;
    }

    .file-delete-btn:hover {
        background: #b91c1c;
    }

    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table {
        width: 100% !important;
    }

    .dataTables_scrollHeadInner table.rekod-car-table,
    .dataTables_scrollBody table.rekod-car-table {
        min-width: 1750px !important;
    }

    .lupus-btn {
        background: #c55454;
        color: #ffffff;
        border-color: #da9494;
    }

    .lupus-btn:hover {
        background: #c55454;
        border-color: #da9494;
        color: #ffffff;
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
function openVehicleModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeVehicleModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');

    const openedModal = document.querySelector('.vehicle-modal:not(.hidden)');

    if (!openedModal) {
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.vehicle-modal').forEach(function (modal) {
            modal.classList.add('hidden');
        });

        document.body.style.overflow = '';
    }
});

$(document).ready(function () {
    if (!document.querySelector('#rekodCarTable')) return;

    const rekodCarTable = $('#rekodCarTable').DataTable({
        responsive: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,

        columns: [
            { width: '60px' },
            { width: '130px' },
            { width: '260px' },
            { width: '300px' },
            { width: '180px' },
            { width: '100px' },
            { width: '220px' },
            { width: '110px' },
            { width: '100px' },
            { width: '150px' },
            { width: '140px', orderable: false }
        ],

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            zeroRecords: "Tiada data dijumpai",
            emptyTable: "Tiada data kenderaan.",
            info: "Paparan _START_ hingga _END_ daripada _TOTAL_ rekod",
            infoEmpty: "Tiada rekod",
            infoFiltered: "(ditapis daripada _MAX_ jumlah rekod)",
            paginate: {
                first: "Pertama",
                last: "Akhir",
                next: "Seterusnya",
                previous: "Sebelumnya"
            }
        },

        initComplete: function () {
            this.api().columns.adjust();
        }
    });

    setTimeout(function () {
        rekodCarTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        rekodCarTable.columns.adjust();
    });
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