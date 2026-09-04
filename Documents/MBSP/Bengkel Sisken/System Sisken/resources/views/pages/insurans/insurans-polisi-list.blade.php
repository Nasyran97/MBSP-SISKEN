@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title ?? 'Senarai Polisi Insurans' }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai kenderaan dan maklumat polisi insurans.
            </p>
        </div>
    </div>

    <x-common.component-card title="Senarai Polisi Insurans">
        <div class="p-1 overflow-x-auto">
            <table id="insuransPolisiTable" class="user-table">
                @php
                    $polisiStatus = strtoupper(trim($car->POLISI_STATUS ?? ''));

                    $canRenew = in_array($polisiStatus, [
                        'AKAN TAMAT TEMPOH',
                        'TAMAT TEMPOH'
                    ]);

                    $hasFuturePolicy = $car->HAS_FUTURE_POLICY ?? false;

                    $hasAnyPolicy = !empty($car->POLISI_ID);
                @endphp
                @php
                    $polisiStatus = strtoupper(trim($car->POLISI_STATUS ?? ''));

                    $statusClass = match($polisiStatus) {
                        'AKTIF' => 'status-active',
                        'AKAN TAMAT TEMPOH' => 'status-warning',
                        'TAMAT TEMPOH' => 'status-expired',
                        'AKAN AKTIF' => 'status-upcoming',
                        default => 'status-default',
                    };
                @endphp
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>No Plat</th>
                        <th>No Enjin</th>
                        <th>No Chasis</th>
                        <th>Tarikh Pendaftaran</th>
                        <th>Usia</th>
                        <th>Kelas Kegunaan</th>
                        <th>Polisi</th>
                        <th>Panel Insurans</th>
                        <th>Tarikh Tamat Insurans</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($vehicles as $index => $car)
                    @php
                        $vehicleId = $car->VEHICLE_ID ?? $car->vehicle_id ?? null;
                        $noPlat = $car->NO_PLAT ?? $car->no_plat ?? '-';
                        $noEnjin = $car->NO_ENJIN ?? $car->no_enjin ?? '-';
                        $noChasis = $car->NO_CHASIS ?? $car->no_chasis ?? '-';
                        $tarikhPendaftaran = $car->TARIKH_PENDAFTARAN ?? $car->tarikh_pendaftaran ?? null;
                        $usia = $car->USIA ?? $car->usia ?? null;
                        $kelasKegunaan = $car->KELAS_KEGUNAAN ?? $car->kelas_kegunaan ?? '-';

                        $jenisPolisi = $car->JENIS_POLISI ?? $car->jenis_polisi ?? null;
                        $panelId = $car->PANEL_ID ?? $car->panel_id ?? null;
                        $panelName = $car->PANEL_NAME ?? $car->panel_name ?? null;
                        $tarikhTamatInsurans = $car->TARIKH_TAMAT_INSURANS ?? $car->tarikh_tamat_insurans ?? null;

                        $polisiId = $car->POLISI_ID ?? $car->polisi_id ?? null;

                        $polisiStatus = strtoupper(trim(
                            $car->POLISI_STATUS
                            ?? $car->polisi_status
                            ?? ''
                        ));

                        if (!$polisiStatus && $polisiId) {
                            $polisiStatus = 'AKTIF';
                        }

                        $statusClass = match($polisiStatus) {
                            'AKTIF' => 'status-active',
                            'AKAN TAMAT TEMPOH' => 'status-warning',
                            'TAMAT TEMPOH' => 'status-expired',
                            'AKAN AKTIF' => 'status-upcoming',
                            default => 'status-default',
                        };

                        $hasAnyPolicy = !empty($polisiId);

                        $canRenew = in_array($polisiStatus, [
                            'AKAN TAMAT TEMPOH',
                            'TAMAT TEMPOH'
                        ]);

                        $hasFuturePolicy = $car->HAS_FUTURE_POLICY ?? false;
                    @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td class="font-semibold">
                                {{ $noPlat }}
                            </td>

                            <td class="cell-text">
                                {{ $noEnjin }}
                            </td>

                            <td class="cell-text">
                                {{ $noChasis }}
                            </td>

                            <td>
                                @if($tarikhPendaftaran)
                                    {{ \Carbon\Carbon::parse($tarikhPendaftaran)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                {{ $usia !== null ? $usia . ' Tahun' : '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $kelasKegunaan }}
                            </td>

                            <td>
                                @if($jenisPolisi)
                                    <span class="policy-badge">
                                        {{ $jenisPolisi }}
                                    </span>
                                @else
                                    <span class="text-muted">Tiada Insurans</span>
                                @endif
                            </td>

                            <td class="cell-text">
                                {{ $panelName ?: '-' }}
                            </td>

                            <td>
                                {{ !empty($tarikhTamatInsurans)
                                    ? \Carbon\Carbon::parse($tarikhTamatInsurans)->format('d-m-Y')
                                    : '-'
                                }}
                            </td>

                            <td>
                                @if($polisiStatus)
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $polisiStatus }}
                                    </span>
                                @else
                                    <span class="status-badge status-default">
                                        TIADA
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                            
                                    {{-- No policy yet: show Add/Edit button --}}
                                    @if(!$hasAnyPolicy)
                                        <button type="button"
                                            class="action-btn edit-btn"
                                            onclick="openPolisiEditModal('polisi-edit-modal-{{ $vehicleId }}')"
                                            title="Tambah Polisi">
                                            ✏️
                                        </button>
                            
                                    {{-- Has policy: info button always appears --}}
                                    @else
                                        <button type="button"
                                            class="action-btn info-btn"
                                            onclick="openPolisiInfoModal('polisi-info-modal-{{ $vehicleId }}')"
                                            title="Maklumat Polisi">
                                            ℹ️
                                        </button>
                            
                                        {{-- Edit/Renew only if near expiry or expired --}}
                                        @if($canRenew && !$hasFuturePolicy)
                                            <button type="button"
                                                class="action-btn edit-btn"
                                                onclick="openPolisiEditModal('polisi-edit-modal-{{ $vehicleId }}')"
                                                title="Renew Polisi">
                                                ✏️
                                            </button>
                                        @endif
                                    @endif
                            
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-4 text-gray-500">
                                Tiada data kenderaan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>

{{-- MODAL EDIT POLISI --}}
@foreach($vehicles as $car)
    @php
        $vehicleId = $car->VEHICLE_ID ?? $car->vehicle_id;
        $noPlat = $car->NO_PLAT ?? $car->no_plat ?? '-';
        $jenisPolisi = $car->JENIS_POLISI ?? $car->jenis_polisi ?? '';
        $panelId = $car->PANEL_ID ?? $car->panel_id ?? '';
        $ncd = $car->NCD ?? $car->ncd ?? '';

        $tarikhMulaInsurans = !empty($car->TARIKH_MULA_INSURANS)
            ? \Carbon\Carbon::parse($car->TARIKH_MULA_INSURANS)->format('Y-m-d')
            : '';

        $tarikhTamatInsurans = !empty($car->TARIKH_TAMAT_INSURANS)
            ? \Carbon\Carbon::parse($car->TARIKH_TAMAT_INSURANS)->format('Y-m-d')
            : '';
    @endphp

    <div id="polisi-edit-modal-{{ $vehicleId }}" class="polisi-modal hidden">
        <div class="polisi-modal-overlay" onclick="closePolisiEditModal('polisi-edit-modal-{{ $vehicleId }}')"></div>

        <div class="polisi-modal-card">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Kemaskini Polisi Insurans
                    </h3>
                    <p class="text-sm text-gray-500">
                        No Plat: {{ $noPlat }}
                    </p>
                </div>

                <button type="button"
                    class="modal-close-btn"
                    onclick="closePolisiEditModal('polisi-edit-modal-{{ $vehicleId }}')">
                    ×
                </button>
            </div>

            <form method="POST" action="{{ route('insurans-polisi-store') }}">
                @csrf

                <input type="hidden" name="vehicle_id" value="{{ $vehicleId }}">
                <input type="hidden" name="no_plat" value="{{ $noPlat }}">

                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12">
                        <label class="block mb-1 text-sm font-medium text-gray-700">
                            No Plat
                        </label>

                        <input type="text"
                            value="{{ $noPlat }}"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            readonly>
                    </div>

                    <div class="col-span-6">
                        <label class="block mb-1 text-sm font-medium text-gray-700">
                            Jenis Polisi <span class="text-red-500">*</span>
                        </label>

                        <select name="jenis_polisi"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            required>
                            {{-- <option value="" selected disabled>-- Sila Pilih Jenis Polisi --</option> --}}
                            <option value="Comprehensive" {{ $jenisPolisi == 'Comprehensive' ? 'selected' : '' }} selected>
                                Comprehensive
                            </option>
                            <option value="Third Party" {{ $jenisPolisi == 'Third Party' ? 'selected' : '' }}>
                                Third Party
                            </option>
                        </select>
                    </div>

                    @php
                        $selectedPanelId = old('panel_id');

                        if ($selectedPanelId === null || $selectedPanelId === '') {
                            $selectedPanelId = '4';
                        }
                    @endphp

                    <div class="col-span-6">
                        <label class="block mb-1 text-sm font-medium text-gray-700">
                            Syarikat Insurans <span class="text-red-500">*</span>
                        </label>

                        <select name="panel_id"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            required>

                            <option value="" disabled>-- Sila Pilih Syarikat Insurans --</option>

                            @foreach($panels as $panel)
                                @php
                                    $pid = $panel->ID ?? $panel->id;
                                    $pname = $panel->PANEL_NAME ?? $panel->panel_name ?? '-';
                                @endphp

                                <option value="{{ $pid }}"
                                    {{ (string) trim($selectedPanelId) === (string) trim($pid) ? 'selected' : '' }}>
                                    {{ $pname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-span-4">
                        <label class="block mb-1 text-sm font-medium text-gray-700">
                            NCD (%)
                        </label>
                
                        <input type="number"
                            step="0.01"
                            name="ncd"
                            value="{{ $ncd }}"
                            min="0"
                            max="100"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Contoh: 55">
                    </div>

                    @php
                        $currentYear = now('Asia/Kuala_Lumpur')->year;

                        $defaultTarikhMulaInsurans = \Carbon\Carbon::create($currentYear, 1, 1)->format('d-m-Y');
                        $defaultTarikhTamatInsurans = \Carbon\Carbon::create($currentYear, 12, 31)->format('d-m-Y');

                        $tarikhMulaInsurans = old(
                            'tarikh_mula_insurans',
                            !empty($tarikhMulaInsurans) ? $tarikhMulaInsurans : $defaultTarikhMulaInsurans
                        );

                        $tarikhTamatInsurans = old(
                            'tarikh_tamat_insurans',
                            !empty($tarikhTamatInsurans) ? $tarikhTamatInsurans : $defaultTarikhTamatInsurans
                        );
                    @endphp
                
                    <div class="col-span-4">
                        <label class="block mb-1 text-sm font-medium text-gray-700">
                            Tarikh Mula Insurans <span class="text-red-500">*</span>
                        </label>
                    
                        <input type="text"
                            name="tarikh_mula_insurans"
                            value="{{ $tarikhMulaInsurans }}"
                            class="roadtax-date-picker polisi-start-date w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Pilih tarikh"
                            required>
                    </div>
                    
                    <div class="col-span-4">
                        <label class="block mb-1 text-sm font-medium text-gray-700">
                            Tarikh Tamat Insurans
                        </label>
                    
                        <input type="text"
                            name="tarikh_tamat_insurans"
                            value="{{ $tarikhTamatInsurans }}"
                            class="polisi-end-date w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                            placeholder="Auto kira"
                            readonly>
                    </div>

                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium"
                        onclick="closePolisiEditModal('polisi-edit-modal-{{ $vehicleId }}')">
                        Batal
                    </button>

                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- MODAL INFO POLISI --}}
@foreach($vehicles as $car)
    @php
    $vehicleId = $car->VEHICLE_ID
        ?? $car->vehicle_id
        ?? $car->ID
        ?? $car->id
        ?? null;

    $noPlat = $car->NO_PLAT
        ?? $car->no_plat
        ?? '-';

    $policies = $car->POLICIES
        ?? $car->policies
        ?? collect();
    @endphp

    <div id="polisi-info-modal-{{ $vehicleId }}" class="polisi-modal hidden">
        <div class="polisi-modal-overlay" onclick="closePolisiInfoModal('polisi-info-modal-{{ $vehicleId }}')"></div>

        <div class="polisi-modal-card">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Maklumat Polisi Insurans
                    </h3>
                    <p class="text-sm text-gray-500">
                        No Plat: {{ $noPlat }}
                    </p>
                </div>

                <button type="button"
                    class="modal-close-btn"
                    onclick="closePolisiInfoModal('polisi-info-modal-{{ $vehicleId }}')">
                    ×
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="polisi-info-table">
                    <thead>
                        <tr>
                            <th>Bil</th>
                            <th>Jenis Polisi</th>
                            <th>Panel</th>
                            <th>NCD</th>
                            <th>Tarikh Mula</th>
                            <th>Tarikh Tamat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
            
                    <tbody>
                        @forelse($policies as $pIndex => $policy)
                            @php
                                $infoStatus = strtoupper(trim(
                                    $policy->POLISI_STATUS
                                    ?? $policy->polisi_status
                                    ?? ''
                                ));
            
                                $infoStatusClass = match($infoStatus) {
                                    'AKTIF' => 'status-active',
                                    'AKAN TAMAT TEMPOH' => 'status-warning',
                                    'TAMAT TEMPOH' => 'status-expired',
                                    'AKAN AKTIF' => 'status-upcoming',
                                    default => 'status-default',
                                };
            
                                $mula = $policy->TARIKH_MULA_INSURANS
                                    ?? $policy->tarikh_mula_insurans
                                    ?? null;
            
                                $tamat = $policy->TARIKH_TAMAT_INSURANS
                                    ?? $policy->tarikh_tamat_insurans
                                    ?? null;
                            @endphp
            
                            <tr>
                                <td>{{ $pIndex + 1 }}</td>
            
                                <td>
                                    {{ $policy->JENIS_POLISI ?? $policy->jenis_polisi ?? '-' }}
                                </td>
            
                                <td class="panel-cell">
                                    {{ $policy->PANEL_NAME ?? $policy->panel_name ?? '-' }}
                                </td>
            
                                <td class="text-center">
                                    {{ $policy->NCD ?? $policy->ncd ?? '-' }}
                                </td>
            
                                <td>
                                    {{ !empty($mula) ? \Carbon\Carbon::parse($mula)->format('d/m/Y') : '-' }}
                                </td>
            
                                <td>
                                    {{ !empty($tamat) ? \Carbon\Carbon::parse($tamat)->format('d/m/Y') : '-' }}
                                </td>
            
                                <td>
                                    <span class="status-badge {{ $infoStatusClass }}">
                                        {{ $infoStatus ?: '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-gray-500">
                                    Tiada maklumat polisi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endforeach

<style>
    .polisi-modal {
        position: fixed;
        inset: 0;
        z-index: 999999;
    }

    .polisi-modal.hidden {
        display: none;
    }

    .polisi-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }

    .polisi-modal-card {
        position: relative;
        z-index: 1;
        width: min(1200px, calc(100% - 40px));
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        margin: 24px auto;
        background: #fff;
        border-radius: 18px;
        padding: 32px 36px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
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

    .user-table {
        width: 100% !important;
        min-width: 1780px;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
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

    .user-table th:nth-child(1),
    .user-table td:nth-child(1) {
        width: 60px !important;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(2),
    .user-table td:nth-child(2) {
        width: 120px !important;
        min-width: 120px;
        max-width: 120px;
        white-space: nowrap;
    }

    .user-table th:nth-child(3),
    .user-table td:nth-child(3) {
        width: 180px !important;
        min-width: 180px;
        max-width: 180px;
        white-space: nowrap;
    }

    .user-table th:nth-child(4),
    .user-table td:nth-child(4) {
        width: 210px !important;
        min-width: 210px;
        max-width: 210px;
        white-space: nowrap;
    }

    .user-table th:nth-child(5),
    .user-table td:nth-child(5) {
        width: 160px !important;
        min-width: 160px;
        max-width: 160px;
        white-space: nowrap;
    }

    .user-table th:nth-child(6),
    .user-table td:nth-child(6) {
        width: 100px !important;
        min-width: 100px;
        max-width: 100px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(7),
    .user-table td:nth-child(7) {
        width: 220px !important;
        min-width: 220px;
        max-width: 220px;
        white-space: normal;
    }

    .user-table th:nth-child(8),
    .user-table td:nth-child(8) {
        width: 150px !important;
        min-width: 150px;
        max-width: 150px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(9),
    .user-table td:nth-child(9) {
        width: 260px !important;
        min-width: 260px;
        max-width: 260px;
        white-space: normal;
    }

    .user-table th:nth-child(10),
    .user-table td:nth-child(10) {
        width: 130px !important;
        min-width: 130px;
        max-width: 130px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(11),
    .user-table td:nth-child(11) {
        width: 120px !important;
        min-width: 120px;
        max-width: 120px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(12),
    .user-table td:nth-child(12) {
        width: 120px !important;
        min-width: 120px;
        max-width: 120px;
        text-align: center;
        white-space: nowrap;
    }

    .cell-text {
        line-height: 1.45;
        font-weight: 500;
        overflow-wrap: break-word;
        word-break: normal;
    }

    .text-muted {
        color: #98a2b3;
        font-size: 12px;
        font-weight: 600;
    }

    .policy-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
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

    .status-warning {
        background: #fffaeb;
        color: #b54708;
        border: 1px solid #fedf89;
    }

    .status-expired {
        background: #fef3f2;
        color: #b42318;
        border: 1px solid #fecdca;
    }

    .status-upcoming {
        background: #f3f4fa;
        color: #535DF5;
        border: 1px solid #b1b3fc;
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
        border: 1px solid #e4e7ec;
        background: #fff;
        color: #344054;
        text-decoration: none;
        transition: all .2s ease;
        cursor: pointer;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    .edit-btn {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .form-control,
    .form-control-select {
        width: 100%;
        height: 48px;
        border: 1px solid #d0d5dd;
        border-radius: 10px;
        padding: 0 14px;
        font-size: 14px;
        background: #fff;
        color: #101828;
        outline: none;
    }

    .form-control[readonly] {
        background: #f9fafb;
    }

    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table {
        width: 100% !important;
    }

    .flatpickr-calendar {
        z-index: 1000001 !important;
    }

    .info-btn {
        background: #f0f9ff;
        color: #026aa2;
        border-color: #bae6fd;
    }

    .polisi-info-table {
        width: 100%;
        min-width: 1100px;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .polisi-info-table thead th {
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

    .polisi-info-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #eef1f4;
        vertical-align: middle;
        color: #101828;
        white-space: nowrap;
    }

    .polisi-info-table tbody tr:hover td {
        background: #fafbfc;
    }

    .polisi-info-table th:nth-child(1),
    .polisi-info-table td:nth-child(1) {
        width: 60px;
        text-align: center;
    }

    .polisi-info-table th:nth-child(2),
    .polisi-info-table td:nth-child(2) {
        width: 160px;
    }

    .polisi-info-table th:nth-child(3),
    .polisi-info-table td:nth-child(3) {
        width: 360px;
    }

    .polisi-info-table th:nth-child(4),
    .polisi-info-table td:nth-child(4) {
        width: 80px;
        text-align: center;
    }

    .polisi-info-table th:nth-child(5),
    .polisi-info-table td:nth-child(5),
    .polisi-info-table th:nth-child(6),
    .polisi-info-table td:nth-child(6) {
        width: 150px;
    }

    .polisi-info-table th:nth-child(7),
    .polisi-info-table td:nth-child(7) {
        width: 180px;
        text-align: center;
    }

    .panel-cell {
        white-space: normal !important;
        line-height: 1.4;
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

function parseDateValue(value) {
    if (!value) return null;

    const parts = value.split('-').map(Number);

    if (parts.length === 3) {
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    return new Date(value);
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function calculateInsuranceEndDate(startDateValue) {
    const startDate = parseDateValue(startDateValue);

    if (!startDate || isNaN(startDate.getTime())) {
        return '';
    }

    const endDate = new Date(startDate);
    endDate.setFullYear(endDate.getFullYear() + 1);
    endDate.setDate(endDate.getDate() - 1);

    return formatDate(endDate);
}

function initPolisiDatePicker(modal) {
    if (typeof flatpickr === 'undefined') {
        console.error('flatpickr is not loaded');
        return;
    }

    const dateInputs = modal.querySelectorAll('.roadtax-date-picker');

    dateInputs.forEach(function (input) {
        if (input._flatpickr) {
            return;
        }

        flatpickr(input, {
            dateFormat: "d-m-Y",
            allowInput: true,
            position: "above",
            appendTo: document.body,
            onChange: function (selectedDates, dateStr, instance) {
                if (!instance.input.classList.contains('polisi-start-date')) return;

                const modalCard = instance.input.closest('.polisi-modal-card');
                if (!modalCard) return;

                const endDateInput = modalCard.querySelector('.polisi-end-date');
                if (!endDateInput) return;

                endDateInput.value = calculateInsuranceEndDate(dateStr);
            },
            onClose: function (selectedDates, dateStr, instance) {
                if (!instance.input.classList.contains('polisi-start-date')) return;

                const modalCard = instance.input.closest('.polisi-modal-card');
                if (!modalCard) return;

                const endDateInput = modalCard.querySelector('.polisi-end-date');
                if (!endDateInput) return;

                endDateInput.value = calculateInsuranceEndDate(instance.input.value);
            }
        });
    });
}

function openPolisiInfoModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closePolisiInfoModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openPolisiEditModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    initPolisiDatePicker(modal);

    const startDateInput = modal.querySelector('.polisi-start-date');
    const endDateInput = modal.querySelector('.polisi-end-date');

    if (startDateInput && endDateInput && startDateInput.value && !endDateInput.value) {
        endDateInput.value = calculateInsuranceEndDate(startDateInput.value);
    }
}

function closePolisiEditModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('polisi-start-date')) return;

    const modal = e.target.closest('.polisi-modal-card');
    if (!modal) return;

    const endDateInput = modal.querySelector('.polisi-end-date');
    if (!endDateInput) return;

    endDateInput.value = calculateInsuranceEndDate(e.target.value);
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.polisi-modal:not(.hidden)').forEach(function (modal) {
            modal.classList.add('hidden');
        });

        document.body.classList.remove('overflow-hidden');
    }
});

$(document).ready(function () {
    const insuransPolisiTable = $('#insuransPolisiTable').DataTable({
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
            { width: '120px' },
            { width: '180px' },
            { width: '210px' },
            { width: '160px' },
            { width: '100px' },
            { width: '220px' },
            { width: '150px' },
            { width: '260px' },
            { width: '130px' },
            { width: '150px' },
            { width: '120px', orderable: false }
        ],

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            zeroRecords: "Tiada data dijumpai",
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
        insuransPolisiTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        insuransPolisiTable.columns.adjust();
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