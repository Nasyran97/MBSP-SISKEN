<?php

namespace App\Helpers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Records every insert / update / delete run through the DB layer into user_log.
 * Hooked once via DB::listen (AppServiceProvider) so controllers need no changes.
 */
class UserLog
{
    private const IGNORED_TABLES = ['user_log', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'migrations'];

    private const ACTIONS = [
        'insert' => 'CREATE',
        'update' => 'EDIT',
        'delete' => 'DELETE',
    ];

    private static bool $writing = false;

    public static function handle(QueryExecuted $event): void
    {
        if (self::$writing || app()->runningInConsole()) {
            return;
        }

        if (!preg_match('/^\s*(insert)\s+into\s+["`]?([\w.]+)|^\s*(update)\s+["`]?([\w.]+)|^\s*(delete)\s+from\s+["`]?([\w.]+)/i', $event->sql, $m)) {
            return;
        }

        $verb  = strtolower($m[1] ?: ($m[3] ?: $m[5]));
        $table = strtolower($m[2] ?: ($m[4] ?: $m[6]));

        if (in_array($table, self::IGNORED_TABLES, true)) {
            return;
        }

        // Never persist credentials / login tokens.
        $sensitive = (bool) preg_match('/token|password/i', $event->sql);

        self::$writing = true;
        try {
            $request = request();

            DB::connection('oracle')->table('user_log')->insert([
                'user_id'    => session('user_id'),
                'nostaff'    => session('nostaff'),
                'nama'       => session('nama'),
                'action'     => self::ACTIONS[$verb],
                'table_name' => $table,
                'query'      => $event->sql,
                'bindings'   => $sensitive ? null : json_encode(self::cleanBindings($event->bindings), JSON_UNESCAPED_UNICODE),
                'method'     => $request->method(),
                'url'        => mb_substr($request->fullUrl(), 0, 500),
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e); // logging must never break the user's action
        } finally {
            self::$writing = false;
        }
    }

    private static function cleanBindings(array $bindings): array
    {
        return array_map(function ($v) {
            if ($v instanceof \DateTimeInterface) {
                return $v->format('Y-m-d H:i:s');
            }
            return is_scalar($v) || $v === null ? $v : '[' . get_debug_type($v) . ']';
        }, $bindings);
    }
}
