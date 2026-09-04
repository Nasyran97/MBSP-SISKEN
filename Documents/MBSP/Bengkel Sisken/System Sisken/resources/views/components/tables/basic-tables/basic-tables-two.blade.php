@props(['users' => collect()])

<div class="table-responsive">
    <table id="usersTable" class="table align-middle mb-0 user-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 60px;">Bil</th>
                <th style="width: 110px;">No Staff</th>
                <th style="width: 220px;">Nama</th>
                <th style="width: 220px;">Jawatan</th>
                <th style="width: 240px;">Jabatan</th>
                <th style="width: 180px;">Email</th>
                <th style="width: 280px;">Modul &amp; Role</th>
                <th class="text-center" style="width: 120px;">Status</th>
                <th class="text-center" style="width: 120px;">Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse($users as $index => $user)
                @php
                    $nostaff = $user->NOSTAFF ?? $user->nostaff ?? '-';
                    $nama = $user->NAMA ?? $user->nama ?? '-';
                    $jawatan = $user->JAWATAN ?? $user->jawatan ?? '-';
                    $jabatan = $user->JABATAN ?? $user->jabatan ?? '-';
                    $email = $user->EMAIL ?? $user->email ?? '-';
                    $status = strtoupper(trim($user->STATUS_DATA ?? $user->status_data ?? ''));

                    $statusClass = match($status) {
                        'ACTIVE' => 'status-active',
                        'PENDING' => 'status-pending',
                        'DELETE', 'DELETED' => 'status-delete',
                        default => 'status-default',
                    };

                    $moduleRoleDisplay = $user->module_role_display ?? [];
                @endphp

                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>

                    <td class="fw-semibold">{{ $nostaff }}</td>

                    <td class="cell-text">
                        {{ ucwords(strtolower($nama)) }}
                    </td>

                    <td class="cell-text">
                        {{ ucwords(strtolower($jawatan)) }}
                    </td>

                    <td class="cell-text">
                        {{ ucwords(strtolower($jabatan)) }}
                    </td>

                    <td>
                        @if($email !== '-')
                            <a href="mailto:{{ $email }}" class="email-link">
                                {{ strtolower($email) }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td>
                        @if(count($moduleRoleDisplay))
                            <div class="module-role-list">
                                @foreach($moduleRoleDisplay as $item)
                                    <div class="module-role-row">
                                        <span class="module-badge">
                                            {{ strtoupper($item['module'] ?? '-') }}
                                        </span>
                                        <span class="role-text">
                                            {{ $item['role'] ?? '-' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <span class="status-badge {{ $statusClass }}">
                            {{ $status ?: '-' }}
                        </span>
                    </td>

                    <td class="text-center">
                        <div class="action-btn-group">
                            <a href="{{ route('user-edit', $user->ID ?? $user->id) }}" class="action-btn edit-btn" title="Edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M13.93 6.93L17.07 10.07M4 20H7.14L16.3 10.84C16.69 10.45 16.69 9.82 16.3 9.43L14.57 7.7C14.18 7.31 13.55 7.31 13.16 7.7L4 16.86V20Z"
                                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                    
                            @if($status === 'ACTIVE')
                                <form action="{{ route('user-delete', $user->ID ?? $user->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="action-btn delete-btn" title="Delete"
                                        onclick="return confirm('Adakah anda pasti mahu padam pengguna ini?')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <path d="M10 11V17M14 11V17M5 7H19M17 7L16.47 19.13C16.43 20.03 15.69 20.75 14.79 20.75H9.21C8.31 20.75 7.57 20.03 7.53 19.13L7 7M9 7V4.75C9 4.34 9.34 4 9.75 4H14.25C14.66 4 15 4.34 15 4.75V7"
                                                stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </form>
                            @elseif($status === 'DELETE')
                                <form action="{{ route('user-activate', $user->ID ?? $user->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <button type="submit" class="action-btn activate-btn" title="Activate"
                                        onclick="return confirm('Aktifkan semula pengguna ini?')">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <path d="M20 12A8 8 0 1 1 17.66 6.34" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M20 4V10H14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        Tiada data pengguna.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
    .user-table {
        width: 100%;
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
    }

    .user-table tbody td {
        padding: 16px 14px;
        border-bottom: 1px solid #eef1f4;
        vertical-align: top;
        color: #101828;
    }

    .user-table tbody tr:hover td {
        background: #fafbfc;
    }

    .cell-text {
        line-height: 1.5;
        font-weight: 500;
    }

    .email-link {
        color: #155eef;
        font-size: 12px;
        text-decoration: none;
    }

    .email-link:hover {
        text-decoration: underline;
    }

    .module-role-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .module-role-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: nowrap;
    }

    .module-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 105px;
        padding: 6px 12px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        white-space: nowrap;
    }

    .role-text {
        font-size: 13px;
        font-weight: 500;
        color: #101828;
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

    .action-btn-group {
        display: flex;
        justify-content: center;
        gap: 8px;
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

    .delete-btn {
        background: #fef3f2;
        color: #b42318;
        border-color: #fecdca;
    }

    .activate-btn {
        background: #ecfdf3;
        color: #027a48;
        border-color: #abefc6;
    }

    /* Card spacing */
    .dataTables_wrapper {
        padding-top: 8px;
    }

    /* Top controls */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 14px;
    }

    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label {
        font-size: 14px;
        font-weight: 500;
        color: #344054;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        height: 42px;
        border: 1px solid #d0d5dd;
        border-radius: 12px;
        padding: 0 14px;
        font-size: 14px;
        background: #fff;
        color: #101828;
        outline: none;
        box-shadow: none;
    }

    .dataTables_wrapper .dataTables_filter input {
        min-width: 260px;
    }

    .dataTables_wrapper .dataTables_length select:focus,
    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #84caff;
        box-shadow: 0 0 0 3px rgba(45, 114, 255, 0.12);
    }

    /* Table */
    #usersTable {
        border-collapse: separate !important;
        border-spacing: 0;
    }

    #usersTable thead th {
        background: #f8fafc !important;
        color: #344054 !important;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 14px 14px !important;
        border-bottom: 1px solid #e4e7ec !important;
        white-space: nowrap;
    }

    #usersTable tbody td {
        padding: 14px 14px !important;
        border-bottom: 1px solid #eef2f6 !important;
        vertical-align: top !important;
        font-size: 14px;
        color: #101828;
    }

    #usersTable tbody tr:hover td {
        background: #fcfcfd !important;
    }
</style>