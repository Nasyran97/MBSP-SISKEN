@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<div class="container-fluid">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">{{ $title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                Senarai rekod permohonan penyelenggaraan kenderaan.
            </p>
        </div>
        
        @if($canApplyPenyelenggaraan)
        <a href="{{ route('penyelenggaraan-add') }}"
           class="inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600">
            + Permohonan Penyelenggaraan
        </a>
        @endif
    </div>

    <x-common.component-card title="Senarai Penyelenggaraan">
        @php
            $tabCounts = ['PENDING' => 0, 'PENGESAHAN' => 0, 'PROGRESS' => 0];

            foreach ($penyelenggaraan as $countRow) {
                if (strtoupper(trim($countRow->STATUS_DATA ?? $countRow->status_data ?? 'ACTIVE')) === 'DELETE') {
                    continue;
                }

                $countStatus = strtoupper(trim($countRow->STATUS ?? $countRow->status ?? 'PENDING'));

                if ($countStatus === 'PENDING') {
                    $tabCounts['PENDING']++;
                } elseif (in_array($countStatus, ['PENGESAHAN', 'PENGESAHAN JURUTERA'], true)) {
                    $tabCounts['PENGESAHAN']++;
                } elseif (in_array($countStatus, ['IN PROGRESS', 'IN_PROGRESS', 'DALAM PROSES'])) {
                    $tabCounts['PROGRESS']++;
                }
            }
        @endphp

        <div class="penyelenggaraan-tabs">
            <button type="button" class="tab-btn active" data-status-tab="PENDING">Pending @if($tabCounts['PENDING'] > 0)<span class="tab-count">{{ $tabCounts['PENDING'] }}</span>@endif</button>
            <button type="button" class="tab-btn" data-status-tab="PENGESAHAN">Pengesahan @if($tabCounts['PENGESAHAN'] > 0)<span class="tab-count">{{ $tabCounts['PENGESAHAN'] }}</span>@endif</button>
            <button type="button" class="tab-btn" data-status-tab="PROGRESS">Dalam Proses @if($tabCounts['PROGRESS'] > 0)<span class="tab-count">{{ $tabCounts['PROGRESS'] }}</span>@endif</button>
            <button type="button" class="tab-btn" data-status-tab="COMPLETED">Selesai</button>
            <button type="button" class="tab-btn" data-status-tab="CANCELLED">Batal</button>
        </div>

        <div class="p-1 overflow-x-auto">
            <table id="penyelenggaraanTable" class="user-table penyelenggaraan-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>No Plat</th>
                        <th>PIC</th>
                        <th>No Arahan Kerja</th>
                        <th>Model Kenderaan</th>
                        <th>Jenis Kenderaan</th>
                        <th>Odometer</th>
                        <th>Kategori</th>
                        <th>Mekanik/Kumpulan</th>
                        <th>Remark</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($penyelenggaraan as $i => $row)
                        @php
                            $id = $row->ID ?? $row->id ?? null;

                            $pic = $row->PIC ?? $row->pic ?? '-';
                            $picNoStaff = $row->PIC_NOSTAFF ?? $row->pic_nostaff ?? null;

                            $noPlat = $row->NO_PLAT ?? $row->no_plat ?? '-';
                            $noArahanKerja = $row->NO_ARAHAN_KERJA ?? $row->no_arahan_kerja ?? '-';

                            $buatan = $row->BUATAN ?? $row->buatan ?? '';
                            $model = $row->MODEL ?? $row->model ?? '-';
                            $modelKenderaan = trim($buatan . ' ' . $model);

                            $jenisKenderaan = $row->JENIS_KENDERAAN ?? $row->jenis_kenderaan ?? '-';
                            $odometer = $row->ODOMETER ?? $row->odometer ?? null;
                            $kategori = $row->KATEGORI_PENYELENGGARAAN_NAMA ?? $row->kategori_penyelenggaraan_nama ?? '-';
                            $remark = $row->REPAIR_REMARK ?? $row->repair_remark ?? '-';

                            $assignmentType = strtoupper(trim($row->ASSIGNMENT_TYPE ?? $row->assignment_type ?? ''));
                            $assignedGroupId = $row->ASSIGNED_GROUP_ID ?? $row->assigned_group_id ?? null;
                            $assignedGroupName = $row->ASSIGNED_GROUP_NAME ?? $row->assigned_group_name ?? null;
                            $assignedUserId = $row->ASSIGNED_USER_ID ?? $row->assigned_user_id ?? null;
                            $assignedMekanikName = $row->ASSIGNED_MEKANIK_NAME ?? $row->assigned_mekanik_name ?? null;
                            $assignedMekanikNoStaff = $row->ASSIGNED_MEKANIK_NOSTAFF ?? $row->assigned_mekanik_nostaff ?? null;

                            $assignedDisplay = match($assignmentType) {
                                'GROUP' => $assignedGroupName ?: '-',
                                'INDIVIDUAL' => $assignedMekanikName
                                    ? trim(($assignedMekanikNoStaff ? $assignedMekanikNoStaff . ' - ' : '') . $assignedMekanikName)
                                    : '-',
                                default => '-',
                            };

                            $statusData = strtoupper(trim($row->STATUS_DATA ?? $row->status_data ?? 'ACTIVE'));
                            $isDeletedRow = $statusData === 'DELETE';

                            $status = $isDeletedRow
                                ? 'BATAL'
                                : strtoupper(trim($row->STATUS ?? $row->status ?? 'PENDING'));

                            $statusClass = match($status) {
                                'PENDING' => 'status-pending',
                                'PENGESAHAN', 'PENGESAHAN JURUTERA' => 'status-pengesahan',
                                'IN PROGRESS', 'IN_PROGRESS', 'DALAM PROSES' => 'status-progress',
                                'COMPLETED', 'COMPLETE', 'SELESAI' => 'status-completed',
                                'CANCELLED', 'CANCELED', 'BATAL' => 'status-cancelled',
                                default => 'status-default',
                            };

                            // Job Controller -> PJ -> Pegawai Jurutera flow (Kimpalan, Aircond, Pembaikan Berat/Ringan, Servis).
                            $kategoriNorm = strtoupper(trim(str_replace('_', ' ', $kategori)));
                            $isFlow = in_array($kategoriNorm, $flowKategori ?? [], true);
                            $isJcForRow = $isFlow && in_array($kategoriNorm, $jcKategori ?? [], true);
                            $repairMode = strtoupper(trim($row->REPAIR_MODE ?? $row->repair_mode ?? ''));
                            $noArahanKerja = $row->NO_ARAHAN_KERJA ?? $row->no_arahan_kerja ?? null;
                            // Pegawai PUSKEB accepts first; the Job Controller then assesses in Pengesahan.
                            $canJcRow = !$isDeletedRow && $isJcForRow
                                && $status === 'PENGESAHAN' && $repairMode === '';
                            $canPjRow = !$isDeletedRow && $status === 'PENGESAHAN' && $repairMode !== '' && $isFlow && ($canApprovePj ?? false);
                            $canJuteraRow = !$isDeletedRow && $status === 'PENGESAHAN JURUTERA' && $isFlow && ($canApproveJurutera ?? false);

                            $createdBy = $row->CREATED_BY ?? $row->created_by ?? null;
                            $isOwnRow = $createdBy && $createdBy === session('nostaff');
                            $canManageRow = !$isDeletedRow
                                && ($isSuperAdmin || $isOwnRow);
                            $canEditRow = $canManageRow && $status === 'PENDING';
                            $canDeleteRow = $canManageRow && !in_array($status, ['COMPLETED', 'COMPLETE', 'SELESAI', 'CANCELLED', 'CANCELED']);
                            $canConfirmRow = !$isDeletedRow && $status === 'PENGESAHAN' && $canConfirmPenyelenggaraan && !$isFlow;

                            $isLeaderOfAssignedGroup = $assignmentType === 'GROUP'
                                && $assignedGroupId
                                && in_array($assignedGroupId, $leaderGroupIds ?? []);

                            $isAssignedIndividualSelf = $assignmentType === 'INDIVIDUAL'
                                && $assignedUserId
                                && (string) $assignedUserId === (string) ($currentUserId ?? '');

                            $canMarkDoneRow = !$isDeletedRow
                                && $status === 'IN PROGRESS'
                                && ($isSuperAdmin || $isLeaderOfAssignedGroup || $isAssignedIndividualSelf
                                    || ($isJcForRow && ($repairMode === 'BENGKEL_PANEL' || $assignmentType === '')));
                        @endphp

                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>

                            <td class="font-semibold">
                                {{ $noPlat }}
                            </td>

                            <td class="cell-text">
                                <div class="font-semibold">
                                    {{ $pic }}
                                </div>
                            
                                @if($picNoStaff)
                                    <div class="text-xs text-gray-500">
                                        {{ $picNoStaff }}
                                    </div>
                                @endif
                            </td>

                            <td class="font-semibold">
                                {{ $noArahanKerja }}
                            </td>

                            <td class="cell-text">
                                {{ $modelKenderaan ?: '-' }}
                            </td>

                            <td class="cell-text">
                                {{ $jenisKenderaan }}
                            </td>

                            <td class="text-right">
                                {{ $odometer !== null ? number_format((float) $odometer, 0) . ' KM' : '-' }}
                            </td>

                            <td>
                                <span class="category-badge">
                                    {{ str_replace('_', ' ', $kategori) }}
                                </span>
                            </td>

                            <td class="cell-text">
                                @if($assignedDisplay !== '-')
                                    <button type="button"
                                            class="mekanik-link"
                                            onclick="openPenyelenggaraanModal('mekanik-modal-{{ $id }}')">
                                        {{ $assignedDisplay }}
                                    </button>
                                @else
                                    {{ $assignedDisplay }}
                                @endif
                            </td>

                            <td class="cell-text">
                                {{ \Illuminate\Support\Str::limit($remark, 80) }}
                            </td>

                            @php
                                // Which approval a flow job is waiting for: 0 = Job Controller, 1 = Penolong Jurutera, 2 = Pegawai Jurutera.
                                $approvalStep = null;
                                $statusLabel = $status;

                                if ($isFlow && !$isDeletedRow) {
                                    if ($status === 'PENGESAHAN') {
                                        $approvalStep = $repairMode === '' ? 0 : 1;
                                    } elseif ($status === 'PENGESAHAN JURUTERA') {
                                        $approvalStep = 2;
                                    }

                                    if (str_starts_with($status, 'PENGESAHAN')) {
                                        $statusLabel = 'PENGESAHAN (' . ['JC', 'PENOLONG JURUTERA', 'PEGAWAI JURUTERA'][$approvalStep] . ')';
                                    }
                                }

                                // What the earlier approvers did, shown in the approve / deny dialog.
                                $fmtApproval = function ($date) {
                                    try {
                                        return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y H:i') : '';
                                    } catch (\Throwable $e) {
                                        return '';
                                    }
                                };

                                $decisionInfo = [
                                    'kategori' => str_replace('_', ' ', $kategori),
                                    'masalah' => $remark,
                                    'mode' => $repairMode === 'INTERNAL' ? 'Pembaikan Dalaman' : ($repairMode === 'BENGKEL_PANEL' ? 'Bengkel Panel' : ''),
                                    'panel' => $row->BENGKEL_PANEL_NAMA ?? $row->bengkel_panel_nama ?? '',
                                    'jcRemark' => $row->JC_REMARK ?? $row->jc_remark ?? '',
                                    'jcBy' => $row->JC_NAMA ?? $row->jc_nama ?? ($row->JC_BY ?? $row->jc_by ?? ''),
                                    'jcAt' => $fmtApproval($row->JC_AT ?? $row->jc_at ?? null),
                                    'pjBy' => $row->PJ_NAMA ?? $row->pj_nama ?? '',
                                    'pjAt' => $fmtApproval($row->PJ_AT ?? $row->pj_at ?? null),
                                ];

                                $approvalSteps = ['JC', 'PJ', 'PEG. JURUTERA'];
                                $approvalStepTitles = ['Job Controller', 'Penolong Jurutera', 'Pegawai Jurutera'];
                            @endphp

                            <td class="text-center">
                                <span class="status-badge status-badge-wrap {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>

                                @if($approvalStep !== null)
                                    <div class="stage-tracker" title="Menunggu: {{ $approvalStepTitles[$approvalStep] }}">
                                        @foreach($approvalSteps as $stepIndex => $stepName)
                                            <span class="stage-step {{ $stepIndex < $approvalStep ? 'done' : ($stepIndex === $approvalStep ? 'current' : '') }}"
                                                  title="{{ $approvalStepTitles[$stepIndex] }}">
                                                {{ $stepIndex < $approvalStep ? '✓' : '' }} {{ $stepName }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <div class="stage-left">Baki: {{ 3 - $approvalStep }}</div>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                                    <button type="button"
                                            class="action-btn info-btn"
                                            title="Maklumat"
                                            onclick="openPenyelenggaraanModal('penyelenggaraan-modal-{{ $id }}')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 17V11M12 7H12.01M21 12A9 9 0 1 1 3 12A9 9 0 0 1 21 12Z"
                                                  stroke="currentColor"
                                                  stroke-width="1.8"
                                                  stroke-linecap="round"
                                                  stroke-linejoin="round" />
                                        </svg>
                                    </button>

                                    @if($canJcRow)
                                        <button type="button"
                                                class="action-btn jc-btn"
                                                title="Nilai Masalah"
                                                onclick="openJcModal({{ $id }}, @js($noPlat), @js($noArahanKerja), @js($kategori), @js($remark))">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2M9 5h6M9 12h6M9 16h4"
                                                      stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    @endif

                                    @if($canPjRow || $canJuteraRow)
                                        <button type="button"
                                                class="action-btn sahkan-btn"
                                                title="{{ $canPjRow ? 'Lulus / Tolak (Penolong Jurutera)' : 'Lulus / Tolak Arahan Kerja (Pegawai Jurutera)' }}"
                                                onclick="decidePenyelenggaraan({{ $id }}, '{{ $canPjRow ? 'pj' : 'jurutera' }}', @js($noPlat), @js($noArahanKerja), @js($decisionInfo))">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                <path d="M5 12L10 17L19 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    @endif

                                    @if($canAcceptPenyelenggaraan && $status === 'PENDING' && !$isDeletedRow)
                                        <form method="POST" action="{{ route('penyelenggaraan-accept', $id) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="action-btn terima-btn"
                                                    title="Terima"
                                                    onclick="return confirm('Adakah anda pasti mahu terima permohonan penyelenggaraan ini?')">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <path d="M5 12L10 17L19 8"
                                                          stroke="currentColor"
                                                          stroke-width="1.8"
                                                          stroke-linecap="round"
                                                          stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    @if($canConfirmRow)
                                        <form method="POST" action="{{ route('penyelenggaraan-confirm', $id) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="action-btn sahkan-btn"
                                                    title="Sahkan"
                                                    onclick="return confirm('Adakah anda pasti mahu sahkan permohonan penyelenggaraan ini?')">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <path d="M5 12L10 17L19 8"
                                                          stroke="currentColor"
                                                          stroke-width="1.8"
                                                          stroke-linecap="round"
                                                          stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif

                                    @if($canMarkDoneRow)
                                        <form id="mark-done-form-{{ $id }}" method="POST" action="{{ route('penyelenggaraan-mark-done', $id) }}">
                                            @csrf
                                            <input type="hidden" name="remark_done" id="mark-done-remark-{{ $id }}">
                                            <input type="hidden" name="breakdown_result" id="mark-done-result-{{ $id }}">
                                            <input type="hidden" name="towing" id="mark-done-towing-{{ $id }}">
                                        </form>
                                        <button type="button"
                                                class="action-btn selesai-btn"
                                                title="Tandakan Selesai"
                                                onclick="markPenyelenggaraanDone({{ $id }}, {{ strtoupper(trim($kategori)) === 'BREAKDOWN' ? 'true' : 'false' }})">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                <path d="M5 12L10 17L19 8"
                                                      stroke="currentColor"
                                                      stroke-width="2.2"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    @endif

                                    @if($canEditRow)
                                        <a href="{{ route('penyelenggaraan-edit', $id) }}"
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
                                    @endif

                                    @if($canDeleteRow)
                                        <form method="POST" action="{{ route('penyelenggaraan-delete', $id) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="action-btn delete-btn"
                                                    title="Delete"
                                                    onclick="return confirm('Adakah anda pasti mahu padam permohonan penyelenggaraan ini?')">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                                    <path d="M10 11V17M14 11V17M5 7H19M17 7L16.47 19.13C16.43 20.03 15.69 20.75 14.79 20.75H9.21C8.31 20.75 7.57 20.03 7.53 19.13L7 7M9 7V4.75C9 4.34 9.34 4 9.75 4H14.25C14.66 4 15 4.34 15 4.75V7"
                                                          stroke="currentColor"
                                                          stroke-width="1.8"
                                                          stroke-linecap="round"
                                                          stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-common.component-card>

    @foreach($penyelenggaraan as $row)
        @php
            $id = $row->ID ?? $row->id ?? null;

            $pic = $row->PIC ?? $row->pic ?? '-';
            $picNoStaff = $row->PIC_NOSTAFF ?? $row->pic_nostaff ?? null;

            $driverName = $row->DRIVER_NAME ?? $row->driver_name ?? '-';
            $driverNoStaff = $row->DRIVER_NOSTAFF ?? $row->driver_nostaff ?? null;
            $driverPhoneNum = $row->DRIVER_PHONE_NUM ?? $row->driver_phone_num ?? '-';

            $noPlat = $row->NO_PLAT ?? $row->no_plat ?? '-';
            $noArahanKerja = $row->NO_ARAHAN_KERJA ?? $row->no_arahan_kerja ?? '-';

            $lokasiPenempatan = $row->LOKASI_PENEMPATAN ?? $row->lokasi_penempatan ?? '-';
            $buatan = $row->BUATAN ?? $row->buatan ?? '';
            $model = $row->MODEL ?? $row->model ?? '-';
            $modelKenderaan = trim($buatan . ' ' . $model);

            $jenisKenderaan = $row->JENIS_KENDERAAN ?? $row->jenis_kenderaan ?? '-';
            $odometer = $row->ODOMETER ?? $row->odometer ?? null;

            $jkjgName = $row->JKJG_NAME ?? $row->jkjg_name ?? '-';
            $jkjgNoStaff = $row->JKJG_NOSTAFF ?? $row->jkjg_nostaff ?? null;

            $kategori = $row->KATEGORI_PENYELENGGARAAN_NAMA ?? $row->kategori_penyelenggaraan_nama ?? '-';
            $repairCategory = strtoupper(trim($kategori));

            $assignmentType = strtoupper(trim($row->ASSIGNMENT_TYPE ?? $row->assignment_type ?? ''));
            $assignedGroupId = $row->ASSIGNED_GROUP_ID ?? $row->assigned_group_id ?? null;
            $assignedGroupName = $row->ASSIGNED_GROUP_NAME ?? $row->assigned_group_name ?? null;
            $assignedMekanikName = $row->ASSIGNED_MEKANIK_NAME ?? $row->assigned_mekanik_name ?? null;
            $assignedMekanikNoStaff = $row->ASSIGNED_MEKANIK_NOSTAFF ?? $row->assigned_mekanik_nostaff ?? null;
            $assignedMekanikJabatan = $row->ASSIGNED_MEKANIK_JABATAN ?? $row->assigned_mekanik_jabatan ?? null;

            $assignedDisplay = match($assignmentType) {
                'GROUP' => $assignedGroupName ?: '-',
                'INDIVIDUAL' => $assignedMekanikName
                    ? trim(($assignedMekanikNoStaff ? $assignedMekanikNoStaff . ' - ' : '') . $assignedMekanikName)
                    : '-',
                default => '-',
            };

            $assignedGroupMembers = $assignmentType === 'GROUP' && $assignedGroupId
                ? $groupMembersMap->get($assignedGroupId, collect())
                : collect();

            $remark = $row->REPAIR_REMARK ?? $row->repair_remark ?? '-';
            $remarkDone = $row->REMARK_DONE ?? $row->remark_done ?? null;
            $completedAt = $row->COMPLETED_AT ?? $row->completed_at ?? null;

            $statusData = strtoupper(trim($row->STATUS_DATA ?? $row->status_data ?? 'ACTIVE'));
            $status = $statusData === 'DELETE'
                ? 'BATAL'
                : strtoupper(trim($row->STATUS ?? $row->status ?? 'PENDING'));

            $locationLatitude = $row->LOCATION_LATITUDE ?? $row->location_latitude ?? null;
            $locationLongitude = $row->LOCATION_LONGITUDE ?? $row->location_longitude ?? null;

            $locationCoordinate = (
                $locationLatitude !== null && $locationLatitude !== '' &&
                $locationLongitude !== null && $locationLongitude !== ''
            )
                ? '(' . $locationLatitude . ', ' . $locationLongitude . ')'
                : '-';

            $locationName = $row->LOCATION_NAME ?? $row->location_name ?? '-';
            $createdAt = $row->CREATED_AT ?? $row->created_at ?? null;
            $pengesahanAt = $row->PENGESAHAN_AT ?? $row->pengesahan_at ?? null;

            $hasBreakdownLocation = (
                $repairCategory === 'BREAKDOWN' &&
                $locationLatitude !== null && $locationLatitude !== '' &&
                $locationLongitude !== null && $locationLongitude !== ''
            );

            $formatDate = function ($date) {
                if (!$date) return '-';

                try {
                    return \Carbon\Carbon::parse($date)->format('d/m/Y H:i');
                } catch (\Exception $e) {
                    return '-';
                }
            };
        @endphp

        <div id="penyelenggaraan-modal-{{ $id }}" class="penyelenggaraan-modal hidden">
            <div class="penyelenggaraan-modal-overlay" onclick="closePenyelenggaraanModal('penyelenggaraan-modal-{{ $id }}')"></div>

            <div class="penyelenggaraan-modal-card">
                <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            Maklumat Penyelenggaraan
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ $noPlat }} / {{ $noArahanKerja }}
                        </p>
                    </div>

                    <button type="button"
                            class="modal-close-btn"
                            onclick="closePenyelenggaraanModal('penyelenggaraan-modal-{{ $id }}')">
                        ×
                    </button>
                </div>

                @php
                    $dRepairMode = strtoupper(trim($row->REPAIR_MODE ?? $row->repair_mode ?? ''));
                    $dPanel = $row->BENGKEL_PANEL_NAMA ?? $row->bengkel_panel_nama ?? null;
                    $dJcRemark = $row->JC_REMARK ?? $row->jc_remark ?? null;
                    $dArahan = $row->NO_ARAHAN_KERJA ?? $row->no_arahan_kerja ?? null;
                    $dRejectRemark = $row->REMARK_REJECT ?? $row->remark_reject ?? null;
                    $dRejectStage = strtoupper(trim($row->REJECTED_STAGE ?? $row->rejected_stage ?? ''));
                    $breakdownResult = strtoupper(trim($row->BREAKDOWN_RESULT ?? $row->breakdown_result ?? ''));
                    $towingValue = strtoupper(trim($row->TOWING ?? $row->towing ?? ''));
                    $breakdownResultLabel = [
                        'PEMBAIKAN_TERUS' => 'Pembaikan Terus',
                        'PUSKEB' => 'PUSKEB',
                        'BENGKEL_PANEL' => 'Bengkel Panel',
                    ][$breakdownResult] ?? null;
                @endphp

                <div class="detail-grid">
                    {{-- MAKLUMAT KENDERAAN --}}
                    <h4 class="detail-section">Maklumat Kenderaan</h4>

                    <div>
                        <span>No Plat</span>
                        <strong>{{ $noPlat }}</strong>
                    </div>

                    <div>
                        <span>Model Kenderaan</span>
                        <strong>{{ $modelKenderaan ?: '-' }}</strong>
                    </div>

                    <div>
                        <span>Jenis Kenderaan</span>
                        <strong>{{ $jenisKenderaan }}</strong>
                    </div>

                    <div>
                        <span>Lokasi Penempatan</span>
                        <strong>{{ $lokasiPenempatan ?: '-' }}</strong>
                    </div>

                    <div>
                        <span>Odometer</span>
                        <strong>{{ $odometer !== null ? number_format((float) $odometer, 0) . ' KM' : '-' }}</strong>
                    </div>

                    {{-- MAKLUMAT KERJA --}}
                    <h4 class="detail-section">Maklumat Kerja</h4>

                    <div>
                        <span>No Arahan Kerja</span>
                        <strong>{{ $dArahan ?: '-' }}</strong>
                    </div>


                    <div>
                        <span>Kategori</span>
                        <strong>{{ str_replace('_', ' ', $kategori) }}</strong>
                    </div>

                    <div>
                        <span>Status</span>
                        <strong>{{ $status }}</strong>
                    </div>

                    @if($dRepairMode)
                        <div>
                            <span>Jenis Pembaikan</span>
                            <strong>{{ $dRepairMode === 'INTERNAL' ? 'Pembaikan Dalaman' : 'Bengkel Panel' . ($dPanel ? ' - ' . $dPanel : '') }}</strong>
                        </div>
                    @endif

                    @if($breakdownResultLabel)
                        <div>
                            <span>Keputusan Breakdown</span>
                            <strong>{{ $breakdownResultLabel }}</strong>
                        </div>
                    @endif

                    @if($towingValue !== '')
                        <div>
                            <span>Towing</span>
                            <strong>{{ $towingValue === 'Y' ? 'Ya' : 'Tidak' }}</strong>
                        </div>
                    @endif

                    {{-- PIHAK TERLIBAT (SIAPA) --}}
                    <h4 class="detail-section">Pihak Terlibat</h4>

                    <div>
                        <span>Nama Pemandu</span>
                        <strong>{{ $driverNoStaff }} - {{ $driverName }}</strong>
                    </div>

                    <div>
                        <span>No Telefon Pemandu</span>
                        <strong>{{ $driverPhoneNum ?: '-' }}</strong>
                    </div>

                    <div>
                        <span>PIC</span>
                        <strong>{{ $picNoStaff }} - {{ $pic }}</strong>
                    </div>

                    <div>
                        <span>JKJG</span>
                        <strong>{{ $jkjgNoStaff }} - {{ $jkjgName }}</strong>
                    </div>

                    <div>
                        <span>Mekanik/Kumpulan Ditugaskan</span>
                        <strong>{{ $assignedDisplay }}</strong>
                    </div>

                    {{-- TARIKH --}}
                    <h4 class="detail-section">Tarikh</h4>

                    <div>
                        <span>Tarikh Daftar</span>
                        <strong>{{ $formatDate($createdAt) }}</strong>
                    </div>

                    <div>
                        <span>Tarikh Pengesahan</span>
                        <strong>{{ $pengesahanAt ? $formatDate($pengesahanAt) : '-' }}</strong>
                    </div>

                    <div>
                        <span>Tarikh Selesai</span>
                        <strong>{{ $completedAt ? $formatDate($completedAt) : '-' }}</strong>
                    </div>

                    {{-- LOKASI --}}
                    <h4 class="detail-section">Lokasi Breakdown</h4>

                    <div>
                        <span>Koordinat Lokasi</span>
                        <strong>{{ $locationCoordinate ?: '-' }}</strong>
                    </div>

                    <div>
                        <span>Nama Lokasi</span>
                        <strong>{{ $locationName ?: '-' }}</strong>
                    </div>

                    @if($hasBreakdownLocation)
                        <div class="detail-full">
                            <span>Peta Lokasi Breakdown</span>

                            <div
                                id="breakdown-map-{{ $id }}"
                                class="breakdown-modal-map"
                                data-lat="{{ $locationLatitude }}"
                                data-lng="{{ $locationLongitude }}"
                                data-title="{{ $locationName ?: 'Lokasi Breakdown' }}">
                            </div>
                        </div>
                    @endif

                    {{-- CATATAN --}}
                    <h4 class="detail-section">Catatan</h4>

                    <div class="detail-full">
                        <span>Catatan / Remark</span>
                        <strong>{{ $remark }}</strong>
                    </div>

                    @if($dJcRemark)
                        <div class="detail-full">
                            <span>Penilaian Job Controller</span>
                            <strong>{{ $dJcRemark }}</strong>
                        </div>
                    @endif

                    @if($dRejectRemark)
                        <div class="detail-full">
                            <span>Sebab Ditolak ({{ $dRejectStage === 'JURUTERA' ? 'Pegawai Jurutera' : 'Penolong Jurutera' }})</span>
                            <strong>{{ $dRejectRemark }}</strong>
                        </div>
                    @endif

                    @if($remarkDone)
                        <div class="detail-full">
                            <span>Catatan Selesai</span>
                            <strong>{{ $remarkDone }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div id="mekanik-modal-{{ $id }}" class="penyelenggaraan-modal hidden">
            <div class="penyelenggaraan-modal-overlay" onclick="closePenyelenggaraanModal('mekanik-modal-{{ $id }}')"></div>

            <div class="penyelenggaraan-modal-card mekanik-modal-card">
                <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            @if($assignmentType === 'GROUP')
                                Ahli Kumpulan Mekanik
                            @else
                                Mekanik Ditugaskan
                            @endif
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ $noPlat }} / {{ $noArahanKerja }}
                        </p>
                    </div>

                    <button type="button"
                            class="modal-close-btn"
                            onclick="closePenyelenggaraanModal('mekanik-modal-{{ $id }}')">
                        ×
                    </button>
                </div>

                @if($assignmentType === 'GROUP')
                    <div class="mb-3">
                        <span class="text-xs font-semibold uppercase text-gray-500">Kumpulan</span>
                        <div class="text-base font-bold text-gray-900">{{ $assignedGroupName ?: '-' }}</div>
                    </div>

                    @if($assignedGroupMembers->isNotEmpty())
                        <div class="mekanik-member-list">
                            @foreach($assignedGroupMembers as $member)
                                @php
                                    $memberName = $member->NAMA ?? $member->nama ?? '-';
                                    $memberNoStaff = $member->NOSTAFF ?? $member->nostaff ?? null;
                                    $memberJabatan = $member->JABATAN ?? $member->jabatan ?? null;
                                    $memberIsLeader = strtoupper(trim($member->IS_LEADER ?? $member->is_leader ?? '')) === 'Y';
                                @endphp

                                <div class="mekanik-member-row">
                                    <div>
                                        <div class="font-semibold text-gray-900">
                                            {{ $memberName }}
                                            @if($memberIsLeader)
                                                <span class="leader-badge">Ketua</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $memberNoStaff }}{{ $memberJabatan ? ' - ' . $memberJabatan : '' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Tiada ahli aktif dalam kumpulan ini.</p>
                    @endif
                @elseif($assignmentType === 'INDIVIDUAL')
                    <div class="mekanik-member-list">
                        <div class="mekanik-member-row">
                            <div>
                                <div class="font-semibold text-gray-900">
                                    {{ $assignedMekanikName ?: '-' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $assignedMekanikNoStaff }}{{ $assignedMekanikJabatan ? ' - ' . $assignedMekanikJabatan : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Belum ditugaskan.</p>
                @endif
            </div>
        </div>
    @endforeach
</div>

{{-- Shared approve / deny form (Penolong Jurutera & Pegawai Jurutera) --}}
<form id="decision-form" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="decision" id="decision-value">
    <input type="hidden" name="remark_reject" id="decision-remark">
</form>

@if(!empty($jcKategori))
    <div id="jc-modal" class="jc-modal hidden">
        <div class="jc-overlay" onclick="closeJcModal()"></div>

        <div class="jc-card">
            <div class="jc-header">
                <div>
                    <h3>Penilaian Job Controller</h3>
                    <p id="jc-job-info"></p>
                </div>
                <button type="button" class="jc-close" onclick="closeJcModal()">&times;</button>
            </div>

            <form id="jc-form" method="POST">
                @csrf

                <div class="jc-problem">
                    <span>Masalah dilaporkan</span>
                    <strong id="jc-problem"></strong>
                </div>

                <div class="jc-field">
                    <label for="jc-remark">Penilaian masalah <em>*</em></label>
                    <textarea id="jc-remark" name="jc_remark" rows="3" placeholder="Nyatakan penilaian anda terhadap masalah kenderaan..."></textarea>
                </div>

                <div class="jc-field">
                    <label>Jenis pembaikan <em>*</em></label>
                    <div class="jc-options">
                        <label class="jc-option">
                            <input type="radio" name="repair_mode" value="INTERNAL">
                            <span class="jc-option-card">
                                <span class="jc-option-title">Pembaikan Dalaman</span>
                                <span class="jc-option-desc">Dibaiki oleh mekanik MBSP</span>
                            </span>
                        </label>
                        <label class="jc-option">
                            <input type="radio" name="repair_mode" value="BENGKEL_PANEL">
                            <span class="jc-option-card">
                                <span class="jc-option-title">Bengkel Panel</span>
                                <span class="jc-option-desc">Dihantar ke bengkel panel</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="jc-field" id="jc-panel-field" style="display:none">
                    <label for="jc-panel-search">Bengkel panel <em>*</em></label>
                    <div class="jc-combo">
                        <input type="text" id="jc-panel-search" placeholder="Cari nama / kod bengkel panel..." autocomplete="off">
                        <input type="hidden" name="bengkel_panel_id" id="jc-panel-id">
                        <div class="jc-combo-list hidden" id="jc-panel-list"></div>
                    </div>
                </div>

                <div class="jc-alat">
                    <button type="button" class="jc-alat-btn" id="jc-alat-toggle">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M12 17V11M12 7H12.01M21 12A9 9 0 1 1 3 12A9 9 0 0 1 21 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Info Alat Ganti
                    </button>

                    <div class="jc-alat-panel hidden" id="jc-alat-panel">
                        <input type="text" id="jc-alat-search" placeholder="Cari alat ganti..." autocomplete="off">
                        <div class="jc-alat-results" id="jc-alat-results"></div>
                    </div>
                </div>

                <div id="jc-error" class="jc-error hidden"></div>

                <div class="jc-footer">
                    <button type="submit" class="jc-btn-submit">Hantar kepada Penolong Jurutera</button>
                </div>
            </form>
        </div>
    </div>
@endif

<style>
    .penyelenggaraan-tabs {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px;
        margin: 0 0 16px;
        background: #f8f9fb;
        border: 1px solid #eef1f4;
        border-radius: 12px;
        width: fit-content;
    }

    .penyelenggaraan-tabs .tab-btn {
        border: none;
        background: transparent;
        color: #667085;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 18px;
        border-radius: 9px;
        cursor: pointer;
        transition: all .15s ease;
    }

    .penyelenggaraan-tabs .tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        margin-left: 6px;
        border-radius: 999px;
        background: #d92d20;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        vertical-align: middle;
    }

    .penyelenggaraan-tabs .tab-btn:hover {
        color: #344054;
    }

    .penyelenggaraan-tabs .tab-btn.active {
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(16, 24, 40, 0.12);
    }

    .penyelenggaraan-tabs .tab-btn[data-status-tab="PENDING"].active {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .penyelenggaraan-tabs .tab-btn[data-status-tab="PENGESAHAN"].active {
        background: #f5f3ff;
        color: #6941c6;
    }

    .penyelenggaraan-tabs .tab-btn[data-status-tab="PROGRESS"].active {
        background: #fffaeb;
        color: #b54708;
    }

    .penyelenggaraan-tabs .tab-btn[data-status-tab="COMPLETED"].active {
        background: #ecfdf3;
        color: #027a48;
    }

    .penyelenggaraan-tabs .tab-btn[data-status-tab="CANCELLED"].active {
        background: #fef3f2;
        color: #b42318;
    }

    .user-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .penyelenggaraan-table {
        min-width: 2030px;
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

    .penyelenggaraan-table th:nth-child(1),
    .penyelenggaraan-table td:nth-child(1) {
        width: 60px !important;
        text-align: center;
    }

    .penyelenggaraan-table th:nth-child(2),
    .penyelenggaraan-table td:nth-child(2) {
        width: 130px !important;
    }

    .penyelenggaraan-table th:nth-child(3),
    .penyelenggaraan-table td:nth-child(3) {
        width: 140px !important;
    }

    .penyelenggaraan-table th:nth-child(4),
    .penyelenggaraan-table td:nth-child(4) {
        width: 220px !important;
    }

    .penyelenggaraan-table th:nth-child(5),
    .penyelenggaraan-table td:nth-child(5) {
        width: 200px !important;
    }

    .penyelenggaraan-table th:nth-child(6),
    .penyelenggaraan-table td:nth-child(6) {
        width: 130px !important;
    }

    .penyelenggaraan-table th:nth-child(7),
    .penyelenggaraan-table td:nth-child(7) {
        width: 180px !important;
    }

    .penyelenggaraan-table th:nth-child(8),
    .penyelenggaraan-table td:nth-child(8) {
        width: 300px !important;
        white-space: normal;
    }

    .penyelenggaraan-table th:nth-child(9),
    .penyelenggaraan-table td:nth-child(9) {
        width: 210px !important;
        white-space: normal;
    }

    .penyelenggaraan-table th:nth-child(10),
    .penyelenggaraan-table td:nth-child(10) {
        width: 150px !important;
        text-align: center;
    }

    .penyelenggaraan-table th:nth-child(11),
    .penyelenggaraan-table td:nth-child(11) {
        width: 170px !important;
        text-align: center;
    }

    .penyelenggaraan-table th:nth-child(12),
    .penyelenggaraan-table td:nth-child(12) {
        width: 190px !important;
        text-align: center;
    }

    .cell-text {
        line-height: 1.45;
        font-weight: 500;
        overflow-wrap: break-word;
        word-break: normal;
    }

    .status-badge,
    .category-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
    }

    .category-badge {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .status-pending {
        background: #fffaeb;
        color: #b54708;
        border: 1px solid #fedf89;
    }

    .status-pengesahan {
        background: #f5f3ff;
        color: #6941c6;
        border: 1px solid #e9d7fe;
    }

    .status-progress {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .status-completed {
        background: #ecfdf3;
        color: #027a48;
        border: 1px solid #abefc6;
    }

    .status-cancelled {
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
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 6px;
    }

    .action-btn-group form {
        margin: 0;
        display: inline-flex;
    }

    .action-btn {
        width: 34px;
        height: 34px;
        border-radius: 9px;
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

    .terima-btn {
        background: #ecfdf3;
        color: #027a48;
        border-color: #abefc6;
    }

    .sahkan-btn {
        background: #ecfdf3;
        color: #027a48;
        border-color: #abefc6;
    }

    .selesai-btn {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .delete-btn {
        background: #fef3f2;
        color: #b42318;
        border-color: #fecdca;
    }

    .penyelenggaraan-modal {
        position: fixed;
        inset: 0;
        z-index: 999999;
    }

    .penyelenggaraan-modal.hidden {
        display: none;
    }

    .penyelenggaraan-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }

    .penyelenggaraan-modal-card {
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
        border: none;
        cursor: pointer;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .detail-grid > div {
        background: #f8fafc;
        border: 1px solid #eef1f4;
        border-radius: 12px;
        padding: 12px 14px;
    }

    .detail-grid > div > span {
        display: block;
        color: #667085;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .detail-grid > div > strong {
        display: block;
        color: #101828;
        font-size: 14px;
        font-weight: 700;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .detail-full {
        grid-column: span 2 / span 2;
    }

    .detail-section {
        grid-column: span 2 / span 2;
        margin: 8px 0 -4px;
        padding-bottom: 6px;
        border-bottom: 1px solid #e4e7ec;
        color: #344054;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .detail-section:first-child {
        margin-top: 0;
    }

    .mekanik-link {
        background: none;
        border: none;
        padding: 0;
        color: #1d4ed8;
        font-weight: 600;
        text-decoration: underline;
        cursor: pointer;
        font: inherit;
        text-align: left;
    }

    .mekanik-link:hover {
        color: #1e40af;
    }

    .mekanik-modal-card {
        width: min(500px, calc(100% - 32px));
    }

    .mekanik-member-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .mekanik-member-row {
        background: #f8fafc;
        border: 1px solid #eef1f4;
        border-radius: 12px;
        padding: 12px 14px;
    }

    .leader-badge {
        display: inline-flex;
        align-items: center;
        margin-left: 6px;
        padding: 2px 8px;
        border-radius: 999px;
        background: #fffaeb;
        color: #b54708;
        border: 1px solid #fedf89;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table {
        width: 100% !important;
    }

    .dataTables_scrollHeadInner table.penyelenggaraan-table,
    .dataTables_scrollBody table.penyelenggaraan-table {
        min-width: 1820px !important;
    }

    .breakdown-modal-map {
        width: 100%;
        height: 360px;
        border-radius: 16px;
        border: 1px solid #d0d5dd;
        overflow: hidden;
        background: #f2f4f7;
        margin-top: 8px;
    }

    .detail-grid > div.breakdown-modal-map,
    .breakdown-modal-map {
        background: #f2f4f7 !important;
        padding: 0 !important;
    }

    .breakdown-modal-map .leaflet-pane,
    .breakdown-modal-map .leaflet-tile,
    .breakdown-modal-map .leaflet-marker-icon,
    .breakdown-modal-map .leaflet-marker-shadow,
    .breakdown-modal-map .leaflet-popup,
    .breakdown-modal-map .leaflet-control {
        padding: 0 !important;
        border: 0 !important;
        background: transparent;
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

    /* Breakdown "Tandakan Selesai" dialog */
    .bd-swal-popup { border-radius: 16px; padding: 24px 24px 20px; }
    .bd-swal-popup .swal2-title { font-size: 20px; font-weight: 700; color: #101828; padding: 0; }
    .bd-modal { text-align: left; }
    .bd-subtitle { margin: 4px 0 16px; font-size: 13px; color: #667085; text-align: center; }
    .bd-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .bd-option { margin: 0; cursor: pointer; }
    .bd-option input, .bd-toggle input { position: absolute; opacity: 0; pointer-events: none; }
    .bd-option-card {
        display: flex; flex-direction: column; align-items: center; gap: 4px;
        padding: 14px 8px; height: 100%; text-align: center;
        border: 1.5px solid #e4e7ec; border-radius: 12px; background: #fff;
        transition: all .15s ease;
    }
    .bd-option:hover .bd-option-card { border-color: #98a2b3; }
    .bd-option input:checked + .bd-option-card {
        border-color: #1d4ed8; background: #eff6ff; box-shadow: 0 0 0 3px rgba(29, 78, 216, .12);
    }
    .bd-option-icon { font-size: 24px; line-height: 1; }
    .bd-option-title { font-size: 13px; font-weight: 700; color: #101828; }
    .bd-option-desc { font-size: 11px; color: #667085; line-height: 1.3; }
    .bd-section { margin-top: 16px; }
    .bd-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #344054; }
    .bd-toggle { display: flex; gap: 10px; }
    .bd-toggle label { flex: 1; margin: 0; cursor: pointer; }
    .bd-toggle span {
        display: block; padding: 10px; text-align: center; font-size: 13px; font-weight: 600;
        color: #344054; border: 1.5px solid #e4e7ec; border-radius: 10px; transition: all .15s ease;
    }
    .bd-toggle label:hover span { border-color: #98a2b3; }
    .bd-toggle input:checked + span { border-color: #1d4ed8; background: #eff6ff; color: #1d4ed8; }
    #bd-remark {
        width: 100%; padding: 10px 12px; font-size: 13px; resize: vertical;
        border: 1.5px solid #e4e7ec; border-radius: 10px; outline: none; box-sizing: border-box;
    }
    #bd-remark:focus { border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29, 78, 216, .12); }
    .bd-confirm-btn { background: #1d4ed8 !important; border-radius: 10px !important; padding: 10px 24px !important; font-weight: 600 !important; }
    @media (max-width: 480px) { .bd-options { grid-template-columns: 1fr; } }

    /* Approve / deny dialog: what the earlier approvers did */
    .ap-popup { border-radius: 16px; }
    .ap-info { text-align: left; max-height: 55vh; overflow-y: auto; padding: 2px; }
    .ap-title { margin: 12px 0 6px; font-size: 11px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #667085; }
    .ap-title:first-child { margin-top: 0; }
    .ap-row { padding: 8px 12px; margin-bottom: 6px; background: #f8f9fb; border: 1px solid #eef1f4; border-radius: 10px; }
    .ap-row span { display: block; font-size: 11px; color: #667085; }
    .ap-row strong { display: block; margin-top: 2px; font-size: 13px; font-weight: 600; color: #101828; white-space: pre-wrap; }
    .ap-note { margin: 12px 0 0; font-size: 13px; color: #667085; text-align: center; }

    /* Approval stage indicator */
    .status-badge-wrap { white-space: normal; text-align: center; line-height: 1.3; border-radius: 12px; }
    .stage-tracker { display: flex; flex-wrap: wrap; justify-content: center; gap: 4px; margin-top: 6px; }
    .stage-step {
        padding: 2px 7px; font-size: 10px; font-weight: 700; white-space: nowrap;
        color: #667085; background: #f2f4f7; border: 1px solid #e4e7ec; border-radius: 999px;
    }
    .stage-step.done { color: #027a48; background: #ecfdf3; border-color: #abefc6; }
    .stage-step.current { color: #b54708; background: #fffaeb; border-color: #fedf89; }
    .stage-left { margin-top: 4px; font-size: 11px; font-weight: 600; color: #667085; }

    /* Job Controller assessment modal */
    .action-btn.jc-btn { background: #fffaeb; color: #b54708; border-color: #fedf89; }
    .jc-modal { position: fixed; inset: 0; z-index: 999999; }
    .jc-modal.hidden, .jc-combo-list.hidden, .jc-alat-panel.hidden, .jc-error.hidden { display: none; }
    .jc-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); }
    .jc-card {
        position: relative; z-index: 1; width: min(620px, calc(100% - 32px)); max-height: calc(100vh - 48px);
        overflow-y: auto; margin: 24px auto; background: #fff; border-radius: 18px; padding: 28px 30px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
    }
    .jc-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
    .jc-header h3 { margin: 0; font-size: 20px; font-weight: 700; color: #101828; }
    .jc-header p { margin: 4px 0 0; font-size: 13px; color: #667085; }
    .jc-close {
        position: absolute; top: 10px; right: 10px; width: 34px; height: 34px; padding: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border: none; border-radius: 8px; background: transparent; font-size: 24px; line-height: 1;
        color: #667085; cursor: pointer; transition: background .15s ease, color .15s ease;
    }
    .jc-close:hover { background: #e81123; color: #fff; }
    .jc-header { padding-right: 36px; }
    .jc-problem { padding: 12px 14px; margin-bottom: 16px; background: #f8f9fb; border: 1px solid #eef1f4; border-radius: 12px; }
    .jc-problem span { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #667085; }
    .jc-problem strong { display: block; margin-top: 4px; font-size: 13px; font-weight: 500; color: #101828; white-space: pre-wrap; }
    .jc-field { margin-bottom: 16px; }
    .jc-field > label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #344054; }
    .jc-field em { color: #d92d20; font-style: normal; }
    .jc-field textarea, .jc-combo input[type="text"], .jc-alat-panel input {
        width: 100%; padding: 10px 12px; font-size: 13px; border: 1.5px solid #e4e7ec; border-radius: 10px;
        outline: none; box-sizing: border-box; background: #fff; color: #101828;
    }
    .jc-field textarea:focus, .jc-combo input[type="text"]:focus, .jc-alat-panel input:focus {
        border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29, 78, 216, .12);
    }
    .jc-options { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .jc-option { margin: 0; cursor: pointer; }
    .jc-option input { position: absolute; opacity: 0; pointer-events: none; }
    .jc-option-card {
        display: flex; flex-direction: column; gap: 2px; padding: 12px 14px; height: 100%;
        border: 1.5px solid #e4e7ec; border-radius: 12px; transition: all .15s ease;
    }
    .jc-option:hover .jc-option-card { border-color: #98a2b3; }
    .jc-option input:checked + .jc-option-card { border-color: #1d4ed8; background: #eff6ff; box-shadow: 0 0 0 3px rgba(29, 78, 216, .12); }
    .jc-option-title { font-size: 13px; font-weight: 700; color: #101828; }
    .jc-option-desc { font-size: 12px; color: #667085; }
    .jc-combo { position: relative; }
    .jc-combo-list {
        position: absolute; left: 0; right: 0; top: calc(100% + 4px); z-index: 5; max-height: 220px; overflow-y: auto;
        background: #fff; border: 1px solid #e4e7ec; border-radius: 10px; box-shadow: 0 8px 24px rgba(16, 24, 40, .12);
    }
    .jc-combo-item { padding: 9px 12px; font-size: 13px; cursor: pointer; border-bottom: 1px solid #f2f4f7; }
    .jc-combo-item:last-child { border-bottom: none; }
    .jc-combo-item:hover { background: #eff6ff; }
    .jc-combo-item small { display: block; color: #667085; font-size: 11px; }
    .jc-combo-empty { padding: 10px 12px; font-size: 13px; color: #98a2b3; }
    .jc-alat { margin-bottom: 16px; }
    .jc-alat-btn {
        display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; font-size: 13px; font-weight: 600;
        color: #1d4ed8; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; cursor: pointer;
    }
    .jc-alat-panel { margin-top: 10px; padding: 12px; border: 1px solid #eef1f4; border-radius: 12px; background: #f8f9fb; }
    .jc-alat-results { margin-top: 10px; max-height: 200px; overflow-y: auto; }
    .jc-alat-row { display: flex; justify-content: space-between; gap: 10px; padding: 7px 4px; font-size: 13px; border-bottom: 1px solid #eef1f4; }
    .jc-alat-row:last-child { border-bottom: none; }
    .jc-stock { flex-shrink: 0; padding: 1px 8px; font-size: 11px; font-weight: 700; border-radius: 999px; background: #ecfdf3; color: #027a48; }
    .jc-stock.out { background: #fef3f2; color: #b42318; }
    .jc-error { margin-bottom: 12px; padding: 10px 12px; font-size: 13px; color: #b42318; background: #fef3f2; border-radius: 10px; }
    .jc-footer { display: flex; justify-content: flex-end; gap: 10px; }
    .jc-btn-submit { padding: 10px 20px; font-size: 13px; font-weight: 600; border: none; border-radius: 10px; cursor: pointer; }
    .jc-btn-submit { background: #1d4ed8; color: #fff; }
    @media (max-width: 480px) { .jc-options { grid-template-columns: 1fr; } }
</style>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const breakdownModalMaps = {};

function openPenyelenggaraanModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    setTimeout(function () {
        initBreakdownModalMap(modal);
    }, 250);
}

function initBreakdownModalMap(modal) {
    const mapEl = modal.querySelector('.breakdown-modal-map');

    if (!mapEl) {
        return;
    }

    const mapId = mapEl.id;
    const lat = parseFloat(mapEl.dataset.lat);
    const lng = parseFloat(mapEl.dataset.lng);
    const title = mapEl.dataset.title || 'Lokasi Breakdown';

    if (Number.isNaN(lat) || Number.isNaN(lng)) {
        console.error('Invalid breakdown coordinate:', mapEl.dataset.lat, mapEl.dataset.lng);
        return;
    }

    if (breakdownModalMaps[mapId]) {
        breakdownModalMaps[mapId].invalidateSize();
        breakdownModalMaps[mapId].setView([lat, lng], 16);
        return;
    }

    const map = L.map(mapId).setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng;

    const popupContent = document.createElement('div');
    const titleEl = document.createElement('strong');
    titleEl.textContent = title;
    const linkEl = document.createElement('a');
    linkEl.href = directionsUrl;
    linkEl.target = '_blank';
    linkEl.rel = 'noopener';
    linkEl.textContent = 'Buka arah di Google Maps';
    popupContent.appendChild(titleEl);
    popupContent.appendChild(document.createElement('br'));
    popupContent.appendChild(linkEl);

    const marker = L.marker([lat, lng], { title: 'Klik untuk buka arah di Google Maps' })
        .addTo(map)
        .bindPopup(popupContent)
        .openPopup();

    marker.on('click', function () {
        window.open(directionsUrl, '_blank', 'noopener');
    });

    breakdownModalMaps[mapId] = map;

    setTimeout(function () {
        map.invalidateSize();
    }, 300);
}

function closePenyelenggaraanModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');

    const openedModal = document.querySelector('.penyelenggaraan-modal:not(.hidden)');

    if (!openedModal) {
        document.body.style.overflow = '';
    }
}

function markBreakdownDone(id) {
    const options = [
        { value: 'PEMBAIKAN_TERUS', title: 'Pembaikan Terus', desc: 'Dibaiki di lokasi', icon: '🔧' },
        { value: 'PUSKEB', title: 'PUSKEB', desc: 'Dihantar ke PUSKEB', icon: '🏢' },
        { value: 'BENGKEL_PANEL', title: 'Bengkel Panel', desc: 'Dihantar ke bengkel panel', icon: '🛠️' }
    ];

    const optionHtml = options.map(function (o) {
        return '<label class="bd-option">' +
            '<input type="radio" name="bd-result" value="' + o.value + '">' +
            '<span class="bd-option-card">' +
            '<span class="bd-option-icon">' + o.icon + '</span>' +
            '<span class="bd-option-title">' + o.title + '</span>' +
            '<span class="bd-option-desc">' + o.desc + '</span>' +
            '</span></label>';
    }).join('');

    Swal.fire({
        title: 'Tandakan Selesai',
        html:
            '<div class="bd-modal">' +
            '<p class="bd-subtitle">Pilih keputusan untuk kerosakan (breakdown) ini</p>' +
            '<div class="bd-options">' + optionHtml + '</div>' +
            '<div id="bd-towing-wrap" class="bd-section" style="display:none">' +
            '<div class="bd-label">Towing diperlukan?</div>' +
            '<div class="bd-toggle">' +
            '<label><input type="radio" name="bd-towing" value="Y"><span>Ya</span></label>' +
            '<label><input type="radio" name="bd-towing" value="N"><span>Tidak</span></label>' +
            '</div></div>' +
            '<div id="bd-remark-wrap" class="bd-section" style="display:none">' +
            '<label class="bd-label" for="bd-remark" id="bd-remark-label">Catatan</label>' +
            '<textarea id="bd-remark" rows="3" placeholder="Masukkan catatan..."></textarea>' +
            '</div></div>',
        showCloseButton: true,
        confirmButtonText: 'Hantar',
        width: 520,
        customClass: {
            popup: 'bd-swal-popup',
            confirmButton: 'bd-confirm-btn'
        },
        didOpen: function (popup) {
            const labels = {
                PEMBAIKAN_TERUS: 'Catatan pembaikan yang telah dilakukan',
                PUSKEB: 'Catatan (cth: sebab dihantar ke PUSKEB)',
                BENGKEL_PANEL: 'Catatan (cth: sebab dihantar ke bengkel panel)'
            };

            popup.querySelectorAll('input[name="bd-result"]').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    popup.querySelector('#bd-towing-wrap').style.display =
                        this.value === 'PEMBAIKAN_TERUS' ? 'none' : 'block';
                    popup.querySelector('#bd-remark-wrap').style.display = 'block';
                    popup.querySelector('#bd-remark-label').textContent = labels[this.value];
                    Swal.resetValidationMessage();
                });
            });
        },
        preConfirm: function () {
            const popup = Swal.getPopup();
            const result = popup.querySelector('input[name="bd-result"]:checked');
            const towing = popup.querySelector('input[name="bd-towing"]:checked');
            const remark = popup.querySelector('#bd-remark').value.trim();

            if (!result) {
                Swal.showValidationMessage('Sila pilih keputusan breakdown.');
                return false;
            }

            if (result.value !== 'PEMBAIKAN_TERUS' && !towing) {
                Swal.showValidationMessage('Sila pilih sama ada towing diperlukan atau tidak.');
                return false;
            }

            if (!remark) {
                Swal.showValidationMessage('Sila masukkan catatan.');
                return false;
            }

            return {
                result: result.value,
                remark: remark,
                towing: result.value === 'PEMBAIKAN_TERUS' ? '' : towing.value
            };
        }
    }).then(function (res) {
        if (res.isConfirmed) {
            document.getElementById('mark-done-result-' + id).value = res.value.result;
            document.getElementById('mark-done-remark-' + id).value = res.value.remark;
            document.getElementById('mark-done-towing-' + id).value = res.value.towing;
            document.getElementById('mark-done-form-' + id).submit();
        }
    });
}

function markPenyelenggaraanDone(id, isBreakdown) {
    if (isBreakdown) {
        markBreakdownDone(id);
        return;
    }

    Swal.fire({
        title: 'Tandakan Selesai',
        input: 'textarea',
        inputLabel: 'Catatan kerja yang telah dilakukan',
        inputPlaceholder: 'Masukkan catatan...',
        showCloseButton: true,
        confirmButtonText: 'Hantar',
        inputValidator: function (value) {
            if (!value || !value.trim()) {
                return 'Sila masukkan catatan.';
            }
        }
    }).then(function (result) {
        if (result.isConfirmed) {
            document.getElementById('mark-done-remark-' + id).value = result.value;
            document.getElementById('mark-done-form-' + id).submit();
        }
    });
}

const jcRoutes = {
    assess: @json(route('penyelenggaraan-jc-assess', '__ID__')),
    pj: @json(route('penyelenggaraan-pj-decision', '__ID__')),
    jurutera: @json(route('penyelenggaraan-jurutera-decision', '__ID__')),
    alatGanti: @json(route('penyelenggaraan-alat-ganti-search'))
};

const jcPanels = @json($bengkelPanels ?? []);

function openJcModal(id, noPlat, noArahanKerja, kategori, problem) {
    const modal = document.getElementById('jc-modal');
    if (!modal) return;

    document.getElementById('jc-form').action = jcRoutes.assess.replace('__ID__', id);
    document.getElementById('jc-job-info').textContent = noPlat + ' • ' + noArahanKerja + ' • ' + kategori.replace(/_/g, ' ');
    document.getElementById('jc-problem').textContent = problem || '-';
    document.getElementById('jc-remark').value = '';
    document.getElementById('jc-panel-search').value = '';
    document.getElementById('jc-panel-id').value = '';
    document.getElementById('jc-panel-field').style.display = 'none';
    document.getElementById('jc-alat-panel').classList.add('hidden');
    document.getElementById('jc-error').classList.add('hidden');
    document.querySelectorAll('#jc-form input[name="repair_mode"]').forEach(function (r) { r.checked = false; });

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeJcModal() {
    const modal = document.getElementById('jc-modal');
    if (!modal) return;

    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function escapeApprovalHtml(text) {
    const div = document.createElement('div');
    div.textContent = text == null ? '' : String(text);
    return div.innerHTML;
}

function approvalInfoHtml(noPlat, arahanKerja, info, isPj) {
    const row = function (label, value) {
        return value
            ? '<div class="ap-row"><span>' + label + '</span><strong>' + escapeApprovalHtml(value) + '</strong></div>'
            : '';
    };

    const mode = info.mode + (info.panel ? ' - ' + info.panel : '');
    const jcBy = (info.jcBy || '-') + (info.jcAt ? ' • ' + info.jcAt : '');
    const pjBy = (info.pjBy || '-') + (info.pjAt ? ' • ' + info.pjAt : '');

    return '<div class="ap-info">' +
        '<div class="ap-title">Tindakan Job Controller</div>' +
        row('Kenderaan', noPlat + (info.kategori ? ' (' + info.kategori + ')' : '')) +
        row('Masalah dilaporkan', info.masalah) +
        row('Penilaian JC', info.jcRemark) +
        row('Jenis pembaikan', mode) +
        row('Dinilai oleh', jcBy) +
        (isPj ? '' :
            '<div class="ap-title">Kelulusan Penolong Jurutera</div>' +
            row('No Arahan Kerja', arahanKerja) +
            row('Diluluskan oleh', pjBy)) +
        '</div>' +
        '<p class="ap-note">' + (isPj
            ? 'Jika diluluskan, permohonan akan dihantar kepada Pegawai Jurutera.'
            : 'Jika diluluskan, no arahan kerja akan dijana dan kerja akan bermula (Dalam Proses).') + '</p>';
}

function decidePenyelenggaraan(id, stage, noPlat, arahanKerja, info) {
    const isPj = stage === 'pj';

    Swal.fire({
        title: isPj ? 'Keputusan Penolong Jurutera' : 'Kelulusan Arahan Kerja',
        html: approvalInfoHtml(noPlat, arahanKerja, info || {}, isPj),
        width: 560,
        customClass: { popup: 'ap-popup' },
        showDenyButton: true,
        showCloseButton: true,
        confirmButtonText: 'Lulus',
        denyButtonText: 'Tolak',
        confirmButtonColor: '#12b76a',
        denyButtonColor: '#d92d20'
    }).then(function (res) {
        const form = document.getElementById('decision-form');
        form.action = jcRoutes[stage].replace('__ID__', id);

        if (res.isConfirmed) {
            document.getElementById('decision-value').value = 'approve';
            document.getElementById('decision-remark').value = '';
            form.submit();
            return;
        }

        if (res.isDenied) {
            Swal.fire({
                title: 'Sebab Penolakan',
                input: 'textarea',
                inputPlaceholder: 'Masukkan sebab penolakan...',
                showCloseButton: true,
                confirmButtonText: 'Tolak Permohonan',
                confirmButtonColor: '#d92d20',
                inputValidator: function (value) {
                    if (!value || !value.trim()) {
                        return 'Sila masukkan sebab penolakan.';
                    }
                }
            }).then(function (r2) {
                if (r2.isConfirmed) {
                    document.getElementById('decision-value').value = 'deny';
                    document.getElementById('decision-remark').value = r2.value;
                    form.submit();
                }
            });
        }
    });
}

(function initJcModal() {
    const form = document.getElementById('jc-form');
    if (!form) return;

    const panelField = document.getElementById('jc-panel-field');
    const panelSearch = document.getElementById('jc-panel-search');
    const panelId = document.getElementById('jc-panel-id');
    const panelList = document.getElementById('jc-panel-list');
    const errorBox = document.getElementById('jc-error');

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function renderPanels(term) {
        const q = (term || '').trim().toLowerCase();
        const matches = jcPanels.filter(function (p) {
            return !q || (p.nama || '').toLowerCase().includes(q) || (p.kod || '').toLowerCase().includes(q);
        }).slice(0, 50);

        panelList.innerHTML = matches.length
            ? matches.map(function (p) {
                return '<div class="jc-combo-item" data-id="' + p.id + '" data-label="' + escapeHtml((p.kod ? p.kod + ' - ' : '') + p.nama) + '">' +
                    escapeHtml(p.nama) + '<small>' + escapeHtml((p.kod || '') + (p.servis ? ' • ' + p.servis : '')) + '</small></div>';
            }).join('')
            : '<div class="jc-combo-empty">Tiada bengkel panel dijumpai.</div>';

        panelList.classList.remove('hidden');
    }

    document.querySelectorAll('#jc-form input[name="repair_mode"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            panelField.style.display = this.value === 'BENGKEL_PANEL' ? 'block' : 'none';

            if (this.value !== 'BENGKEL_PANEL') {
                panelId.value = '';
                panelSearch.value = '';
            }
        });
    });

    panelSearch.addEventListener('focus', function () { renderPanels(this.value); });
    panelSearch.addEventListener('input', function () {
        panelId.value = '';
        renderPanels(this.value);
    });

    panelList.addEventListener('mousedown', function (event) {
        const item = event.target.closest('.jc-combo-item');
        if (!item) return;

        panelId.value = item.dataset.id;
        panelSearch.value = item.dataset.label;
        panelList.classList.add('hidden');
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.jc-combo')) {
            panelList.classList.add('hidden');
        }
    });

    // Alat ganti info panel (search as you type)
    const alatPanel = document.getElementById('jc-alat-panel');
    const alatSearch = document.getElementById('jc-alat-search');
    const alatResults = document.getElementById('jc-alat-results');
    let alatTimer = null;

    function loadAlat() {
        alatResults.innerHTML = '<div class="jc-combo-empty">Memuatkan...</div>';

        fetch(jcRoutes.alatGanti + '?q=' + encodeURIComponent(alatSearch.value.trim()), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (items) {
                alatResults.innerHTML = items.length
                    ? items.map(function (a) {
                        return '<div class="jc-alat-row"><span>' + escapeHtml(a.name) + '</span>' +
                            '<span class="jc-stock' + (a.quantity > 0 ? '' : ' out') + '">Stok: ' + a.quantity + '</span></div>';
                    }).join('')
                    : '<div class="jc-combo-empty">Tiada alat ganti dijumpai.</div>';
            })
            .catch(function () {
                alatResults.innerHTML = '<div class="jc-combo-empty">Gagal memuatkan senarai alat ganti.</div>';
            });
    }

    document.getElementById('jc-alat-toggle').addEventListener('click', function () {
        alatPanel.classList.toggle('hidden');

        if (!alatPanel.classList.contains('hidden')) {
            alatSearch.focus();
            loadAlat();
        }
    });

    alatSearch.addEventListener('input', function () {
        clearTimeout(alatTimer);
        alatTimer = setTimeout(loadAlat, 300);
    });

    form.addEventListener('submit', function (event) {
        const mode = form.querySelector('input[name="repair_mode"]:checked');
        let message = '';

        if (!document.getElementById('jc-remark').value.trim()) {
            message = 'Sila masukkan penilaian masalah.';
        } else if (!mode) {
            message = 'Sila pilih jenis pembaikan.';
        } else if (mode.value === 'BENGKEL_PANEL' && !panelId.value) {
            message = 'Sila pilih bengkel panel daripada senarai.';
        }

        if (message) {
            event.preventDefault();
            errorBox.textContent = message;
            errorBox.classList.remove('hidden');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeJcModal();
    });
})();

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.penyelenggaraan-modal').forEach(function (modal) {
            modal.classList.add('hidden');
        });

        document.body.style.overflow = '';
    }
});

$(document).ready(function () {
    if (!document.querySelector('#penyelenggaraanTable')) return;

    const penyelenggaraanTable = $('#penyelenggaraanTable').DataTable({
        responsive: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        ordering: true,
        searching: true,
        autoWidth: false,
        scrollX: true,
        scrollCollapse: true,

        columns: [
            { width: '60px', orderable: false, searchable: false },
            { width: '130px' },
            { width: '240px' },
            { width: '140px' },
            { width: '220px' },
            { width: '200px' },
            { width: '130px' },
            { width: '180px' },
            { width: '210px' },
            { width: '300px' },
            { width: '150px' },
            { width: '190px', orderable: false }
        ],

        language: {
            search: "Carian:",
            lengthMenu: "Papar _MENU_ rekod",
            zeroRecords: "Tiada data dijumpai",
            emptyTable: "Tiada data penyelenggaraan.",
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
        },

        drawCallback: function () {
            this.api()
                .column(0, { search: 'applied', order: 'applied' })
                .nodes()
                .each(function (cell, i) {
                    cell.innerHTML = i + 1;
                });
        }
    });

    const statusTabPatterns = {
        PENDING: '\\bPENDING\\b',
        PENGESAHAN: '\\bPENGESAHAN\\b',
        PROGRESS: '\\b(IN PROGRESS|IN_PROGRESS|DALAM PROSES)\\b',
        COMPLETED: '\\b(COMPLETED|COMPLETE|SELESAI)\\b',
        CANCELLED: '\\b(CANCELLED|CANCELED|BATAL)\\b'
    };

    function applyStatusTab(tab) {
        penyelenggaraanTable
            .column(10)
            .search(statusTabPatterns[tab] || '', true, false)
            .draw();
    }

    document.querySelectorAll('.penyelenggaraan-tabs .tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.penyelenggaraan-tabs .tab-btn').forEach(function (b) {
                b.classList.remove('active');
            });

            this.classList.add('active');
            applyStatusTab(this.dataset.statusTab);
        });
    });

    applyStatusTab('PENDING');

    setTimeout(function () {
        penyelenggaraanTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        penyelenggaraanTable.columns.adjust();
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
