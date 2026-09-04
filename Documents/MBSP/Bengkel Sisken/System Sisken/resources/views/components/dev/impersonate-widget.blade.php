@php
    $__impersonateOriginal = session('impersonate_original');
    $__isImpersonating = (bool) $__impersonateOriginal;
    $__realNostaff = $__impersonateOriginal['nostaff'] ?? session('nostaff');
    $__canImpersonate = false;

    if ($__realNostaff) {
        $__realUser = \Illuminate\Support\Facades\DB::connection('oracle')
            ->table('users')
            ->where('nostaff', $__realNostaff)
            ->first();

        if ($__realUser) {
            $__moduleRole = json_decode($__realUser->module_role ?? $__realUser->MODULE_ROLE ?? '[]', true);

            if (is_array($__moduleRole)) {
                foreach ($__moduleRole as $__item) {
                    $__mod = strtoupper(trim($__item['module'] ?? ''));
                    $__role = (int) ($__item['role'] ?? 0);

                    if ($__mod === 'SUPERADMIN' && $__role === 1) {
                        $__canImpersonate = true;
                        break;
                    }
                }
            }
        }
    }
@endphp

@if($__canImpersonate)
    <div id="impersonate-widget">
        <button type="button" id="impersonate-toggle-btn" title="Mod Ujian - Tukar Akaun">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" />
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1.08-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1.08 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"
                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            @if($__isImpersonating)
                <span class="impersonate-toggle-dot"></span>
            @endif
        </button>

        <div id="impersonate-panel" class="hidden">
            <div class="impersonate-panel-header">
                <strong>Mod Ujian (Superadmin)</strong>
                <button type="button" id="impersonate-close-btn">&times;</button>
            </div>

            @if($__isImpersonating)
                <p class="impersonate-current">
                    Sedang menyemak sistem sebagai:<br>
                    <strong>{{ session('nostaff') }} - {{ session('nama') }}</strong>
                </p>

                <form method="POST" action="{{ route('impersonate-stop') }}">
                    @csrf
                    <button type="submit" class="impersonate-btn impersonate-btn-danger">
                        Kembali ke Akaun Asal ({{ $__impersonateOriginal['nostaff'] }})
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('impersonate-start') }}">
                    @csrf
                    <label for="impersonate-nostaff">No Staf untuk diuji</label>
                    <input type="text" id="impersonate-nostaff" name="nostaff" placeholder="Contoh: 12345" required autocomplete="off">
                    <button type="submit" class="impersonate-btn impersonate-btn-primary">Tukar Akaun</button>
                </form>
            @endif
        </div>
    </div>

    <style>
        #impersonate-widget {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 999999;
        }

        #impersonate-toggle-btn {
            position: relative;
            width: 52px;
            height: 52px;
            border-radius: 999px;
            background: #101828;
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(16, 24, 40, 0.35);
            transition: transform .15s ease;
        }

        #impersonate-toggle-btn:hover {
            transform: translateY(-2px);
        }

        .impersonate-toggle-dot {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: #f59e0b;
            border: 2px solid #101828;
        }

        #impersonate-panel {
            position: absolute;
            bottom: 64px;
            right: 0;
            width: 280px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 20px 45px rgba(16, 24, 40, 0.25);
            border: 1px solid #eef1f4;
            padding: 16px;
        }

        #impersonate-panel.hidden {
            display: none;
        }

        .impersonate-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 13px;
            color: #101828;
        }

        #impersonate-close-btn {
            border: none;
            background: transparent;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
            color: #667085;
        }

        .impersonate-current {
            font-size: 12.5px;
            color: #667085;
            margin-bottom: 10px;
        }

        .impersonate-current strong {
            color: #101828;
        }

        #impersonate-panel label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #667085;
            margin-bottom: 6px;
        }

        #impersonate-panel input[type="text"] {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #d0d5dd;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .impersonate-btn {
            width: 100%;
            padding: 9px 12px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .impersonate-btn-primary {
            background: #1d4ed8;
            color: #fff;
        }

        .impersonate-btn-danger {
            background: #fef3f2;
            color: #b42318;
            border: 1px solid #fecdca;
        }
    </style>

    <script>
        (function () {
            const toggleBtn = document.getElementById('impersonate-toggle-btn');
            const closeBtn = document.getElementById('impersonate-close-btn');
            const panel = document.getElementById('impersonate-panel');

            toggleBtn?.addEventListener('click', function () {
                panel.classList.toggle('hidden');
            });

            closeBtn?.addEventListener('click', function () {
                panel.classList.add('hidden');
            });

            document.addEventListener('click', function (event) {
                if (!panel || panel.classList.contains('hidden')) return;
                if (panel.contains(event.target) || toggleBtn.contains(event.target)) return;
                panel.classList.add('hidden');
            });
        })();
    </script>

    @if(session('impersonate_success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berjaya',
                        text: @json(session('impersonate_success')),
                        confirmButtonText: 'OK'
                    });
                }
            });
        </script>
    @endif

    @if(session('impersonate_error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat',
                        text: @json(session('impersonate_error')),
                        confirmButtonText: 'OK'
                    });
                }
            });
        </script>
    @endif
@endif
