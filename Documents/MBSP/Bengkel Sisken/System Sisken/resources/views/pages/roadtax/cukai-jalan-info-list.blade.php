@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai maklumat cukai jalan kenderaan.
            </p>
        </div>
    </div>

    <x-common.component-card title="Senarai Maklumat Cukai Jalan">
        <form method="GET" action="{{ route('cukai-jalan-info-list') }}">
            <div class="grid grid-cols-12 gap-4 mb-6 items-end">
        
                <div class="col-span-12 md:col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Bulan Tamat Cukai Jalan
                    </label>
        
                    <select name="tamat_month" class="form-input">
                        <option value="">-- Semua Bulan --</option>
                        <option value="01" {{ request('tamat_month') == '01' ? 'selected' : '' }}>Januari</option>
                        <option value="02" {{ request('tamat_month') == '02' ? 'selected' : '' }}>Februari</option>
                        <option value="03" {{ request('tamat_month') == '03' ? 'selected' : '' }}>Mac</option>
                        <option value="04" {{ request('tamat_month') == '04' ? 'selected' : '' }}>April</option>
                        <option value="05" {{ request('tamat_month') == '05' ? 'selected' : '' }}>Mei</option>
                        <option value="06" {{ request('tamat_month') == '06' ? 'selected' : '' }}>Jun</option>
                        <option value="07" {{ request('tamat_month') == '07' ? 'selected' : '' }}>Julai</option>
                        <option value="08" {{ request('tamat_month') == '08' ? 'selected' : '' }}>Ogos</option>
                        <option value="09" {{ request('tamat_month') == '09' ? 'selected' : '' }}>September</option>
                        <option value="10" {{ request('tamat_month') == '10' ? 'selected' : '' }}>Oktober</option>
                        <option value="11" {{ request('tamat_month') == '11' ? 'selected' : '' }}>November</option>
                        <option value="12" {{ request('tamat_month') == '12' ? 'selected' : '' }}>Disember</option>
                    </select>
                </div>
        
                <div class="col-span-12 md:col-span-4">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Tahun Tamat Cukai Jalan
                    </label>
        
                    <input type="number"
                        name="tamat_year"
                        value="{{ request('tamat_year') }}"
                        class="form-input"
                        placeholder="Contoh: 2026"
                        min="2000"
                        max="2100">
                </div>
        
                <div class="col-span-12 md:col-span-4 flex gap-2">
                    <button type="submit"
                        class="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-medium text-white hover:bg-blue-700">
                        Filter
                    </button>
        
                    <a href="{{ route('cukai-jalan-info-list') }}"
                        class="w-full rounded-lg bg-gray-500 px-4 py-3 text-center text-sm font-medium text-white hover:bg-gray-600">
                        Reset
                    </a>
                </div>
        
            </div>
        </form>
        <div class="p-1 overflow-x-auto">
            <table id="cukaiCarInfoTable" class="user-table roadtax-main-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>No Plat</th>
                        <th>Jenis Kenderaan</th>
                        <th>Jabatan</th>
                        <th>Lokasi Penempatan</th>
                        <th>Pengawas Kenderaan</th>
                        <th>Tarikh Mula Cukai Jalan</th>
                        <th>Tarikh Tamat Cukai Jalan</th>
                        <th>JKP ke JPJ</th>
                        <th>JKP ke Jabatan</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($cars as $index => $car)
                        @php
                            $vehicleId = $car->id ?? $car->ID ?? null;

                            $roadtaxStatus = strtoupper(trim($car->roadtax_status ?? '-'));

                            $statusClass = match($roadtaxStatus) {
                                'ACTIVE', 'AKTIF' => 'status-active',
                                'AKAN TAMAT TEMPOH' => 'status-warning',
                                'TAMAT TEMPOH' => 'status-expired',
                                'AKAN AKTIF' => 'status-upcoming',
                                default => 'status-default',
                            };

                            $canEditRoadtax = ($isSuperAdmin ?? false)
                                || in_array((string) $vehicleId, $myPengawasVehicleIds ?? []);
                        @endphp

                        <tr>
                            <td>{{ $index + 1 }}</td>

                            <td class="font-semibold">
                                <button type="button"
                                    class="text-blue-600 hover:underline font-semibold"
                                    onclick="openRoadtaxModal('roadtax-modal-{{ $vehicleId }}')">
                                    {{ $car->no_plat ?? $car->NO_PLAT ?? '-' }}
                                </button>
                            </td>

                            <td class="cell-text">
                                {{ $car->JENIS_KENDERAAN ?? $car->jenis_kenderaan ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $car->JABATAN ?? $car->jabatan ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $car->LOKASI_PENEMPATAN ?? $car->lokasi_penempatan ?? '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $car->PENGAWAS_KENDERAAN ?? $car->pengawas_kenderaan ?? '-' }}
                            </td>

                            <td>
                                {{ !empty($car->tarikh_mula)
                                    ? \Carbon\Carbon::parse($car->tarikh_mula)->format('d/m/Y')
                                    : '-'
                                }}
                            </td>

                            <td>
                                {{ !empty($car->tarikh_tamat)
                                    ? \Carbon\Carbon::parse($car->tarikh_tamat)->format('d/m/Y')
                                    : '-'
                                }}
                            </td>

                            <td>
                                {{ !empty($car->jkptojpj)
                                    ? \Carbon\Carbon::parse($car->jkptojpj)->format('d/m/Y')
                                    : '-'
                                }}
                            </td>

                            <td>
                                {{ !empty($car->jkptojab)
                                    ? \Carbon\Carbon::parse($car->jkptojab)->format('d/m/Y')
                                    : '-'
                                }}
                            </td>

                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $roadtaxStatus }}
                                </span>
                            </td>

                            <td class="text-center">
                                @if($canEditRoadtax)
                                    <button type="button"
                                        class="action-btn edit-btn"
                                        onclick="openRoadtaxEditModal('roadtax-edit-modal-{{ $vehicleId }}')"
                                        title="Tambah Roadtax">
                                        ✏️
                                    </button>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- MODAL ROADTAX HISTORY --}}
            @foreach($cars as $car)
                @php
                    $vehicleId = $car->id ?? $car->ID ?? null;
                @endphp

                <div id="roadtax-modal-{{ $vehicleId }}" class="roadtax-modal hidden">
                    <div class="roadtax-modal-overlay" onclick="closeRoadtaxModal('roadtax-modal-{{ $vehicleId }}')"></div>

                    <div class="roadtax-modal-card">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">
                                    Sejarah Cukai Jalan
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ $car->no_plat ?? $car->NO_PLAT ?? '-' }}
                                </p>
                            </div>

                            <button type="button"
                                class="modal-close-btn"
                                onclick="closeRoadtaxModal('roadtax-modal-{{ $vehicleId }}')">
                                ×
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="user-table roadtax-history-table">
                                <thead>
                                    <tr>
                                        <th>Bil</th>
                                        <th>Tarikh Mula</th>
                                        <th>Tarikh Tamat</th>
                                        <th>JKP ke JPJ</th>
                                        <th>JKP ke Jabatan</th>
                                        <th>Status</th>
                                        @if($isSuperAdmin ?? false)
                                            <th>Tindakan</th>
                                        @endif
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse(($roadtaxHistories[$vehicleId] ?? []) as $rtIndex => $rt)
                                        <tr>
                                            <td>{{ $rtIndex + 1 }}</td>

                                            <td>
                                                {{ !empty($rt->tarikh_mula)
                                                    ? \Carbon\Carbon::parse($rt->tarikh_mula)->format('d/m/Y')
                                                    : '-'
                                                }}
                                            </td>

                                            <td>
                                                {{ !empty($rt->tarikh_tamat)
                                                    ? \Carbon\Carbon::parse($rt->tarikh_tamat)->format('d/m/Y')
                                                    : '-'
                                                }}
                                            </td>

                                            <td>
                                                {{ !empty($rt->jkptojpj)
                                                    ? \Carbon\Carbon::parse($rt->jkptojpj)->format('d/m/Y')
                                                    : '-'
                                                }}
                                            </td>

                                            <td>
                                                {{ !empty($rt->jkptojab)
                                                    ? \Carbon\Carbon::parse($rt->jkptojab)->format('d/m/Y')
                                                    : '-'
                                                }}
                                            </td>

                                            <td>
                                                {{ $rt->roadtax_status ?? '-' }}
                                            </td>

                                            @if($isSuperAdmin ?? false)
                                                <td>
                                                    <button type="button"
                                                        class="action-btn edit-btn"
                                                        title="Kemaskini Tarikh"
                                                        onclick="document.getElementById('roadtax-row-edit-{{ $rt->id }}').classList.toggle('hidden')">
                                                        ✏️
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>

                                        @if($isSuperAdmin ?? false)
                                            <tr id="roadtax-row-edit-{{ $rt->id }}" class="hidden">
                                                <td colspan="7">
                                                    <form method="POST" action="{{ route('roadtax-update', $rt->id) }}"
                                                        class="grid grid-cols-1 gap-3 p-3 md:grid-cols-5 items-end bg-gray-50 rounded-lg">
                                                        @csrf
                                                        @method('PUT')

                                                        <label class="text-xs font-medium text-gray-700">Tarikh Mula
                                                            <input type="date" name="tarikh_mula" required
                                                                value="{{ !empty($rt->tarikh_mula) ? \Carbon\Carbon::parse($rt->tarikh_mula)->format('Y-m-d') : '' }}"
                                                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                                        </label>

                                                        <label class="text-xs font-medium text-gray-700">Tarikh Tamat
                                                            <input type="date" name="tarikh_tamat" required
                                                                value="{{ !empty($rt->tarikh_tamat) ? \Carbon\Carbon::parse($rt->tarikh_tamat)->format('Y-m-d') : '' }}"
                                                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                                        </label>

                                                        <label class="text-xs font-medium text-gray-700">JKP ke JPJ
                                                            <input type="date" name="jkptojpj"
                                                                value="{{ !empty($rt->jkptojpj) ? \Carbon\Carbon::parse($rt->jkptojpj)->format('Y-m-d') : '' }}"
                                                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                                        </label>

                                                        <label class="text-xs font-medium text-gray-700">JKP ke Jabatan
                                                            <input type="date" name="jkptojab"
                                                                value="{{ !empty($rt->jkptojab) ? \Carbon\Carbon::parse($rt->jkptojab)->format('Y-m-d') : '' }}"
                                                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                                        </label>

                                                        <button type="submit"
                                                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                                            Simpan
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-gray-500">
                                                Tiada sejarah cukai jalan.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- MODAL ADD ROADTAX --}}
            @foreach($cars as $car)
                @php
                $vehicleId = $car->id ?? $car->ID ?? null;
            
                $roadtaxStatus = strtoupper(trim($car->roadtax_status ?? '-'));
            
                $hasRoadtaxDate = !empty($car->tarikh_mula) && !empty($car->tarikh_tamat);
                $hasNoRoadtax = empty($car->roadtax_id) || !$hasRoadtaxDate;
            
                /*
                    Allow add/renew when:
                    1. No roadtax yet
                    2. Roadtax akan tamat tempoh
                    3. Roadtax tamat tempoh
                */
                $canRenew = $hasNoRoadtax || in_array($roadtaxStatus, [
                    'AKAN TAMAT TEMPOH',
                    'TAMAT TEMPOH'
                ]);
            
                $today = now('Asia/Kuala_Lumpur')->startOfDay();
            
                $currentEndDate = !empty($car->tarikh_tamat)
                    ? \Carbon\Carbon::parse($car->tarikh_tamat)->startOfDay()
                    : null;
            
                $oneMonthAfterEnd = $currentEndDate
                    ? $currentEndDate->copy()->addMonth()
                    : null;
            
                /*
                    User must insert Tarikh Mula Baharu when:
                    1. No roadtax yet
                    2. Roadtax expired more than 1 month
                */
                $needManualStartDate = $hasNoRoadtax
                    || ($currentEndDate && $today->gt($oneMonthAfterEnd));
            
                $autoStartDate = (!$needManualStartDate && $currentEndDate)
                    ? $currentEndDate->copy()->addDay()
                    : null;
            
                $autoEndDate = $autoStartDate
                    ? $autoStartDate->copy()->addMonths(12)->subDay()
                    : null;
            
                $modalStatusClass = match($roadtaxStatus) {
                    'ACTIVE', 'AKTIF' => 'status-active',
                    'AKAN TAMAT TEMPOH' => 'status-warning',
                    'TAMAT TEMPOH' => 'status-expired',
                    'AKAN AKTIF' => 'status-upcoming',
                    default => 'status-default',
                };
            @endphp

                <div id="roadtax-edit-modal-{{ $vehicleId }}" class="roadtax-modal hidden">
                    <div class="roadtax-modal-overlay" onclick="closeRoadtaxEditModal('roadtax-edit-modal-{{ $vehicleId }}')"></div>

                    <div class="roadtax-modal-card">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">
                                    Tambah Cukai Jalan
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ $car->no_plat ?? $car->NO_PLAT ?? '-' }}
                                </p>
                            </div>

                            <button type="button"
                                class="modal-close-btn"
                                onclick="closeRoadtaxEditModal('roadtax-edit-modal-{{ $vehicleId }}')">
                                ×
                            </button>
                        </div>

                        <form method="POST" action="{{ route('roadtax-store') }}">
                            @csrf

                            <input type="hidden" name="vehicle_id" value="{{ $vehicleId }}">
                            <input type="hidden" name="mode" value="{{ $canRenew ? 'renew' : 'jkp' }}">
                            <input type="hidden" name="roadtax_id" value="{{ $car->roadtax_id ?? '' }}">

                            @if($canRenew)
                                <div class="grid grid-cols-12 gap-4">

                                    <input type="hidden"
                                        name="tempoh_bulan"
                                        class="roadtax-duration"
                                        value="12"
                                        data-current-end="{{ !empty($car->tarikh_tamat) ? \Carbon\Carbon::parse($car->tarikh_tamat)->format('Y-m-d') : '' }}"
                                        data-has-roadtax="{{ $hasRoadtaxDate ? '1' : '0' }}"
                                        data-need-manual-start="{{ $needManualStartDate ? '1' : '0' }}">

                                    <div class="col-span-12">
                                        <div class="rounded-lg bg-blue-50 border border-blue-200 p-4 text-sm text-blue-900">
                                            <b>Tempoh Pembaharuan:</b> 12 Bulan
                                        </div>
                                    </div>

                                    @if($needManualStartDate)
                                        <div class="col-span-12">
                                            <label class="block mb-1 text-sm font-medium text-gray-700">
                                                Tarikh Mula Baharu <span class="text-red-600">*</span>
                                            </label>

                                            <input type="text"
                                                name="new_tarikh_mula"
                                                class="roadtax-date-picker roadtax-start-date new-start-date w-full rounded-lg border border-gray-300 px-4 py-3 text-sm"
                                                placeholder="Pilih tarikh mula baharu"
                                                required>

                                            @if($hasNoRoadtax)
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Kenderaan ini belum mempunyai rekod cukai jalan. Sila masukkan tarikh mula cukai jalan.
                                                </p>
                                            @else
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Cukai jalan telah tamat lebih daripada 1 bulan. Sila masukkan tarikh mula baharu.
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <input type="hidden"
                                            name="new_tarikh_mula"
                                            class="new-start-date"
                                            value="{{ $autoStartDate ? $autoStartDate->format('Y-m-d') : '' }}">
                                    @endif

                                    <div class="col-span-12">
                                        <div class="rounded-lg bg-gray-50 border border-gray-200 p-4 text-sm">
                                            <div>
                                                <b>Tarikh Mula Baharu:</b>
                                                <span class="preview-start">
                                                    {{ $autoStartDate ? $autoStartDate->format('d/m/Y') : '-' }}
                                                </span>
                                            </div>

                                            <div>
                                                <b>Tarikh Tamat Baharu:</b>
                                                <span class="preview-end">
                                                    {{ $autoEndDate ? $autoEndDate->format('d/m/Y') : '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden"
                                        name="new_tarikh_tamat"
                                        class="new-end-date"
                                        value="{{ $autoEndDate ? $autoEndDate->format('Y-m-d') : '' }}">

                                </div>
                            @else
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                    <p class="text-sm text-gray-600 mb-2">
                                        Cukai jalan masih belum boleh diperbaharui.
                                    </p>

                                    <div class="text-base font-semibold text-gray-900">
                                        Tarikh Tamat Cukai Jalan:
                                        {{ !empty($car->tarikh_tamat)
                                            ? \Carbon\Carbon::parse($car->tarikh_tamat)->format('d/m/Y')
                                            : '-'
                                        }}
                                    </div>

                                    <div class="mt-2">
                                        <span class="status-badge {{ $modalStatusClass }}">
                                            {{ $roadtaxStatus }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-12 gap-4 mt-4">
                                <div class="col-span-6">
                                    <label class="block mb-1 text-sm font-medium text-gray-700">
                                        JKP ke JPJ
                                    </label>
                                    <input type="text"
                                        name="jkptojpj"
                                        value="{{ !empty($car->jkptojpj)
                                            ? \Carbon\Carbon::parse($car->jkptojpj)->format('Y-m-d')
                                            : ''
                                        }}"
                                        class="roadtax-date-picker w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                                        placeholder="Pilih tarikh">
                                </div>

                                <div class="col-span-6">
                                    <label class="block mb-1 text-sm font-medium text-gray-700">
                                        JKP ke Jabatan
                                    </label>
                                    <input type="text"
                                        name="jkptojab"
                                        value="{{ !empty($car->jkptojab)
                                            ? \Carbon\Carbon::parse($car->jkptojab)->format('Y-m-d')
                                            : ''
                                        }}"
                                        class="roadtax-date-picker w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
                                        placeholder="Pilih tarikh">
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-2">
                                <button type="button"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium"
                                    onclick="closeRoadtaxEditModal('roadtax-edit-modal-{{ $vehicleId }}')">
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
        </div>
    </x-common.component-card>
</div>

<style>
    .form-input {
        width: 100%;
        border-radius: 8px;
        border: 1px solid #d0d5dd;
        padding: 10px 12px;
        font-size: 13px;
        background: #ffffff;
        color: #101828;
    }

    .form-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        outline: none;
    }
    .user-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .roadtax-main-table {
        width: 100% !important;
        min-width: 2170px;
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

    .roadtax-main-table th:nth-child(1),
    .roadtax-main-table td:nth-child(1) {
        width: 60px !important;
        min-width: 60px;
        max-width: 60px;
        text-align: center;
        white-space: nowrap;
    }

    .roadtax-main-table th:nth-child(2),
    .roadtax-main-table td:nth-child(2) {
        width: 130px !important;
        min-width: 130px;
        max-width: 130px;
        white-space: nowrap;
    }

    .roadtax-main-table th:nth-child(3),
    .roadtax-main-table td:nth-child(3) {
        width: 190px !important;
        min-width: 190px;
        max-width: 190px;
        white-space: normal;
    }

    .roadtax-main-table th:nth-child(4),
    .roadtax-main-table td:nth-child(4) {
        width: 260px !important;
        min-width: 260px;
        max-width: 260px;
        white-space: normal;
    }

    .roadtax-main-table th:nth-child(5),
    .roadtax-main-table td:nth-child(5) {
        width: 260px !important;
        min-width: 260px;
        max-width: 260px;
        white-space: normal;
    }

    .roadtax-main-table th:nth-child(6),
    .roadtax-main-table td:nth-child(6) {
        width: 220px !important;
        min-width: 220px;
        max-width: 220px;
        white-space: normal;
    }

    .roadtax-main-table th:nth-child(7),
    .roadtax-main-table td:nth-child(7) {
        width: 220px !important;
        min-width: 220px;
        max-width: 220px;
        white-space: nowrap;
    }

    .roadtax-main-table th:nth-child(8),
    .roadtax-main-table td:nth-child(8) {
        width: 220px !important;
        min-width: 220px;
        max-width: 220px;
        white-space: nowrap;
    }

    .roadtax-main-table th:nth-child(9),
    .roadtax-main-table td:nth-child(9) {
        width: 150px !important;
        min-width: 150px;
        max-width: 150px;
        white-space: nowrap;
    }

    .roadtax-main-table th:nth-child(10),
    .roadtax-main-table td:nth-child(10) {
        width: 180px !important;
        min-width: 180px;
        max-width: 180px;
        white-space: nowrap;
    }

    .roadtax-main-table th:nth-child(11),
    .roadtax-main-table td:nth-child(11) {
        width: 170px !important;
        min-width: 170px;
        max-width: 170px;
        text-align: center;
        white-space: nowrap;
    }

    .roadtax-main-table th:nth-child(12),
    .roadtax-main-table td:nth-child(12) {
        width: 110px !important;
        min-width: 110px;
        max-width: 110px;
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

    .status-default {
        background: #f2f4f7;
        color: #344054;
        border: 1px solid #d0d5dd;
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
        padding: 0;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    .edit-btn {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .roadtax-history-table {
        min-width: 780px;
    }

    .roadtax-history-table th,
    .roadtax-history-table td {
        white-space: nowrap;
    }

    .roadtax-modal {
        position: fixed;
        inset: 0;
        z-index: 999999;
    }

    .roadtax-modal.hidden {
        display: none;
    }

    .roadtax-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }

    .roadtax-modal-card {
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

    .modal-close-btn {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        background: #f2f4f7;
        color: #344054;
        font-size: 26px;
        line-height: 1;
    }

    .flatpickr-calendar {
        z-index: 1000001 !important;
    }

    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table {
        width: 100% !important;
    }

    .dataTables_scrollHeadInner table.roadtax-main-table,
    .dataTables_scrollBody table.roadtax-main-table {
        min-width: 2170px !important;
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
function openRoadtaxModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeRoadtaxModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function openRoadtaxEditModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    calculateRoadtaxRenewal(modal);
}

function closeRoadtaxEditModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function parseDateValue(value) {
    if (!value) return null;

    const parts = value.split('-').map(Number);

    if (parts.length === 3) {
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    return new Date(value);
}

function addMonthsMinusOneDay(startDate, months) {
    const date = new Date(startDate);
    date.setMonth(date.getMonth() + parseInt(months));
    date.setDate(date.getDate() - 1);
    return date;
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function formatDisplayDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${day}/${month}/${year}`;
}

function calculateRoadtaxRenewal(modal) {
    if (!modal) return;

    const durationInput = modal.querySelector('.roadtax-duration');
    const startInput = modal.querySelector('.roadtax-start-date');
    const previewStart = modal.querySelector('.preview-start');
    const previewEnd = modal.querySelector('.preview-end');
    const newStartInput = modal.querySelector('.new-start-date');
    const newEndInput = modal.querySelector('.new-end-date');

    if (!durationInput) return;

    const duration = durationInput.value || '12';
    const currentEnd = durationInput.dataset.currentEnd;
    const needManualStart = durationInput.dataset.needManualStart === '1';

    let newStart = null;

    if (needManualStart) {
        if (!startInput || !startInput.value) {
            if (previewStart) previewStart.textContent = '-';
            if (previewEnd) previewEnd.textContent = '-';
            if (newEndInput) newEndInput.value = '';
            return;
        }

        newStart = parseDateValue(startInput.value);
    } else {
        if (!currentEnd) return;

        newStart = parseDateValue(currentEnd);
        newStart.setDate(newStart.getDate() + 1);
    }

    if (!newStart || isNaN(newStart.getTime())) return;

    const newEnd = addMonthsMinusOneDay(newStart, duration);

    if (previewStart) previewStart.textContent = formatDisplayDate(newStart);
    if (previewEnd) previewEnd.textContent = formatDisplayDate(newEnd);

    if (newStartInput) newStartInput.value = formatDate(newStart);
    if (newEndInput) newEndInput.value = formatDate(newEnd);
}

document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('roadtax-start-date')) {
        return;
    }

    const modal = e.target.closest('.roadtax-modal-card');
    calculateRoadtaxRenewal(modal);
});

document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('roadtax-start-date')) {
        return;
    }

    const modal = e.target.closest('.roadtax-modal-card');
    calculateRoadtaxRenewal(modal);
});

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

$(document).ready(function () {
    if (!document.querySelector('#cukaiCarInfoTable')) return;

    const roadtaxTable = $('#cukaiCarInfoTable').DataTable({
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
            { width: '190px' },
            { width: '260px' },
            { width: '260px' },
            { width: '220px' },
            { width: '220px' },
            { width: '220px' },
            { width: '150px' },
            { width: '180px' },
            { width: '170px' },
            { width: '110px', orderable: false }
        ],

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            zeroRecords: "Tiada data dijumpai",
            emptyTable: "Tiada data cukai jalan.",
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
        roadtaxTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        roadtaxTable.columns.adjust();
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