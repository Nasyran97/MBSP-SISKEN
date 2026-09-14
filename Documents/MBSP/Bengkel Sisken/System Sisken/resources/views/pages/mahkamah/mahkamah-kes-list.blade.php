@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-3xl font-bold text-gray-900">
                {{ $title ?? 'Senarai Kes Mahkamah' }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ $description ?? 'Senarai rekod kes mahkamah.' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if(($currentStatus ?? 'DALAM PROSES') === 'DALAM PROSES')
                <a href="{{ route('mahkamah-kes-selesai-list') }}"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">
                    Selesai Perbicaraan
                </a>
            @else
                <a href="{{ route('mahkamah-kes-list') }}"
                    class="inline-flex items-center rounded-lg bg-orange-600 px-5 py-3 text-sm font-semibold text-white hover:bg-orange-700">
                    Dalam Proses
                </a>
            @endif

            <a href="{{ route('mahkamah-data-add') }}"
                class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                + Tambah Data
            </a>
        </div>
    </div>

    <x-common.component-card title="Senarai Kes Mahkamah">
        <div class="p-1 overflow-x-auto">
            <table id="mahkamahKesTable" class="user-table">
                <thead>
                    <tr>
                        <th>Bil</th>
                        <th>Tahun</th>
                        <th>Nombor Kes</th>
                        <th>Daerah</th>
                        <th>Insurans</th>
                        <th>Jenis Tuntutan</th>
                        <th>Tarikh Bicara Seterusnya</th>
                        <th>Nama Pekerja MBSP</th>
                        <th>Status Kes</th>
                        <th class="text-center">Fail</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($kesMahkamah as $index => $row)
                        @php
                            $rowArray = (array) $row;

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
                            $tahun = $get('TAHUN') ?? '-';
                            $noKes = $get('NO_KES_MAHKAMAH') ?? '-';
                            $daerah = $get('DAERAH_KEJADIAN') ?? '-';
                            $insurans = $get('SYARIKAT_INSURANS') ?? '-';
                            $jenisTuntutan = $get('JENIS_TUNTUTAN') ?? '-';
                            $namaStaf = $get('NAMA_STAF') ?? '-';
                            $noPekerja = $get('NO_PEKERJA') ?? '-';
                            $statusKes = strtoupper(trim($get('STATUS_KES') ?? 'DALAM PROSES'));
                            $fileUpload = $get('FILE_UPLOAD');

                            $tarikhBicaraRaw = $get('TARIKH_BICARA_SETERUSNYA');
                            $tarikhBicara = !empty($tarikhBicaraRaw)
                                ? \Carbon\Carbon::parse($tarikhBicaraRaw)->format('d/m/Y')
                                : '-';

                            $statusClass = match($statusKes) {
                                'SELESAI PERBICARAAN' => 'status-active',
                                'DALAM PROSES' => 'status-warning',
                                default => 'status-default',
                            };
                        @endphp

                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>

                            <td class="text-center">
                                {{ $tahun }}
                            </td>

                            <td class="cell-text font-semibold">
                                {{ $noKes }}
                            </td>

                            <td class="text-center">
                                {{ $daerah }}
                            </td>

                            <td class="cell-text">
                                {{ $insurans }}
                            </td>

                            <td class="cell-text">
                                {{ $jenisTuntutan }}
                            </td>

                            <td class="text-center">
                                {{ $tarikhBicara }}
                            </td>

                            <td class="cell-text">
                                <div class="font-semibold">{{ $namaStaf }}</div>
                                <div class="text-xs text-gray-500">{{ $noPekerja }}</div>
                            </td>

                            <td class="text-center">
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statusKes }}
                                </span>
                            </td>

                            <td class="text-center">
                                @if(!empty($fileUpload))
                                    <a href="{{ route('mahkamah-kes-file', $id) }}"
                                        target="_blank"
                                        class="file-view-btn">
                                        Lihat
                                    </a>
                                @else
                                    <span class="text-muted">Tiada</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="action-btn-group">
                                    <button type="button"
                                        class="action-btn info-btn"
                                        onclick="openMahkamahInfoModal('mahkamah-info-modal-{{ $id }}')"
                                        title="Maklumat">
                                        ℹ️
                                    </button>

                                    @if($canEdit)
                                        <a href="{{ route('mahkamah-kes-edit', $id) }}"
                                            class="action-btn edit-btn"
                                            title="Edit">
                                            ✏️
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-gray-500">
                                Tiada data kes mahkamah.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
</div>

{{-- MODAL INFO KES MAHKAMAH --}}
@foreach($kesMahkamah as $row)
    @php
        $rowArray = (array) $row;

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
        $noKes = $get('NO_KES_MAHKAMAH') ?? '-';
        $tarikhBicara = $get('TARIKH_BICARA_SETERUSNYA');
        $createdAt = $get('CREATED_AT');
        $updatedAt = $get('UPDATED_AT');
        $fileUpload = $get('FILE_UPLOAD');
        $statusKes = strtoupper(trim($get('STATUS_KES') ?? 'DALAM PROSES'));

        $statusKesClass = match($statusKes) {
            'SELESAI PERBICARAAN' => 'status-active',
            'DALAM PROSES' => 'status-warning',
            default => 'status-default',
        };
    @endphp

    <div id="mahkamah-info-modal-{{ $id }}" class="mahkamah-modal hidden">
        <div class="mahkamah-modal-overlay" onclick="closeMahkamahInfoModal('mahkamah-info-modal-{{ $id }}')"></div>

        <div class="mahkamah-modal-card">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Maklumat Kes Mahkamah
                    </h3>
                    <p class="text-sm text-gray-500">
                        Nombor Kes: {{ $noKes }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('mahkamah-kes-pdf', $id) }}"
                        target="_blank"
                        class="modal-print-btn"
                        title="Cetak Kes">
                        🖨️ Cetak
                    </a>
                
                    <button type="button"
                        class="modal-close-btn"
                        onclick="closeMahkamahInfoModal('mahkamah-info-modal-{{ $id }}')">
                        ×
                    </button>
                </div>
            </div>

            <div class="detail-section">
                <h4>Maklumat Kes</h4>

                <div class="detail-grid">
                    <div>
                        <span>Tahun</span>
                        <strong>{{ $get('TAHUN') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Daerah Kejadian</span>
                        <strong>{{ $get('DAERAH_KEJADIAN') ?? '-' }}</strong>
                    </div>

                    <div class="detail-full">
                        <span>No Kes Mahkamah</span>
                        <strong>{{ $noKes }}</strong>
                    </div>

                    <div>
                        <span>Peguam Plaintif</span>
                        <strong>{{ $get('PEGUAM_PLAINTIF') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Peguam Mewakili MBSP</span>
                        <strong>{{ $get('PEGUAM_MBSP') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Nama Penuntut</span>
                        <strong>{{ $get('NAMA_PENUNTUT') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>No Kenderaan / Jenis</span>
                        <strong>{{ $get('NO_KENDERAAN_JENIS') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Jenis Tuntutan</span>
                        <strong>{{ $get('JENIS_TUNTUTAN') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Tarikh Bicara Seterusnya</span>
                        <strong>
                            {{ !empty($tarikhBicara)
                                ? \Carbon\Carbon::parse($tarikhBicara)->format('d/m/Y')
                                : '-'
                            }}
                        </strong>
                    </div>

                    <div>
                        <span>Status Perbicaraan</span>
                        <strong>{{ $get('STATUS_PERBICARAAN') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Keputusan Kes</span>
                        <strong>{{ $get('KEPUTUSAN_KES') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Status Kes</span>
                        <strong>
                            <span class="status-badge {{ $statusKesClass }}">
                                {{ $statusKes }}
                            </span>
                        </strong>
                    </div>

                    <div class="detail-full">
                        <span>Penyata Tuntutan</span>
                        <strong>{{ $get('PENYATA_TUNTUTAN') ?? '-' }}</strong>
                    </div>

                    <div class="detail-full">
                        <span>Fail Lampiran</span>
                        <strong>
                            @if(!empty($fileUpload))
                                <a href="{{ route('mahkamah-kes-file', $id) }}"
                                    target="_blank"
                                    class="file-view-btn">
                                    Lihat Fail
                                </a>
                            @else
                                Tiada
                            @endif
                        </strong>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h4>Maklumat Kenderaan / Insurans</h4>

                <div class="detail-grid">
                    <div>
                        <span>No Plat Kenderaan</span>
                        <strong>{{ $get('NO_PLAT') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Syarikat Insurans</span>
                        <strong>{{ $get('SYARIKAT_INSURANS') ?? '-' }}</strong>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h4>Maklumat Jabatan / Pegawai</h4>

                <div class="detail-grid">
                    <div>
                        <span>Jabatan Kod</span>
                        <strong>{{ $get('JABATAN_KOD') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Jabatan</span>
                        <strong>{{ $get('JABATAN_NAMA') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>No Pekerja</span>
                        <strong>{{ $get('NO_PEKERJA') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Nama Pekerja MBSP</span>
                        <strong>{{ $get('NAMA_STAF') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Jawatan</span>
                        <strong>{{ $get('JAWATAN_STAF') ?? '-' }}</strong>
                    </div>
                </div>
            </div>

            {{-- Optional audit section --}}
            {{--
            <div class="detail-section">
                <h4>Maklumat Sistem</h4>

                <div class="detail-grid">
                    <div>
                        <span>Created By</span>
                        <strong>{{ $get('CREATED_BY') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Created At</span>
                        <strong>
                            {{ !empty($createdAt)
                                ? \Carbon\Carbon::parse($createdAt)->format('d/m/Y H:i')
                                : '-'
                            }}
                        </strong>
                    </div>

                    <div>
                        <span>Updated By</span>
                        <strong>{{ $get('UPDATED_BY') ?? '-' }}</strong>
                    </div>

                    <div>
                        <span>Updated At</span>
                        <strong>
                            {{ !empty($updatedAt)
                                ? \Carbon\Carbon::parse($updatedAt)->format('d/m/Y H:i')
                                : '-'
                            }}
                        </strong>
                    </div>
                </div>
            </div>
            --}}
        </div>
    </div>
@endforeach

<style>
    .user-table {
        width: 100% !important;
        min-width: 1770px;
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
        width: 90px !important;
        min-width: 90px;
        max-width: 90px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(3),
    .user-table td:nth-child(3) {
        width: 260px !important;
        min-width: 260px;
        max-width: 260px;
        white-space: normal;
    }

    .user-table th:nth-child(4),
    .user-table td:nth-child(4) {
        width: 100px !important;
        min-width: 100px;
        max-width: 100px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(5),
    .user-table td:nth-child(5) {
        width: 220px !important;
        min-width: 220px;
        max-width: 220px;
        white-space: normal;
    }

    .user-table th:nth-child(6),
    .user-table td:nth-child(6) {
        width: 220px !important;
        min-width: 220px;
        max-width: 220px;
        white-space: normal;
    }

    .user-table th:nth-child(7),
    .user-table td:nth-child(7) {
        width: 180px !important;
        min-width: 180px;
        max-width: 180px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(8),
    .user-table td:nth-child(8) {
        width: 240px !important;
        min-width: 240px;
        max-width: 240px;
        white-space: normal;
    }

    .user-table th:nth-child(9),
    .user-table td:nth-child(9) {
        width: 180px !important;
        min-width: 180px;
        max-width: 180px;
        text-align: center;
        white-space: nowrap;
    }

    .user-table th:nth-child(10),
    .user-table td:nth-child(10) {
        width: 100px !important;
        min-width: 100px;
        max-width: 100px;
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
        background: #fff7ed;
        color: #c2410c;
        border: 1px solid #fed7aa;
    }

    .status-expired {
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

    .info-btn {
        background: #f0f9ff;
        color: #026aa2;
        border-color: #bae6fd;
    }

    .file-view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        height: 32px;
        padding: 0 12px;
        border-radius: 10px;
        background: #ecfdf3;
        color: #027a48;
        border: 1px solid #abefc6;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all .2s ease;
    }

    .file-view-btn:hover {
        background: #d1fadf;
        color: #05603a;
        transform: translateY(-1px);
    }

    .text-muted {
        color: #98a2b3;
        font-size: 12px;
        font-weight: 600;
    }

    .edit-btn {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .mahkamah-modal {
        position: fixed;
        inset: 0;
        z-index: 999999;
    }

    .mahkamah-modal.hidden {
        display: none;
    }

    .mahkamah-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
    }

    .mahkamah-modal-card {
        position: relative;
        z-index: 1;
        width: min(1200px, calc(100% - 40px));
        max-height: calc(100vh - 48px);
        overflow-y: auto;
        margin: 24px auto;
        background: #ffffff;
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
        border: none;
        cursor: pointer;
    }

    .modal-edit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        height: 38px;
        padding: 0 14px;
        border-radius: 10px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: all .2s ease;
    }

    .modal-edit-btn:hover {
        background: #dbeafe;
        color: #1e40af;
        transform: translateY(-1px);
    }

    .modal-print-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        height: 38px;
        padding: 0 14px;
        border-radius: 10px;
        background: #ecfdf3;
        color: #027a48;
        border: 1px solid #abefc6;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: all .2s ease;
    }

    .modal-print-btn:hover {
        background: #d1fadf;
        color: #05603a;
        transform: translateY(-1px);
    }

    .detail-section {
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid #eef1f4;
    }

    .detail-section:first-of-type {
        margin-top: 0;
        padding-top: 0;
        border-top: none;
    }

    .detail-section h4 {
        font-size: 15px;
        font-weight: 800;
        color: #1d2939;
        margin-bottom: 14px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .detail-grid div {
        background: #f8fafc;
        border: 1px solid #eef1f4;
        border-radius: 12px;
        padding: 12px 14px;
    }

    .detail-grid span {
        display: block;
        color: #667085;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .detail-grid strong {
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

    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table,
    .dataTables_scrollBody table {
        width: 100% !important;
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

    @media (max-width: 768px) {
        .mahkamah-modal-card {
            width: calc(100% - 24px);
            margin: 12px auto;
            padding: 22px;
        }

        .detail-grid {
            grid-template-columns: 1fr;
        }

        .detail-full {
            grid-column: span 1 / span 1;
        }
    }
</style>

@push('scripts')
<script>
function openMahkamahInfoModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeMahkamahInfoModal(id) {
    const modal = document.getElementById(id);

    if (!modal) {
        console.error('Modal not found:', id);
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.mahkamah-modal:not(.hidden)').forEach(function (modal) {
            modal.classList.add('hidden');
        });

        document.body.classList.remove('overflow-hidden');
    }
});

$(document).ready(function () {
    const mahkamahKesTable = $('#mahkamahKesTable').DataTable({
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
            { width: '90px' },
            { width: '260px' },
            { width: '100px' },
            { width: '220px' },
            { width: '220px' },
            { width: '180px' },
            { width: '240px' },
            { width: '180px' },
            { width: '100px', orderable: false },
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
        mahkamahKesTable.columns.adjust().draw(false);
    }, 300);

    $(window).on('resize', function () {
        mahkamahKesTable.columns.adjust();
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