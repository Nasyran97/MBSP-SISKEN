<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

// Approval inbox for servis harian forms. Used by the Kelulusan page and the sidemenu badge.
class ServisKelulusan
{
    public const JKJG_ROLE = 66;
    public const ASSISTANT_ROLE = 81;  // Penolong Jurutera (approver)
    public const OFFICER_ROLE = 101;   // Pegawai Jurutera (sees everything)

    private static array $memo = [];

    public static function hasRole($user, string $module, int $role): bool
    {
        $moduleRole = json_decode($user->MODULE_ROLE ?? $user->module_role ?? '[]', true);

        if (!is_array($moduleRole)) {
            return false;
        }

        foreach ($moduleRole as $item) {
            if (strtoupper(trim($item['module'] ?? '')) === $module && (int) ($item['role'] ?? 0) === $role) {
                return true;
            }
        }

        return false;
    }

    public static function forUser($user): array
    {
        $userId = $user->ID ?? $user->id;

        return self::$memo[$userId] ??= self::compute($user, $userId);
    }

    public static function pendingCount($user): int
    {
        return count(self::forUser($user)['pending']);
    }

    private static function compute($user, $userId): array
    {
        $isJkjg = self::hasRole($user, 'PENYELENGGARAAN', self::JKJG_ROLE);
        $isAssistant = self::hasRole($user, 'PENYELENGGARAAN', self::ASSISTANT_ROLE);
        $isOfficer = self::hasRole($user, 'PENYELENGGARAAN', self::OFFICER_ROLE);

        $query = DB::connection('oracle')
            ->table('SERVIS_HARIAN as s')
            ->join('VEHICLE as v', 'v.ID', '=', 's.VEHICLE_ID')
            ->whereIn('s.STATUS', ['SUBMITTED', 'COMPLETE']);

        // Pegawai Jurutera sees all data; everyone else only their own.
        if (!$isOfficer) {
            $query->where(function ($q) use ($userId, $isJkjg, $isAssistant) {
                $q->where(function ($p) use ($userId, $isJkjg, $isAssistant) {
                    $p->where('s.STATUS', 'SUBMITTED')->where(function ($r) use ($userId, $isJkjg, $isAssistant) {
                        $r->where(function ($d) use ($userId) {
                            $d->where('s.DRIVER_USER_ID', $userId)->whereNull('s.DRIVER_APPROVED_AT');
                        });

                        if ($isJkjg) {
                            $r->orWhereNull('s.JKJG_APPROVED_AT');
                        }

                        if ($isAssistant) {
                            $r->orWhereNull('s.JURUTERA_APPROVED_AT');
                        }
                    });
                })
                ->orWhere('s.JKJG_USER_ID', $userId)
                ->orWhere('s.DRIVER_APPROVED_BY', $userId)
                ->orWhere('s.JURUTERA_USER_ID', $userId);
            });
        }

        $rows = $query
            ->orderByDesc('s.TARIKH')
            ->orderByDesc('s.ID')
            ->select(
                DB::raw('s.KALENDAR_SERVICE_ID as "KID"'),
                DB::raw("TO_CHAR(s.TARIKH, 'YYYY-MM-DD') as \"TARIKH\""),
                DB::raw('v.NO_PLAT as "PLAT"'),
                DB::raw('s.NO_ARAHAN_KERJA as "NO_ARAHAN_KERJA"'),
                DB::raw('s.STATUS as "STATUS"'),
                DB::raw('s.DRIVER_USER_ID as "DRIVER_USER_ID"'),
                DB::raw('s.JKJG_USER_ID as "JKJG_USER_ID"'),
                DB::raw('s.JKJG_APPROVED_AT as "JKJG_AT"'),
                DB::raw('s.DRIVER_APPROVED_BY as "DRIVER_BY"'),
                DB::raw('s.DRIVER_APPROVED_AT as "DRIVER_AT"'),
                DB::raw('s.JURUTERA_USER_ID as "JURUTERA_USER_ID"'),
                DB::raw('s.JURUTERA_APPROVED_AT as "JURUTERA_AT"')
            )
            ->get()
            ->map(fn ($r) => (object) array_change_key_case((array) $r, CASE_LOWER));

        $pending = [];
        $done = [];

        foreach ($rows as $r) {
            $waiting = [];

            if ($r->status === 'SUBMITTED') {
                if ((string) $r->driver_user_id === (string) $userId && !$r->driver_at) {
                    $waiting[] = 'Pemandu';
                }

                if ($isJkjg && !$r->jkjg_at) {
                    $waiting[] = 'JK / JG';
                }

                if ($isAssistant && !$r->jurutera_at) {
                    $waiting[] = 'Penolong Jurutera';
                }
            }

            $mine = [];

            if ((string) $r->jkjg_user_id === (string) $userId) {
                $mine[] = ['JK / JG', $r->jkjg_at];
            }

            if ((string) $r->driver_by === (string) $userId) {
                $mine[] = ['Pemandu', $r->driver_at];
            }

            if ((string) $r->jurutera_user_id === (string) $userId) {
                $mine[] = ['Penolong Jurutera', $r->jurutera_at];
            }

            $r->menunggu = self::waitingFor($r);

            if ($waiting || ($isOfficer && $r->status === 'SUBMITTED')) {
                $r->peranan = $waiting ? implode(', ', $waiting) : 'Pegawai Jurutera (pemantau)';
                $pending[] = $r;
            }

            if ($mine || ($isOfficer && $r->status === 'COMPLETE')) {
                $r->peranan = $mine ? implode(', ', array_column($mine, 0)) : 'Pegawai Jurutera (pemantau)';
                $r->diluluskan_at = collect($mine ? array_column($mine, 1) : [$r->jkjg_at, $r->driver_at, $r->jurutera_at])
                    ->filter()->sort()->last();
                $done[] = $r;
            }
        }

        return ['pending' => $pending, 'done' => $done];
    }

    // Who is still outstanding on this form (for the officer's overview).
    private static function waitingFor($r): string
    {
        $out = [];

        if (!$r->jkjg_at) {
            $out[] = 'JK / JG';
        }

        if ($r->driver_user_id && !$r->driver_at) {
            $out[] = 'Pemandu';
        }

        if (!$r->jurutera_at) {
            $out[] = 'Penolong Jurutera';
        }

        return $out ? implode(', ', $out) : '-';
    }
}
